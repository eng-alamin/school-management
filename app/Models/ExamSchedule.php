<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class ExamSchedule extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    public function getDetailsAttribute()
    {
        return $this->data ?? [];
    }

    public function exam()
    {
        return $this->belongsTo(ExamSetup::class, 'exam_id');
    }

    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }
}
