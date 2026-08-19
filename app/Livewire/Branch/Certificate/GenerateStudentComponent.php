<?php

namespace App\Livewire\Branch\Certificate;

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\CertificateTemplate;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;
use App\Models\Institution;
use Carbon\Carbon;

class GenerateStudentComponent extends Component
{
    // ── Filters ──
    public string $filterClass    = '';
    public string $filterSection  = '';
    public ?int   $filterTemplate = null;
    public bool   $filtered       = false;

    // Class-e section thake kina — false hole section select hide hobe (document 1-er pattern)
    public bool $filterClassHasSection = true;

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
        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }

        if (!$this->filterTemplate) {
            $this->dispatch('toast', type: 'error', message: 'Please select a template.');
            return;
        }

        // Class-e section na thakle filterSection forcibly khali rakhbo (data integrity)
        if (!$this->filterClassHasSection) {
            $this->filterSection = '';
        }

        $this->filtered    = true;
        $this->selectedIds = [];
        $this->selectAll   = false;
        unset($this->students);
    }

    // ── Reset Filter ──
    public function resetFilter(): void
    {
        $this->filtered              = false;
        $this->filterClass           = '';
        $this->filterSection         = '';
        $this->filterTemplate        = null;
        $this->filterClassHasSection = true;
        $this->selectedIds           = [];
        $this->selectAll             = false;
        $this->resetValidation();
        unset($this->students);
    }

    public function updatedFilterClass(): void
    {
        $this->filterSection         = '';
        $this->selectedIds           = [];
        $this->selectAll             = false;
        $this->filterClassHasSection = true;
        unset($this->students);

        if ($this->filterClass) {
            $class = AcademicClass::where('institution_id', institution()->id)
                ->find($this->filterClass);

            // Class na paoa gele safe default: section select active thakbe
            $this->filterClassHasSection = $class ? (bool) $class->has_section : true;
        }
    }

    public function updatedFilterSection(): void
    {
        $this->selectedIds = [];
        $this->selectAll   = false;
        unset($this->students);
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIds = $this->students
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds(): void
    {
        $total           = $this->students->count();
        $this->selectAll = count($this->selectedIds) === $total && $total > 0;
    }

    // ── Generate Certificates ──
    public function generateCertificates(): void
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('toast', type: 'error', message: 'Please select at least one student.');
            return;
        }

        $this->validate([
            'issue_date' => 'required|date',
        ]);

        $template  = CertificateTemplate::findOrFail($this->filterTemplate);
        $institute = Institution::find(auth()->user()->institution_id);

        $students = Student::with(['class', 'section', 'group', 'guardians'])
            ->whereIn('id', $this->selectedIds)
            ->get();

        $this->printCards = $students->map(function ($student) use ($template, $institute) {

            $content = $template->certificate_content;

            // Student photo HTML
            $photoHtml = $student->photo
                ? '<img src="' . asset('storage/' . $student->photo) . '"
                         style="width:80px;height:80px;object-fit:cover;
                                border-radius:6px;border:2px solid #ddd;">'
                : '<div style="width:80px;height:80px;background:#f3f4f6;
                               display:inline-flex;align-items:center;
                               justify-content:center;border-radius:6px;
                               font-size:1.5rem;color:#9ca3af;">👤</div>';

            // Institute mobile — computed once, reused by {institute_mobile} and the
            // legacy {mobileno} alias below.
            $institutePhone = e($institute?->phone ?? '');

            // {logo} placeholder — the 5 ready-made designs place this inline in the
            // content (separate from the header logo rendered by the Blade view).
            $logoHtml = $this->buildLogoHtml($template, $institute);

            // {print_date} — "Date of Publication" in the ready-made designs; uses the
            // same Issue Date the admin picked on the filter screen.
            $printDateHtml = e(Carbon::parse($this->issue_date)->format('d M Y'));

            // {qr_code} — text-value placeholder driven by the template's configured
            // qr_code_text field (registration_no / roll_no / name / email / mobile).
            // NOTE: this renders the chosen field as a labeled value box, not a real
            // scannable QR image. Generating an actual QR image needs a package like
            // simplesoftwareio/simple-qrcode — ask if you'd like that added.
            $qrCodeHtml = $this->buildQrCodeHtml($template, $student);

            // Replace all {placeholder} → actual value
            $content = str_replace(
                [
                    // ── Institute placeholders ──
                    '{institute_name}',
                    '{institute_email}',
                    '{institute_mobile}',
                    '{institute_address}',

                    // ── Student placeholders (current) ──
                    '{student_id}',
                    '{name}',
                    '{registration_no}',
                    '{roll}',
                    '{class}',
                    '{section}',
                    '{group}',
                    '{mobile_no}',
                    '{blood}',
                    '{birthday}',
                    '{gender}',
                    '{religion}',
                    '{session}',
                    '{admission_date}',
                    '{issue_date}',

                    // ── Student placeholders (legacy aliases, kept for old templates) ──
                    '{register_no}',
                    '{roll_no}',
                    '{mobileno}',
                    '{email}',

                    // ── Guardian placeholder ──
                    '{father_name}',
                    '{mother_name}',

                    // ── Photo placeholder ──
                    '{photo}',
                    '{student_photo}',

                    // ── Logo / date / QR (used by the 5 ready-made designs) ──
                    '{logo}',
                    '{print_date}',
                    '{qr_code}',
                ],
                [
                    // ── Institute values ──
                    e($institute?->name    ?? ''),
                    e($institute?->email   ?? ''),
                    $institutePhone,
                    e($institute?->address ?? ''),

                    // ── Student values (current) ──
                    e($student->student_id),
                    e($student->name),
                    e($student->registration_no    ?? ''),
                    e($student->roll_no        ?? ''),
                    e($student->class?->name   ?? ''),
                    e($student->section?->name ?? ''),
                    e($student->group?->name   ?? ''),
                    e($student->mobile       ?? ''),
                    e($student->blood_group     ?? ''),
                    e($student->dob ? Carbon::parse($student->dob)->format('d M Y') : ''),
                    e($student->gender        ?? ''),
                    e($student->religion      ?? ''),
                    e($student->academic_year ?? ''),
                    e($student->admission_date
                        ? Carbon::parse($student->admission_date)->format('d M Y') : ''),
                    e(Carbon::parse($this->issue_date)->format('d M Y')),

                    // ── Student values (legacy aliases) ──
                    e($student->registration_no ?? ''),
                    e($student->roll_no         ?? ''),
                    $institutePhone,
                    e($student->email ?? ''),

                    // ── Guardian placeholder (eager-loaded, no N+1) ──
                    e($student->guardians->first()?->father_name ?? ''),
                    e($student->guardians->first()?->mother_name ?? ''),

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
                'student_id'  => $student->id,
                'name'        => $student->name,
                'registration_no' => $student->registration_no,
                'roll_no'     => $student->roll_no,
                'class'       => $student->class?->name,
                'section'     => $student->section?->name,
                'photo'       => $student->photo,
                'issue_date'  => $this->issue_date,
                'content'     => $content,   // fully parsed HTML content
                'template'    => $template,
            ];

        })->toArray();

        $this->showPrintPreview = true;

        activity()
            ->withProperties([
                'template'     => $template->certificate_name,
                'student_count'=> count($this->printCards),
                'issue_date'   => $this->issue_date,
            ])
            ->log('Generated ' . count($this->printCards) . ' certificate(s) using template: ' . $template->certificate_name);
    }

    // ── Helpers ──

    /**
     * {logo} placeholder-এর জন্য inline <img> HTML বানায়। Template-এর নিজস্ব
     * logo_image থাকলে সেটা ব্যবহার হয়, না থাকলে Institution-এর system_logo
     * fallback হিসেবে ব্যবহার হয়। কোনোটাই না থাকলে খালি string (তাহলে জায়গাটা
     * blank থাকবে, কিন্তু raw "{logo}" text আর কখনো print হবে না)।
     */
    private function buildLogoHtml(CertificateTemplate $template, ?Institution $institute): string
    {
        $path = $template->logo_image ?: $institute?->system_logo;

        if (!$path) {
            return '';
        }

        // Certificate template images (logo_image/signature_image/background_image)
        // are stored as public-relative paths — see the asset() usage in the
        // print Blade view. Institution's own system_logo follows the same
        // storage-disk convention as student/employee photos elsewhere in this
        // module, so we resolve it via the storage URL instead.
        $url = $template->logo_image
            ? asset($template->logo_image)
            : asset('storage/' . $institute->system_logo);

        return '<img src="' . $url . '" style="height:56px;object-fit:contain;">';
    }

    /**
     * {qr_code} placeholder resolve করে। Template-এর qr_code_text field
     * (registration_no/roll_no/name/email/mobile) অনুযায়ী student-এর সেই
     * value-টা একটা ছোট labeled box আকারে দেখায়।
     *
     * NOTE: এটা আসল scannable QR image না — শুধু value-টা readable text
     * হিসেবে দেখায়, যাতে raw "{qr_code}" placeholder কখনো print না হয়।
     * সত্যিকারের QR barcode image চাইলে `simplesoftwareio/simple-qrcode`
     * package যোগ করে এটা প্রতিস্থাপন করা যাবে।
     */
    private function buildQrCodeHtml(CertificateTemplate $template, Student $student): string
    {
        $value = match ($template->qr_code_text) {
            'registration_no' => $student->registration_no,
            'roll_no'          => $student->roll_no,
            'name'             => $student->name,
            'email'            => $student->email,
            'mobile'           => $student->mobile,
            default            => $student->registration_no,
        } ?? '';

        if ($value === '') {
            return '';
        }

        return '<div style="display:inline-block;padding:6px 10px;border:1px solid #999;'
            . 'font-size:.7rem;font-family:monospace;letter-spacing:.03em;">'
            . e($value) . '</div>';
    }

    /**
     * Cached per-request student list. Prevents duplicate queries across
     * render(), updatedSelectAll(), updatedSelectedIds(), and the view.
     */
    #[Computed]
    public function students()
    {
        if (!$this->filtered) {
            return collect();
        }

        return Student::query()
            ->with(['class:id,name', 'section:id,name'])
            ->when($this->filterClass, fn ($q) => $q->where('class_id', $this->filterClass))
            ->when(
                $this->filterSection && $this->filterSection !== 'all',
                fn ($q) => $q->where('section_id', $this->filterSection)
            )
            ->orderBy('roll_no')
            ->get();
    }

    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->get();
    }

    public function getAvailableSections()
    {
        // Class select na thakle, ba class-e section na thakle — empty
        if (!$this->filterClass || !$this->filterClassHasSection) {
            return [];
        }

        return AcademicSection::whereIn('id',
            AcademicClassAssign::where('class_id', $this->filterClass)->pluck('section_id')
        )->orderBy('name')->get();
    }

    public function render()
    {
        $templates = CertificateTemplate::where('applicable_user', 'student')
            ->where('is_active', true)
            ->get();

        $sections = $this->getAvailableSections();
        $classes  = $this->getAvailableClasses();

        $selectedTemplate = $this->filterTemplate
            ? CertificateTemplate::find($this->filterTemplate)
            : null;

        return view('livewire.admin.certificate.generate-student-component')
            ->with([
                'templates'        => $templates,
                'students'         => $this->students,
                'sections'         => $sections,
                'classes'          => $classes,
                'selectedTemplate' => $selectedTemplate,
            ])
            ->layout('layouts.branch.app', [
                'title' => 'Generate Student Certificates | ' . institution()->name,
            ]);
    }
}