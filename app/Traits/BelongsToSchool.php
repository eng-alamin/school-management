<?php

namespace App\Traits;

use App\Models\Scopes\SchoolScope;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolScope());

        static::creating(function ($model) {
            if (auth()->check()) {
                $model->school_id ??= auth()->user()->school_id;
            }
        });
    }
}