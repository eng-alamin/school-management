<?php

namespace App\Livewire\Admin\Certificate;

use Livewire\Component;
use App\Models\CertificateTemplate;
use App\Models\Employee;
use App\Models\Setting;
use Carbon\Carbon;

class GenerateEmployeeComponent extends Component
{
    // ── Filters ──
    public string $filterRole     = '';
    public ?int   $filterTemplate = null;
    public bool   $filtered       = false;

    // ── Date fields ──
    public string $issue_date = '';

    // ── Selection ──
    public array $selectedIds = [];
    public bool  $selectAll   = false;

    // ── Print / Preview ──
    public bool  $showPrintPreview = false;
    public array $printCards       = [];

    public function mount(): void
    {
        $this->issue_date = now()->format('Y-m-d');
    }

    // ── Apply Filter ──
    public function applyFilter(): void
    {
        $this->validate([
            'filterRole'     => 'required',
            'filterTemplate' => 'required|exists:certificate_templates,id',
        ], [
            'filterRole.required'     => 'Please select a role.',          // BUG FIX: was 'class'
            'filterTemplate.required' => 'Please select a template.',
            'filterTemplate.exists'   => 'Selected template is invalid.',
        ]);

        $this->filtered    = true;
        $this->selectedIds = [];
        $this->selectAll   = false;
    }

    // ── Reset Filter ──
    public function resetFilter(): void
    {
        $this->filtered       = false;
        $this->filterRole     = '';
        $this->filterTemplate = null;
        $this->selectedIds    = [];
        $this->selectAll      = false;
        $this->resetValidation();
    }

    // BUG FIX: was resetting filterTemplate instead of selectedIds only
    public function updatedFilterRole(): void
    {
        $this->selectedIds = [];
        $this->selectAll   = false;
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

    // ── Generate Certificates ──
    public function generateCertificates(): void
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Please select at least one employee.');
            return;
        }

        $this->validate([
            'issue_date' => 'required|date',
        ]);

        $template  = CertificateTemplate::findOrFail($this->filterTemplate);
        $institute = Setting::first();

        $employees = Employee::with(['department', 'designation'])
            ->whereIn('id', $this->selectedIds)
            ->get();

        $this->printCards = $employees->map(function ($employee) use ($template, $institute) {

            $content = $template->certificate_content;

            // Employee photo HTML
            $photoHtml = $employee->photo
                ? '<img src="' . asset('storage/' . $employee->photo) . '"
                         style="width:80px;height:80px;object-fit:cover;
                                border-radius:6px;border:2px solid #ddd;">'
                : '<div style="width:80px;height:80px;background:#f3f4f6;
                               display:inline-flex;align-items:center;
                               justify-content:center;border-radius:6px;
                               font-size:1.5rem;color:#9ca3af;">👤</div>';

            // BUG FIX: fixed all placeholder → value mismatches
            $content = str_replace(
                [
                    // ── Institute placeholders ──
                    '{institute_name}',
                    '{institute_email}',
                    '{institute_mobile}',
                    '{institute_address}',

                    // ── Employee placeholders ──
                    '{name}',
                    '{employee_id}',
                    '{gender}',
                    '{blood_group}',
                    '{dob}',
                    '{religion}',
                    '{mobile}',
                    '{email}',
                    '{address}',
                    '{designation}',
                    '{department}',
                    '{joining_date}',
                    '{issue_date}',

                    // ── Photo placeholder ──
                    '{photo}',
                    '{employee_photo}',
                ],
                [
                    // ── Institute values ──
                    $institute?->name  ?? '',
                    $institute?->email ?? '',
                    $institute?->mobile          ?? '',
                    $institute?->address         ?? '',

                    // ── Employee values ──
                    $employee->name                 ?? '',
                    $employee->employee_id          ?? '',   // staff ID / emp code
                    $employee->gender               ?? '',
                    $employee->blood_group          ?? '',   // BUG FIX: was merged with dob
                    $employee->dob
                        ? Carbon::parse($employee->dob)->format('d M Y') : '',
                    $employee->religion             ?? '',
                    $employee->mobile               ?? '',
                    $employee->email                ?? '',
                    $employee->present_address      ?? '',
                    $employee->designation?->name   ?? '',
                    $employee->department?->name    ?? '',
                    $employee->joining_date
                        ? Carbon::parse($employee->joining_date)->format('d M Y') : '',
                    Carbon::parse($this->issue_date)->format('d M Y'),

                    // ── Photo as inline img ──
                    $photoHtml,
                    $photoHtml,
                ],
                $content
            );

            return [
                'employee_id' => $employee->id,
                'name'        => $employee->name,
                'gender'      => $employee->gender        ?? '',
                'blood_group' => $employee->blood_group   ?? '',
                'dob'         => $employee->dob           ?? '',
                'religion'    => $employee->religion      ?? '',
                'mobile'      => $employee->mobile        ?? '',
                'email'       => $employee->email         ?? '',
                'address'     => $employee->present_address ?? '',
                'photo'       => $employee->photo         ?? '',
                'designation' => $employee->designation?->name ?? '',
                'department'  => $employee->department?->name  ?? '',
                'issue_date'  => $this->issue_date,
                'content'     => $content,   // fully parsed HTML
                'template'    => $template,
            ];

        })->toArray();

        $this->showPrintPreview = true;
    }

    // ── Helpers ──
    private function getEmployees()
    {
        if (!$this->filtered) return collect();

        return Employee::with(['department', 'designation'])
            ->when($this->filterRole, fn($q) => $q->where('role', $this->filterRole))
            ->orderBy('name')
            ->get();
    }

    public function getAvailableRoles(): array
    {
        return [
            'admin'        => 'Admin',
            'teacher'      => 'Teacher',
            'accountant'   => 'Accountant',
            'librarian'    => 'Librarian',
            'receptionist' => 'Receptionist',
        ];
    }

    public function render()
    {
        $templates = CertificateTemplate::where('applicable_user', 'employee')
            ->where('is_active', true)
            ->get();

        $employees = $this->filtered ? $this->getEmployees() : collect();
        $roles     = $this->getAvailableRoles();

        $selectedTemplate = $this->filterTemplate
            ? CertificateTemplate::find($this->filterTemplate)
            : null;

        return view('livewire.admin.certificate.generate-employee-component')
            ->with(compact('templates', 'employees', 'roles', 'selectedTemplate'))
            ->layout('layouts.admin.app', [
                'title' => 'Generate Employee Certificates | Monarchy School',
            ]);
    }
}