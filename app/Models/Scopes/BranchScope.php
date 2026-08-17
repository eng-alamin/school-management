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
        User::ROLE_ADMIN,
        User::ROLE_ACCOUNTANT,
        User::ROLE_TEACHER,
        User::ROLE_STUDENT,
        User::ROLE_PARENT,
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

        if (!feature_enabled(Feature::BRANCH_MODULE)) {
            $mainBranchId = self::resolveMainBranchId($user);

            if (empty($mainBranchId)) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where($model->getTable() . '.branch_id', $mainBranchId);

            return;
        }

        if (empty($user->branch_id)) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where(
            $model->getTable() . '.branch_id',
            $user->branch_id
        );
    }

    protected static function resolveMainBranchId(User $user): ?int
    {
        static $cache = [];

        $institutionId = $user->institution_id;

        if (empty($institutionId)) {
            return null;
        }

        if (array_key_exists($institutionId, $cache)) {
            return $cache[$institutionId];
        }

        $mainBranchId = Branch::withoutGlobalScopes()
            ->where('institution_id', $institutionId)
            ->where('is_main', true)
            ->value('id');

        return $cache[$institutionId] = $mainBranchId;
    }
}