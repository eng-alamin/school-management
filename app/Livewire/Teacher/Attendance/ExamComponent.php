<?php

namespace App\Livewire\Teacher\Attendance;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicSection;
use App\Models\AttendanceExamAssign;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamComponent extends Component
{
    public $filterExam    = '';
    public $filterClass   = '';
    public $filterSection = '';
    public $filterSubject = '';

    public $subjects = [];

    public $data          = [];
    public $hasAttendance = false;

    // ── Ekbar query kore memoize kora hocche, ekই request-e barbar DB hit thekano jonno ──
    private ?Collection $cachedMySchedules = null;
    private ?Collection $cachedMyCombos    = null;

    /**
     * Ei logged-in teacher-ke exam invigilator duty hisebe je je ExamSchedule
     * assign kora hoyeche, shegulo (relations shoho) return kore.
     */
    private function myAssignedSchedules(): Collection
    {
        if ($this->cachedMySchedules !== null) {
            return $this->cachedMySchedules;
        }

        $institutionId = institution()->id;

        return $this->cachedMySchedules = AttendanceExamAssign::with([
                'examSchedule.examSetup.classAssign.academicClass',
                'examSchedule.examSetup.classAssign.academicSection',
                'examSchedule.examSetupDetail.classAssignDetail.subject',
            ])
            ->where('institution_id', $institutionId)
            ->where('teacher_id', auth()->id())
            ->get()
            ->pluck('examSchedule')
            ->filter();
    }

    /**
     * myAssignedSchedules()-ke flatten kore [exam_setup_id, exam_name, class_id, class_name,
     * section_id, section_name, subject_id, subject_name] combination-er ekta list বানায়।
     *
     * Security note: ei method-i single source of truth. Dropdown, filter(), ebong save() —
     * shob jaygay ei combos theke e authorization check kora hocche, jate teacher tar
     * assign kora chara kono Exam/Class/Section/Subject-er attendance dekhte
     * ba save korte na pare (IDOR protection).
     */
    private function myAssignedCombos(): Collection
    {
        if ($this->cachedMyCombos !== null) {
            return $this->cachedMyCombos;
        }

        return $this->cachedMyCombos = $this->myAssignedSchedules()
            ->map(function ($schedule) {
                $classAssign = $schedule->examSetup?->classAssign;
                $subject     = $schedule->examSetupDetail?->classAssignDetail?->subject;

                return [
                    'exam_setup_id' => $schedule->examSetup?->id,
                    'exam_name'     => $schedule->examSetup?->name,
                    'class_id'      => $classAssign?->class_id,
                    'class_name'    => $classAssign?->academicClass?->name,
                    'section_id'    => $classAssign?->section_id,
                    'section_name'  => $classAssign?->academicSection?->name,
                    'subject_id'    => $schedule->examSetupDetail?->classAssignDetail?->subject_id,
                    'subject_name'  => $subject?->name,
                ];
            })
            ->filter(fn ($c) => $c['exam_setup_id'] && $c['class_id'] && $c['subject_id']);
    }

    // Exam
    public function getExams()
    {
        return $this->myAssignedCombos()
            ->unique('exam_setup_id')
            ->map(fn ($c) => (object) ['id' => $c['exam_setup_id'], 'name' => $c['exam_name']])
            ->sortBy('name')
            ->values();
    }

    // Classes (filterExam select kora thakle tar modheই limit thakbe)
    public function getAvailableClasses()
    {
        return $this->myAssignedCombos()
            ->when($this->filterExam, fn ($c) => $c->where('exam_setup_id', (int) $this->filterExam))
            ->unique('class_id')
            ->map(fn ($c) => (object) ['id' => $c['class_id'], 'name' => $c['class_name']])
            ->sortBy('name')
            ->values();
    }

    // Sections (filterExam + filterClass onujayi)
    public function getAvailableSections()
    {
        if (! $this->filterClass) {
            return collect();
        }

        return $this->myAssignedCombos()
            ->when($this->filterExam, fn ($c) => $c->where('exam_setup_id', (int) $this->filterExam))
            ->where('class_id', (int) $this->filterClass)
            ->filter(fn ($c) => $c['section_id'])
            ->unique('section_id')
            ->map(fn ($c) => (object) ['id' => $c['section_id'], 'name' => $c['section_name']])
            ->sortBy('name')
            ->values();
    }

    /**
     * filterExam + filterClass (+ filterSection thakle) onujayi amake (teacher)
     * assign kora subject list ber kore. Age AcademicClassAssignDetail theke
     * shob subject asto — ekhon shudhu amar exam duty-r subject-i ashbe.
     */
    protected function loadSubjects(): void
    {
        $combos = $this->myAssignedCombos()
            ->when($this->filterExam, fn ($c) => $c->where('exam_setup_id', (int) $this->filterExam))
            ->where('class_id', (int) $this->filterClass);

        if ($this->isSpecificSectionSelected()) {
            $combos = $combos->where('section_id', (int) $this->filterSection);
        }

        $this->subjects = $combos
            ->unique('subject_id')
            ->map(fn ($c) => (object) ['id' => $c['subject_id'], 'name' => $c['subject_name']])
            ->sortBy('name')
            ->values()
            ->toArray();
    }

    // filterSection blank ba 'all' hole "specific section select kora hoyni" dhora hoy
    private function isSpecificSectionSelected(): bool
    {
        return (bool) $this->filterSection && $this->filterSection !== 'all';
    }

    /**
     * Given exam+class(+subject), amake (teacher) assign kora section_id gulo return kore.
     * "All Section" select korle attendance list ei section_id-gulor moddheই limit thakbe.
     */
    private function myAssignedSectionIds($examId, $classId, $subjectId = null): array
    {
        return $this->myAssignedCombos()
            ->where('exam_setup_id', (int) $examId)
            ->where('class_id', (int) $classId)
            ->when($subjectId, fn ($c) => $c->where('subject_id', (int) $subjectId))
            ->pluck('section_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Defense-in-depth: dropdown-e shudhu assigned combination dekhano hoyeo,
     * front-end theke wire:model tamper kore onno exam/class/section/subject
     * pathale eikhane dhora porbe (filter() ebong save() duitatei call kora hocche).
     */
    private function isAssignedToMe($examId, $classId, $sectionId, $subjectId): bool
    {
        if (! $examId || ! $classId || ! $subjectId) {
            return false;
        }

        return $this->myAssignedCombos()
            ->where('exam_setup_id', (int) $examId)
            ->where('class_id', (int) $classId)
            ->where('subject_id', (int) $subjectId)
            ->when($sectionId && $sectionId !== 'all', fn ($c) => $c->where('section_id', (int) $sectionId))
            ->isNotEmpty();
    }

    public function updatedFilterExam(): void
    {
        $this->filterClass   = '';
        $this->filterSection = '';
        $this->filterSubject = '';
        $this->subjects      = [];
        $this->data          = [];
        $this->hasAttendance = false;
    }

    public function updatedFilterClass(): void
    {
        $this->filterSection = '';
        $this->filterSubject = '';
        $this->subjects      = [];
        $this->data          = [];
        $this->hasAttendance = false;

        if (! $this->filterClass) {
            return;
        }

        $this->loadSubjects();
    }

    public function updatedFilterSection(): void
    {
        $this->filterSubject = '';
        $this->data          = [];
        $this->hasAttendance = false;

        if (! $this->filterClass) {
            return;
        }

        $this->loadSubjects();
    }

    public function filter()
    {
        if (! $this->filterExam) {
            $this->dispatch('toast', type: 'error', message: 'Please select an exam.');
            return;
        }
        if (! $this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }
        if (! $this->filterSubject) {
            $this->dispatch('toast', type: 'error', message: 'Please select a subject.');
            return;
        }

        // ── Authorization check: shudhu amake assign kora combination-e-i allow ──
        if (! $this->isAssignedToMe($this->filterExam, $this->filterClass, $this->filterSection, $this->filterSubject)) {
            $this->dispatch('toast', type: 'error', message: 'You are not assigned invigilator duty for this exam/class/subject.');
            return;
        }

        $institutionId = institution()->id;

        // "All Section" select korle o attendance shudhu amar assigned section_id-gulor moddheই limit thakbe
        $mySectionIds = collect($this->myAssignedSectionIds($this->filterExam, $this->filterClass, $this->filterSubject));

        $studentsQuery = Student::where('institution_id', $institutionId)
            ->where('class_id', $this->filterClass)
            ->orderBy('section_id')
            ->orderBy('roll_no');

        if ($this->isSpecificSectionSelected()) {
            $studentsQuery->where('section_id', $this->filterSection);
        } elseif ($mySectionIds->isNotEmpty()) {
            $studentsQuery->whereIn('section_id', $mySectionIds);
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No students found for the selected exam.');
            $this->hasAttendance = false;
            return;
        }

        $sectionNames = AcademicSection::where('institution_id', $institutionId)
            ->whereIn('id', $students->pluck('section_id')->unique())
            ->pluck('name', 'id');

        $existingQuery = Attendance::where('institution_id', $institutionId)
            ->where('type', 'exam')
            ->where('exam_id', $this->filterExam)
            ->where('class_id', $this->filterClass)
            ->where('subject_id', $this->filterSubject);

        if ($this->isSpecificSectionSelected()) {
            $existingQuery->where('section_id', $this->filterSection);
        } elseif ($mySectionIds->isNotEmpty()) {
            $existingQuery->whereIn('section_id', $mySectionIds);
        }

        $existing = $existingQuery->get()->keyBy('attendable_id');

        $this->data = $students->map(function ($student) use ($existing, $sectionNames) {
            $att = $existing[$student->id] ?? null;

            return [
                'student_id'   => $student->id,
                'section_id'   => $student->section_id,
                'section_name' => $sectionNames[$student->section_id] ?? '',
                'name'         => $student->name,
                'roll_no'      => $student->roll_no,
                'register_no'  => $student->register_no,
                'status'       => $att->status ?? 'present',
                'remarks'      => $att->remarks ?? '',
            ];
        })->toArray();

        $this->hasAttendance = true;
    }

    public function save()
    {
        $this->validate([
            'filterExam'    => 'required',
            'filterClass'   => 'required',
            'filterSubject' => 'required',
        ]);

        // ── Authorization check: save-er somoyo abar verify kora hocche (defense-in-depth) ──
        if (! $this->isAssignedToMe($this->filterExam, $this->filterClass, $this->filterSection, $this->filterSubject)) {
            $this->dispatch('toast', type: 'error', message: 'You are not assigned invigilator duty for this exam/class/subject.');
            return;
        }

        $institutionId = institution()->id;

        DB::beginTransaction();
        try {
            foreach ($this->data as $item) {
                Attendance::updateOrCreate(
                    [
                        'attendable_id'   => $item['student_id'],
                        'attendable_type' => Student::class,
                        'type'            => 'exam',
                        'exam_id'         => $this->filterExam,
                        'class_id'        => $this->filterClass,
                        'section_id'      => $item['section_id'],
                        'subject_id'      => $this->filterSubject,
                    ],
                    [
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
            Log::error('Teacher exam attendance save failed: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Something went wrong while saving attendance.');
        }
    }

    public function resetForm()
    {
        $this->filterExam    = '';
        $this->filterClass   = '';
        $this->filterSection = '';
        $this->filterSubject = '';
        $this->subjects      = [];
        $this->data          = [];
        $this->hasAttendance = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.teacher.attendance.exam-component')
            ->with('exams', $this->getExams())
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->with('subjects', $this->subjects)
            ->layout('layouts.teacher.app', [
                'title' => 'Exam Attendance | ' . institution()->name,
            ]);
    }
}