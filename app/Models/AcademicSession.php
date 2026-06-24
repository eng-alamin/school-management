<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class AcademicSession extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'session_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
