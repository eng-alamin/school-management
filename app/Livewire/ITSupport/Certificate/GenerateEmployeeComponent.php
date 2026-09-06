<?php

namespace App\Livewire\ITSupport\Certificate;

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

            // {logo} / {print_date} / {qr_code} — used by the 5 ready-made designs
            // shared between Student and Employee certificates (see
            // GenerateStudentComponent::buildLogoHtml()/buildQrCodeHtml() for the
            // same logic, kept in sync here).
            $logoHtml      = $this->buildLogoHtml($template, $institute);
            $printDateHtml = e(Carbon::parse($this->issue_date)->format('d M Y'));
            $qrCodeHtml    = $this->buildQrCodeHtml($template, $employee);

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
                    '{mobileno}',
                    '{email}',
                    '{address}',
                    '{issue_date}',

                    // ── Photo placeholders ──
                    '{photo}',
                    '{employee_photo}',

                    // ── Logo / date / QR (used by the 5 ready-made designs) ──
                    '{logo}',
                    '{print_date}',
                    '{qr_code}',
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
                    $institute?->phone         ?? '',
                    $employee->email           ?? '',
                    $employee->present_address ?? '',
                    Carbon::parse($this->issue_date)->format('d M Y'),

                    // ── Photo as inline img ──
                    $photoHtml,
                    $photoHtml,

                    // ── Logo / date / QR ──
                    $logoHtml,
                    $printDateHtml,
                    $qrCodeHtml,
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
     * {logo} placeholder-এর জন্য inline <img> HTML বানায়। Template-এর নিজস্ব
     * logo_image থাকলে সেটা ব্যবহার হয়, না থাকলে Institution-এর system_logo
     * fallback হিসেবে ব্যবহার হয়। GenerateStudentComponent-এর একই মেথডের
     * সাথে logic sync রাখা হয়েছে (dual maintenance এড়াতে চাইলে ভবিষ্যতে এটা
     * একটা shared trait/service-এ move করা যেতে পারে)।
     */
    private function buildLogoHtml(CertificateTemplate $template, ?Institution $institute): string
    {
        $path = $template->logo_image ?: $institute?->system_logo;

        if (!$path) {
            return '';
        }

        $url = $template->logo_image
            ? asset($template->logo_image)
            : asset('storage/' . $institute->system_logo);

        return '<img src="' . $url . '" style="height:56px;object-fit:contain;">';
    }

    /**
     * {qr_code} placeholder resolve করে employee-এর জন্য। Template-এর
     * qr_code_text field student/employee উভয়ের জন্য একই enum shares করে
     * (registration_no/roll_no/name/email/mobile) — employee-এর
     * registration_no/roll_no নেই, তাই সেক্ষেত্রে employee_id fallback হয়।
     *
     * NOTE: এটা আসল scannable QR image না, শুধু value readable text আকারে
     * দেখায় (student component-এর সাথে সামঞ্জস্যপূর্ণ)।
     */
    private function buildQrCodeHtml(CertificateTemplate $template, Employee $employee): string
    {
        $value = match ($template->qr_code_text) {
            'registration_no', 'roll_no' => $employee->employee_id,
            'name'                       => $employee->name,
            'email'                      => $employee->email,
            'mobile'                     => $employee->mobile,
            default                      => $employee->employee_id,
        } ?? '';

        if ($value === '') {
            return '';
        }

        return '<div style="display:inline-block;padding:6px 10px;border:1px solid #999;'
            . 'font-size:.7rem;font-family:monospace;letter-spacing:.03em;">'
            . e($value) . '</div>';
    }

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
            ->layout('layouts.itsupport.app', [
                'title' => 'Generate Employee Certificates | ' . institution()->name,
            ]);
    }
}