<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveApplication extends Model
{
    use BelongsToSchool;
    use SoftDeletes;
 
    protected $guarded = [];
 
    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'approved_at' => 'datetime',
    ];
 
    // আবেদনকারী — polymorphic (Teacher, Student, User, etc.)
    public function applicable()
    {
        return $this->morphTo();
    }
 
    // ছুটির ধরন
    public function leaveCategory()
    {
        return $this->belongsTo(LeaveCategory::class, 'leave_category_id');
    }
 
    // যিনি অনুমোদন দিয়েছেন
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}