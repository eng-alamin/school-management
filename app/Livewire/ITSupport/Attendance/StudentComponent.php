<?php

namespace App\Livewire\ITSupport\Attendance;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentComponent extends Component
{
    public $filterClass   = '';
    public $filterSection = '';
    public $filterDate;

    public bool $selectedClassHasSection = true;
    
    public array $availableSections = [];

    public $data          = [];
    public $hasAttendance = false;

    public function mount()
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    // ✅ Fix: updatedClassId() -> updatedFilterClass() (Livewire property
    // hook naming filterClass property-r sathe match korte hobe).
    // Purono updatedFilterClass()-er logic + notun has_section logic
    // ekhane merge kora holo — duita alada method rakha jabe na
    public function updatedFilterClass(): void
    {
        $this->filterSection     = '';
        $this->availableSections = [];
        $this->data              = [];
        $this->hasAttendance     = false;

        if (!$this->filterClass) {
            $this->selectedClassHasSection = true;
            return;
        }

        $institutionId = institution()->id;

        $class = AcademicClass::where('institution_id', $institutionId)
            ->find($this->filterClass);

        $this->selectedClassHasSection = $class ? (bool) $class->has_section : true;

        if ($this->selectedClassHasSection) {
            $this->availableSections = AcademicSection::where('institution_id', $institutionId)
                ->whereIn('id',
                    AcademicClassAssign::where('institution_id', $institutionId)
                        ->where('class_id', $this->filterClass)
                        ->whereNotNull('section_id')
                        ->pluck('section_id')
                )
                ->orderBy('name')
                ->get()
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
                ->toArray();
        }
    }

    public function getAvailableClasses()
    {
        $institutionId = institution()->id;

        return AcademicClass::where('institution_id', $institutionId)
            ->whereIn('id', AcademicClassAssign::where('institution_id', $institutionId)->distinct()->pluck('class_id'))
            ->orderBy('id')
            ->get();
    }

    public function updatedFilterSection()
    {
        $this->data          = [];
        $this->hasAttendance = false;

        if (!$this->filterClass) return;
    }


    public function filter()
    {
        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }

        if ($this->selectedClassHasSection && !$this->filterSection) {
            $this->dispatch('toast', type: 'error', message: 'Please select a section.');
            return;
        }

        $institutionId = institution()->id;

        $studentsQuery = Student::where('institution_id', $institutionId)
            ->where('class_id', $this->filterClass)
            ->orderBy('section_id')
            ->orderBy('roll_no');

        if ($this->filterSection && $this->filterSection !== 'all') {
            $studentsQuery->where('section_id', $this->filterSection);
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No students found.');
            $this->hasAttendance = false;
            return;
        }

        $sectionNames = AcademicSection::where('institution_id', $institutionId)
            ->whereIn('id', $students->pluck('section_id')->unique())
            ->pluck('name', 'id');

        $existingQuery = Attendance::where('institution_id', $institutionId)
            ->where('type', 'student')
            ->where('date', $this->filterDate)
            ->where('class_id', $this->filterClass);

        if ($this->filterSection && $this->filterSection !== 'all') {
            $existingQuery->where('section_id', $this->filterSection);
        }

        // ── FIX: attendable_id (students.id, integer PK) diye key kora hocche,
        // age student_id (string code) diye key hoyeche bole mismatch hoto ──
        $existing = $existingQuery->get()->keyBy('attendable_id');

        $this->data = $students->map(function ($student) use ($existing, $sectionNames) {
            $att = $existing[$student->id] ?? null;

            return [
                'id'           => $student->id,
                'student_id'   => $student->student_id,
                'section_id'   => $student->section_id,
                // ✅ Fix: section_name khali thakle 'All Section' na, '—' dekhano hocche
                'section_name' => $sectionNames[$student->section_id] ?? '',
                'photo'        => $student->photo,
                'name'         => $student->name,
                'roll_no'      => $student->roll_no,
                'status'       => $att->status ?? 'present',
                'remarks'      => $att->remarks ?? '',
            ];
        })->toArray();

        $this->hasAttendance = true;
    }

    public function save()
    {
        $this->validate([
            'filterClass' => 'required',
            'filterDate'  => 'required|date',
        ]);

        $institutionId = institution()->id;

        DB::beginTransaction();
        try {
            foreach ($this->data as $item) {
                Attendance::updateOrCreate(
                    [
                        // ── FIX: student_id (string code) na diye students.id
                        // (integer PK) pathano hocche - main bug fix ──
                        'attendable_id'   => $item['id'],
                        'attendable_type' => Student::class,
                        'date'            => $this->filterDate,
                        'type'            => 'student',
                        'class_id'        => $this->filterClass,
                        'section_id'      => $item['section_id'],
                    ],
                    [
                        // ✅ Fix: institution_id explicit set kora holo
                        'institution_id' => $institutionId,
                        'status'         => $item['status'],
                        'remarks'        => $item['remarks'],
                    ]
                );
            }

            DB::commit();
            $this->dispatch('toast', type: 'success', message: 'Attendance saved successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Student attendance save failed: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function resetForm()
    {
        $this->filterClass             = '';
        $this->filterSection           = '';
        $this->filterDate              = now()->format('Y-m-d');
        $this->selectedClassHasSection = true;
        $this->availableSections       = [];
        $this->data                    = [];
        $this->hasAttendance           = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.attendance.student-component')
            ->with('classes', $this->getAvailableClasses())
            ->layout('layouts.itsupport.app', [
                'title' => 'Student Attendance | ' . institution()->name,
            ]);
    }
}