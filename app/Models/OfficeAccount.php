<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class OfficeAccount extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];
}
