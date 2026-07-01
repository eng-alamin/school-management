<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class ExamSetupDetail extends Model
{
    // use BelongsToInstitution;

    protected $guarded = [];

    protected $casts = [
        'full_mark'      => 'float',
        'pass_mark'      => 'float',
        'written_mark'   => 'float',
        'mcq_mark'       => 'float',
        'practical_mark' => 'float',
    ];

    public function classAssignDetail()
    {
        return $this->belongsTo(AcademicClassAssignDetail::class, 'academic_class_assign_detail_id');
    }

    public function schedule()
    {
        return $this->hasOne(ExamSchedule::class);
    }

    public function entries()
    {
        return $this->hasMany(ExamEntry::class, 'exam_setup_detail_id');
    }

}