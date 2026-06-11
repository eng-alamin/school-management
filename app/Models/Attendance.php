<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class Attendance extends Model
{
    use BelongsToSchool;
    protected $guarded = [];
    
    public function attendable()
    {
        return $this->morphTo();
    }

}
