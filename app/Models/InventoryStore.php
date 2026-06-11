<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class InventoryStore extends Model
{
    use BelongsToSchool;
    protected $guarded = [];

    public function purchases()
    {
        return $this->hasMany(InventoryPurchase::class);
    }
}
