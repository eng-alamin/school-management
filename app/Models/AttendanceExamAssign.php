<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceExamAssign extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}