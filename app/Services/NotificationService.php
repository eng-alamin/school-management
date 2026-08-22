<?php

namespace App\Services;

use App\Models\Branch;
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
        // BelongsToInstitution / BelongsToBranch trait creating hook এ institution_id, branch_id auto set করবে
        return $user->notifications()->create([
            'type'     => $type,
            'title'    => $title,
            'message'  => $message,
            'data'     => $data,
            'priority' => $priority,
        ]);
    }

    // ─── একটা role এর সবাইকে পাঠান (branch-aware) ──────────────────────────

    public static function sendToRole(
        int     $institutionId,
        string  $role,
        string  $type,
        string  $title,
        string  $message,
        array   $data     = [],
        string  $priority = 'normal',
        ?int    $branchId = null
    ): int {
        $query = User::where('institution_id', $institutionId)
            ->where('role', $role);

        self::applyBranchFilter($query, $institutionId, $branchId);

        return self::sendToMany($query->get(), $type, $title, $message, $data, $priority);
    }

    // ─── একাধিক role কে পাঠান (branch-aware) ────────────────────────────────

    public static function sendToRoles(
        int    $institutionId,
        array  $roles,
        string $type,
        string $title,
        string $message,
        array  $data     = [],
        string $priority = 'normal',
        ?int   $branchId = null
    ): int {
        $query = User::where('institution_id', $institutionId)
            ->whereIn('role', $roles);

        self::applyBranchFilter($query, $institutionId, $branchId);

        return self::sendToMany($query->get(), $type, $title, $message, $data, $priority);
    }

    // ─── institution এর সবাইকে পাঠান (branch-aware) ─────────────────────────

    public static function sendToAll(
        int    $institutionId,
        string $type,
        string $title,
        string $message,
        array  $data     = [],
        string $priority = 'normal',
        ?int   $branchId = null
    ): int {
        $query = User::where('institution_id', $institutionId);

        self::applyBranchFilter($query, $institutionId, $branchId);

        return self::sendToMany($query->get(), $type, $title, $message, $data, $priority);
    }

    // ─── নির্দিষ্ট একটা Branch এর সবাইকে পাঠান (shortcut) ────────────────────

    public static function sendToBranch(
        int    $institutionId,
        int    $branchId,
        string $type,
        string $title,
        string $message,
        array  $data     = [],
        string $priority = 'normal'
    ): int {
        return self::sendToAll($institutionId, $type, $title, $message, $data, $priority, $branchId);
    }

    // ─── Branch filter helper ────────────────────────────────────────────────
    // $branchId দেওয়া থাকলে শুধু সেই branch (null branch_id থাকা user-দের Main
    // Branch হিসেবে ধরা হয়) — না দিলে institution-এর সব branch।

    protected static function applyBranchFilter($query, int $institutionId, ?int $branchId): void
    {
        if ($branchId === null) {
            return; // সব branch — filter দরকার নেই
        }

        $mainBranchId = Branch::resolveMainBranchId($institutionId);

        $query->where(function ($q) use ($branchId, $mainBranchId) {
            $q->where('branch_id', $branchId);

            // branch_id null মানে Main Branch হিসেবে fallback — শুধু তখনই include
            // করব যদি target branch টাই Main Branch হয়
            if ($branchId === $mainBranchId) {
                $q->orWhereNull('branch_id');
            }
        });
    }

    // ─── Bulk insert — insert() Eloquent bypass করে তাই institution_id/branch_id manually ──

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
            'institution_id'  => $user->institution_id, // insert() এ hook কাজ করে না, manually দিতে হবে
            'branch_id'       => $user->branch_id, // insert() এ hook কাজ করে না, manually দিতে হবে
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

    // newAdmission এখন branch-aware — student যে branch এ ভর্তি হয়েছে শুধু সেই
    // branch এর admin-দের পাঠানো হয় (branchId না দিলে সব branch এর admin)
    public static function newAdmission(int $institutionId, string $studentName, ?int $branchId = null): int
    {
        return self::sendToRole(
            $institutionId,
            'admin',
            'admission',
            'New Admission',
            "{$studentName} নতুন ভর্তি হয়েছে।",
            ['icon' => 'person_add', 'url' => route('admin.students.index')],
            'normal',
            $branchId
        );
    }

    public static function announcement(
        int    $institutionId,
        array  $roles,
        string $title,
        string $message,
        ?int   $branchId = null
    ): int {
        return self::sendToRoles(
            $institutionId,
            $roles,
            'announcement',
            $title,
            $message,
            ['icon' => 'campaign'],
            'normal',
            $branchId
        );
    }
}