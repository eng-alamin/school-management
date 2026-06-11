<?php

namespace App\Livewire\Admin\Salary;

use Livewire\Component;
use App\Models\SalaryTemplate;

class EditTemplateComponent extends Component
{
    public SalaryTemplate $template;

    public string $salary_grade  = '';
    public string $basic_salary  = '';
    public string $overtime_rate = '';

    public array $allowances = [];
    public array $deductions = [];

    // ─── Mount ────────────────────────────────────────────────────────────────

    public function mount($id): void
    {
        $template = SalaryTemplate::findOrFail($id);

        $this->template_id  = $template->id;
        $this->template     = $template;

        $this->salary_grade  = $template->salary_grade;
        $this->basic_salary  = (string) $template->basic_salary;
        $this->overtime_rate = (string) ($template->overtime_rate ?? '');

        $this->allowances = $template->allowances
            ->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'amount' => (string) $a->amount])
            ->toArray();

        $this->deductions = $template->deductions
            ->map(fn($d) => ['id' => $d->id, 'name' => $d->name, 'amount' => (string) $d->amount])
            ->toArray();

        if (empty($this->allowances)) {
            $this->allowances = [['id' => null, 'name' => '', 'amount' => '']];
        }

        if (empty($this->deductions)) {
            $this->deductions = [['id' => null, 'name' => '', 'amount' => '']];
        }
    }

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
        $this->allowances[] = ['id' => null, 'name' => '', 'amount' => ''];
    }

    public function removeAllowanceRow(int $index): void
    {
        unset($this->allowances[$index]);
        $this->allowances = array_values($this->allowances);
    }

    public function addDeductionRow(): void
    {
        $this->deductions[] = ['id' => null, 'name' => '', 'amount' => ''];
    }

    public function removeDeductionRow(int $index): void
    {
        unset($this->deductions[$index]);
        $this->deductions = array_values($this->deductions);
    }

    // ─── Rules ────────────────────────────────────────────────────────────────

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

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

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(): void
    {
        try {
            $this->validate($this->rules());

            $this->template->update([
                'salary_grade'    => $this->salary_grade,
                'basic_salary'    => $this->basic_salary,
                'overtime_rate'   => $this->overtime_rate ?: null,
                'total_allowance' => $this->totalAllowance,
                'total_deduction' => $this->totalDeduction,
                'net_salary'      => $this->netSalary,
            ]);

            // Sync Allowances
            $keepAllowanceIds = [];
            foreach ($this->allowances as $allowance) {
                if (empty($allowance['name'])) continue;

                if (!empty($allowance['id'])) {
                    $this->template->allowances()->where('id', $allowance['id'])->update([
                        'name'   => $allowance['name'],
                        'amount' => $allowance['amount'] ?? 0,
                    ]);
                    $keepAllowanceIds[] = $allowance['id'];
                } else {
                    $new = $this->template->allowances()->create([
                        'name'   => $allowance['name'],
                        'amount' => $allowance['amount'] ?? 0,
                    ]);
                    $keepAllowanceIds[] = $new->id;
                }
            }
            $this->template->allowances()->whereNotIn('id', $keepAllowanceIds)->delete();

            // Sync Deductions
            $keepDeductionIds = [];
            foreach ($this->deductions as $deduction) {
                if (empty($deduction['name'])) continue;

                if (!empty($deduction['id'])) {
                    $this->template->deductions()->where('id', $deduction['id'])->update([
                        'name'   => $deduction['name'],
                        'amount' => $deduction['amount'] ?? 0,
                    ]);
                    $keepDeductionIds[] = $deduction['id'];
                } else {
                    $new = $this->template->deductions()->create([
                        'name'   => $deduction['name'],
                        'amount' => $deduction['amount'] ?? 0,
                    ]);
                    $keepDeductionIds[] = $new->id;
                }
            }
            $this->template->deductions()->whereNotIn('id', $keepDeductionIds)->delete();

            $this->dispatch('toast', type: 'success', message: 'Salary template updated successfully!');

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'An error occurred while updating the template.');
            throw $e;
        }
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.salary.edit-template-component')
            ->layout('layouts.admin.app', [
                'title' => 'Edit Salary Template | HR',
            ]);
    }
}