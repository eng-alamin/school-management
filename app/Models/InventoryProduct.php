<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class InventoryProduct extends Model
{
    use BelongsToSchool;
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
