<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class InstitutionScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check()) {
            return;
        }

        // Super Admin bypass
        if (auth()->user()->role === 'super_admin') {
            return;
        }

        $builder->where(
            $model->getTable().'.institution_id',
            auth()->user()->institution_id
        );

        // if (auth()->check()) {
        //     $builder->where($model->getTable() . '.institution_id', auth()->user()->institution_id);
        // }
    }
}