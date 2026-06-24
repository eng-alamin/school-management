<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class Purchase extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];
}
