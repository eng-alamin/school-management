<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class InventoryPurchaseItem extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];
    
    public function purchase()
    {
        return $this->belongsTo(InventoryPurchase::class);
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class);
    }
}
