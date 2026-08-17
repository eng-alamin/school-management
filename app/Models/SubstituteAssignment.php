<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;

class SubstituteAssignment extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;

    protected $table = 'class_schedule_substitutes';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    const STATUS_PENDING   = 'pending';
    const STATUS_ASSIGNED  = 'assigned';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    public function classSchedule()
    {
        return $this->belongsTo(AcademicClassSchedule::class, 'class_schedule_id');
    }

    public function academicClass()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function academicSection()
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(AcademicSubject::class, 'subject_id');
    }

    public function originalTeacher()
    {
        return $this->belongsTo(User::class, 'original_teacher_id');
    }

    public function substituteTeacher()
    {
        return $this->belongsTo(User::class, 'substitute_teacher_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}