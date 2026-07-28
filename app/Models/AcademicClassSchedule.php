<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicClassSchedule extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
    ];

    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function academicSection(): BelongsTo
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }
}