<?php

namespace App\Traits;

use App\Models\Scopes\InstitutionScope;

trait BelongsToInstitution
{
    protected static function bootBelongsToInstitution(): void
    {
        static::addGlobalScope(new InstitutionScope());

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->institution_id ??= auth()->user()->institution_id;
            }
        });
    }
}