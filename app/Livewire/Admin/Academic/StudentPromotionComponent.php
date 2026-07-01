<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;
use App\Models\AcademicSession;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentPromotion;
use Illuminate\Support\Facades\DB;

class StudentPromotionComponent extends Component
{
    // Filter (Select Ground)
    public string $class_id   = '';
    public string $section_id = '';

    // Promotion settings
    public bool   $hasStudents      = false;
    public bool   $carryForwardDue  = false;
    public string $to_session_id    = '';
    public string $to_class_id      = '';
    public string $to_section_id    = '';

    // Students table
    public array $students         = [];
    public array $selectedStudents = [];
    public bool  $selectAll        = false;

    public function updatedClassId(): void
    {
        $this->section_id  = '';
        $this->hasStudents = false;
        $this->students    = [];
    }

    public function updatedToClassId(): void
    {
        $this->to_section_id = '';
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedStudents = $value ? array_keys($this->students) : [];
    }

    // ── শুধু সেই Class গুলো, যেগুলোর জন্য AcademicClassAssign তৈরি করা আছে ──
    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    // ── নির্বাচিত Class এর জন্য যেসব Section Assign করা আছে ──
    public function getAvailableSections(?string $classId)
    {
        if (!$classId) {
            return collect();
        }

        return AcademicSection::whereIn('id',
            AcademicClassAssign::where('class_id', $classId)
                ->whereNotNull('section_id')
                ->pluck('section_id')
        )->orderBy('name')->get();
    }

    public function filter(): void
    {
        $this->validate([
            'class_id'   => 'required|exists:academic_classes,id',
            'section_id' => 'nullable', // section এখন optional
        ]);

        $sectionId = ($this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        $students = Student::where('class_id', $this->class_id)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->orderBy('roll_no')
            ->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'এই Class/Section এ কোনো Student পাওয়া যায়নি।');
            return;
        }

        $this->students = [];
        foreach ($students as $student) {
            $this->students[$student->id] = [
                'student_id'     => $student->id,
                'name'           => $student->name,
                'registration_no' => $student->registration_no,
                'guardian_name'  => $student->guardians->first()?->name ?? '—',
                'roll'           => $student->roll_no ?? '',
                'status'         => 'running',
                'due_amount'     => 0,
                'is_alumni'      => false,

                // ── প্রতিটা student এর নিজস্ব আসল class/section সংরক্ষণ করা হচ্ছে ──
                // কারণ Section optional হওয়ায় একাধিক section এর student একসাথে আসতে পারে,
                // filter এর common class_id/section_id ব্যবহার করলে ভুল data সেভ হবে
                'original_class_id'   => $student->class_id,
                'original_section_id' => $student->section_id,
            ];
        }

        $this->selectedStudents = array_keys($this->students);
        $this->selectAll        = true;
        $this->hasStudents      = true;
    }

    public function promote(): void
    {
        $this->validate([
            'to_session_id' => 'required|exists:academic_sessions,id',
            'to_class_id'   => 'required|exists:academic_classes,id',
            'to_section_id' => 'nullable', // section এখন optional
        ]);

        if (empty($this->selectedStudents)) {
            $this->dispatch('toast', type: 'error', message: 'No students selected!');
            return;
        }

        $toSectionId = ($this->to_section_id && $this->to_section_id !== 'all')
            ? $this->to_section_id
            : null;

        $activeSession = AcademicSession::where('is_current', true)->first();

        DB::beginTransaction();
        try {
            foreach ($this->selectedStudents as $studentId) {
                $row = $this->students[$studentId] ?? null;
                if (!$row) {
                    continue;
                }

                $student = Student::find($studentId);
                if (!$student) {
                    continue;
                }

                $fromClassId   = $row['original_class_id'];
                $fromSectionId = $row['original_section_id'];
                $fromRoll      = $student->roll_no;

                if ($row['is_alumni']) {

                    // ===== ALUMNI =====
                    $student->update([
                        'session_id' => $this->to_session_id,
                    ]);

                    StudentEnrollment::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'session_id' => $this->to_session_id,
                            'class_id'   => $fromClassId,
                            'section_id' => $fromSectionId,
                        ],
                        [
                            'roll'              => $row['roll'],
                            'status'            => 'alumni',
                            'carry_forward_due' => $this->carryForwardDue,
                        ]
                    );

                } elseif ($row['status'] === 'running') {

                    // ===== RUNNING (same class/section, শুধু session পরিবর্তন) =====
                    $student->update([
                        'session_id' => $this->to_session_id,
                    ]);

                    StudentEnrollment::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'session_id' => $this->to_session_id,
                            'class_id'   => $fromClassId,
                            'section_id' => $fromSectionId,
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
                        'session_id' => $this->to_session_id,
                        'class_id'   => $this->to_class_id,
                        'section_id' => $toSectionId,
                        'roll_no'    => $row['roll'] ?: $student->roll_no,
                    ]);

                    StudentEnrollment::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'session_id' => $this->to_session_id,
                            'class_id'   => $this->to_class_id,
                            'section_id' => $toSectionId,
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
                    'from_class_id'     => $fromClassId,
                    'to_class_id'       => $this->to_class_id,
                    'from_section_id'   => $fromSectionId,
                    'to_section_id'     => $toSectionId,
                    'from_roll'         => $fromRoll,
                    'to_roll'           => $row['roll'],
                    'carry_forward_due' => $this->carryForwardDue,
                    'is_alumni'         => $row['is_alumni'],
                    'promoted_by'       => auth()->id(),
                    'promoted_at'       => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            return;
        }

        $this->dispatch('toast', type: 'success', message: 'Students promoted successfully!');
        $this->hasStudents      = false;
        $this->students         = [];
        $this->selectedStudents = [];
        $this->selectAll        = false;
    }

    public function render()
    {
        $sessions = AcademicSession::orderBy('name')->get();

        return view('livewire.admin.academic.student-promotion-component')
            ->with('classes', $this->getAvailableClasses())
            ->with('availableSections', $this->getAvailableSections($this->class_id))
            ->with('toAvailableSections', $this->getAvailableSections($this->to_class_id))
            ->with('sessions', $sessions)
            ->layout('layouts.admin.app', [
                'title' => 'Student Promotion | ' . institution()->name,
            ]);
    }
}