<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSubject extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;
     
    protected $guarded = [];

    public function classAssignDetails(): HasMany
    {
        return $this->hasMany(AcademicClassAssignDetail::class);
    }
}
