<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicTeacherAssign;
use App\Models\Employee;

class TeacherScheduleComponent extends Component
{
    public string $teacher_id = '';

    public bool $hasSchedule = false;
    public array $scheduleGrid = [];
    public array $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function filter(): void
    {
        $this->validate([
            'teacher_id' => 'required|exists:employees,id',
        ]);

        $this->hasSchedule   = false;
        $this->scheduleGrid  = [];

        // Find all class+section pairs assigned to this teacher
        $assigns = AcademicTeacherAssign::where('teacher_id', $this->teacher_id)->get();

        if ($assigns->isEmpty()) {
            return;
        }

        // Collect all schedules for those class+section pairs
        $allRows = collect();

        foreach ($assigns as $assign) {
            $schedules = AcademicClassSchedule::where('class_id', $assign->class_id)
                ->where('section_id', $assign->section_id)
                ->get();

            foreach ($schedules as $schedule) {
                foreach ($schedule->data ?? [] as $period) {
                    $allRows->push([
                        'day'        => $schedule->day,
                        'class'      => $assign->class?->name ?? '—',
                        'section'    => $assign->section?->name ?? '—',
                        'subject'    => $period['subject']    ?? '—',
                        'teacher'    => $period['teacher']    ?? '—',
                        'start_time' => $period['start_time'] ?? null,
                        'end_time'   => $period['end_time']   ?? null,
                        'class_room' => $period['class_room'] ?? null,
                    ]);
                }
            }
        }

        if ($allRows->isEmpty()) {
            return;
        }

        // Determine unique time-slots (periods) sorted by start_time
        $timeSlots = $allRows
            ->map(fn($r) => ['start_time' => $r['start_time'], 'end_time' => $r['end_time']])
            ->unique('start_time')
            ->sortBy('start_time')
            ->values();

        // Build grid: period × day
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
        $teachers = Employee::with(['designation', 'department', 'user'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'teacher');
            })
            ->orderBy('id', 'asc')
            ->get();

        return view('livewire.admin.academic.teacher-schedule-component')
            ->with('teachers', $teachers)
            ->layout('layouts.admin.app', [
                'title' => "Teacher Schedule | School SaaS",
            ]);
    }
}