<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeFine extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

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