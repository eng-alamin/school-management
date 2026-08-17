<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;

class InventoryPurchaseItem extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;

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
