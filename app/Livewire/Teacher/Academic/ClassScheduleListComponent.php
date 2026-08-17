<?php

namespace App\Livewire\Teacher\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssignDetail;
use App\Models\AcademicSubject;

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
        $institutionId = institution()->id;
        $teacherId     = auth()->id();

        // Admin panel theke EI teacher (login kora user) ke je je class+section e
        // subject assign kora ache, shegula AcademicClassAssignDetail theke nao
        $details = AcademicClassAssignDetail::with(['subject', 'classAssign.class', 'classAssign.section'])
            ->where('institution_id', $institutionId)
            ->where('teacher_id', $teacherId)
            ->get();

        if ($details->isEmpty()) {
            return;
        }

        $assigns = $details->pluck('classAssign')->filter()->unique('id');

        // NOTE (bug fix — N+1 avoidance): the old code ran one
        // AcademicClassSchedule query PER assign inside the foreach below.
        // A teacher with many class/section assignments meant one query per
        // assignment on every page load. Fetch every relevant schedule row
        // in a single query, then group them in memory for O(1) lookup.
        $classIds = $assigns->pluck('class_id')->unique()->values();

        $schedulesByAssign = AcademicClassSchedule::where('institution_id', $institutionId)
            ->whereIn('class_id', $classIds)
            ->get()
            ->groupBy(fn($s) => $s->class_id . ':' . ($s->section_id ?? 'null'));

        // ── data JSON e shudhu subject_id/teacher_id thake (name na) ──
        // Age subject NAME diye match kora hocchilo ($period['subject']), ja
        // key-i exist kore na ebong duita alada class e same name-er subject
        // thakle bhul match howar risk o thake. Ekhon সরাসরি teacher_id diye
        // match kora hocche — eta e actual source of truth (ei teacher ki
        // shotti-i ei period-e assigned).
        $allRows = collect();

        foreach ($assigns as $assign) {
            $key = $assign->class_id . ':' . ($assign->section_id ?? 'null');
            $schedules = $schedulesByAssign[$key] ?? collect();

            foreach ($schedules as $schedule) {
                foreach ($schedule->data ?? [] as $period) {
                    // shudhu nijer teacher_id-r row e dhukbe
                    if ((int) ($period['teacher_id'] ?? 0) !== (int) $teacherId) {
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
                        'subject_id' => $period['subject_id'] ?? null,
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

        // ── Subject name bulk resolve (N+1 thekano jonno ekbare) ──
        $subjectIds = $allRows->pluck('subject_id')->filter()->unique()->values();

        $subjectNames = AcademicSubject::where('institution_id', $institutionId)
            ->whereIn('id', $subjectIds)
            ->pluck('name', 'id');

        $allRows = $allRows->map(function ($row) use ($subjectNames) {
            $row['subject'] = $subjectNames[$row['subject_id']] ?? '—';
            unset($row['subject_id']);
            return $row;
        });

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