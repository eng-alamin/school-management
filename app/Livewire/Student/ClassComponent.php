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
        $student = $this->currentStudent();

        if (!$student || !$student->class_id) {
            return;
        }

        $schedules = AcademicClassSchedule::where('class_id', $student->class_id)
            ->where(function ($query) use ($student) {
                $query->whereNull('section_id')
                      ->orWhere('section_id', $student->section_id);
            })
            ->get()
            // If both a section-specific row and a null-section (common) row exist
            // for the same day, prefer the section-specific one.
            ->sortByDesc(fn ($s) => $s->section_id !== null)
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