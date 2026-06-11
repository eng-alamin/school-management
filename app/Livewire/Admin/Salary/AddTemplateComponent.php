<?php

namespace App\Livewire\Admin\Salary;

use Livewire\Component;
use App\Models\SalaryTemplate;
use App\Models\SalaryTemplateAllowance;
use App\Models\SalaryTemplateDeduction;

class AddTemplateComponent extends Component
{
    public string $salary_grade  = '';
    public string $basic_salary  = '';
    public string $overtime_rate = '';

    public array $allowances = [
        ['name' => '', 'amount' => ''],
    ];

    public array $deductions = [
        ['name' => '', 'amount' => ''],
    ];

    // ─── Computed ─────────────────────────────────────────────────────────────

    public function getTotalAllowanceProperty(): float
    {
        return collect($this->allowances)->sum(fn($a) => (float) ($a['amount'] ?? 0));
    }

    public function getTotalDeductionProperty(): float
    {
        return collect($this->deductions)->sum(fn($d) => (float) ($d['amount'] ?? 0));
    }

    public function getNetSalaryProperty(): float
    {
        return (float) $this->basic_salary + $this->totalAllowance - $this->totalDeduction;
    }

    // ─── Row Management ───────────────────────────────────────────────────────

    public function addAllowanceRow(): void
    {
        $this->allowances[] = ['name' => '', 'amount' => ''];
    }

    public function removeAllowanceRow(int $index): void
    {
        unset($this->allowances[$index]);
        $this->allowances = array_values($this->allowances);
    }

    public function addDeductionRow(): void
    {
        $this->deductions[] = ['name' => '', 'amount' => ''];
    }

    public function removeDeductionRow(int $index): void
    {
        unset($this->deductions[$index]);
        $this->deductions = array_values($this->deductions);
    }

    // ─── Reset ────────────────────────────────────────────────────────────────

    public function resetForm(): void
    {
        $this->reset(['salary_grade', 'basic_salary', 'overtime_rate']);
        $this->allowances = [['name' => '', 'amount' => '']];
        $this->deductions = [['name' => '', 'amount' => '']];
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    // ─── Rules ────────────────────────────────────────────────────────────────

    public function rules(): array
    {
        return [
            'salary_grade'          => 'required|string|max:255',
            'basic_salary'          => 'required|numeric|min:0',
            'overtime_rate'         => 'nullable|numeric|min:0',
            'allowances.*.name'     => 'nullable|string|max:255',
            'allowances.*.amount'   => 'nullable|numeric|min:0',
            'deductions.*.name'     => 'nullable|string|max:255',
            'deductions.*.amount'   => 'nullable|numeric|min:0',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    // ─── Save ─────────────────────────────────────────────────────────────────

    public function save(): void
    {
        try {
            $this->validate($this->rules());

            $template = SalaryTemplate::create([
                'salary_grade'    => $this->salary_grade,
                'basic_salary'    => $this->basic_salary,
                'overtime_rate'   => $this->overtime_rate ?: null,
                'total_allowance' => $this->totalAllowance,
                'total_deduction' => $this->totalDeduction,
                'net_salary'      => $this->netSalary,
            ]);

            foreach ($this->allowances as $allowance) {
                if (!empty($allowance['name'])) {
                    $template->allowances()->create([
                        'name'   => $allowance['name'],
                        'amount' => $allowance['amount'] ?? 0,
                    ]);
                }
            }

            foreach ($this->deductions as $deduction) {
                if (!empty($deduction['name'])) {
                    $template->deductions()->create([
                        'name'   => $deduction['name'],
                        'amount' => $deduction['amount'] ?? 0,
                    ]);
                }
            }

            $this->dispatch('toast', type: 'success', message: 'Salary template created successfully!');
            $this->resetForm();

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'An error occurred while creating the template.');
            throw $e;
        }
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.salary.add-template-component')
            ->layout('layouts.admin.app', [
                'title' => 'Salary Template | HR',
            ]);
    }
}