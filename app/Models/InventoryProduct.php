<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryProduct extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;

    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function purchaseUnit()
    {
        return $this->belongsTo(InventoryUnit::class, 'purchase_unit_id');
    }

    public function salesUnit()
    {
        return $this->belongsTo(InventoryUnit::class, 'sales_unit_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(InventoryPurchaseItem::class);
    }
}
