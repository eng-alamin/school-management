<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class FeeFine extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];
    
    public function feeGroup()
    {
        return $this->belongsTo(FeeGroup::class);
    }

    public function feeGroupItem()
    {
        return $this->belongsTo(FeeGroupItem::class);
    }
}
