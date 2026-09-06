<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssignDetail;
use App\Models\AcademicSubject;
use App\Models\AcademicSession;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Validation\Rule;

class TeacherScheduleComponent extends Component
{
    public string $teacher_id = '';

    public bool  $hasSchedule  = false;
    public array $scheduleGrid = [];
    public array $days         = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];


    public ?int $currentSessionId = null;

    public function mount(): void
    {
        $this->currentSessionId = $this->resolveCurrentSessionId();
    }

    private function resolveCurrentSessionId(): ?int
    {
        return AcademicSession::query()
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->active() // scopeActive() -> is_current = true
            ->value('id');
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    public function filter(): void
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $this->validate([
            'teacher_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)
                        ->where('branch_id', $branchId)
                        ->where('role', User::ROLE_TEACHER)),
            ],
        ]);

        $this->hasSchedule  = false;
        $this->scheduleGrid = [];

        $assignDetails = AcademicClassAssignDetail::with('classAssign')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('teacher_id', $this->teacher_id)
            ->whereHas('classAssign', fn ($q) => $q->where('session_id', $this->currentSessionId))
            ->get();

        if ($assignDetails->isEmpty()) {
            return;
        }

        // class+section pair collect করো
        $classSectionPairs = $assignDetails
            ->map(fn($d) => [
                'class_id'   => $d->classAssign->class_id ?? null,
                'section_id' => $d->classAssign->section_id ?? null,
            ])
            ->filter(fn($p) => $p['class_id'])
            ->unique(fn($p) => $p['class_id'] . '-' . $p['section_id'])
            ->values();

        if ($classSectionPairs->isEmpty()) {
            return;
        }

        $classIds = $classSectionPairs->pluck('class_id')->unique()->values();

        // Ekbare shob relevant class-er schedule load kora holo (N+1 thekano)
        // branch_id + session_id scope shohokare.
        $schedules = AcademicClassSchedule::with(['academicClass', 'academicSection'])
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
            ->whereIn('class_id', $classIds)
            ->get()
            ->filter(function ($schedule) use ($classSectionPairs) {
                return $classSectionPairs->contains(
                    fn($p) => $p['class_id'] == $schedule->class_id && $p['section_id'] == $schedule->section_id
                );
            });

        if ($schedules->isEmpty()) {
            return;
        }

        // Schedule-e thaka shob subject_id ekbare resolve kore name map banano holo
        $subjectIds = $schedules
            ->flatMap(fn($s) => collect($s->data ?? [])->pluck('subject_id'))
            ->filter()
            ->unique();

        $subjectNames = AcademicSubject::where('institution_id', $institutionId)
            ->whereIn('id', $subjectIds)
            ->pluck('name', 'id');

        $allRows = collect();

        foreach ($schedules as $schedule) {
            foreach ($schedule->data ?? [] as $period) {
                // teacher_id diye direct match kora holo (name string matching er bodole)
                $periodTeacherId = $period['teacher_id'] ?? null;

                if ((int) $periodTeacherId !== (int) $this->teacher_id) {
                    continue;
                }

                $allRows->push([
                    'day'        => $schedule->day,
                    'class'      => $schedule->academicClass?->name ?? '—',
                    'section'    => $schedule->academicSection?->name ?? '',
                    'subject'    => $subjectNames[$period['subject_id'] ?? null] ?? '—',
                    'start_time' => $period['start_time'] ?? null,
                    'end_time'   => $period['end_time']   ?? null,
                    'class_room' => $period['class_room'] ?? null,
                ]);
            }
        }

        if ($allRows->isEmpty()) {
            return;
        }

        // Unique time slots sort by start_time
        $timeSlots = $allRows
            ->map(fn($r) => ['start_time' => $r['start_time'], 'end_time' => $r['end_time']])
            ->unique('start_time')
            ->sortBy('start_time')
            ->values();

        // Grid: period × day
        $grid = [];
        foreach ($timeSlots as $slot) {
            $row = [
                'start_time' => $slot['start_time'],
                'end_time'   => $slot['end_time'],
            ];
            foreach ($this->days as $day) {
                $match = $allRows->first(
                    fn($r) => $r['day'] === $day && $r['start_time'] === $slot['start_time']
                );
                $row[$day] = $match ?: null;
            }
            $grid[] = $row;
        }

        $this->scheduleGrid = $grid;
        $this->hasSchedule  = true;
    }

    public function render()
    {
        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.academic.teacher-schedule-component')
            ->with('teachers', $teachers)
            ->layout('layouts.admin.app', [
                'title' => 'Teacher Schedule | ' . institution()->name,
            ]);
    }
}