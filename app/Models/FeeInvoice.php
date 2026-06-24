<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;

class FeeInvoice extends Model
{
    use BelongsToInstitution;
    protected $guarded = [];

    protected $casts = [
        'invoice_date'    => 'date',
        'due_date'        => 'date',
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'fine_amount'     => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'due_amount'      => 'decimal:2',
        'status'          => 'boolean',
    ];
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function allocation()
    {
        return $this->belongsTo(FeeAllocation::class, 'fee_allocation_id');
    }

    public function class()
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(AcademicSection::class);
    }

    public function items()
    {
        return $this->hasMany(FeeInvoiceItem::class, 'fee_invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(FeePayment::class, 'fee_allocation_id', 'fee_allocation_id')
            ->where('student_id', $this->student_id);
    }

    // Recalculate and update invoice totals
    public function recalculate(): void
    {
        $items = $this->items()->get();

        $subtotal        = $items->sum('amount');
        $discountAmount  = $items->sum('discount_amount');
        $fineAmount      = $items->sum('fine_amount');
        $totalAmount     = $subtotal - $discountAmount + $fineAmount;

        $paidAmount = FeePayment::where('student_id', $this->student_id)
            ->where('fee_allocation_id', $this->fee_allocation_id)
            ->sum('paid_amount');

        $dueAmount = max(0, $totalAmount - $paidAmount);

        $status = match(true) {
            $paidAmount <= 0             => 'unpaid',
            $paidAmount >= $totalAmount  => 'paid',
            default                      => 'partial',
        };

        $this->update([
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'fine_amount'     => $fineAmount,
            'total_amount'    => $totalAmount,
            'paid_amount'     => $paidAmount,
            'due_amount'      => $dueAmount,
            'payment_status'  => $status,
        ]);
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getBalanceAttribute(): float
    {
        return (float) $this->due_amount;
    }

}
