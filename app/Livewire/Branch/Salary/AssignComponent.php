<?php

namespace App\Livewire\Branch\Salary;

use Livewire\Component;
use App\Models\SalaryAssign;
use App\Models\SalaryTemplate;
use App\Models\EmployeeDesignation;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class AssignComponent extends Component
{
    // ── Filter ────────────────────────────────────────────────────
    public string $role           = '';
    public ?int   $designation_id = null;
    public string $employeeSearch = '';

    // ── Dynamic lists ─────────────────────────────────────────────
    public array $designations       = [];
    public array $employees          = [];
    public array $salaryTemplate     = [];
    public array $alreadyAssignedIds = [];
    public bool  $hasFiltered        = false;

    public array $selectedIds = [];
    public bool  $selectAll   = false;

    // ─────────────────────────────────────────────────────────────

    public function updatedRole(): void
    {
        $this->designation_id = null;
        $this->resetListState();

        $this->designations = $this->role
            ? EmployeeDesignation::orderBy('name')->get()->toArray()
            : [];
    }

    public function updatedDesignationId(): void
    {
        $this->resetListState();
    }

    private function resetListState(): void
    {
        $this->employees          = [];
        $this->salaryTemplate     = [];
        $this->alreadyAssignedIds = [];
        $this->hasFiltered        = false;
        $this->employeeSearch     = '';
    }

    public function filter(): void
    {
        $this->validate([
            'role' => 'required|string',
        ]);

        $this->loadEmployees();
        $this->hasFiltered = true;
    }

    private function loadEmployees(): void
    {
        $employees = Employee::with(['user', 'designation', 'department'])
            ->whereHas('user', fn ($q) => $q->where('role', $this->role))
            ->when($this->designation_id, fn ($q) => $q->where('designation_id', $this->designation_id))
            ->orderBy('name')
            ->get();

        $this->employees          = $employees->toArray();
        $this->salaryTemplate     = [];
        $this->alreadyAssignedIds = [];

        if ($employees->isEmpty()) {
            return;
        }

        // N+1 এড়াতে সব existing assign একসাথে load করা হচ্ছে
        $existingAssigns = SalaryAssign::whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->keyBy('employee_id');

        foreach ($employees as $employee) {
            $existing = $existingAssigns->get($employee->id);
            $this->salaryTemplate[$employee->id] = $existing?->salary_template_id ?? '';

            if ($existing) {
                $this->alreadyAssignedIds[] = $employee->id;
            }
        }
    }

    /**
     * নির্দিষ্ট employee-র জন্য সিলেক্ট করা template থেকে Net Salary বের করে (UI preview এর জন্য)
     */
    public function getTemplateAmountsProperty(): array
    {
        $templateIds = array_values(array_filter(
            $this->salaryTemplate,
            fn ($id) => $id !== '' && $id !== null
        ));

        $templates = SalaryTemplate::whereIn('id', $templateIds)
            ->get()
            ->keyBy('id');

        $amounts = [];
        foreach ($this->salaryTemplate as $employeeId => $templateId) {
            if ($templateId === '' || $templateId === null || !$templates->has($templateId)) {
                $amounts[$employeeId] = null;
                continue;
            }

            $template = $templates->get($templateId);
            $gross    = (float) $template->basic_salary + (float) $template->total_allowance;
            $net      = $gross - (float) $template->total_deduction;

            $amounts[$employeeId] = [
                'gross' => $gross,
                'net'   => $net,
            ];
        }

        return $amounts;
    }

    public function save(): void
    {
        if (empty($this->employees)) {
            $this->dispatch('toast', type: 'error', message: 'No employees to assign.');
            return;
        }

        $rowsToSave = array_filter(
            $this->employees,
            fn ($employee) => !empty($this->salaryTemplate[$employee['id']])
        );

        if (empty($rowsToSave)) {
            $this->dispatch('toast', type: 'error', message: 'Please select at least one salary grade.');
            return;
        }

        DB::beginTransaction();

        try {
            $templates = SalaryTemplate::whereIn(
                'id',
                array_unique(array_values(array_filter($this->salaryTemplate)))
            )->get()->keyBy('id');

            $assigned = 0;

            foreach ($rowsToSave as $employee) {
                $templateId = $this->salaryTemplate[$employee['id']];
                $template   = $templates->get($templateId);

                if (!$template) {
                    continue;
                }

                $totalAllowance = (float) ($template->total_allowance ?? 0);
                $totalDeduction = (float) ($template->total_deduction ?? 0);
                $basicSalary    = (float) ($template->basic_salary ?? 0);
                $overtimeRate   = (float) ($template->overtime_rate ?? 0);
                $gross          = $basicSalary + $totalAllowance;
                $net            = $gross - $totalDeduction;

                $salaryAssign = SalaryAssign::updateOrCreate(
                    [
                        'institution_id' => institution()->id,
                        'employee_id'    => $employee['id'],
                    ],
                    [
                        'role'               => $this->role,
                        'designation_id'     => $employee['designation_id'] ?? null,
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

                activity()
                    ->performedOn($salaryAssign)
                    ->withProperties([
                        'institution_id' => institution()->id,
                        'employee_id'    => $employee['id'],
                        'employee_name'  => $employee['name'] ?? null,
                        'salary_template' => $template->name,
                        'salary_grade'   => $template->salary_grade,
                    ])
                    ->log('Salary Assigned');

                $assigned++;
            }

            DB::commit();

            if ($assigned > 0) {
                $this->dispatch('toast', type: 'success', message: "{$assigned} salary assignment(s) saved successfully!");
                $this->loadEmployees(); // updated "already assigned" state দেখানোর জন্য
            } else {
                $this->dispatch('toast', type: 'error', message: 'Please select at least one salary grade.');
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'An error occurred while saving salary assignments.');
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'role', 'designation_id', 'designations', 'employees',
            'salaryTemplate', 'alreadyAssignedIds', 'hasFiltered',
            'selectedIds', 'selectAll', 'employeeSearch',
        ]);
        $this->resetValidation();
    }

    public function getAvailableRoles(): array
    {
        return [
            'teacher'    => 'Teacher',
            'accountant' => 'Accountant',
            'staff'      => 'Staff',
        ];
    }

    /**
     * Client-side visible list — search filter apply করা হয় এখানে
     */
    public function getFilteredEmployeesProperty(): array
    {
        if (blank($this->employeeSearch)) {
            return $this->employees;
        }

        $search = mb_strtolower($this->employeeSearch);

        return array_values(array_filter($this->employees, function ($employee) use ($search) {
            return str_contains(mb_strtolower($employee['name'] ?? ''), $search)
                || str_contains(mb_strtolower($employee['employee_id'] ?? ''), $search);
        }));
    }

    public function render()
    {
        $salaryTemplates = SalaryTemplate::orderBy('name')->get();
        $roles           = $this->getAvailableRoles();

        return view('livewire.admin.salary.assign-component')
            ->with([
                'salaryTemplates'   => $salaryTemplates,
                'roles'             => $roles,
                'filteredEmployees' => $this->filteredEmployees,
                'templateAmounts'   => $this->templateAmounts,
            ])
            ->layout('layouts.branch.app', [
                'title' => 'Salary Assign | ' . institution()->name,
            ]);
    }
}