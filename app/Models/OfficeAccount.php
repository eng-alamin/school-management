<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeAccount extends Model
{
    use SoftDeletes, BelongsToInstitution;

    protected $guarded = [];

    protected $casts = [
        'opening_balance' => 'decimal:2',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
