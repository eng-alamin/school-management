<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryAdvance extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'amount'              => 'decimal:2',
        'remaining_amount'    => 'decimal:2',
        'installment_amount'  => 'decimal:2',
        'advance_date'        => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function repayments(): HasMany
    {
        return $this->hasMany(SalaryAdvanceRepayment::class);
    }

    public function nextDeductionAmount(): float
    {
        if ($this->remaining_amount <= 0) {
            return 0;
        }

        if ($this->installment_amount === null) {
            return (float) $this->remaining_amount;
        }

        return min((float) $this->installment_amount, (float) $this->remaining_amount);
    }

    public function recalculateRemaining(): void
    {
        $totalRepaid = $this->repayments()->sum('amount');
        $remaining   = max(0, (float) $this->amount - (float) $totalRepaid);

        $this->withoutEvents(function () use ($remaining) {
            $this->forceFill([
                'remaining_amount' => $remaining,
                'status'           => $remaining <= 0 ? 'settled' : 'active',
            ])->save();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}