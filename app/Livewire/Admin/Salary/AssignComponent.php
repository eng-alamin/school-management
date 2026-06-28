<?php

namespace App\Livewire\Admin\Salary;

use Livewire\Component;
use App\Models\SalaryAssign;
use App\Models\SalaryTemplate;
use App\Models\EmployeeDesignation;
use App\Models\Employee;

class AssignComponent extends Component
{
    // ── Filter ────────────────────────────────────────────────────
    public string $role           = '';
    public ?int   $designation_id = null;

    // ── Dynamic lists ─────────────────────────────────────────────
    public array $designations   = [];
    public array $employees      = [];
    public array $salaryTemplate = [];
    public bool  $hasFiltered    = false;

    public array $selectedIds = [];
    public bool  $selectAll   = false;

    // ─────────────────────────────────────────────────────────────

    public function updatedRole(): void
    {
        $this->designation_id = null;
        $this->employees      = [];
        $this->salaryTemplate = [];
        $this->hasFiltered    = false;
        $this->selectedIds    = [];
        $this->selectAll      = false;

        $this->designations = $this->role
            ? EmployeeDesignation::orderBy('name')->get()->toArray()
            : [];
    }

    public function updatedDesignationId(): void
    {
        $this->employees     = [];
        $this->salaryTemplate = [];
        $this->hasFiltered   = false;
    }

    public function filter(): void
    {
        $this->validate(['role' => 'required|string']);

        $this->loadEmployees();
        $this->hasFiltered = true;
    }

    private function loadEmployees(): void
    {
        $employees = Employee::with(['user', 'designation', 'department'])
            ->whereHas('user', fn($q) => $q->where('role', $this->role))
            ->when($this->designation_id, fn($q) => $q->where('designation_id', $this->designation_id))
            ->orderBy('name')
            ->get();

        $this->employees     = $employees->toArray();
        $this->salaryTemplate = [];

        foreach ($employees as $employee) {
            $existing = SalaryAssign::where('employee_id', $employee->id)->latest()->first();
            $this->salaryTemplate[$employee->id] = $existing?->salary_template_id ?? '';
        }
    }

    public function save(): void
    {
        if (empty($this->employees)) {
            $this->dispatch('toast', type: 'error', message: 'No employees to assign.');
            return;
        }

        $assigned = 0;

        foreach ($this->employees as $employee) {
            $templateId = $this->salaryTemplate[$employee['id']] ?? null;
            if (!$templateId) continue;

            $template = SalaryTemplate::find($templateId);
            if (!$template) continue;

            $totalAllowance = (float) ($template->total_allowance ?? 0);
            $totalDeduction = (float) ($template->total_deduction ?? 0);
            $basicSalary    = (float) ($template->basic_salary    ?? 0);
            $overtimeRate   = (float) ($template->overtime_rate   ?? 0);
            $gross          = $basicSalary + $totalAllowance;
            $net            = $gross - $totalDeduction;

            SalaryAssign::updateOrCreate(
                ['employee_id' => $employee['id']],
                [
                    'role'               => $this->role,
                    'designation_id'     => $employee['designation_id'], 
                    'salary_template_id' => $templateId,
                    'salary_grade'       => $template->salary_grade,
                    'basic_salary'       => $basicSalary,
                    'overtime_rate'      => $overtimeRate,
                    'total_allowance'    => $totalAllowance,
                    'total_deduction'    => $totalDeduction,
                    'gross_salary'       => $gross,
                    'net_salary'         => $net,
                ]
            );

            $assigned++;
        }

        if ($assigned > 0) {
            $this->dispatch('toast', type: 'success', message: "{$assigned} salary assignment(s) saved successfully!");
        } else {
            $this->dispatch('toast', type: 'error', message: 'Please select at least one salary grade.');
        }
    }

    public function resetForm(): void
    {
        $this->reset(['role', 'designation_id', 'designations', 'employees', 'salaryTemplate', 'hasFiltered', 'selectedIds', 'selectAll']);
        $this->resetValidation();
    }

    public function getAvailableRoles(): array
    {
        return [
            'admin'        => 'Admin',
            'teacher'      => 'Teacher',
            'accountant'   => 'Accountant',
            'staff'        => 'Staff',
        ];
    }

    public function render()
    {
        $salaryTemplates = SalaryTemplate::orderBy('salary_grade')->get();
        $roles           = $this->getAvailableRoles();

        return view('livewire.admin.salary.assign-component')
            ->with(['salaryTemplates' => $salaryTemplates, 'roles' => $roles])
            ->layout('layouts.admin.app', [
                'title' => 'Salary Assign | ' . institution()->name,
            ]);
    }
}