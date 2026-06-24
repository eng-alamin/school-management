<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class FeeType extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];
    
    public function feeGroupItems()
    {
        return $this->hasMany(FeeGroupItem::class);
    }
}
