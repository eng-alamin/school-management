<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;

class InventorySaleItem extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;

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
