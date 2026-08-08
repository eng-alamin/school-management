<?php

use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

if (! function_exists('currentBranchId')) {
    /**
     * Returns the active branch ID for the current session.
     * Returns null when the logged-in user's role is admin or super_admin
     * and no explicit branch has been picked - meaning they are
     * intentionally viewing "all branches".
     */
    function currentBranchId(): ?int
    {
        if (! Auth::check()) {
            return null;
        }

        // Explicit session override (branch switcher) takes priority
        if (session()->has('current_branch_id')) {
            return session('current_branch_id');
        }

        $user = Auth::user();

        if (in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN], true)) {
            return null;
        }

        return $user->branch_id;
    }
}

if (! function_exists('currentBranch')) {
    function currentBranch(): ?Branch
    {
        $branchId = currentBranchId();

        if (! $branchId) {
            return null;
        }

        return Cache::remember(
            "branch_{$branchId}",
            now()->addHours(6),
            fn () => Branch::find($branchId)
        );
    }
}