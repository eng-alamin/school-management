<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class AcademicClassAssign extends Model
{
    use BelongsToInstitution;
    
    protected $guarded = [];

    protected $casts = [
        'subjects' => 'array',
    ];

    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function sections()
    {
        return $this->hasMany(AcademicSection::class, 'class_id');
    }

    public function teacherAssign()
    {
        return $this->hasOne(AcademicTeacherAssign::class, 'class_id', 'class_id')->where('section_id', $this->section_id);
    }
}
