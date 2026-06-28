<?php

namespace App\Livewire\Teacher\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClass;
use App\Models\AcademicSection;

class ClassScheduleListComponent extends Component
{
    public $filterClass   = '';
    public $filterSection = '';

    public $hasSchedule  = false;
    public $scheduleGrid = [];
    public $days         = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    public function getAvailableSections()
    {
        if (!$this->filterClass) return collect();

        return AcademicSection::whereIn('id',
            AcademicClassAssign::where('class_id', $this->filterClass)->pluck('section_id')
        )->orderBy('name')->get();
    }

    public function updatedFilterClass()
    {
        $this->filterSection = '';
        $this->hasSchedule   = false;
        $this->scheduleGrid  = [];
    }

    public function updatedFilterSection()
    {
        $this->hasSchedule  = false;
        $this->scheduleGrid = [];
    }

    public function filter()
    {
        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }

        $sectionId = ($this->filterSection && $this->filterSection !== 'all')
            ? $this->filterSection
            : null;

        $schedules = AcademicClassSchedule::where('class_id', $this->filterClass)
            ->where('section_id', $sectionId)
            ->get()
            ->keyBy('day');

        $maxPeriods = $schedules->max(fn($s) => count($s->data ?? [])) ?? 0;

        $grid = [];
        for ($i = 0; $i < $maxPeriods; $i++) {
            $row = [];
            foreach ($this->days as $day) {
                $row[$day] = isset($schedules[$day]) ? ($schedules[$day]->data[$i] ?? null) : null;
            }
            $grid[] = $row;
        }

        $this->scheduleGrid = $grid;
        $this->hasSchedule  = true;
    }

    public function render()
    {
        return view('livewire.teacher.academic.class-schedule-list-component')
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->with('days', $this->days)
            ->layout('layouts.teacher.app', [
                'title' => 'Class Schedule | ' . institution()->name,
            ]);
    }
}