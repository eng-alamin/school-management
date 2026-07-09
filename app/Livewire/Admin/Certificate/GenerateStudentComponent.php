<?php

namespace App\Livewire\Admin\Certificate;

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

        $this->filtered    = true;
        $this->selectedIds = [];
        $this->selectAll   = false;
        unset($this->students);
    }

    // ── Reset Filter ──
    public function resetFilter(): void
    {
        $this->filtered       = false;
        $this->filterClass    = '';
        $this->filterSection  = '';
        $this->filterTemplate = null;
        $this->selectedIds    = [];
        $this->selectAll      = false;
        $this->resetValidation();
        unset($this->students);
    }

    public function updatedFilterClass(): void
    {
        $this->filterSection = '';
        $this->selectedIds   = [];
        $this->selectAll     = false;
        unset($this->students);
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

            // Replace all {placeholder} → actual value
            $content = str_replace(
                [
                    // ── Institute placeholders ──
                    '{institute_name}',
                    '{institute_email}',
                    '{institute_mobile}',
                    '{institute_address}',

                    // ── Student placeholders ──
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

                    // ── Guardian placeholder ──
                    '{father_name}',
                    '{mother_name}',

                    // ── Photo placeholder ──
                    '{photo}',
                    '{student_photo}',
                ],
                [
                    // ── Institute values ──
                    $institute?->name    ?? '',
                    $institute?->email   ?? '',
                    $institute?->phone  ?? '',
                    $institute?->address ?? '',

                    // ── Student values ──
                    $student->student_id,
                    $student->name,
                    $student->registration_no    ?? '',
                    $student->roll_no        ?? '',
                    $student->class?->name   ?? '',
                    $student->section?->name ?? '',
                    $student->group?->name   ?? '',
                    $student->mobile       ?? '',
                    $student->blood_group     ?? '',
                    $student->dob ? Carbon::parse($student->dob)->format('d M Y') : '',
                    $student->gender        ?? '',
                    $student->religion      ?? '',
                    $student->academic_year ?? '',
                    $student->admission_date
                        ? Carbon::parse($student->admission_date)->format('d M Y') : '',
                    Carbon::parse($this->issue_date)->format('d M Y'),

                    // ── Guardian placeholder (now eager-loaded, no N+1) ──
                    $student->guardians->first()?->father_name ?? '',
                    $student->guardians->first()?->mother_name ?? '',

                    // ── Photo as inline img ──
                    $photoHtml,
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
            ->orderBy('name')
            ->get();
    }

    public function getAvailableSections()
    {
        if (!$this->filterClass) {
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
            ->layout('layouts.admin.app', [
                'title' => 'Generate Student Certificates | ' . institution()->name,
            ]);
    }
}