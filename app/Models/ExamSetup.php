<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class ExamSetup extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    protected $casts = [
        'is_published'        => 'boolean',
        'is_result_published' => 'boolean',
    ];

    public function term()
    {
        return $this->belongsTo(ExamTerm::class, 'exam_term_id');
    }

    public function type()
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    public function classAssign()
    {
        return $this->belongsTo(AcademicClassAssign::class, 'academic_class_assign_id');
    }

    public function details()
    {
        return $this->hasMany(ExamSetupDetail::class);
    }

    public function schedules()
    {
        return $this->hasMany(ExamSchedule::class);
    }
}