<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseItem extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;

    protected $guarded = [];
}
