<?php

namespace App\Livewire\Student;

use App\Models\AcademicSession;
use App\Models\ExamEntry;
use App\Models\ExamGrade;
use App\Models\ExamPosition;
use App\Models\ExamSetup;
use App\Models\Student;
use Illuminate\Support\Collection;
use Livewire\Component;

class ResultComponent extends Component
{
    // Filters
    public ?int $academic_session_id = null;
    public ?int $exam_setup_id       = null;

    // Result state
    public bool $hasResults = false;

    // 'mark' | 'grade' | 'both' — resolved from exam_types.name
    public string $displayMode = 'both';

    // Overall summary for the selected exam
    public ?ExamPosition $selectedPosition = null;

    // Subject-wise marks for the selected exam
    public Collection $entries;

    // Institution-wide grade bands (name => ExamGrade), for remarks lookup
    public Collection $grades;

    public function mount(): void
    {
        $student = $this->studentOrFail();

        $this->entries = collect();
        $this->grades  = collect();

        $active = AcademicSession::where('institution_id', $student->institution_id)
            ->where('is_current', true)
            ->first();

        $this->academic_session_id = $active?->id;

        $this->loadGrades();
    }

    public function updatedAcademicSessionId(): void
    {
        $this->exam_setup_id = null;
        $this->resetResults();
    }

    /**
     * Resolves the logged-in user's own student profile.
     * No student_id is ever taken from the route/user input, so there is no IDOR risk.
     */
    protected function studentOrFail(): Student
    {
        $student = auth()->user()->student;

        abort_unless($student, 403, 'Student profile not found.');

        return $student;
    }

    /**
     * Loads institution-wide grade bands (A+, A, B... F) for remarks lookup.
     */
    protected function loadGrades(): void
    {
        $student = $this->studentOrFail();

        $this->grades = ExamGrade::query()
            ->where('institution_id', $student->institution_id)
            ->get()
            ->keyBy('name');
    }

    /**
     * Returns the remarks text (e.g. "Excellent") for a given grade name (e.g. "A+").
     */
    public function gradeRemark(?string $gradeName): ?string
    {
        if (! $gradeName) {
            return null;
        }

        return $this->grades->get($gradeName)?->remarks;
    }

    /**
     * Resolves a grade band (ExamGrade) whose min/max percentage range contains
     * the given percentage. Used as a fallback when a stored grade is missing.
     */
    protected function resolveGradeByPercentage(?float $percentage): ?ExamGrade
    {
        if ($percentage === null) {
            return null;
        }

        return $this->grades->first(
            fn (ExamGrade $grade) => $percentage >= (float) $grade->min_percentage
                && $percentage <= (float) $grade->max_percentage
        );
    }

    /**
     * Subject-wise entries store `grade` only when it was computed and saved at
     * mark-entry time. Older/incomplete entries can have a null grade even though
     * marks exist, so this falls back to deriving the grade from the obtained
     * percentage against the institution's grade bands (same source used for the
     * overall ExamPosition grade).
     */
    public function subjectGrade(ExamEntry $entry): ?string
    {
        if ($entry->is_absent) {
            return null;
        }

        if (! empty($entry->grade)) {
            return $entry->grade;
        }

        $fullMark = (float) ($entry->examSetupDetail?->full_mark ?? 0);

        if ($fullMark <= 0 || $entry->total_obtained === null) {
            return null;
        }

        $percentage = ($entry->total_obtained / $fullMark) * 100;

        return $this->resolveGradeByPercentage($percentage)?->name;
    }

    /**
     * Published exams (for the selected academic session) that this student has a
     * result-position for. Used both for the dropdown and as the IDOR-safe allowlist
     * when filtering.
     */
    protected function availableExamSetups(): Collection
    {
        if (! $this->academic_session_id) {
            return collect();
        }

        $student = $this->studentOrFail();

        $examSetupIds = ExamPosition::query()
            ->where('institution_id', $student->institution_id)
            ->where('student_id', $student->id)
            ->pluck('exam_setup_id');

        return ExamSetup::query()
            ->where('institution_id', $student->institution_id)
            ->where('academic_session_id', $this->academic_session_id)
            ->where('is_result_published', true)
            ->whereIn('id', $examSetupIds)
            ->with(['term', 'type'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Resolves display mode from the exam's ExamType name:
     * 'mark'  => numbers only
     * 'grade' => letter grade only
     * 'both'  => both (default/fallback)
     */
    protected function resolveDisplayMode(?ExamSetup $examSetup): string
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

    protected function resetResults(): void
    {
        $this->selectedPosition = null;
        $this->entries          = collect();
        $this->hasResults       = false;
        $this->displayMode      = 'both';
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

        $student = $this->studentOrFail();

        // IDOR prevention: only match against the already-scoped (own + published) exam list
        $examSetup = $this->availableExamSetups()->firstWhere('id', $this->exam_setup_id);

        abort_unless($examSetup, 403, 'You are not allowed to view this exam result.');

        $this->selectedPosition = ExamPosition::query()
            ->where('institution_id', $student->institution_id)
            ->where('student_id', $student->id)
            ->where('exam_setup_id', $this->exam_setup_id)
            ->first();

        if (! $this->selectedPosition) {
            $this->dispatch('toast', type: 'error', message: 'Result not found for the selected exam.');
            $this->resetResults();
            return;
        }

        $this->entries = ExamEntry::query()
            ->where('institution_id', $student->institution_id)
            ->where('student_id', $student->id)
            ->where('exam_setup_id', $this->exam_setup_id)
            ->with(['examSetupDetail.classAssignDetail.subject'])
            ->get()
            ->sortBy(fn ($entry) => $entry->examSetupDetail?->serial ?? 0)
            ->values();

        $this->displayMode = $this->resolveDisplayMode($examSetup);
        $this->hasResults  = true;
    }

    public function render()
    {
        $student = $this->studentOrFail();

        $academicSessions = AcademicSession::where('institution_id', $student->institution_id)
            ->orderByDesc('id')
            ->get();

        $exams = $this->availableExamSetups();

        return view('livewire.student.result-component')
            ->with('academicSessions', $academicSessions)
            ->with('exams', $exams)
            ->layout('layouts.student.app', [
                'title' => 'Result | Monarchy School',
            ]);
    }
}