<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class StudentPromotion extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromSession()
    {
        return $this->belongsTo(AcademicSession::class, 'from_session_id');
    }

    public function toSession()
    {
        return $this->belongsTo(AcademicSession::class, 'to_session_id');
    }

    public function fromClass()
    {
        return $this->belongsTo(AcademicClass::class, 'from_class_id');
    }

    public function toClass()
    {
        return $this->belongsTo(AcademicClass::class, 'to_class_id');
    }

    public function fromSection()
    {
        return $this->belongsTo(AcademicSection::class, 'from_section_id');
    }

    public function toSection()
    {
        return $this->belongsTo(AcademicSection::class, 'to_section_id');
    }

    public function promotedBy()
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}
