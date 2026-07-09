<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryTemplate extends Model
{
    use BelongsToInstitution, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'total_allowance' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function allowances(): HasMany
    {
        return $this->hasMany(SalaryTemplateAllowance::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(SalaryTemplateDeduction::class);
    }

    /**
     * Recalculate total_allowance, total_deduction, and net_salary
     * based on current child records, then persist silently
     * (withoutEvents avoids re-triggering observers / infinite loops).
     *
     * Called automatically by SalaryTemplateChildObserver whenever an
     * allowance/deduction row is created, updated, or deleted.
     */
    public function recalculateTotals(): void
    {
        $totalAllowance = $this->allowances()
            ->get()
            ->sum(fn (SalaryTemplateAllowance $allowance) => $allowance->resolvedAmount($this->basic_salary));

        $totalDeduction = $this->deductions()
            ->get()
            ->sum(fn (SalaryTemplateDeduction $deduction) => $deduction->resolvedAmount($this->basic_salary));

        $netSalary = $this->basic_salary + $totalAllowance - $totalDeduction;

        $this->withoutEvents(function () use ($totalAllowance, $totalDeduction, $netSalary) {
            $this->forceFill([
                'total_allowance' => $totalAllowance,
                'total_deduction' => $totalDeduction,
                'net_salary' => $netSalary,
            ])->save();
        });
    }
}