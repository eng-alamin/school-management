<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class FeeFine extends Model
{
    use BelongsToSchool;
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
