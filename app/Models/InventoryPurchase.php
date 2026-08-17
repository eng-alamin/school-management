<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryPurchase extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;
     
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
