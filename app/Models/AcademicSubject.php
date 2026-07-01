<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSubject extends Model
{
    use BelongsToInstitution;
    
    protected $guarded = [];

    public function classAssignDetails(): HasMany
    {
        return $this->hasMany(AcademicClassAssignDetail::class);
    }
}
