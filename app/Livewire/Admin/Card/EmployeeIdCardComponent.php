<?php

namespace App\Livewire\Admin\Card;

use Livewire\Component;
use App\Models\IdCardTemplate;
use App\Models\EmployeeIdCard;
use App\Models\Employee;

class EmployeeIdCardComponent extends Component
{
    // Filter / Search
    public string $filterRole = '';
    public ?int $filterTemplate = null;
    public bool $filtered = false;

    // Date fields
    public string $print_date = '';
    public string $expiry_date = '';

    // Selection
    public array $selectedIds = [];
    public bool $selectAll = false;

    // Print
    public bool $showPrintPreview = false;
    public array $printCards = [];

    public function mount(): void
    {
        $this->print_date  = now()->format('Y-m-d');
        $this->expiry_date = now()->format('Y-m-d');
    }

    public function applyFilter(): void
    {
        $this->validate([
            'filterRole'     => 'required|string',
            'filterTemplate' => 'required|exists:id_card_templates,id',
        ], [
            'filterRole.required'     => 'Role is required.',
            'filterTemplate.required' => 'Template is required.',
        ]);

        $this->filtered    = true;
        $this->selectedIds = [];
        $this->selectAll   = false;
    }

    public function resetFilter(): void
    {
        $this->filtered       = false;
        $this->filterRole     = '';
        $this->filterTemplate = null;
        $this->selectedIds    = [];
        $this->selectAll      = false;
        $this->resetValidation();
    }

    public function updatedFilterRole(): void
    {
        $this->filterTemplate = null;
        $this->selectedIds    = [];
        $this->selectAll      = false;
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIds = $this->getEmployees()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds(): void
    {
        $total           = $this->getEmployees()->count();
        $this->selectAll = count($this->selectedIds) === $total && $total > 0;
    }

    public function generateCards(): void
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Please select at least one employee.');
            return;
        }

        $this->validate([
            'print_date'  => 'required|date',
            'expiry_date' => 'required|date',
        ]);

        $employees = Employee::with(['department', 'designation'])
            ->whereIn('id', $this->selectedIds)
            ->get();

        // Grab school_id once — upsert() skips Eloquent model events
        // so the BelongsToSchool trait won't fire automatically.
        $schoolId = auth()->user()->school_id;
        $data     = [];

        foreach ($employees as $employee) {
            $data[] = [
                'school_id'   => $schoolId,
                'employee_id' => $employee->id,

                'issue_date'  => $this->print_date,
                'expiry_date' => $this->expiry_date,
                'template_id' => $this->filterTemplate,

                'name'        => $employee->name,
                'gender'      => $employee->gender,
                'blood_group' => $employee->blood_group,
                'dob'         => $employee->dob,
                'religion'    => $employee->religion,
                'mobile'      => $employee->mobile,
                'email'       => $employee->email,
                'address'     => $employee->present_address,
                'photo'       => $employee->photo,

                'designation' => $employee->designation?->name,
                'department'  => $employee->department?->name,

                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        EmployeeIdCard::upsert(
            $data,
            ['employee_id'],  // unique key to match on
            [
                // school_id intentionally omitted — never overwrite on duplicate
                'issue_date',
                'expiry_date',
                'template_id',
                'name',
                'gender',
                'blood_group',
                'dob',
                'religion',
                'mobile',
                'email',
                'address',
                'photo',
                'designation',
                'department',
                'updated_at',
            ]
        );

        $this->printCards = EmployeeIdCard::with('template')
            ->whereIn('employee_id', $this->selectedIds)
            ->get()
            ->toArray();

        $this->showPrintPreview = true;
    }

    private function getEmployees()
    {
        if (!$this->filtered) return collect();

        return Employee::query()
            ->with(['designation', 'department', 'user'])
            ->whereHas('user', function ($q) {
                $q->where('role', $this->filterRole);
            })
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getAvailableRoles(): array
    {
        return [
            'admin',
            'teacher',
            'accountant',
            'librarian',
            'receptionist',
        ];
    }

    public function render()
    {
        $templates = IdCardTemplate::where('is_active', true)
            ->where('type', 'employee')  // fixed: was '!= employee', should be employee templates
            ->get();

        $employees        = $this->filtered ? $this->getEmployees() : collect();
        $roles            = $this->getAvailableRoles();
        $selectedTemplate = $this->filterTemplate
            ? IdCardTemplate::find($this->filterTemplate)
            : null;

        return view('livewire.admin.card.employee-id-card-component')
            ->with('templates', $templates)
            ->with('employees', $employees)
            ->with('roles', $roles)
            ->with('selectedTemplate', $selectedTemplate)
            ->layout('layouts.admin.app', [
                'title' => 'Employee ID Cards | School SaaS',
            ]);
    }
}