<?php

namespace App\Observers;

use App\Models\SalaryTemplateAllowance;
use App\Models\SalaryTemplateDeduction;

class SalaryTemplateChildObserver
{
    public function created(SalaryTemplateAllowance|SalaryTemplateDeduction $model): void
    {
        $this->syncParentTotals($model);
    }

    public function updated(SalaryTemplateAllowance|SalaryTemplateDeduction $model): void
    {
        $this->syncParentTotals($model);
    }

    public function deleted(SalaryTemplateAllowance|SalaryTemplateDeduction $model): void
    {
        $this->syncParentTotals($model);
    }

    private function syncParentTotals(SalaryTemplateAllowance|SalaryTemplateDeduction $model): void
    {
        $template = $model->salaryTemplate()->first();

        if ($template) {
            $template->recalculateTotals();
        }
    }
}