<?php

namespace App\Livewire\Teacher\Academic;

use Livewire\Component;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\StudentEnrollment;
use App\Models\StudentPromotion;

class StudentPromotionComponent extends Component
{
    // Filter
    public string $class_id = '';
    public string $section_id = '';
    public array $availableSections = [];

    // Promotion settings
    public bool $hasStudents = false;
    public bool $carryForwardDue = false;
    public string $to_session_id = '';
    public string $to_class_id = '';
    public string $to_section_id = '';
    public array $toAvailableSections = [];

    // Students table
    public array $students = [];
    public array $selectedStudents = [];
    public bool $selectAll = false;

    public function updatedClassId(string $value): void
    {
        $this->section_id        = '';
        $this->availableSections = [];
        $this->hasStudents       = false;
        $this->students          = [];

        if ($value) {
            $class = AcademicClass::with('sections')->find($value);
            if ($class) {
                $this->availableSections = $class->sections
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->toArray();
            }
        }
    }

    public function updatedToClassId(string $value): void
    {
        $this->to_section_id       = '';
        $this->toAvailableSections = [];

        if ($value) {
            $class = AcademicClass::with('sections')->find($value);
            if ($class) {
                $this->toAvailableSections = $class->sections
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->toArray();
            }
        }
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedStudents = $value
            ? array_keys($this->students)
            : [];
    }

    public function filter(): void
    {
        $this->validate([
            'class_id'   => 'required|exists:academic_classes,id',
            'section_id' => 'required|exists:academic_sections,id',
        ]);

        $students = \App\Models\Student::where('class_id', $this->class_id)
            ->where('section_id', $this->section_id)
            ->get();

        $this->students = [];
        foreach ($students as $student) {
            $this->students[$student->id] = [
                'student_id'    => $student->id,
                'name'          => $student->name,
                'register_no'   => $student->register_no,
                'guardian_name' => $student->guardians->first()?->name ?? '—' ,
                'roll'          => $student->roll_no ?? '',
                'status'        => 'running',
                'due_amount'    => 0,
                'is_alumni'     => false,
            ];
        }

        $this->selectedStudents = array_keys($this->students);
        $this->selectAll        = true;
        $this->hasStudents      = true;
    }

    public function updatedStudents(mixed $value, string $key): void
    {
        // students.{enrollmentId}.roll or status update হলে sync
    }

    public function promote()
    {
        $this->validate([
            'to_session_id' => 'required|exists:academic_sessions,id',
            'to_class_id'   => 'required|exists:academic_classes,id',
            'to_section_id' => 'required|exists:academic_sections,id',
        ]);

        if (empty($this->selectedStudents)) {
            $this->dispatch('toast', type: 'error', message: 'No students selected!');
            return;
        }

        $activeSession = AcademicSession::where('is_current', true)->first();

        foreach ($this->selectedStudents as $studentId) {
            $row = $this->students[$studentId] ?? null;
            if (!$row) continue;

            $student = \App\Models\Student::find($studentId);
            if (!$student) continue;

            if ($row['is_alumni']) {

                // ===== ALUMNI =====
                $student->update([
                    'session_id'    => $this->to_session_id,
                ]);

                StudentEnrollment::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'session_id' => $this->to_session_id,
                        'class_id'   => $this->class_id,
                        'section_id' => $this->section_id,
                    ],
                    [
                        'roll'              => $row['roll'],
                        'status'            => 'alumni',
                        'carry_forward_due' => $this->carryForwardDue,
                    ]
                );

            } elseif ($row['status'] === 'running') {

                // ===== RUNNING =====
                $student->update([
                    'session_id'    => $this->to_session_id,
                ]);

                StudentEnrollment::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'session_id' => $this->to_session_id,
                        'class_id'   => $this->class_id,
                        'section_id' => $this->section_id,
                    ],
                    [
                        'roll'              => $row['roll'],
                        'status'            => 'running',
                        'carry_forward_due' => $this->carryForwardDue,
                    ]
                );

            } else {

                // ===== PROMOTED =====
                $student->update([
                    'session_id'    => $this->to_session_id,
                    'class_id'      => $this->to_class_id,
                    'section_id'    => $this->to_section_id,
                    'roll_no'       => $row['roll'] ?: $student->roll_no,
                ]);

                StudentEnrollment::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'session_id' => $this->to_session_id,
                        'class_id'   => $this->to_class_id,
                        'section_id' => $this->to_section_id,
                    ],
                    [
                        'roll'              => $row['roll'],
                        'status'            => 'promoted',
                        'carry_forward_due' => $this->carryForwardDue,
                    ]
                );
            }

            // ===== PROMOTION HISTORY LOG =====
            StudentPromotion::create([
                'student_id'        => $studentId,
                'from_session_id'   => $activeSession?->id,
                'to_session_id'     => $this->to_session_id,
                'from_class_id'     => $this->class_id,
                'to_class_id'       => $this->to_class_id,
                'from_section_id'   => $this->section_id,
                'to_section_id'     => $this->to_section_id,
                'from_roll'         => $student->roll_no,
                'to_roll'           => $row['roll'],
                'carry_forward_due' => $this->carryForwardDue,
                'is_alumni'         => $row['is_alumni'],
                'promoted_by'       => auth()->id(),
                'promoted_at'       => now(),
            ]);
        }

        $this->dispatch('toast', type: 'success', message: 'Students promoted successfully!');
        $this->hasStudents      = false;
        $this->students         = [];
        $this->selectedStudents = [];
        $this->selectAll        = false;
    }

    public function render()
    {
        $classes  = AcademicClass::orderBy('id')->get();
        $sessions = AcademicSession::orderBy('name')->get();

        return view('livewire.teacher.academic.student-promotion-component')
            ->with('classes', $classes)
            ->with('sessions', $sessions)
            ->layout('layouts.teacher.app', [
                'title' => 'Student Promotion | ' . institution()->name,
            ]);
    }
}