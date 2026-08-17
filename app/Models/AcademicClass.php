<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AcademicClass extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'has_section' => 'boolean',
    ];

    /**
     * Sections that are valid/allowed for this class (static mapping).
     */
    public function sections(): BelongsToMany
    {
        return $this->belongsToMany(
            AcademicSection::class,
            'academic_class_sections',
            'class_id',
            'section_id'
        )->withPivot('institution_id');
    }

    /**
     * Session-wise class + section assignments (actual usage records).
     */
    public function classAssigns(): HasMany
    {
        return $this->hasMany(AcademicClassAssign::class, 'class_id');
    }
}