<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Models\User;

class BranchScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) {
            return;
        }

        // Admin Bypass - institution's top-level role sees all branches
        if (auth()->user()->role === User::ROLE_ADMIN) {
            return;
        }

        // Super Admin Bypass - same as InstitutionScope, oversees everything
        if (auth()->user()->role === User::ROLE_SUPER_ADMIN) {
            return;
        }

        $branchId = auth()->user()->branch_id;

        if ($branchId === null) {
            return;
        }

        $builder->where(
            $model->getTable() . '.branch_id',
            $branchId
        );
    }
}