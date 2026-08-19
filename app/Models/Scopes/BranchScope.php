<?php

namespace App\Models\Scopes;

use App\Models\Branch;
use App\Models\User;
use App\Support\Feature;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    protected const BRANCH_EXEMPT_ROLES = [
        User::ROLE_SUPER_ADMIN,
    ];

    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        if (in_array($user->role, self::BRANCH_EXEMPT_ROLES, true)) {
            return;
        }

        $branchId = $user->branch_id
            ?? Branch::resolveMainBranchId($user->institution_id);

        if (empty($branchId)) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where(
            $model->getTable() . '.branch_id',
            $branchId
        );
    }
}