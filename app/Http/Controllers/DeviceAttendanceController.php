<?php
// app/Http/Controllers/DeviceAttendanceController.php

namespace App\Http\Controllers;

use App\Jobs\ProcessBiometricAttendanceLog;
use App\Models\BiometricAttendanceLog;
use App\Models\BiometricDevice;
use App\Models\BiometricDeviceCommand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ZKTeco ADMS Protocol Receiver.
 *
 * এই controller CSRF-exempt রাখতে হবে (bootstrap/app.php বা VerifyCsrfToken
 * middleware exclude list-এ 'iclock/*' যোগ করতে হবে), কারণ device
 * raw POST করে, কোনো CSRF token পাঠায় না।
 */
class DeviceAttendanceController extends Controller
{
    /**
     * Handshake: device চালু হওয়ার সময় বা periodic check-in-এ কল করে।
     * GET /iclock/cdata?SN=xxx&options=all&pushver=...
     *
     * POST যখন আসে তখন এটাই attendance data upload (table=ATTLOG)।
     */
    public function cdata(Request $request): Response
    {
        $serial = $request->query('SN');

        if (blank($serial)) {
            return $this->plain('SN missing', 400);
        }

        $device = BiometricDevice::where('device_serial', $serial)
            ->where('is_active', true)
            ->first();

        if (! $device) {
            Log::warning('Biometric device push rejected: unknown/inactive SN', ['serial' => $serial]);
            return $this->plain('unauthorized device', 401);
        }

        $device->forceFill(['last_seen_at' => now()])->save();

        if ($request->isMethod('get')) {
            return $this->handleHandshake($device);
        }

        return $this->handleAttendanceUpload($device, $request);
    }

    /**
     * GET /iclock/getrequest?SN=xxx
     * Device জিজ্ঞেস করে server-এর পাঠানোর মতো কোনো command আছে কিনা।
     * এখানে biometric_device_commands টেবিলের pending command গুলো ফেরত
     * দেওয়া হয় — যেমন App থেকে Device User Mapping করলে "নতুন user push
     * করো" command এখান দিয়েই device পর্যন্ত পৌঁছায়।
     */
    public function getRequest(Request $request): Response
    {
        $serial = $request->query('SN');

        if (blank($serial)) {
            return $this->plain('OK');
        }

        $device = BiometricDevice::where('device_serial', $serial)
            ->where('is_active', true)
            ->first();

        if (! $device) {
            return $this->plain('OK');
        }

        $device->forceFill(['last_seen_at' => now()])->save();

        $pendingCommands = BiometricDeviceCommand::where('biometric_device_id', $device->id)
            ->where('status', 'pending')
            ->orderBy('id')
            ->limit(20) // একবারে অতিরিক্ত command পাঠিয়ে device-কে overload না করা
            ->get();

        if ($pendingCommands->isEmpty()) {
            return $this->plain('OK');
        }

        $commandIds = $pendingCommands->pluck('id')->toArray();

        BiometricDeviceCommand::whereIn('id', $commandIds)->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $body = $pendingCommands->pluck('command_text')->implode("\r\n") . "\r\n";

        Log::info('Biometric device commands sent', [
            'device' => $device->device_serial,
            'command_count' => $pendingCommands->count(),
        ]);

        return $this->plain($body);
    }

    /**
     * POST /iclock/devicecmd
     * Device কোনো command execute করার পর result পাঠায়।
     * Format (ADMS convention): ID=<command_id>&Return=<code>&CMD=<...>
     * Return=0 সাধারণত success বোঝায়, non-zero হলে fail।
     */
    public function deviceCmd(Request $request): Response
    {
        $commandId = $request->input('ID') ?? $request->query('ID');
        $returnCode = $request->input('Return') ?? $request->query('Return');

        if (blank($commandId)) {
            Log::info('Biometric device command result (unparsable)', $request->all());
            return $this->plain('OK');
        }

        $command = BiometricDeviceCommand::where('command_id', $commandId)->first();

        if ($command) {
            $success = ((string) $returnCode === '0');

            $command->forceFill([
                'status' => $success ? 'confirmed' : 'failed',
                'response' => json_encode($request->all()),
                'confirmed_at' => now(),
            ])->save();

            if (! $success) {
                Log::warning('Biometric device command failed', [
                    'command_id' => $commandId,
                    'return_code' => $returnCode,
                ]);
            } else {
                Log::info('Biometric device command confirmed', ['command_id' => $commandId]);
            }
        }

        return $this->plain('OK');
    }

    private function handleHandshake(BiometricDevice $device): Response
    {
        // ADMS handshake response format — device-কে বলে দিচ্ছি data push করতে থাকো
        $body = "GET OPTION FROM: {$device->device_serial}\r\n"
            . "Stamp=9999\r\n"
            . "OpStamp=9999\r\n"
            . "ErrorDelay=60\r\n"
            . "Delay=30\r\n"
            . "TransTimes=00:00;14:05\r\n"
            . "TransInterval=1\r\n"
            . "TransFlag=1111000000\r\n"
            . "Realtime=1\r\n"
            . "Encrypt=0\r\n";

        return $this->plain($body);
    }

    private function handleAttendanceUpload(BiometricDevice $device, Request $request): Response
    {
        $table = $request->query('table');

        if ($table !== 'ATTLOG') {
            // OPERLOG (user enrollment sync) ইত্যাদি আসতে পারে, আপাতত শুধু ACK করছি
            return $this->plain('OK');
        }

        $raw = $request->getContent();

        if (blank($raw)) {
            return $this->plain('OK');
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($raw));
        $insertedCount = 0;

        DB::transaction(function () use ($lines, $device, &$insertedCount) {
            foreach ($lines as $line) {
                $parsed = $this->parseAttlogLine($line);

                if (! $parsed) {
                    continue;
                }

                $log = BiometricAttendanceLog::firstOrCreate(
                    [
                        'biometric_device_id' => $device->id,
                        'device_user_id' => $parsed['device_user_id'],
                        'punch_time' => $parsed['punch_time'],
                    ],
                    [
                        'institution_id' => $device->institution_id,
                        'verify_mode' => $parsed['verify_mode'],
                        'in_out_mode' => $parsed['in_out_mode'],
                        'work_code' => $parsed['work_code'],
                        'raw_payload' => $line,
                        'processed' => false,
                    ]
                );

                if ($log->wasRecentlyCreated) {
                    $insertedCount++;
                    ProcessBiometricAttendanceLog::dispatch($log);
                }
            }
        });

        Log::info('Biometric attendance batch received', [
            'device' => $device->device_serial,
            'lines' => count($lines),
            'inserted' => $insertedCount,
        ]);

        return $this->plain('OK: ' . $insertedCount);
    }

    /**
     * ATTLOG লাইন ফরম্যাট (tab-separated):
     * device_user_id \t punch_time \t status(in/out) \t verify_mode \t work_code \t ...
     * উদাহরণ: 1001	2026-08-05 09:03:12	0	1	0	0	0
     */
    private function parseAttlogLine(string $line): ?array
    {
        $parts = preg_split('/\t/', trim($line));

        if (count($parts) < 4) {
            return null;
        }

        return [
            'device_user_id' => $parts[0],
            'punch_time' => $parts[1],
            'in_out_mode' => (int) $parts[2],
            'verify_mode' => (int) $parts[3],
            'work_code' => isset($parts[4]) ? (int) $parts[4] : null,
        ];
    }

    private function plain(string $body, int $status = 200): Response
    {
        return response($body, $status)->header('Content-Type', 'text/plain');
    }
}