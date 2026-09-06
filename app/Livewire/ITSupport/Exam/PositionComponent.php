<?php

namespace App\Livewire\ITSupport\Exam;

use Livewire\Component;
use App\Models\AcademicSession;
use App\Models\ExamSetup;
use App\Models\ExamSetupDetail;
use App\Models\ExamEntry;
use App\Models\ExamPosition;
use App\Models\ExamGrade;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PositionComponent extends Component
{
    // Filters
    public ?int $academic_session_id = null;
    public ?int $exam_setup_id       = null;

    // Read-only display (derived from selected exam's classAssign)
    public ?string $selectedClassLabel = null;

    // 'mark' | 'grade' | 'both' — exam_types.name onujayi decide hoy
    public string $displayMode = 'both';

    // Results
    public array $rows = []; // student_id => row data
    public bool $hasResults = false;
    public bool $alreadyGenerated = false;

    // Must be public so Livewire persists these across requests (filter -> save)
    public ?int $resolvedClassAssignId = null;
    public ?int $resolvedClassId = null;
    public ?int $resolvedSectionId = null;

    public function mount(): void
    {
        $active = AcademicSession::where('is_current', true)->first();
        $this->academic_session_id = $active?->id;
    }

    public function updatedAcademicSessionId(): void
    {
        $this->exam_setup_id = null;
        $this->resetResults();
        $this->selectedClassLabel = null;
    }

    public function updatedExamSetupId(): void
    {
        $this->resetResults();

        if (!$this->exam_setup_id) {
            $this->selectedClassLabel = null;
            $this->displayMode = 'both';
            return;
        }

        // ✅ Fix: academicClass()/academicSection() relation names ব্যবহার করা হলো
        // (পুরনো class()/section() নাম আর কোথাও নেই, তাই আগে এখানে সবসময় "—" দেখাত)
        $examSetup = ExamSetup::with('classAssign.academicClass', 'classAssign.academicSection', 'type')
            ->find($this->exam_setup_id);

        $this->selectedClassLabel = $examSetup
            ? ($examSetup->classAssign->academicClass->name ?? '—') .
              ($examSetup->classAssign->academicSection ? ' - ' . $examSetup->classAssign->academicSection->name : '')
            : null;

        $this->displayMode = $this->resolveDisplayMode($examSetup);
    }

    /**
     * ExamType-er name theke display-mode resolve kore:
     * 'mark'  => shudhu marks dekhabe (GPA/Grade hide)
     * 'grade' => shudhu grade dekhabe
     * 'both'  => shob dekhabe (default/fallback)
     */
    private function resolveDisplayMode(?ExamSetup $examSetup): string
    {
        $typeName = strtolower(trim((string) ($examSetup?->type?->name ?? '')));

        $hasMark  = str_contains($typeName, 'mark');
        $hasGrade = str_contains($typeName, 'grade');

        return match (true) {
            $hasMark && $hasGrade => 'both',
            $hasGrade             => 'grade',
            $hasMark              => 'mark',
            default               => 'both',
        };
    }

    private function resetResults(): void
    {
        $this->rows = [];
        $this->hasResults = false;
        $this->alreadyGenerated = false;
        $this->resolvedClassAssignId = null;
        $this->resolvedClassId = null;
        $this->resolvedSectionId = null;
    }

    /**
     * Institution-er sob grade band (A+, A, B... F) percentage-range shoho.
     */
    private function loadExamGrades()
    {
        return ExamGrade::where('institution_id', institution()->id)
            ->orderByDesc('min_percentage')
            ->get();
    }

    /**
     * Percentage-tা kon grade-range-e pore seta khuje ber kore.
     */
    private function resolveGrade($examGrades, float $percentage): ?ExamGrade
    {
        return $examGrades->first(
            fn (ExamGrade $grade) => $percentage >= (float) $grade->min_percentage
                && $percentage <= (float) $grade->max_percentage
        );
    }

    /**
     * Result 'fail' hole percentage-range match na kore, sobcheye kom
     * grade_point-er grade (shadharonoto "F") force kora hoy — karon
     * ekta subject fail thakleo overall percentage passing range-e pore
     * jete pare, jeta bhul upper grade dekhabe.
     */
    private function lowestGrade($examGrades): ?ExamGrade
    {
        return $examGrades->sortBy('grade_point')->first();
    }

    public function filter(): void
    {
        $this->validate([
            'academic_session_id' => 'required',
            'exam_setup_id'       => 'required',
        ], [], [
            'academic_session_id' => 'Academic Session',
            'exam_setup_id'       => 'Exam',
        ]);

        $examSetup = ExamSetup::with('classAssign')->find($this->exam_setup_id);

        if (!$examSetup || !$examSetup->classAssign) {
            $this->dispatch('toast', type: 'error', message: 'Class information পাওয়া যায়নি।');
            return;
        }

        // ✅ Fix: cross-session tampering guard — exam_setup_id ট্যাম্পার করে অন্য
        // academic_session_id এর exam পাঠানো হলে ধরা পড়বে।
        if ((int) $examSetup->academic_session_id !== (int) $this->academic_session_id) {
            $this->dispatch('toast', type: 'error', message: 'নির্বাচিত Exam টি এই Academic Session এর সাথে মিলছে না।');
            $this->resetResults();
            return;
        }

        $this->resolvedClassAssignId = $examSetup->classAssign->id;
        $this->resolvedClassId       = $examSetup->classAssign->class_id;
        $this->resolvedSectionId     = $examSetup->classAssign->section_id;

        $details = ExamSetupDetail::where('exam_setup_id', $this->exam_setup_id)->get();

        if ($details->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'এই Exam এ কোনো subject setup নেই।');
            return;
        }

        $detailIds = $details->pluck('id');
        $fullMarkTotal = $details->sum('full_mark');

        $students = Student::with('academicClass', 'academicSection')
            ->where('class_id', $this->resolvedClassId)
            ->when($this->resolvedSectionId, function ($q) {
                $q->where('section_id', $this->resolvedSectionId);
            })
            ->orderBy('roll_no')
            ->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'এই class এ কোনো student পাওয়া যায়নি।');
            return;
        }

        $entryTotals = ExamEntry::whereIn('exam_setup_detail_id', $detailIds)
            ->whereIn('student_id', $students->pluck('id'))
            ->select('student_id')
            ->selectRaw('SUM(total_obtained) as total_obtained')
            ->selectRaw('SUM(CASE WHEN is_absent = 1 THEN 1 ELSE 0 END) as absent_count')
            ->selectRaw('COUNT(*) as entry_count')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $existingPositions = ExamPosition::where('exam_setup_id', $this->exam_setup_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $this->alreadyGenerated = $existingPositions->isNotEmpty();

        if ($this->alreadyGenerated) {
            $this->dispatch('toast', type: 'info', message: 'This position has already been generated.');
        }

        $currentExam = $examSetup;
        $examGrades  = $this->loadExamGrades();

        $computed = [];
        foreach ($students as $student) {
            $entry = $entryTotals->get($student->id);
            $totalObtained = $entry->total_obtained ?? 0;
            $enteredSubjects = $entry->entry_count ?? 0;
            $absentSubjects = $entry->absent_count ?? 0;

            $allRegistered = $enteredSubjects >= $detailIds->count();
            $percentage = $fullMarkTotal > 0 ? round(($totalObtained / $fullMarkTotal) * 100, 2) : 0;

            if (!$allRegistered) {
                $result = 'incomplete';
            } elseif ($absentSubjects > 0) {
                $result = 'fail';
            } else {
                $result = $percentage >= 33 ? 'pass' : 'fail'; // adjust threshold as needed
            }

            // ✅ New: result onujayi gpa/grade resolve kora hocche exam_grades theke
            $gradeModel = match ($result) {
                'pass'  => $this->resolveGrade($examGrades, $percentage) ?? $this->lowestGrade($examGrades),
                'fail'  => $this->lowestGrade($examGrades),
                default => null, // incomplete / absent → nishchit na hoye grade dewa hocche na
            };

            // ✅ Fix: একই class_assign (একই class+section+exam-series) এর মধ্যেই
            // আগের position খোঁজা হচ্ছে — আগে শুধু session + সময় দিয়ে filter হতো,
            // যেটা ভুল class/exam থেকে position টেনে আনতে পারত।
            $previousPosition = ExamPosition::where('student_id', $student->id)
                ->where('academic_session_id', $this->academic_session_id)
                ->where('academic_class_assign_id', $this->resolvedClassAssignId)
                ->whereHas('examSetup', function ($q) use ($currentExam) {
                    $q->where('created_at', '<', $currentExam->created_at);
                })
                ->orderByDesc('created_at')
                ->value('position');

            $existing = $existingPositions->get($student->id);

            $computed[$student->id] = [
                'student_name'      => $student->name,
                'registration_no'   => $student->registration_no,
                'roll_no'           => $student->roll_no,
                // ✅ Fix: academicClass/academicSection
                'class_name'        => $student->academicClass->name ?? '',
                'section_name'      => $student->academicSection->name ?? '',
                'total_obtained'    => $totalObtained,
                'total_full_mark'   => $fullMarkTotal,
                'percentage'        => $percentage,
                'result'            => $result,
                'gpa'               => $gradeModel?->grade_point,
                'grade'             => $gradeModel?->name,
                'previous_position' => $previousPosition,
                'position'          => $existing->position ?? null,
                'principal_comment' => $existing->principal_comment ?? null,
                'teacher_comment'   => $existing->teacher_comment ?? null,
                'all_registered'    => $allRegistered,
            ];
        }

        if (!$this->alreadyGenerated) {
            $ranked = collect($computed)
                ->filter(fn ($r) => $r['all_registered'])
                ->sortByDesc('total_obtained')
                ->keys()
                ->values();

            foreach ($ranked as $rank => $studentId) {
                $computed[$studentId]['position'] = $rank + 1;
            }
        }

        $this->rows = $computed;
        $this->hasResults = true;
    }

    public function save(): void
    {
        if (!$this->hasResults || !$this->resolvedClassAssignId || empty($this->rows)) {
            $this->dispatch('toast', type: 'error', message: 'দয়া করে আগে Filter করুন।');
            return;
        }

        $rules = [];
        foreach ($this->rows as $studentId => $row) {
            $rules["rows.{$studentId}.position"] = 'required|integer|min:1';
        }

        $this->validate($rules, [], [
            'rows.*.position' => 'Position',
        ]);

        $institutionId = institution()->id;

        DB::beginTransaction();
        try {
            foreach ($this->rows as $studentId => $row) {
                ExamPosition::updateOrCreate(
                    [
                        'exam_setup_id'            => $this->exam_setup_id,
                        'academic_class_assign_id' => $this->resolvedClassAssignId,
                        'student_id'               => $studentId,
                    ],
                    [
                        'institution_id'      => $institutionId,
                        'academic_session_id' => $this->academic_session_id,
                        'total_obtained'      => $row['total_obtained'],
                        'total_full_mark'     => $row['total_full_mark'],
                        'percentage'          => $row['percentage'],
                        'result'              => $row['result'],
                        'gpa'                 => $row['gpa'],
                        'grade'               => $row['grade'],
                        'previous_position'   => $row['previous_position'],
                        'position'            => $row['position'],
                        'principal_comment'   => $row['principal_comment'],
                        'teacher_comment'     => $row['teacher_comment'],
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ExamPosition save failed: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            return;
        }

        $this->alreadyGenerated = true;
        $this->dispatch('toast', type: 'success', message: 'Position saved successfully!');
    }

    public function render()
    {
        $academicSessions = AcademicSession::orderByDesc('id')->get();

        $exams = $this->academic_session_id
            ? ExamSetup::with('classAssign.academicClass', 'classAssign.academicSection')
                ->where('academic_session_id', $this->academic_session_id)
                ->where('is_published', true)
                ->whereHas('details')
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.admin.exam.position-component')
            ->with('academicSessions', $academicSessions)
            ->with('exams', $exams)
            ->layout('layouts.itsupport.app', [
                'title' => 'Class Position | ' . institution()->name,
            ]);
    }
}