<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class InventorySalePayment extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    public function sale()
    {
        return $this->belongsTo(InventorySale::class);
    }
}
