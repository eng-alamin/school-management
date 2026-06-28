<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class Attendance extends Model
{
    use BelongsToInstitution;
    
    protected $guarded = [];
    
    public function attendable()
    {
        return $this->morphTo();
    }

}
