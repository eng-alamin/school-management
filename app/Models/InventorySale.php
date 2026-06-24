<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class InventorySale extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    public function saleable()
    {
        return $this->morphTo();
    }

    public function items()
    {
        return $this->hasMany(InventorySaleItem::class, 'sale_id');
    }

    public function payments()
    {
        return $this->hasMany(InventorySalePayment::class);
    }
}
