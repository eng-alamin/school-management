<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssign;
use App\Models\AcademicTeacherAssign;
use App\Models\AcademicSubject;
use App\Models\AcademicClass;
use App\Models\AcademicSection;

class ClassScheduleCreateComponent extends Component
{
    public $filterClass   = '';
    public $filterSection = '';
    public $filterDay           = 'Sunday';
    public $data          = [];

    public $hasSchedule = false;
    public $schedule_id;

    public array $availableSubjects = [];

    public function mount()
    {
        $this->filterDay = 'Sunday';
    }

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
        $this->filterSection     = '';
        $this->availableSubjects = [];
        $this->hasSchedule       = false;
        $this->data              = [];

        if (!$this->filterClass) return;

        $this->loadSubjects($this->filterClass, null);
    }

    public function updatedFilterSection()
    {
        $this->availableSubjects = [];
        $this->hasSchedule       = false;
        $this->data              = [];

        if (!$this->filterClass || !$this->filterSection) return;

        if ($this->filterSection === 'all') {
            $rows = AcademicClassAssign::where('class_id', $this->filterClass)->get();

            $subjectNames = $rows->flatMap(function ($row) {
                $subjects = $row->subjects;
                if (is_string($subjects)) {
                    $subjects = json_decode($subjects, true) ?? [];
                }
                return $subjects ?: [];
            })
            ->filter()
            ->unique()
            ->values();

            $this->availableSubjects = $subjectNames->isNotEmpty()
                ? AcademicSubject::whereIn('name', $subjectNames)
                    ->orderBy('name')
                    ->get()
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->toArray()
                : [];

            return;
        }

        $this->loadSubjects($this->filterClass, $this->filterSection);
    }

    protected function loadSubjects($class_id, $section_id = null): void
    {
        $query = AcademicClassAssign::where('class_id', $class_id);

        if ($section_id) {
            $query->where('section_id', $section_id);
        } else {
            $query->whereNull('section_id');
        }

        $assign = $query->first();

        if ($assign && !empty($assign->subjects)) {
            $this->availableSubjects = AcademicSubject::whereIn('name', $assign->subjects)
                ->orderBy('name')
                ->get()
                ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                ->toArray();
        } else {
            $this->availableSubjects = [];
        }
    }

    public function filter()
    {
        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }

        if (!$this->filterDay) {
            $this->dispatch('toast', type: 'error', message: 'Please select a day.');
            return;
        }

        $sectionId = ($this->filterSection && $this->filterSection !== 'all')
            ? $this->filterSection
            : null;

        $schedule = AcademicClassSchedule::where('class_id', $this->filterClass)
            ->where('section_id', $sectionId)
            ->where('day', $this->filterDay)
            ->first();

        if ($schedule) {
            $this->data        = $schedule->data;
            $this->schedule_id = $schedule->id;
            $this->hasSchedule = true;
        } else {
            $this->schedule_id = null;

            $subjectCount = count($this->availableSubjects);
            $rows         = $subjectCount > 0 ? $subjectCount : 1;

            $this->data = array_map(fn($i) => [
                'subject'    => $this->availableSubjects[$i]['name'] ?? '',
                'teacher'    => '',
                'start_time' => '09:00',
                'end_time'   => '10:00',
                'class_room' => '',
            ], range(0, $rows - 1));

            $this->hasSchedule = true;
        }
    }

    public function addRow()
    {
        $this->data[] = [
            'subject'    => '',
            'teacher'    => '',
            'start_time' => '09:00',
            'end_time'   => '10:00',
            'class_room' => '',
        ];

        $this->dispatch('rowAdded');
    }

    public function removeRow($index)
    {
        unset($this->data[$index]);
        $this->data = array_values($this->data);
    }

    public function resetForm()
    {
        $this->filterClass       = '';
        $this->filterSection     = '';
        $this->filterDay               = 'Sunday';
        $this->data              = [];
        $this->hasSchedule       = false;
        $this->schedule_id       = null;
        $this->availableSubjects = [];
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'filterClass'       => 'required|exists:academic_classes,id',
            'filterSection'     => 'nullable',
            'filterDay'               => 'required|string|max:20',
            'data.*.subject'    => 'required',
            'data.*.teacher'    => 'required',
            'data.*.start_time' => 'required|date_format:H:i',
            'data.*.end_time'   => 'required|date_format:H:i|after:data.*.start_time',
            'data.*.class_room' => 'nullable|string|max:100',
        ]);

        try {
            $sectionId = ($this->filterSection && $this->filterSection !== 'all')
                ? $this->filterSection
                : null;

            AcademicClassSchedule::updateOrCreate(
                [
                    'class_id'   => $this->filterClass,
                    'section_id' => $sectionId,
                    'day'        => $this->filterDay,
                ],
                [
                    'data' => $this->data,
                ]
            );

            $this->dispatch('toast', type: 'success', message: 'Class schedule saved successfully!');

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Creation failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $teachers = AcademicTeacherAssign::with(['teacher.user', 'teacher.designation', 'teacher.department'])
            ->when($this->filterClass, fn($q) => $q->where('class_id', $this->filterClass))
            ->when($this->filterSection && $this->filterSection !== 'all',
                fn($q) => $q->where('section_id', $this->filterSection))
            ->get()
            ->map(fn($a) => $a->teacher)
            ->filter()
            ->unique('id')
            ->map(fn($t) => [
                'id'   => $t->id,
                'name' => $t->user->name ?? $t->name ?? 'Unknown',
            ])
            ->values();

        return view('livewire.admin.academic.class-schedule-create-component')
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->with('subjects', $this->availableSubjects)
            ->with('teachers', $teachers)
            ->layout('layouts.admin.app', [
                'title' => 'Class Schedule | ' . institution()->name,
            ]);
    }
}