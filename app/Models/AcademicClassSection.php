<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class AcademicClassSection extends Model
{
    use BelongsToSchool;
    
    protected $guarded = [];
}
