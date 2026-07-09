<?php

namespace App\Livewire\Admin\Certificate;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\CertificateTemplate;
use App\Models\Employee;
use App\Models\Institution;
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
            'filterRole.required'     => 'Please select a role.',
            'filterTemplate.required' => 'Please select a template.',
            'filterTemplate.exists'   => 'Selected template is invalid.',
        ]);

        $this->filtered    = true;
        $this->selectedIds = [];
        $this->selectAll   = false;
        unset($this->employees);
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
        unset($this->employees);
    }

    public function updatedFilterRole(): void
    {
        $this->selectedIds = [];
        $this->selectAll   = false;
        unset($this->employees);
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIds = $this->employees
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds(): void
    {
        $total           = $this->employees->count();
        $this->selectAll = count($this->selectedIds) === $total && $total > 0;
    }

    // ── Generate Certificates ──
    public function generateCertificates(): void
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('toast', type: 'error', message: 'Please select at least one employee.');
            return;
        }

        $this->validate([
            'issue_date' => 'required|date',
        ]);

        $template  = CertificateTemplate::findOrFail($this->filterTemplate);
        $institute = Institution::find(auth()->user()->institution_id);

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

            $formattedDob = $employee->dob
                ? Carbon::parse($employee->dob)->format('d M Y')
                : '';

            $content = str_replace(
                [
                    // ── Institute placeholders ──
                    '{institute_name}',
                    '{institute_email}',
                    '{institute_mobile}',
                    '{institute_address}',

                    // ── Employee placeholders (current) ──
                    '{joining_date}',
                    '{employee_id}',
                    '{name}',
                    '{designation}',
                    '{department}',
                    '{gender}',
                    '{blood}',
                    '{birthday}',
                    '{religion}',
                    '{qualification}',
                    '{experience_detail}',
                    '{total_experience}',

                    // ── Employee placeholders (legacy, kept for old templates) ──
                    '{blood_group}',
                    '{dob}',
                    '{mobile}',
                    '{email}',
                    '{address}',
                    '{issue_date}',

                    // ── Photo placeholders ──
                    '{photo}',
                    '{employee_photo}',
                ],
                [
                    // ── Institute values ──
                    $institute?->name    ?? '',
                    $institute?->email   ?? '',
                    $institute?->phone  ?? '',
                    $institute?->address ?? '',

                    // ── Employee values (current) ──
                    $employee->joining_date
                        ? Carbon::parse($employee->joining_date)->format('d M Y') : '',
                    $employee->employee_id       ?? '',
                    $employee->name              ?? '',
                    $employee->designation?->name ?? '',
                    $employee->department?->name  ?? '',
                    $employee->gender             ?? '',
                    $employee->blood_group        ?? '',
                    $formattedDob,
                    $employee->religion           ?? '',
                    $employee->qualification      ?? '',
                    $employee->experience_detail  ?? '',
                    $employee->total_experience   ?? '',

                    // ── Employee values (legacy) ──
                    $employee->blood_group     ?? '',
                    $formattedDob,
                    $employee->mobile          ?? '',
                    $employee->email           ?? '',
                    $employee->present_address ?? '',
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
                'gender'      => $employee->gender           ?? '',
                'blood_group' => $employee->blood_group      ?? '',
                'dob'         => $employee->dob               ?? '',
                'religion'    => $employee->religion          ?? '',
                'mobile'      => $employee->mobile            ?? '',
                'email'       => $employee->email             ?? '',
                'address'     => $employee->present_address   ?? '',
                'photo'       => $employee->photo             ?? '',
                'designation' => $employee->designation?->name ?? '',
                'department'  => $employee->department?->name  ?? '',
                'issue_date'  => $this->issue_date,
                'content'     => $content,
                'template'    => $template,
            ];

        })->toArray();

        $this->showPrintPreview = true;

        activity()
            ->withProperties([
                'template'      => $template->certificate_name,
                'employee_count'=> count($this->printCards),
                'issue_date'    => $this->issue_date,
            ])
            ->log('Generated ' . count($this->printCards) . ' employee certificate(s) using template: ' . $template->certificate_name);
    }

    // ── Helpers ──

    /**
     * Cached per-request employee list. Prevents duplicate queries across
     * render(), updatedSelectAll(), updatedSelectedIds(), and the view.
     */
    #[Computed]
    public function employees()
    {
        if (!$this->filtered) {
            return collect();
        }

        return Employee::with(['user', 'department', 'designation'])
            ->when($this->filterRole, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('role', $this->filterRole);
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function getAvailableRoles(): array
    {
        return [
            'admin'      => 'Admin',
            'teacher'    => 'Teacher',
            'accountant' => 'Accountant',
            'staff'      => 'Staff',
        ];
    }

    public function render()
    {
        $templates = CertificateTemplate::where('applicable_user', 'employee')
            ->where('is_active', true)
            ->get();

        $roles = $this->getAvailableRoles();

        $selectedTemplate = $this->filterTemplate
            ? CertificateTemplate::find($this->filterTemplate)
            : null;

        return view('livewire.admin.certificate.generate-employee-component')
            ->with([
                'templates'        => $templates,
                'employees'        => $this->employees,
                'roles'            => $roles,
                'selectedTemplate' => $selectedTemplate,
            ])
            ->layout('layouts.admin.app', [
                'title' => 'Generate Employee Certificates | ' . institution()->name,
            ]);
    }
}