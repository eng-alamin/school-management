<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;
    
    protected $guarded = [];
    
    public function attendable()
    {
        return $this->morphTo();
    }

}
