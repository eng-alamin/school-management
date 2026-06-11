<?php

namespace App\Livewire\Admin\Card;

use Livewire\Component;
use App\Models\IdCardTemplate;
use App\Models\StudentIdCard;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;

class StudentIdCardComponent extends Component
{
    // Filter / Search
    public string $filterClass = '';
    public string $filterSection = '';
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
            'filterClass'    => 'required|string',
            'filterTemplate' => 'required|exists:id_card_templates,id',
        ], [
            'filterClass.required'    => 'Class is required.',
            'filterTemplate.required' => 'Template is required.',
        ]);

        $this->filtered    = true;
        $this->selectedIds = [];
        $this->selectAll   = false;
    }

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

    public function generateCards(): void
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'Please select at least one student.');
            return;
        }

        $this->validate([
            'print_date'  => 'required|date',
            'expiry_date' => 'required|date',
        ]);

        $students = Student::with(['class', 'section', 'category'])
            ->whereIn('id', $this->selectedIds)
            ->get();

        // Grab school_id once — upsert() skips Eloquent model events
        // so the BelongsToSchool trait won't fire automatically.
        $schoolId = auth()->user()->school_id;
        $data     = [];

        foreach ($students as $student) {
            $data[] = [
                'school_id'   => $schoolId,
                'student_id'  => $student->id,

                'issue_date'  => $this->print_date,
                'expiry_date' => $this->expiry_date,
                'template_id' => $this->filterTemplate,

                'name'        => $student->full_name,
                'gender'      => $student->gender,
                'blood_group' => $student->full_blood_group,
                'dob'         => $student->dob,
                'religion'    => $student->religion,
                'mobile'      => $student->mobile,
                'address'     => $student->present_address,
                'photo'       => $student->photo,
                'session'     => $student->academic_year,
                'register_no' => $student->register_no,
                'roll_no'     => $student->roll_no,

                'class'       => $student->class?->name,
                'section'     => $student->section?->name,
                'category'    => $student->category?->name,

                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        StudentIdCard::upsert(
            $data,
            ['student_id'],   // unique key to match on
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
                'address',
                'photo',
                'session',
                'register_no',
                'roll_no',
                'class',
                'section',
                'category',
                'updated_at',
            ]
        );

        $this->printCards = StudentIdCard::with('template')
            ->whereIn('student_id', $this->selectedIds)
            ->get()
            ->toArray();

        $this->showPrintPreview = true;
    }

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
        $templates = IdCardTemplate::where('is_active', true)
            ->where('type', '!=', 'employee')
            ->get();

        $students         = $this->filtered ? $this->getStudents() : collect();
        $sections         = $this->getAvailableSections();
        $classes          = $this->getAvailableClasses();
        $selectedTemplate = $this->filterTemplate
            ? IdCardTemplate::find($this->filterTemplate)
            : null;

        return view('livewire.admin.card.student-id-card-component')
            ->with('templates', $templates)
            ->with('students', $students)
            ->with('sections', $sections)
            ->with('classes', $classes)
            ->with('selectedTemplate', $selectedTemplate)
            ->layout('layouts.admin.app', [
                'title' => 'Student ID Cards | School SaaS',
            ]);
    }
}