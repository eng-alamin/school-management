<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicSession extends Model
{
    use BelongsToInstitution;
    use SoftDeletes;

    protected $guarded = [];

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'session_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_current', true);
    }
}
