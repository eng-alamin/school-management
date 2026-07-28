<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use App\Models\User;

class ClassScheduleListComponent extends Component
{
    public $filterClass   = '';
    public $filterSection = '';

    public $hasSchedule  = false;
    public $scheduleGrid = [];
    public $days         = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // Selected class-er has_section flag, blade e section dropdown show/hide korar jonno
    public bool $selectedClassHasSection = true;

    public function getAvailableClasses()
    {
        $institutionId = institution()->id;

        return AcademicClass::where('institution_id', $institutionId)
            ->whereIn('id', AcademicClassAssign::where('institution_id', $institutionId)
                ->distinct()
                ->pluck('class_id'))
            ->orderBy('id')
            ->get();
    }

    public function getAvailableSections()
    {
        if (!$this->filterClass) return collect();

        $institutionId = institution()->id;

        return AcademicSection::where('institution_id', $institutionId)
            ->whereIn('id',
                AcademicClassAssign::where('institution_id', $institutionId)
                    ->where('class_id', $this->filterClass)
                    ->whereNotNull('section_id')
                    ->pluck('section_id')
            )->orderBy('name')->get();
    }

    public function updatedFilterClass()
    {
        $this->filterSection           = '';
        $this->hasSchedule             = false;
        $this->scheduleGrid            = [];
        $this->selectedClassHasSection = true;

        if (!$this->filterClass) return;

        $institutionId = institution()->id;

        // Defense-in-depth: institution_id explicitly check kora holo (IDOR protection)
        $class = AcademicClass::where('institution_id', $institutionId)->find($this->filterClass);

        if (!$class) {
            $this->filterClass = '';
            return;
        }

        $this->selectedClassHasSection = (bool) $class->has_section;
    }

    public function updatedFilterSection()
    {
        $this->hasSchedule  = false;
        $this->scheduleGrid = [];
    }

    public function filter()
    {
        $institutionId = institution()->id;

        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }

        // Defense-in-depth: filterClass ei institution-er against valid kina check kora holo
        $class = AcademicClass::where('institution_id', $institutionId)->find($this->filterClass);

        if (!$class) {
            $this->dispatch('toast', type: 'error', message: 'Selected class is invalid.');
            return;
        }

        // Defense-in-depth: filterSection tampering thekano — shudhu ei class-er
        // jonno valid section allow kora hocche
        $allowedSectionIds = $this->getAvailableSections()->pluck('id')->toArray();

        if ($this->filterSection && $this->filterSection !== 'all' && ! in_array((int) $this->filterSection, $allowedSectionIds, true)) {
            $this->dispatch('toast', type: 'error', message: 'Selected section is not valid for this class.');
            return;
        }

        $sectionId = ($this->filterSection && $this->filterSection !== 'all')
            ? $this->filterSection
            : null;

        $schedules = AcademicClassSchedule::where('institution_id', $institutionId)
            ->where('class_id', $this->filterClass)
            ->where('section_id', $sectionId)
            ->get()
            ->keyBy('day');

        $maxPeriods = $schedules->max(fn($s) => count($s->data ?? [])) ?? 0;

        // Schedule row-gulote subject_id/teacher_id thake, tai display korar
        // jonno ekbare shob subject/teacher name ke id => name map e load kora holo (N+1 thekano)
        $subjectIds = $schedules->flatMap(fn($s) => collect($s->data ?? [])->pluck('subject_id'))
            ->filter()
            ->unique();

        $teacherIds = $schedules->flatMap(fn($s) => collect($s->data ?? [])->pluck('teacher_id'))
            ->filter()
            ->unique();

        $subjectNames = AcademicSubject::where('institution_id', $institutionId)
            ->whereIn('id', $subjectIds)
            ->pluck('name', 'id');

        $teacherNames = User::where('institution_id', $institutionId)
            ->whereIn('id', $teacherIds)
            ->pluck('name', 'id');

        $grid = [];
        for ($i = 0; $i < $maxPeriods; $i++) {
            $row = [];
            foreach ($this->days as $day) {
                $item = isset($schedules[$day]) ? ($schedules[$day]->data[$i] ?? null) : null;

                if ($item) {
                    $item['subject_name'] = $subjectNames[$item['subject_id'] ?? null] ?? 'Unknown';
                    $item['teacher_name'] = $teacherNames[$item['teacher_id'] ?? null] ?? 'Unassigned';
                }

                $row[$day] = $item;
            }
            $grid[] = $row;
        }

        $this->scheduleGrid = $grid;
        $this->hasSchedule  = true;
    }

    public function render()
    {
        return view('livewire.admin.academic.class-schedule-list-component')
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->with('days', $this->days)
            ->layout('layouts.admin.app', [
                'title' => 'Class Schedule | ' . institution()->name,
            ]);
    }
}