<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    // ─── একজন user কে পাঠান ──────────────────────────────────────────────────

    public static function send(
        User   $user,
        string $type,
        string $title,
        string $message,
        array  $data     = [],
        string $priority = 'normal'
    ): Notification {
        // BelongsToSchool trait creating hook এ school_id auto set করবে
        return $user->notifications()->create([
            'type'     => $type,
            'title'    => $title,
            'message'  => $message,
            'data'     => $data,
            'priority' => $priority,
        ]);
    }

    // ─── একটা role এর সবাইকে পাঠান ──────────────────────────────────────────

    public static function sendToRole(
        int    $schoolId,
        string $role,
        string $type,
        string $title,
        string $message,
        array  $data     = [],
        string $priority = 'normal'
    ): int {
        $users = User::where('school_id', $schoolId)
            ->where('role', $role)
            ->get();

        return self::sendToMany($users, $type, $title, $message, $data, $priority);
    }

    // ─── একাধিক role কে পাঠান ────────────────────────────────────────────────

    public static function sendToRoles(
        int    $schoolId,
        array  $roles,
        string $type,
        string $title,
        string $message,
        array  $data     = [],
        string $priority = 'normal'
    ): int {
        $users = User::where('school_id', $schoolId)
            ->whereIn('role', $roles)
            ->get();

        return self::sendToMany($users, $type, $title, $message, $data, $priority);
    }

    // ─── school এর সবাইকে পাঠান ──────────────────────────────────────────────

    public static function sendToAll(
        int    $schoolId,
        string $type,
        string $title,
        string $message,
        array  $data     = [],
        string $priority = 'normal'
    ): int {
        $users = User::where('school_id', $schoolId)->get();

        return self::sendToMany($users, $type, $title, $message, $data, $priority);
    }

    // ─── Bulk insert — insert() Eloquent bypass করে তাই school_id manually ──

    public static function sendToMany(
        Collection $users,
        string     $type,
        string     $title,
        string     $message,
        array      $data     = [],
        string     $priority = 'normal'
    ): int {
        if ($users->isEmpty()) {
            return 0;
        }

        $now     = now();
        $inserts = $users->map(fn(User $user) => [
            'school_id'       => $user->school_id, // insert() এ hook কাজ করে না, manually দিতে হবে
            'notifiable_id'   => $user->id,
            'notifiable_type' => User::class,
            'type'            => $type,
            'title'           => $title,
            'message'         => $message,
            'data'            => json_encode($data),
            'priority'        => $priority,
            'read_at'         => null,
            'created_at'      => $now,
            'updated_at'      => $now,
        ])->toArray();

        Notification::insert($inserts);

        return count($inserts);
    }

    // ─── Shortcut Methods ─────────────────────────────────────────────────────

    public static function feeOverdue(User $user, string $month, float $amount): Notification
    {
        return self::send(
            $user,
            'fee_due',
            'Fee Overdue',
            "{$month} মাসের ফি বাকি আছে। পরিমাণ: ৳" . number_format($amount, 2),
            ['icon' => 'payments', 'url' => route('admin.fees.index')],
            'high'
        );
    }

    public static function feePaid(User $user, float $amount): Notification
    {
        return self::send(
            $user,
            'fee_paid',
            'Payment Received',
            '৳' . number_format($amount, 2) . ' সফলভাবে পেমেন্ট হয়েছে।',
            ['icon' => 'paid', 'url' => route('admin.fees.index')]
        );
    }

    public static function attendanceAbsent(User $user, string $date): Notification
    {
        return self::send(
            $user,
            'attendance',
            'Absent Alert',
            "{$date} তারিখে অনুপস্থিত চিহ্নিত হয়েছে।",
            ['icon' => 'event_busy'],
            'high'
        );
    }

    public static function examResult(User $user, string $exam, string $grade): Notification
    {
        return self::send(
            $user,
            'exam_result',
            'Result Published',
            "{$exam} পরীক্ষার ফলাফল প্রকাশিত হয়েছে। গ্রেড: {$grade}",
            ['icon' => 'grade', 'url' => route('admin.results.index')]
        );
    }

    public static function newAdmission(int $schoolId, string $studentName): int
    {
        return self::sendToRole(
            $schoolId,
            'admin',
            'admission',
            'New Admission',
            "{$studentName} নতুন ভর্তি হয়েছে।",
            ['icon' => 'person_add', 'url' => route('admin.students.index')]
        );
    }

    public static function announcement(
        int    $schoolId,
        array  $roles,
        string $title,
        string $message
    ): int {
        return self::sendToRoles(
            $schoolId,
            $roles,
            'announcement',
            $title,
            $message,
            ['icon' => 'campaign']
        );
    }
}
