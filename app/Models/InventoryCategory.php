<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class InventoryCategory extends Model
{
    use BelongsToSchool;

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(InventoryProduct::class, 'category_id');
    }

}
