<?php

namespace App\Livewire\Teacher\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssignDetail;

class ClassScheduleListComponent extends Component
{
    public array $scheduleGrid = [];
    public array $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function mount(): void
    {
        $this->loadSchedule();
    }

    public function loadSchedule(): void
    {
        // Admin panel theke EI teacher (login kora user) ke je je class+section e
        // subject assign kora ache, shegula AcademicClassAssignDetail theke nao
        $details = AcademicClassAssignDetail::with(['subject', 'classAssign.class', 'classAssign.section'])
            ->where('teacher_id', auth()->id())
            ->get();

        if ($details->isEmpty()) {
            return;
        }

        // class_assign_id => [tar nijer subject name gula] — shudhu EI subject gula e
        // schedule theke dekhabo, oi class/section er onno subject na
        $mySubjectsByAssign = $details->groupBy('academic_class_assign_id')
            ->map(fn($group) => $group->pluck('subject.name')->filter()->values());

        $assigns = $details->pluck('classAssign')->filter()->unique('id');

        // NOTE (bug fix — N+1 avoidance): the old code ran one
        // AcademicClassSchedule query PER assign inside the foreach below.
        // A teacher with many class/section assignments meant one query per
        // assignment on every page load. Fetch every relevant schedule row
        // in a single query, then group them in memory for O(1) lookup.
        $classIds = $assigns->pluck('class_id')->unique()->values();

        $schedulesByAssign = AcademicClassSchedule::whereIn('class_id', $classIds)
            ->get()
            ->groupBy(fn($s) => $s->class_id . ':' . ($s->section_id ?? 'null'));

        $allRows = collect();

        foreach ($assigns as $assign) {
            $mySubjects = $mySubjectsByAssign[$assign->id] ?? collect();

            $key = $assign->class_id . ':' . ($assign->section_id ?? 'null');
            $schedules = $schedulesByAssign[$key] ?? collect();

            foreach ($schedules as $schedule) {
                foreach ($schedule->data ?? [] as $period) {
                    // shudhu nijer subject hole e row e dhukbe
                    if (!$mySubjects->contains($period['subject'] ?? null)) {
                        continue;
                    }

                    // Skip malformed period entries instead of letting a bad
                    // start_time/end_time blow up the schedule grid later.
                    if (empty($period['start_time']) || empty($period['end_time'])) {
                        continue;
                    }

                    $allRows->push([
                        'day'        => $schedule->day,
                        'class'      => $assign->class?->name ?? '—',
                        'section'    => $assign->section?->name ?? '—',
                        'subject'    => $period['subject']    ?? '—',
                        'start_time' => $period['start_time'],
                        'end_time'   => $period['end_time'],
                        'class_room' => $period['class_room'] ?? null,
                    ]);
                }
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

        // Grid toiri koro period × day
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
    }

    public function render()
    {
        return view('livewire.teacher.academic.class-schedule-list-component')
            ->with('days', $this->days)
            ->layout('layouts.teacher.app', [
                'title' => 'My Schedule | ' . institution()->name,
            ]);
    }
}