<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Models\User;

class InstitutionScope implements Scope
{
    protected const INSTITUTION_EXEMPT_ROLES = [
        User::ROLE_SUPER_ADMIN,
    ];

    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        if (in_array($user->role, self::INSTITUTION_EXEMPT_ROLES, true)) {
            return;
        }

        $builder->where(
            $model->getTable().'.institution_id',
            auth()->user()->institution_id
        );
    }
}