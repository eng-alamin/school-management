<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
        'discount'     => 'decimal:2',
        'fine'         => 'decimal:2',
        'paid_amount'  => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeAllocation(): BelongsTo
    {
        return $this->belongsTo(FeeAllocation::class);
    }

    public function feeGroupItem(): BelongsTo
    {
        return $this->belongsTo(FeeGroupItem::class);
    }

    public function feeInvoiceItem(): BelongsTo
    {
        return $this->belongsTo(FeeInvoiceItem::class);
    }

    public function officeAccount(): BelongsTo
    {
        return $this->belongsTo(OfficeAccount::class);
    }

    // Balance = amount - paid_amount (database value)
    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'paid'    => 'Total Paid',
            'partial' => 'Partial',
            default   => 'Unpaid',
        };
    }
}