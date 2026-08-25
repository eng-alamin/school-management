<?php

namespace App\Services;

use App\Models\BiometricDevice;
use App\Models\BiometricDeviceCommand;

class BiometricCommandService
{
    public static function queueUserUpsert(
        BiometricDevice $device,
        string $devicePin,
        string $name,
        ?string $cardNumber = null
    ): BiometricDeviceCommand {
        $safeName = self::sanitize($name, 24); // ZKTeco সাধারণত ২৪ ক্যারেক্টার পর্যন্ত নাম সাপোর্ট করে
        $safeCard = $cardNumber !== null ? self::sanitizeCardNumber($cardNumber) : '';
        $commandId = BiometricDeviceCommand::generateCommandId();

        $tab = "\t"; // ডাবল-কোটেড, real TAB character

        $commandText = "C:{$commandId}:DATA UPDATE USERINFO PIN={$devicePin}{$tab}Name={$safeName}{$tab}Pri=0{$tab}Passwd={$tab}Card={$safeCard}{$tab}Grp=1{$tab}TZ=0000000000000000{$tab}Verify=0";

        return BiometricDeviceCommand::create([
            'institution_id' => $device->institution_id,
            'branch_id' => $device->branch_id,
            'biometric_device_id' => $device->id,
            'card_number' => $cardNumber,
            'command_id' => $commandId,
            'command_text' => $commandText,
            'status' => 'pending',
        ]);
    }

    public static function queueUserDelete(BiometricDevice $device, string $devicePin): array
    {
        $commands = [];

        $variants = [
            "DATA DELETE USERINFO PIN=%s",
            "DATA DELETE FP PIN=%s",
            "DATA DELETE USER PIN=%s",
        ];

        foreach ($variants as $template) {
            $commandId = BiometricDeviceCommand::generateCommandId();
            $commandText = sprintf("C:%s:{$template}", $commandId, $devicePin);

            $commands[] = BiometricDeviceCommand::create([
                'institution_id' => $device->institution_id,
                'branch_id' => $device->branch_id,
                'biometric_device_id' => $device->id,
                'command_id' => $commandId,
                'command_text' => $commandText,
                'status' => 'pending',
            ]);
        }

        return $commands;
    }

    private static function sanitize(string $value, int $maxLength): string
    {
        $clean = str_replace(["\t", "\r", "\n"], ' ', $value);
        return mb_substr(trim($clean), 0, $maxLength);
    }

    private static function sanitizeCardNumber(string $value): string
    {
        $digitsOnly = preg_replace('/\D/', '', $value) ?? '';

        return mb_substr($digitsOnly, 0, 10);
    }
}