<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class FeeGroup extends Model
{
    use BelongsToSchool;
    protected $guarded = [];
    
    public function items()
    {
        return $this->hasMany(FeeGroupItem::class);
    }

    public function fines()
    {
        return $this->hasMany(FeeFine::class);
    }

    public function allocations()
    {
        return $this->hasMany(FeeAllocation::class);
    }
}
