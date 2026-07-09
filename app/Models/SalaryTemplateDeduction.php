<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryTemplateDeduction extends Model
{
    use BelongsToInstitution;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function salaryTemplate(): BelongsTo
    {
        return $this->belongsTo(SalaryTemplate::class);
    }

    /**
     * Resolve the actual monetary value of this deduction.
     * If type is 'percentage', amount is treated as a percentage of basic salary.
     * If type is 'fixed', amount is used as-is.
     */
    public function resolvedAmount(float $basicSalary): float
    {
        if ($this->type === 'percentage') {
            return round(($basicSalary * $this->amount) / 100, 2);
        }

        return (float) $this->amount;
    }
}