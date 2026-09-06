<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicClassAssign extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;

    protected $guarded = [];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    // pore class() remove korbo
    public function class(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }
    public function academicClass(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    // pore section() remove korbo
    public function section(): BelongsTo
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }
    public function academicSection(): BelongsTo
    {
        return $this->belongsTo(AcademicSection::class, 'section_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(AcademicClassAssignDetail::class, 'academic_class_assign_id');
    }

    public function hasSection(): bool
    {
        return (bool) $this->academicClass?->has_section;
    }
}