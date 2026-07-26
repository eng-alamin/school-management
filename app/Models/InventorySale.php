<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventorySale extends Model
{
    use BelongsToInstitution;
    use SoftDeletes;
    
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
