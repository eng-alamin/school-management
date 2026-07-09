<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

class FeeFine extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    protected $casts = [
        'fine_value' => 'decimal:2',
        'status'     => 'boolean',
    ];

    public function feeSetup()
    {
        return $this->belongsTo(FeeSetup::class);
    }
}