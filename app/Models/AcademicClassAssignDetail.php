<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicClassAssignDetail extends Model
{
    use BelongsToInstitution;
    
    protected $guarded = [];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(AcademicSubject::class, 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classAssign(): BelongsTo
        {
            return $this->belongsTo(AcademicClassAssign::class, 'academic_class_assign_id');
        }
}
