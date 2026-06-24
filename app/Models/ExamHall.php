<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class ExamHall extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];
}
