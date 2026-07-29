<?php

namespace App\Services;

use App\Models\Student;

/**
 * Single responsibility: generate Student ID / Registration No. based on
 * Institution Settings (prefix, digit length, starting serial), the same
 * way App\Services\AdmissionService does for the Admin admission-approval
 * flow. Extracted here so Teacher's direct "Add Student" flow follows the
 * exact same rule instead of a separate, hardcoded, out-of-sync pattern.
 *
 * lockForUpdate() is used to prevent duplicate IDs when two teachers
 * submit an admission at the same time (race-condition safety).
 */
class StudentIdGeneratorService
{
    public function generateStudentId(int $institutionId, string $year): string
    {
        $inst = institution();

        $digit     = (int) ($inst?->student_id_digit_length ?? 6);
        $startFrom = (int) ($inst?->student_id_start_from ?? 1);

        $prefix = ($inst?->enable_student_id_prefix && $inst?->student_id_code_prefix)
            ? $inst->student_id_code_prefix
            : 'SCH' . str_pad((string) $institutionId, 2, '0', STR_PAD_LEFT);

        $lastStudent = Student::where('institution_id', $institutionId)
            ->whereNotNull('student_id')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $serial = $lastStudent
            ? ((int) substr($lastStudent->student_id, -$digit)) + 1
            : $startFrom;

        return $prefix . $year . str_pad((string) $serial, $digit, '0', STR_PAD_LEFT);
    }

    public function generateRegisterNo(int $institutionId, string $year): string
    {
        $inst = institution();

        $digit     = (int) ($inst?->registration_digit_length ?? 6);
        $startFrom = (int) ($inst?->registration_start_from ?? 1);

        $prefix = ($inst?->enable_registration_prefix && $inst?->registration_code_prefix)
            ? $inst->registration_code_prefix
            : 'RG' . str_pad((string) $institutionId, 2, '0', STR_PAD_LEFT);

        $lastStudent = Student::where('institution_id', $institutionId)
            ->whereNotNull('register_no')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $serial = $lastStudent
            ? ((int) substr($lastStudent->register_no, -$digit)) + 1
            : $startFrom;

        return $prefix . $year . str_pad((string) $serial, $digit, '0', STR_PAD_LEFT);
    }
}