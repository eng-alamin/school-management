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
use Illuminate\Validation\Rule;

class StudentPromotionComponent extends Component
{
    // Filter (Select Ground)
    public string $class_id   = '';
    public string $section_id = '';

    // ── Class-level section-support flags (academic_classes.has_section).
    // Two independent flags because promotion involves both a source class
    // (class_id) and a destination class (to_class_id), which may differ in
    // whether they support sections at all.
    public bool $classHasSection   = true;
    public bool $toClassHasSection = true;

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
        $this->classHasSection = $this->resolveClassHasSection($this->class_id);
    }

    public function updatedToClassId(): void
    {
        $this->to_section_id = '';
        $this->toClassHasSection = $this->resolveClassHasSection($this->to_class_id);
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedStudents = $value ? array_keys($this->students) : [];
    }

    public function getAvailableClasses()
    {
        $institutionId = auth()->user()->institution_id;

        return AcademicClass::where('institution_id', $institutionId)
            ->whereIn('id', AcademicClassAssign::where('institution_id', $institutionId)->distinct()->pluck('class_id'))
            ->orderBy('id')
            ->get();
    }

    /**
     * Resolves academic_classes.has_section for a given class id, scoped to
     * the current institution. Defaults to true (section required) when the
     * class can't be found, to avoid silently dropping a section requirement.
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

    /**
     * Returns the valid sections for a class per the static
     * academic_class_sections mapping — NOT the session-wise
     * academic_class_assigns table. Returns an empty collection when the
     * class has has_section = false, so the "All Section" / section
     * dropdown correctly disappears for section-less classes.
     */
    public function getAvailableSections(?string $classId)
    {
        if (!$classId) {
            return collect();
        }

        $institutionId = auth()->user()->institution_id;

        $class = AcademicClass::with('sections')
            ->where('institution_id', $institutionId)
            ->find($classId);

        if (!$class || !$class->has_section) {
            return collect();
        }

        return $class->sections->sortBy('name')->values();
    }

    public function filter(): void
    {
        $this->validate([
            'class_id'   => 'required|exists:academic_classes,id',
            'section_id' => [
                Rule::requiredIf($this->classHasSection),
                'nullable',
            ],
        ]);

        // Class-e section na thakle, section_id kokhono query-te use kora jabe na
        $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        $students = Student::where('class_id', $this->class_id)
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->orderBy('roll_no')
            ->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No students found in this class/section.');
            return;
        }

        $this->students = [];
        foreach ($students as $student) {
            $this->students[$student->id] = [
                'student_id'       => $student->id,
                'name'             => $student->name,
                'registration_no'  => $student->registration_no,
                'guardian_name'    => $student->guardians->first()?->name ?? '—',
                'roll'             => $student->roll_no ?? '',
                'status'           => 'running',
                'due_amount'       => 0,
                'is_alumni'        => false,

                // ── প্রতিটা student এর নিজস্ব আসল class/section/group সংরক্ষণ করা হচ্ছে ──
                // কারণ Section optional হওয়ায় একাধিক section এর student একসাথে আসতে পারে,
                // filter এর common class_id/section_id ব্যবহার করলে ভুল data সেভ হবে
                'original_class_id'   => $student->class_id,
                'original_section_id' => $student->section_id,
                'original_group_id'   => $student->group_id ?? null,
            ];
        }

        $this->selectedStudents = array_keys($this->students);
        $this->selectAll        = true;
        $this->hasStudents      = true;
    }

    /**
     * নির্দিষ্ট class/section এ roll_no ইউনিক কিনা চেক করে (session-less enrollment,
     * তাই বর্তমানে ওই class/section এ যারা আছে তাদের মধ্যে conflict চেক করাই যথেষ্ট)।
     * Conflict থাকলে বিদ্যমান সবচেয়ে বড় numeric roll_no এর পরের নাম্বার রিটার্ন করে।
     */
    private function resolveUniqueRoll(
        int $classId,
        ?int $sectionId,
        ?string $desiredRoll,
        int $excludeStudentId
    ): ?string {
        if (!$desiredRoll) {
            return $desiredRoll;
        }

        $conflict = StudentEnrollment::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('roll_no', $desiredRoll)
            ->where('student_id', '!=', $excludeStudentId)
            ->exists();

        if (!$conflict) {
            return $desiredRoll;
        }

        $maxRoll = (int) StudentEnrollment::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->max('roll_no');

        return (string) ($maxRoll + 1);
    }

    public function promote(): void
    {
        $this->validate([
            'to_session_id' => 'required|exists:academic_sessions,id',
            'to_class_id'   => 'required|exists:academic_classes,id',
            'to_section_id' => [
                Rule::requiredIf($this->toClassHasSection),
                'nullable',
            ],
        ]);

        if (empty($this->selectedStudents)) {
            $this->dispatch('toast', type: 'error', message: 'No students selected!');
            return;
        }

        // Destination class-e section na thakle, to_section_id kokhono use kora jabe na
        $toSectionId = ($this->toClassHasSection && $this->to_section_id && $this->to_section_id !== 'all')
            ? $this->to_section_id
            : null;

        $activeSession = AcademicSession::where('is_current', true)->first();

        if (!$activeSession) {
            $this->dispatch('toast', type: 'error', message: 'No active academic sessions found. Please activate a session first.');
            return;
        }

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
                $fromGroupId   = $row['original_group_id'];
                $fromRoll      = $student->roll_no;

                if ($row['is_alumni']) {

                    // ===== ALUMNI: class/section একই থাকে, শুধু status বদলায় =====
                    $resolvedRoll = $this->resolveUniqueRoll($fromClassId, $fromSectionId, $row['roll'], $studentId);

                    StudentEnrollment::updateOrCreate(
                        ['student_id' => $studentId],
                        [
                            'class_id'          => $fromClassId,
                            'section_id'        => $fromSectionId,
                            'group_id'          => $fromGroupId,
                            'roll_no'           => $resolvedRoll,
                            'status'            => 'alumni',
                            'carry_forward_due' => $this->carryForwardDue,
                        ]
                    );

                    $toClassIdForLog   = $fromClassId;
                    $toSectionIdForLog = $fromSectionId;
                    $toGroupIdForLog   = $fromGroupId;
                    $toRoll            = $resolvedRoll;

                } elseif ($row['status'] === 'running') {

                    // ===== RUNNING: একই class/section এ থেকে যাবে =====
                    $resolvedRoll = $this->resolveUniqueRoll($fromClassId, $fromSectionId, $row['roll'], $studentId);

                    StudentEnrollment::updateOrCreate(
                        ['student_id' => $studentId],
                        [
                            'class_id'          => $fromClassId,
                            'section_id'        => $fromSectionId,
                            'group_id'          => $fromGroupId,
                            'roll_no'           => $resolvedRoll,
                            'status'            => 'running',
                            'carry_forward_due' => $this->carryForwardDue,
                        ]
                    );

                    $toClassIdForLog   = $fromClassId;
                    $toSectionIdForLog = $fromSectionId;
                    $toGroupIdForLog   = $fromGroupId;
                    $toRoll            = $resolvedRoll;

                } else {

                    // ===== PROMOTED: নতুন class/section এ যাবে =====
                    $resolvedRoll = $this->resolveUniqueRoll(
                        (int) $this->to_class_id, $toSectionId, $row['roll'] ?: $student->roll_no, $studentId
                    );

                    StudentEnrollment::updateOrCreate(
                        ['student_id' => $studentId],
                        [
                            'class_id'          => $this->to_class_id,
                            'section_id'        => $toSectionId,
                            'group_id'          => null,
                            'roll_no'           => $resolvedRoll,
                            'status'            => 'promoted',
                            'carry_forward_due' => $this->carryForwardDue,
                        ]
                    );

                    $toClassIdForLog   = $this->to_class_id;
                    $toSectionIdForLog = $toSectionId;
                    $toGroupIdForLog   = null;
                    $toRoll            = $resolvedRoll;
                }

                // ===== student টেবিলের বর্তমান স্ন্যাপশট আপডেট =====
                $student->update([
                    'session_id' => $this->to_session_id,
                    'class_id'   => $toClassIdForLog,
                    'section_id' => $toSectionIdForLog,
                    'roll_no'    => $toRoll,
                ]);

                // ===== PROMOTION HISTORY LOG =====
                StudentPromotion::create([
                    'student_id'        => $studentId,
                    'from_session_id'   => $activeSession->id,
                    'to_session_id'     => $this->to_session_id,
                    'from_class_id'     => $fromClassId,
                    'to_class_id'       => $toClassIdForLog,
                    'from_section_id'   => $fromSectionId,
                    'to_section_id'     => $toSectionIdForLog,
                    'from_group_id'     => $fromGroupId,
                    'to_group_id'       => $toGroupIdForLog,
                    'from_roll_no'      => $fromRoll,
                    'to_roll_no'        => $toRoll,
                    'carry_forward_due' => $this->carryForwardDue,
                    'is_alumni'         => $row['is_alumni'],
                    'promoted_by'       => auth()->id(),
                    'promoted_at'       => now(),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('StudentPromotion failed: ' . $e->getMessage());
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
        $sessions = AcademicSession::orderBy('id')->get();

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