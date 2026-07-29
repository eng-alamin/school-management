<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceClassAssign extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    public function classAssign(): BelongsTo
    {
        return $this->belongsTo(AcademicClassAssign::class, 'academic_class_assign_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}