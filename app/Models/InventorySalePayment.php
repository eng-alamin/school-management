<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class InventorySalePayment extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    public function sale()
    {
        return $this->belongsTo(InventorySale::class);
    }
}
