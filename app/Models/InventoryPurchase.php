<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class InventoryPurchase extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(InventorySupplier::class);
    }

    public function store()
    {
        return $this->belongsTo(InventoryStore::class);
    }

    public function items()
    {
        return $this->hasMany(InventoryPurchaseItem::class, 'purchase_id');
    }
}
