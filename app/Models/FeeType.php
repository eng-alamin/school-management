<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class FeeType extends Model
{
    use BelongsToSchool;
    protected $guarded = [];
    
    public function feeGroupItems()
    {
        return $this->hasMany(FeeGroupItem::class);
    }
}
