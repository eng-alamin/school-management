<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\BiometricAttendanceLog;
use App\Models\BiometricDeviceUserMapping;
use App\Models\Employee;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Raw biometric log-কে actual Student/Employee attendance record-এ convert করে।
 *
 * IN/OUT resolve করার নিয়ম:
 * - in_out_mode = 0 (Check-In) হলে check_in fill হবে
 * - in_out_mode = 1 (Check-Out) হলে check_out fill হবে
 * - অন্য কোনো code (2/3 = break out/in ইত্যাদি) আসলে fallback হিসেবে:
 *      check_in ফাঁকা থাকলে সেটাই fill হবে (প্রথম punch),
 *      নাহলে check_out ওভাররাইট হবে (সর্বশেষ punch)
 *
 * একই ব্যক্তির একই দিনে (institution_id, attendable_type, attendable_id, date, type)
 * এর উপর DB-level unique constraint আছে বলে updateOrCreate নিরাপদ।
 */
class ProcessBiometricAttendanceLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    private const MODE_CHECK_IN = 0;
    private const MODE_CHECK_OUT = 1;

    public function __construct(public BiometricAttendanceLog $log)
    {
    }

    public function handle(): void
    {
        if ($this->log->processed) {
            return;
        }

        $mapping = BiometricDeviceUserMapping::where('biometric_device_id', $this->log->biometric_device_id)
            ->where('device_user_id', $this->log->device_user_id)
            ->first();

        if (! $mapping) {
            Log::warning('Biometric log skipped: no user mapping found', [
                'log_id' => $this->log->id,
                'device_user_id' => $this->log->device_user_id,
            ]);
            return;
        }

        DB::transaction(function () use ($mapping) {
            // mapping resolve করে raw log আপডেট
            $this->log->forceFill([
                'attendable_type' => $mapping->attendable_type,
                'attendable_id' => $mapping->attendable_id,
                'processed' => true,
                'processed_at' => now(),
            ])->save();

            $this->recordAttendance($mapping);
        });
    }

    private function recordAttendance(BiometricDeviceUserMapping $mapping): void
    {
        $attendableType = $mapping->attendable_type;
        $attendableId = $mapping->attendable_id;
        $punchTime = $this->log->punch_time;
        $date = $punchTime->toDateString();
        $time = $punchTime->format('H:i:s');

        $isStudent = $attendableType === Student::class;
        $type = $isStudent ? 'student' : 'employee';

        // context (শুধু student-এর জন্য প্রযোজ্য)
        $classId = null;
        $sectionId = null;

        if ($isStudent) {
            $student = Student::find($attendableId);
            $classId = $student?->class_id;
            $sectionId = $student?->section_id;
        }

        // existing row lock করে নিচ্ছি race-condition এড়াতে (একই সময়ে ২টা punch job একসাথে চললে)
        $attendance = Attendance::where('institution_id', $this->log->institution_id)
            ->where('attendable_type', $attendableType)
            ->where('attendable_id', $attendableId)
            ->where('date', $date)
            ->where('type', $type)
            ->lockForUpdate()
            ->first();

        if (! $attendance) {
            Attendance::create([
                'institution_id' => $this->log->institution_id,
                'date' => $date,
                'type' => $type,
                'attendable_type' => $attendableType,
                'attendable_id' => $attendableId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'status' => 'present',
                'check_in' => $this->log->in_out_mode !== self::MODE_CHECK_OUT ? $time : null,
                'check_out' => $this->log->in_out_mode === self::MODE_CHECK_OUT ? $time : null,
                'remarks' => 'Biometric device punch',
            ]);
            return;
        }

        // ইতিমধ্যে row আছে — in/out_mode অনুযায়ী শুধু relevant ফিল্ড আপডেট
        if ($this->log->in_out_mode === self::MODE_CHECK_IN) {
            $attendance->check_in ??= $time; // প্রথম check-in-ই রাখা হবে, বারবার punch করলে ওভাররাইট হবে না
        } elseif ($this->log->in_out_mode === self::MODE_CHECK_OUT) {
            $attendance->check_out = $time; // সর্বশেষ check-out রাখা হবে
        } else {
            // অজানা mode হলে fallback: check_in ফাঁকা থাকলে সেটাই, নাহলে check_out
            if (blank($attendance->check_in)) {
                $attendance->check_in = $time;
            } else {
                $attendance->check_out = $time;
            }
        }

        if ($attendance->status !== 'present') {
            $attendance->status = 'present';
        }

        $attendance->save();
    }
}