<?php

namespace App\Livewire\Teacher\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicTeacherAssign;

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
        $employeeId = auth()->user()->employee->id;

        // এই teacher এর সব assigned class+section নাও
        $assigns = AcademicTeacherAssign::with(['class', 'section'])
            ->where('teacher_id', $employeeId)
            ->get();

        if ($assigns->isEmpty()) {
            return;
        }

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

        // Unique time slots sort by start_time
        $timeSlots = $allRows
            ->map(fn($r) => ['start_time' => $r['start_time'], 'end_time' => $r['end_time']])
            ->unique('start_time')
            ->sortBy('start_time')
            ->values();

        // Grid তৈরি করো period × day
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