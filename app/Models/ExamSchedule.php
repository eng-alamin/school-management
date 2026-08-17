<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamSchedule extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'exam_date'     => 'date',
        'seat_plan'     => 'array',
        'is_published'  => 'boolean',
    ];

    public function examSetup()
    {
        return $this->belongsTo(ExamSetup::class, 'exam_setup_id');
    }

    public function examSetupDetail()
    {
        return $this->belongsTo(ExamSetupDetail::class, 'exam_setup_detail_id');
    }
}