<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FeePayment extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    /* ---------- Relations ---------- */

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function officeAccount()
    {
        return $this->belongsTo(OfficeAccount::class);
    }

    public function allocations()
    {
        return $this->hasMany(FeePaymentAllocation::class, 'fee_payment_id');
    }

    /**
     * Create a payment and distribute its amount across one or more
     * fee_invoice_items in a single atomic transaction.
     *
     * $itemAmounts format: [fee_invoice_item_id => allocated_amount, ...]
     *
     * Validates that:
     *  - the sum of allocations equals the payment amount
     *  - no single item is allocated more than its own remaining balance
     *  - every item belongs to the invoice being paid
     *
     * Call site is expected to catch InvalidArgumentException and turn it
     * into a user-facing validation message.
     *
     * @param  array<int, mixed>  $attributes  base FeePayment attributes (institution_id, branch_id, student_id, fee_invoice_id, payment_date, payment_method, remarks, office_account_id, etc. — must NOT include 'amount', it is derived from $itemAmounts)
     * @param  array<int, float|string>  $itemAmounts
     */
    public static function createWithAllocations(array $attributes, array $itemAmounts): self
    {
        if (empty($itemAmounts)) {
            throw new InvalidArgumentException('At least one fee_invoice_item allocation is required.');
        }

        return DB::transaction(function () use ($attributes, $itemAmounts) {
            $invoiceId = $attributes['fee_invoice_id'] ?? null;

            $items = FeeInvoiceItem::query()
                ->whereIn('id', array_keys($itemAmounts))
                ->where('fee_invoice_id', $invoiceId)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($items->count() !== count($itemAmounts)) {
                throw new InvalidArgumentException('One or more fee_invoice_item ids do not belong to this invoice.');
            }

            $totalAllocated = 0.0;

            foreach ($itemAmounts as $itemId => $allocatedAmount) {
                $allocatedAmount = round((float) $allocatedAmount, 2);

                if ($allocatedAmount <= 0) {
                    throw new InvalidArgumentException("Allocation for invoice item {$itemId} must be greater than zero.");
                }

                $item = $items->get($itemId);

                // remaining() already excludes already-allocated payments —
                // safe under lockForUpdate() above.
                if ($allocatedAmount > $item->remaining + 0.001) {
                    throw new InvalidArgumentException("Allocation for invoice item {$itemId} exceeds its remaining balance.");
                }

                $totalAllocated += $allocatedAmount;
            }

            $paymentAmount = round((float) ($attributes['amount'] ?? $totalAllocated), 2);

            if (abs($paymentAmount - $totalAllocated) > 0.01) {
                throw new InvalidArgumentException('Sum of item allocations does not match the payment amount.');
            }

            $payment = static::create(array_merge($attributes, [
                'amount' => $totalAllocated,
            ]));

            foreach ($itemAmounts as $itemId => $allocatedAmount) {
                $payment->allocations()->create([
                    'institution_id'      => $payment->institution_id,
                    'branch_id'           => $payment->branch_id,
                    'fee_invoice_item_id' => $itemId,
                    'allocated_amount'    => round((float) $allocatedAmount, 2),
                ]);
            }

            $payment->invoice?->recalculate();

            return $payment;
        });
    }
}