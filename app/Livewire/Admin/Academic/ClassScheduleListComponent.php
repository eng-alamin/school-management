<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClass;
use App\Models\AcademicSection;

class ClassScheduleListComponent extends Component
{
    public $class_id;
    public $section_id;

    public $schedule = null;
    public $hasSchedule = false;
    public $schedule_id;

    public $scheduleGrid = []; // ✅ grid data
    public $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];


    public array $availableSections = [];

    public function updatedClassId($value): void
    {
        $this->section_id        = '';
        $this->availableSections = [];
        $this->hasSchedule       = false;
        $this->data              = [];

        if ($value) {
            $class = AcademicClass::with('sections')->find($value);
            if ($class) {
                $this->availableSections = $class->sections
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->toArray();
            }
        }
    }

    public function filter()
    {
        $this->validate([
            'class_id'   => 'required|exists:academic_classes,id',
            'section_id' => 'required|exists:academic_sections,id',
        ]);

        // সব day এর schedule আনো
        $schedules = AcademicClassSchedule::where('class_id', $this->class_id)
            ->where('section_id', $this->section_id)
            ->get()
            ->keyBy('day'); // ['Sunday' => schedule, 'Monday' => schedule, ...]

        // Max period count বের করো
        $maxPeriods = $schedules->max(fn($s) => count($s->data ?? [])) ?? 0;

        // Grid বানাও: period index × day
        $grid = [];
        for ($i = 0; $i < $maxPeriods; $i++) {
            $row = [];
            foreach ($this->days as $day) {
                $row[$day] = $schedules[$day]->data[$i] ?? null;
            }
            $grid[] = $row;
        }

        $this->scheduleGrid = $grid;
        $this->hasSchedule = true;
    }

    public function render()
    {
        $classes  = AcademicClass::orderBy('id')->get();
        $sections = AcademicSection::orderBy('name')->get();

        return view('livewire.admin.academic.class-schedule-list-component')
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->with('days', $this->days)
            ->layout('layouts.admin.app', [
                'title' => "Class Schedule | School SaaS",
            ]);
    }


}
