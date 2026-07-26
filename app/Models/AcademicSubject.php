<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicSubject extends Model
{
    use BelongsToInstitution;
     use SoftDeletes;
     
    protected $guarded = [];

    public function classAssignDetails(): HasMany
    {
        return $this->hasMany(AcademicClassAssignDetail::class);
    }
}
