<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Models\User;

class InstitutionScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check()) {
            return;
        }

        // Super Admin Bypass
        if (auth()->user()->role === User::ROLE_SUPER_ADMIN) {
            return;
        }

        $builder->where(
            $model->getTable().'.institution_id',
            auth()->user()->institution_id
        );
    }
}