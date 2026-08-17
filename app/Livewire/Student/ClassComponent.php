<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicSubject;
use App\Models\Student;
use App\Models\User;

class ClassComponent extends Component
{
    public array $scheduleGrid = [];
    public bool $hasSchedule = false;
    public array $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function mount(): void
    {
        $student = $this->currentStudent();

        if (!$student || !$student->class_id) {
            return;
        }

        $institutionId = institution()->id;

        $schedules = AcademicClassSchedule::where('institution_id', $institutionId)
            ->where('class_id', $student->class_id)
            ->where(function ($query) use ($student) {
                $query->whereNull('section_id')
                      ->orWhere('section_id', $student->section_id);
            })
            ->get()
            // If both a section-specific row and a null-section (common) row exist
            // for the same day, prefer the section-specific one.
            ->sortByDesc(fn ($s) => $s->section_id !== null)
            ->keyBy('day');

        // ── data JSON e shudhu subject_id / teacher_id thake (name na) ──
        // Tai ekbare shob subject_id ar teacher_id collect kore, single query te
        // name resolve kora hocche (N+1 thekano jonno)
        $subjectIds = [];
        $teacherIds = [];

        foreach ($schedules as $schedule) {
            foreach (($schedule->data ?? []) as $row) {
                if (!empty($row['subject_id'])) {
                    $subjectIds[] = $row['subject_id'];
                }
                if (!empty($row['teacher_id'])) {
                    $teacherIds[] = $row['teacher_id'];
                }
            }
        }

        $subjectNames = AcademicSubject::where('institution_id', $institutionId)
            ->whereIn('id', array_unique($subjectIds))
            ->pluck('name', 'id');

        $teacherNames = User::where('institution_id', $institutionId)
            ->whereIn('id', array_unique($teacherIds))
            ->pluck('name', 'id');

        $maxPeriods = $schedules->max(fn($s) => count($s->data ?? [])) ?? 0;

        $grid = [];
        for ($i = 0; $i < $maxPeriods; $i++) {
            $row = [];
            foreach ($this->days as $day) {
                $rawItem = $schedules[$day]->data[$i] ?? null;

                $row[$day] = $rawItem ? [
                    'subject'    => $subjectNames[$rawItem['subject_id']] ?? '—',
                    'teacher'    => $teacherNames[$rawItem['teacher_id']] ?? '—',
                    'start_time' => $rawItem['start_time'] ?? null,
                    'end_time'   => $rawItem['end_time'] ?? null,
                    'class_room' => $rawItem['class_room'] ?? null,
                ] : null;
            }
            $grid[] = $row;
        }

        $this->scheduleGrid = $grid;
        $this->hasSchedule  = !empty($grid);
    }

    /**
     * Resolve the logged-in student.
     *
     * Default 'web' guard is used. Auth::user() returns a User model,
     * and Student::user_id links back to that User, so we resolve the
     * Student record via that foreign key.
     */
    protected function currentStudent(): ?Student
    {
        $userId = auth()->id();

        if (!$userId) {
            return null;
        }

        return Student::where('user_id', $userId)->first();
    }

    public function render()
    {
        return view('livewire.student.class-component')
            ->layout('layouts.student.app', [
                'title' => 'Class Schedule | ' . institution()->name,
            ]);
    }
}