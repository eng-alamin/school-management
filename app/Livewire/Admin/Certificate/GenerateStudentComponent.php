<?php

namespace App\Livewire\Admin\Certificate;

use Livewire\Component;
use App\Models\CertificateTemplate;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\SettingSchool;
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
        $this->validate([
            'filterClass'    => 'required',
            'filterTemplate' => 'required|exists:certificate_templates,id',
        ], [
            'filterClass.required'    => 'Please select a class.',
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
        $this->filterClass    = '';
        $this->filterSection  = '';
        $this->filterTemplate = null;
        $this->selectedIds    = [];
        $this->selectAll      = false;
        $this->resetValidation();
    }

    public function updatedFilterClass(): void
    {
        $this->filterSection = '';
        $this->selectedIds   = [];
        $this->selectAll     = false;
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIds = $this->getStudents()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds(): void
    {
        $total           = $this->getStudents()->count();
        $this->selectAll = count($this->selectedIds) === $total && $total > 0;
    }

    // ── Generate Certificates ──
    public function generateCertificates(): void
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Please select at least one student.');
            return;
        }

        $this->validate([
            'issue_date' => 'required|date',
        ]);

        $template  = CertificateTemplate::findOrFail($this->filterTemplate);
        $institute = SettingSchool::first(); // ← institute info

        $students = Student::with(['class', 'section', 'category'])
            ->whereIn('id', $this->selectedIds)
            ->get();

        $this->printCards = $students->map(function ($student) use ($template, $institute) {

            $content = $template->certificate_content;

            // Student photo HTML
            $photoHtml = $student->photo
                ? '<img src="' . asset($student->photo) . '"
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
                    '{name}',
                    '{register_no}',
                    '{roll}',
                    '{class}',
                    '{section}',
                    '{category}',
                    '{mobile_no}',
                    '{blood_group}',
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
                    '{student_photo}',
                ],
                [
                    // ── Institute values ──
                    $institute?->name  ?? '',
                    $institute?->email ?? '',
                    $institute?->mobile ?? '',
                    $institute?->address ?? '',

                    // ── Student values ──
                    $student->name,
                    $student->register_no   ?? '',
                    $student->roll_no       ?? '',
                    $student->class?->name  ?? '',
                    $student->section?->name ?? '',
                    $student->category?->name ?? '',
                    $student->mobile_no        ?? '',
                    $student->blood_group ?? '',
                    $student->dob ? Carbon::parse($student->dob)->format('d M Y') : '',
                    $student->gender        ?? '',
                    $student->religion      ?? '',
                    $student->academic_year ?? '',
                    $student->admission_date
                        ? Carbon::parse($student->admission_date)->format('d M Y') : '',
                    Carbon::parse($this->issue_date)->format('d M Y'),

                    // ── Guardian placeholder ──
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
                'register_no' => $student->register_no,
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
    }

    // ── Helpers ──
    private function getStudents()
    {
        if (!$this->filtered) return collect();

        return Student::query()
            ->when($this->filterClass, fn($q) => $q->where('class_id', $this->filterClass))
            ->when(
                $this->filterSection && $this->filterSection !== 'all',
                fn($q) => $q->where('section_id', $this->filterSection)
            )
            ->orderBy('roll_no')
            ->get();
    }

    public function getAvailableClasses(): array
    {
        return AcademicClass::whereIn('id', Student::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function getAvailableSections(): array
    {
        if (!$this->filterClass) return [];

        $sectionIds = Student::where('class_id', $this->filterClass)
            ->whereNotNull('section_id')
            ->distinct()
            ->pluck('section_id');

        return AcademicSection::whereIn('id', $sectionIds)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function render()
    {
        $templates = CertificateTemplate::where('applicable_user', 'student')
            ->where('is_active', true)
            ->get();

        $students = $this->filtered ? $this->getStudents() : collect();
        $sections = $this->getAvailableSections();
        $classes  = $this->getAvailableClasses();

        $selectedTemplate = $this->filterTemplate
            ? CertificateTemplate::find($this->filterTemplate)
            : null;

        return view('livewire.admin.certificate.generate-student-component')
            ->with(compact('templates', 'students', 'sections', 'classes', 'selectedTemplate'))
            ->layout('layouts.admin.app', [
                'title' => 'Generate Student Certificates | ' . institution()->name,
            ]);
    }
}