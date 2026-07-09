<?php

namespace App\Observers;

use App\Models\SalaryAdvanceRepayment;

/**
 * Keeps SalaryAdvance's cached remaining_amount (and status) in sync
 * whenever a repayment row is created, updated, or deleted — mirroring
 * the same pattern used by SalaryTemplateChildObserver for allowances/deductions.
 */
class SalaryAdvanceRepaymentObserver
{
    public function created(SalaryAdvanceRepayment $repayment): void
    {
        $this->syncParent($repayment);
    }

    public function updated(SalaryAdvanceRepayment $repayment): void
    {
        $this->syncParent($repayment);
    }

    public function deleted(SalaryAdvanceRepayment $repayment): void
    {
        $this->syncParent($repayment);
    }

    private function syncParent(SalaryAdvanceRepayment $repayment): void
    {
        $advance = $repayment->salaryAdvance()->first();

        if ($advance) {
            $advance->recalculateRemaining();
        }
    }
}