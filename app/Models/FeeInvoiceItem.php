<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;

class FeeInvoiceItem extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;

    protected $guarded = [];

    protected $casts = [
        'base_amount'     => 'decimal:2',
        'fine_amount'     => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
    ];

    public function feeSetup()
    {
        return $this->belongsTo(FeeSetup::class, 'fee_setup_id');
    }

    public function feeInvoice()
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function allocations()
    {
        return $this->hasMany(FeePaymentAllocation::class, 'fee_invoice_item_id');
    }

    // Total paid for this specific item, from fee_payment_allocations
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->allocations()->sum('allocated_amount');
    }

    // Remaining balance
    public function getRemainingAttribute(): float
    {
        $net = (float) $this->base_amount
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
        $net       = (float) $this->base_amount
                   - (float) $this->discount_amount
                   + (float) $this->fine_amount;

        return match(true) {
            $totalPaid <= 0      => 'unpaid',
            $totalPaid >= $net   => 'paid',
            default              => 'partial',
        };
    }
}