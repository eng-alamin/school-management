<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

class OfficeHead extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}