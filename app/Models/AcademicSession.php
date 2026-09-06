<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicSession extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;
    

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $session) {
            if (! $session->is_current) {
                return;
            }
 
            static::where('institution_id', $session->institution_id)
                ->where('branch_id', $session->branch_id)
                ->when($session->exists, fn ($q) => $q->whereKeyNot($session->getKey()))
                ->where('is_current', true)
                ->update(['is_current' => false]);
        });
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class, 'session_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_current', true);
    }
}
