<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class InventoryCategory extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(InventoryProduct::class, 'category_id');
    }

}
