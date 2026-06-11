<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class AcademicTeacherAssign extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

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

    public function teacher()
    {
        return $this->belongsTo(Employee::class, 'teacher_id');
    }
}
