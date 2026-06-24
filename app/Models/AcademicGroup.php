<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class AcademicGroup extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];
}
