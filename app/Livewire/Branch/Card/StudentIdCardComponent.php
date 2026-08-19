<?php

namespace App\Livewire\Branch\Card;

use Livewire\Component;
use App\Models\IdCardTemplate;
use App\Models\StudentIdCard;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;
use Illuminate\Support\Facades\DB;
use Throwable;

class StudentIdCardComponent extends Component
{
    // ── Filter ──
    public string $filterClass    = '';
    public string $filterSection  = '';
    public ?int   $filterTemplate = null;
    public bool   $hasFiltered    = false;

    // ── Class-level section-support flag (academic_classes.has_section) ──
    public bool $classHasSection = true;

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
        $institutionId = auth()->user()->institution_id;

        return AcademicClass::where('institution_id', $institutionId)
            ->whereIn(
                'id',
                AcademicClassAssign::where('institution_id', $institutionId)->select('class_id')->distinct()
            )
            ->get();
    }

    /**
     * Returns the valid sections for the selected class per the static
     * academic_class_sections mapping. Returns an empty collection when
     * the class has has_section = false, so the "All Section" / section
     * dropdown correctly disappears for section-less classes.
     */
    public function getAvailableSections()
    {
        if (!$this->filterClass || !$this->classHasSection) {
            return collect();
        }

        $institutionId = auth()->user()->institution_id;

        $class = AcademicClass::with('sections')
            ->where('institution_id', $institutionId)
            ->find($this->filterClass);

        if (!$class || !$class->has_section) {
            return collect();
        }

        return $class->sections->sortBy('name')->values();
    }

    /**
     * Resolves academic_classes.has_section for a given class id, scoped to
     * the current institution. Defaults to true when the class can't be
     * found, to avoid silently widening the student query.
     */
    private function resolveClassHasSection(?string $classId): bool
    {
        if (!$classId) {
            return true;
        }

        $institutionId = auth()->user()->institution_id;
        $class = AcademicClass::where('institution_id', $institutionId)->find($classId);

        return $class ? (bool) $class->has_section : true;
    }

    // ── Class changed ──
    public function updatedFilterClass(): void
    {
        $this->filterSection    = '';
        $this->selectedIds      = [];
        $this->selectAll        = false;
        $this->hasFiltered      = false;
        $this->classHasSection  = $this->resolveClassHasSection($this->filterClass);
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
            ? $this->getStudents()->pluck('student_id')->map(fn($sid) => (string) $sid)->toArray()
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
        $this->filterClass      = '';
        $this->filterSection    = '';
        $this->filterTemplate   = null;
        $this->hasFiltered      = false;
        $this->selectedIds      = [];
        $this->selectAll        = false;
        $this->classHasSection  = true;
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
            'expiry_date' => 'required|date|after_or_equal:print_date',
        ]);

        $institutionId = auth()->user()->institution_id;

        $students = Student::with(['class', 'section', 'group'])
            ->where('institution_id', $institutionId)
            ->whereIn('student_id', $this->selectedIds)
            ->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'Selected students could not be found.');
            return;
        }

        $institution   = institution();

        DB::beginTransaction();
        try {
            $cards = collect();

            foreach ($students as $student) {
                $payload = [
                    'institution_id'     => $institutionId,
                    'template_id'        => $this->filterTemplate,
                    'institute_name'     => $institution->name,
                    'institute_address'  => $institution->address ?? null,
                    'issue_date'         => $this->print_date,
                    'expiry_date'        => $this->expiry_date,
                    'name'               => $student->name,
                    'father_name'        => $student->father_name ?? null,
                    'mother_name'        => $student->mother_name ?? null,
                    'gender'             => $student->gender,
                    'blood_group'        => $student->full_blood_group,
                    'dob'                => $student->dob,
                    'religion'           => $student->religion,
                    'mobile'             => $student->mobile,
                    'email'              => $student->email ?? null,
                    'address'            => $student->present_address,
                    'photo'              => $student->photo,
                    'session'            => $student->academic_year,
                    'register_no'        => $student->register_no,
                    'roll_no'            => $student->roll_no,
                    'class'              => $student->class?->name,
                    'section'            => $student->section?->name,
                    'group'              => $student->group?->name,
                ];

                $card = StudentIdCard::withTrashed()
                    ->where('institution_id', $institutionId)
                    ->where('student_id', $student->student_id)
                    ->first();

                if ($card) {
                    $card->fill($payload);
                    if ($card->trashed()) {
                        $card->restore();
                    }
                    $card->save();
                } else {
                    $card = StudentIdCard::create(array_merge($payload, [
                        'student_id' => $student->student_id,
                    ]));
                }

                activity()
                    ->performedOn($card)
                    ->causedBy(auth()->user())
                    ->log('Generated/updated ID card for student: '.$student->name);

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

    // ── Get Students (internal) ──
    private function getStudents()
    {
        if (!$this->filterClass) return collect();

        $institutionId = auth()->user()->institution_id;

        // ── Class-e section support na thakle, section filter kokhono
        // apply kora jabe na — even if a stray filterSection value exists ──
        $sectionFilterActive = $this->classHasSection
            && $this->filterSection
            && $this->filterSection !== 'all';

        return Student::with(['class', 'section', 'group'])
            ->where('institution_id', $institutionId)
            ->where('class_id', $this->filterClass)
            ->when(
                $sectionFilterActive,
                fn($q) => $q->where('section_id', $this->filterSection)
            )
            ->orderBy('section_id')
            ->orderBy('roll_no')
            ->get();
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;

        $students         = $this->hasFiltered ? $this->getStudents() : collect();
        $selectedTemplate = $this->filterTemplate
            ? IdCardTemplate::where('institution_id', $institutionId)->find($this->filterTemplate)
            : null;

        return view('livewire.admin.card.student-id-card-component')
            ->with([
                'classes'          => $this->getAvailableClasses(),
                'sections'         => $this->getAvailableSections(),
                'templates'        => IdCardTemplate::where('institution_id', $institutionId)
                                        ->where('is_active', true)
                                        ->where('type', '!=', 'employee')
                                        ->get(),
                'students'         => $students,
                'selectedTemplate' => $selectedTemplate,
            ])
            ->layout('layouts.branch.app', [
                'title' => 'Student ID Cards | ' . institution()->name,
            ]);
    }
}