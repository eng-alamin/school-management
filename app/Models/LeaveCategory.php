<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveCategory extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'days'            => 'integer',
        'is_paid'         => 'boolean',
        'allow_half_day'  => 'boolean',
        'status'          => 'boolean',
    ];

    /**
     * একটি ক্যাটাগরির অধীনে থাকা সব Leave Application।
     */
    public function applications()
    {
        return $this->hasMany(LeaveApplication::class, 'leave_category_id');
    }
}