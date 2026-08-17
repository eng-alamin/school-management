<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;

class EventSection extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;

    protected $guarded = [];
}
