<?php

namespace App\Livewire\Admin\Card;

use Livewire\Component;
use App\Models\IdCardTemplate;
use App\Models\StudentIdCard;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;

class StudentIdCardComponent extends Component
{
    // ── Filter ──
    public string $filterClass    = '';
    public string $filterSection  = '';
    public ?int   $filterTemplate = null;
    public bool   $hasFiltered    = false;

    // ── Date fields ──
    public string $print_date  = '';
    public string $expiry_date = '';

    // ── Selection ──
    public array $selectedIds = [];
    public bool  $selectAll   = false;

    // ── Print Preview ──
    public bool  $showPrintPreview = false;
    public array $printCards       = [];

    public function mount(): void
    {
        $this->print_date  = now()->format('Y-m-d');
        $this->expiry_date = now()->addYear()->format('Y-m-d');
    }

    // ── Available Classes ──
    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    // ── Available Sections ──
    public function getAvailableSections()
    {
        if (!$this->filterClass) return collect();

        return AcademicSection::whereIn('id',
            AcademicClassAssign::where('class_id', $this->filterClass)->pluck('section_id')
        )->orderBy('name')->get();
    }

    // ── Class changed ──
    public function updatedFilterClass(): void
    {
        $this->filterSection = '';
        $this->selectedIds   = [];
        $this->selectAll     = false;
        $this->hasFiltered   = false;
    }

    // ── Section changed ──
    public function updatedFilterSection(): void
    {
        $this->selectedIds = [];
        $this->selectAll   = false;
        $this->hasFiltered = false;
    }

    // ── Select All toggle ──
    public function updatedSelectAll(bool $value): void
    {
        $this->selectedIds = $value
            ? $this->getStudents()->pluck('id')->map(fn($id) => (string) $id)->toArray()
            : [];
    }

    // ── Individual checkbox ──
    public function updatedSelectedIds(): void
    {
        $total           = $this->getStudents()->count();
        $this->selectAll = $total > 0 && count($this->selectedIds) === $total;
    }

    // ── Filter ──
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

        $students = $this->getStudents();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No students found for selected class/section.');
            $this->hasFiltered = false;
            return;
        }

        $this->hasFiltered = true;
        $this->selectedIds = [];
        $this->selectAll   = false;
    }

    // ── Reset ──
    public function resetFilter(): void
    {
        $this->filterClass    = '';
        $this->filterSection  = '';
        $this->filterTemplate = null;
        $this->hasFiltered    = false;
        $this->selectedIds    = [];
        $this->selectAll      = false;
        $this->resetValidation();
    }

    // ── Generate ID Cards ──
    public function generateCards(): void
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('toast', type: 'error', message: 'Please select at least one student.');
            return;
        }

        $this->validate([
            'print_date'  => 'required|date',
            'expiry_date' => 'required|date',
        ]);

        $students = Student::with(['class', 'section', 'group'])
            ->whereIn('id', $this->selectedIds)
            ->get();

        $institutionId = auth()->user()->institution_id;
        $data     = [];

        foreach ($students as $student) {
            $data[] = [
                'institution_id'   => $institutionId,
                'student_id'  => $student->id,
                'issue_date'  => $this->print_date,
                'expiry_date' => $this->expiry_date,
                'template_id' => $this->filterTemplate,
                'name'        => $student->name,
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
                'group'    => $student->group?->name,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        StudentIdCard::upsert(
            $data,
            ['student_id'],
            [
                'institution_id','issue_date', 'expiry_date', 'template_id',
                'name', 'gender', 'blood_group', 'dob', 'religion',
                'mobile', 'address', 'photo', 'session',
                'register_no', 'roll_no', 'class', 'section', 'group',
                'updated_at',
            ]
        );

        $this->printCards = StudentIdCard::with('template')
            ->whereIn('student_id', $this->selectedIds)
            ->get()
            ->toArray();

        $this->showPrintPreview = true;
    }

    // ── Get Students (internal) ──
    private function getStudents()
    {
        if (!$this->filterClass) return collect();

        return Student::with(['class', 'section', 'group'])
            ->where('class_id', $this->filterClass)
            ->when(
                $this->filterSection && $this->filterSection !== 'all',
                fn($q) => $q->where('section_id', $this->filterSection)
            )
            ->orderBy('section_id')
            ->orderBy('roll_no')
            ->get();
    }

    public function render()
    {
        $students         = $this->hasFiltered ? $this->getStudents() : collect();
        $selectedTemplate = $this->filterTemplate
            ? IdCardTemplate::find($this->filterTemplate)
            : null;

        return view('livewire.admin.card.student-id-card-component')
            ->with([
                'classes'          => $this->getAvailableClasses(),
                'sections'         => $this->getAvailableSections(),
                'templates'        => IdCardTemplate::where('is_active', true)->where('type', '!=', 'employee')->get(),
                'students'         => $students,
                'selectedTemplate' => $selectedTemplate,
            ])
            ->layout('layouts.admin.app', [
                'title' => 'Student ID Cards | ' . institution()->name,
            ]);
    }
}