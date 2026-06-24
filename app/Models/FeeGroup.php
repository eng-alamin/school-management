<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class FeeGroup extends Model
{
    use BelongsToInstitution;
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
