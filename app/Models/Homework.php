<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class Homework extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    protected $table = 'homeworks';

    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(AcademicSubject::class, 'subject_id');
    }
}
