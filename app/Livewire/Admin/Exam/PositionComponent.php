<?php

namespace App\Livewire\Admin\Exam;

use Livewire\Component;
use App\Models\AcademicSession;
use App\Models\ExamSetup;
use App\Models\ExamSetupDetail;
use App\Models\ExamEntry;
use App\Models\ExamPosition;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class PositionComponent extends Component
{
    // Filters
    public ?int $academic_session_id = null;
    public ?int $exam_setup_id       = null;

    // Read-only display (derived from selected exam's classAssign)
    public ?string $selectedClassLabel = null;

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
            return;
        }

        $examSetup = ExamSetup::with('classAssign.class', 'classAssign.section')
            ->find($this->exam_setup_id);

        $this->selectedClassLabel = $examSetup
            ? ($examSetup->classAssign->class->name ?? '—') .
              ($examSetup->classAssign->section ? ' - ' . $examSetup->classAssign->section->name : '')
            : null;
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

        $students = Student::with('class', 'section')
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

        $currentExam = $examSetup;

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

            $previousPosition = ExamPosition::where('student_id', $student->id)
                ->where('academic_session_id', $this->academic_session_id)
                ->whereHas('examSetup', function ($q) use ($currentExam) {
                    $q->where('created_at', '<', $currentExam->created_at);
                })
                ->orderByDesc('created_at')
                ->value('position');

            $existing = $existingPositions->get($student->id);

            $computed[$student->id] = [
                'student_name'       => $student->name,
                'register_no'        => $student->register_no,
                'roll_no'            => $student->roll_no,
                'class_name'         => $student->class->name ?? '',
                'section_name'       => $student->section->name ?? '',
                'total_obtained'     => $totalObtained,
                'total_full_mark'    => $fullMarkTotal,
                'percentage'         => $percentage,
                'result'             => $result,
                'previous_position'  => $previousPosition,
                'position'           => $existing->position ?? null,
                'principal_comment'  => $existing->principal_comment ?? null,
                'teacher_comment'    => $existing->teacher_comment ?? null,
                'all_registered'     => $allRegistered,
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
        $rules = [];
        foreach ($this->rows as $studentId => $row) {
            $rules["rows.{$studentId}.position"] = 'required|integer|min:1';
        }

        $this->validate($rules, [], [
            'rows.*.position' => 'Position',
        ]);

        DB::beginTransaction();
        try {
            foreach ($this->rows as $studentId => $row) {
                ExamPosition::updateOrCreate(
                    [
                        'exam_setup_id'             => $this->exam_setup_id,
                        'academic_class_assign_id'  => $this->resolvedClassAssignId,
                        'student_id'                => $studentId,
                    ],
                    [
                        'academic_session_id' => $this->academic_session_id,
                        'total_obtained'      => $row['total_obtained'],
                        'total_full_mark'     => $row['total_full_mark'],
                        'percentage'          => $row['percentage'],
                        'result'              => $row['result'],
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
            \Log::error('ExamPosition save failed: ' . $e->getMessage());
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
            ? ExamSetup::with('classAssign.class', 'classAssign.section')
                ->where('academic_session_id', $this->academic_session_id)
                ->whereHas('details')
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.admin.exam.position-component')
            ->with('academicSessions', $academicSessions)
            ->with('exams', $exams)
            ->layout('layouts.admin.app', [
                'title' => 'Class Position | ' . institution()->name,
            ]);
    }
}