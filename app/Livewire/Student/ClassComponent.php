<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\Student;

class ClassComponent extends Component
{
    public array $scheduleGrid = [];
    public bool $hasSchedule = false;
    public array $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function mount(): void
    {
        $student = auth()->user()->student;

        if (!$student || !$student->class_id || !$student->section_id) {
            return;
        }

        $schedules = AcademicClassSchedule::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->get()
            ->keyBy('day');

        $maxPeriods = $schedules->max(fn($s) => count($s->data ?? [])) ?? 0;

        $grid = [];
        for ($i = 0; $i < $maxPeriods; $i++) {
            $row = [];
            foreach ($this->days as $day) {
                $row[$day] = $schedules[$day]->data[$i] ?? null;
            }
            $grid[] = $row;
        }

        $this->scheduleGrid = $grid;
        $this->hasSchedule  = !empty($grid);
    }

    public function render()
    {
        return view('livewire.student.class-component')
            ->layout('layouts.student.app', [
                'title' => "Class Schedule | Monarchy School",
            ]);
    }
}