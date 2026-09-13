<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;

class FeePaymentAllocation extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;

    protected $guarded = [];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
    ];

    public function payment()
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }

    public function invoiceItem()
    {
        return $this->belongsTo(FeeInvoiceItem::class, 'fee_invoice_item_id');
    }
}