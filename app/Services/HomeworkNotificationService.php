<?php

namespace App\Services;

use App\Models\Homework;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

/**
 * Handles notifying students & guardians when a Homework becomes published.
 * Extracted from HomeworkAddComponent so both the Livewire "Add" flow and the
 * scheduled "Publish Later" command can reuse the exact same logic (DRY).
 */
class HomeworkNotificationService
{
    public static function notifyStudentsAndGuardians(Homework $homework): void
    {
        try {
            // class_id / section_id "students" টেবিলে থাকে, "users" টেবিলে না —
            // তাই Student model থেকে query শুরু করে, তারপর linked User account বের করতে হবে।
            $studentsQuery = Student::with(['user', 'guardians.user'])
                ->where('institution_id', $homework->institution_id)
                ->where('class_id', $homework->class_id);

            if ($homework->section_id) {
                $studentsQuery->where('section_id', $homework->section_id);
            }

            $students = $studentsQuery->get();

            if ($students->isEmpty()) {
                return;
            }

            $subjectName = optional($homework->subject)->name ?? '';
            $dueDate     = $homework->submission_date instanceof \Carbon\Carbon
                ? $homework->submission_date->format('d M Y')
                : \Carbon\Carbon::parse($homework->submission_date)->format('d M Y');

            $title   = 'New Homework: ' . $homework->title;
            $message = ($subjectName ? "{$subjectName} বিষয়ে " : '')
                . "নতুন Homework দেওয়া হয়েছে। জমা দেওয়ার শেষ তারিখ: {$dueDate}।";

            $data = [
                'icon' => 'assignment',
                'url'  => '#',
                // 'url'  => route('admin.homework.index'),
            ];

            // ── প্রতিটা student-এর নিজস্ব login User account (থাকলে) ──
            $studentUsers = collect();

            foreach ($students as $student) {
                $studentUser = $student->user;

                if ($studentUser instanceof User && $studentUser->is_active) {
                    $studentUsers->push($studentUser);
                } else {
                    Log::warning('Homework notification skipped: student has no active linked User account.', [
                        'homework_id' => $homework->id,
                        'student_id'  => $student->id,
                    ]);
                }
            }

            $studentUsers = $studentUsers->unique('id');

            if ($studentUsers->isNotEmpty()) {
                NotificationService::sendToMany($studentUsers, 'homework', $title, $message, $data);
            }

            // ── প্রতিটা student-এর guardian(s)-কে notify করা ──
            $guardianUsers = collect();

            foreach ($students as $student) {
                foreach ($student->guardians as $guardian) {
                    $guardianUser = $guardian->user;

                    if ($guardianUser instanceof User) {
                        $guardianUsers->push($guardianUser);
                    } else {
                        Log::warning('Homework notification skipped: guardian has no linked User account.', [
                            'homework_id' => $homework->id,
                            'student_id'  => $student->id,
                            'guardian_id' => $guardian->id,
                        ]);
                    }
                }
            }

            $guardianUsers = $guardianUsers->unique('id');

            if ($guardianUsers->isNotEmpty()) {
                NotificationService::sendToMany($guardianUsers, 'homework', $title, $message, $data);
            }
        } catch (\Throwable $e) {
            // Notification ব্যর্থ হলেও homework save/publish সফল থাকবে — শুধু log করে রাখি।
            Log::warning('Homework notification failed.', [
                'homework_id' => $homework->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}