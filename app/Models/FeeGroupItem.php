<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class FeeGroupItem extends Model
{
    use BelongsToSchool;
    protected $guarded = [];
    
    public function feeGroup()
    {
        return $this->belongsTo(FeeGroup::class);
    }

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    public function fines()
    {
        return $this->hasMany(FeeFine::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(FeeInvoiceItem::class);
    }
    public function invoiceItem()
    {
        return $this->belongsTo(FeeInvoiceItem::class);
    }
}
