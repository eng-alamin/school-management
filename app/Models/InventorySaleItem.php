<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class InventorySaleItem extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    public function sale()
    {
        return $this->belongsTo(InventorySale::class);
    }

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class);
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class);
    }
}
