<?php

namespace App\Livewire\ITSupport\Card;

use Livewire\Component;
use App\Models\IdCardTemplate;
use App\Models\EmployeeIdCard;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Throwable;

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
        $this->expiry_date = now()->addYear()->format('Y-m-d');
    }

    public function applyFilter(): void
    {
        $this->validate([
            'filterRole'     => 'required|string|in:' . implode(',', $this->getAvailableRoles()),
            'filterTemplate' => 'required|exists:id_card_templates,id',
        ], [
            'filterRole.required'     => 'Role is required.',
            'filterRole.in'           => 'Selected role is invalid.',
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
        $this->filtered       = false;
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIds = $this->getEmployees()
                ->pluck('employee_id')
                ->map(fn($eid) => (string) $eid)
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
            $this->dispatch('toast', type: 'error', message: 'Please select at least one employee.');
            return;
        }

        if (!$this->filterTemplate) {
            $this->dispatch('toast', type: 'error', message: 'Please select a template.');
            return;
        }

        $this->validate([
            'print_date'  => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:print_date',
        ]);

        $employees = Employee::with(['department', 'designation'])
            ->whereIn('employee_id', $this->selectedIds)
            ->get();

        if ($employees->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'Selected employees could not be found.');
            return;
        }

        $institutionId = institution()->id;

        DB::beginTransaction();
        try {
            $cards = collect();

            foreach ($employees as $employee) {
                $payload = [
                    'institution_id' => $institutionId,
                    'template_id'    => $this->filterTemplate,
                    'issue_date'     => $this->print_date,
                    'expiry_date'    => $this->expiry_date,

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
                ];

                $card = EmployeeIdCard::withTrashed()
                    ->where('institution_id', $institutionId)
                    ->where('employee_id', $employee->employee_id)
                    ->first();

                if ($card) {
                    $card->fill($payload);
                    if (method_exists($card, 'trashed') && $card->trashed()) {
                        $card->restore();
                    }
                    $card->save();
                } else {
                    $card = EmployeeIdCard::create(array_merge($payload, [
                        'employee_id' => $employee->employee_id,
                    ]));
                }

                activity()
                    ->performedOn($card)
                    ->causedBy(auth()->user())
                    ->log('Generated/updated ID card for employee: '.$employee->name);

                $cards->push($card->load('template'));
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'ID cards could not be generated. Please try again.');
            return;
        }

        $this->printCards       = $cards->toArray();
        $this->showPrintPreview = true;

        $this->dispatch('toast', type: 'success', message: count($this->printCards).' ID card(s) generated successfully!');
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
            'teacher',
            'accountant',
            'staff',
        ];
    }

    public function render()
    {
        $templates = IdCardTemplate::where('is_active', true)
            ->where('type', '!=', 'student')
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
            ->layout('layouts.itsupport.app', [
                'title' => 'Employee ID Cards | ' . institution()->name,
            ]);
    }
}