<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class InventorySaleItem extends Model
{
    use BelongsToSchool;
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
