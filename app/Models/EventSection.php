<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class EventSection extends Model
{
    use BelongsToSchool;
    protected $guarded = [];
}
