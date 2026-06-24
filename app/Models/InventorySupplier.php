<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class InventorySupplier extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    public function purchases()
    {
        return $this->hasMany(InventoryPurchase::class);
    }

}
