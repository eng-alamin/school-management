<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class FeeInvoiceItem extends Model
{
    use BelongsToSchool;
    protected $guarded = [];
    
    protected $casts = [
        'amount'          => 'decimal:2',
        'fine_amount'     => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function feeGroupItem()
    {
        return $this->belongsTo(FeeGroupItem::class);
    }

    // This item এর জন্য সব payment
    public function itemPayments()
    {
        return $this->hasMany(FeePayment::class, 'fee_invoice_item_id');
    }

    // Total paid for this specific item
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->itemPayments()->sum('paid_amount');
    }

    // Remaining balance
    public function getRemainingAttribute(): float
    {
        $net = (float) $this->amount
             - (float) $this->discount_amount
             + (float) $this->fine_amount;

        return max(0, $net - $this->total_paid);
    }

    // Fully paid check
    public function getIsPaidAttribute(): bool
    {
        return $this->remaining <= 0;
    }

    // Payment status label
    public function getPaymentStatusAttribute(): string
    {
        $totalPaid = $this->total_paid;
        $net       = (float) $this->amount
                   - (float) $this->discount_amount
                   + (float) $this->fine_amount;

        return match(true) {
            $totalPaid <= 0      => 'unpaid',
            $totalPaid >= $net   => 'paid',
            default              => 'partial',
        };
    }
}
