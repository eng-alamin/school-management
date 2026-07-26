<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeType extends Model
{
    use BelongsToInstitution;
    use SoftDeletes;
    
    protected $guarded = [];

}
