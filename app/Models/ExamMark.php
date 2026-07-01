<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class ExamMark extends Model
{
    use BelongsToInstitution;
    
    protected $guarded = [];
}
