<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssign;
use App\Models\AcademicSubject;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\User;

class ClassScheduleCreateComponent extends Component
{
    public $filterClass   = '';
    public $filterSection = '';
    public $filterDay     = 'Sunday';
    public $data          = [];

    public $hasSchedule = false;
    public $schedule_id;

    // ekhon shudhu subject na, subject + tar default teacher_id o thakbe
    public array $availableSubjects = [];

    // class+section er shob possible teacher (dropdown er jonno)
    public array $availableTeachers = [];

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
            AcademicClassAssign::where('class_id', $this->filterClass)
                ->whereNotNull('section_id')
                ->pluck('section_id')
        )->orderBy('name')->get();
    }

    public function updatedFilterClass()
    {
        $this->filterSection      = '';
        $this->availableSubjects  = [];
        $this->availableTeachers  = [];
        $this->hasSchedule        = false;
        $this->data                = [];

        if (!$this->filterClass) return;

        $this->loadSubjects($this->filterClass, null);
    }

    public function updatedFilterSection()
    {
        $this->availableSubjects = [];
        $this->availableTeachers = [];
        $this->hasSchedule       = false;
        $this->data              = [];

        if (!$this->filterClass || !$this->filterSection) return;

        if ($this->filterSection === 'all') {
            $details = \App\Models\AcademicClassAssignDetail::with(['subject', 'teacher'])
                ->whereHas('classAssign', fn($q) => $q->where('class_id', $this->filterClass))
                ->get();

            $this->buildSubjectsAndTeachers($details);
            return;
        }

        $this->loadSubjects($this->filterClass, $this->filterSection);
    }

    protected function loadSubjects($class_id, $section_id = null): void
    {
        $query = AcademicClassAssign::with('details.subject', 'details.teacher')
            ->where('class_id', $class_id);

        if ($section_id) {
            $query->where('section_id', $section_id);
        } else {
            $query->whereNull('section_id');
        }

        $assign = $query->first();

        $details = $assign ? $assign->details : collect();

        $this->buildSubjectsAndTeachers($details);
    }

    /**
     * AcademicClassAssignDetail collection theke availableSubjects (with default teacher)
     * ar availableTeachers (unique list) build kore
     */
    protected function buildSubjectsAndTeachers($details): void
    {
        $this->availableSubjects = $details
            ->filter(fn($d) => $d->subject)
            ->unique('subject_id')
            ->map(fn($d) => [
                'id'              => $d->subject->id,
                'name'            => $d->subject->name,
                'default_teacher' => $d->teacher->name ?? '',
            ])
            ->values()
            ->toArray();

        $this->availableTeachers = $details
            ->filter(fn($d) => $d->teacher)
            ->unique('teacher_id')
            ->map(fn($d) => [
                'id'   => $d->teacher->id,
                'name' => $d->teacher->name,
            ])
            ->values()
            ->toArray();
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

            // subject select korar shathe shathei tar default teacher o auto-fill hoye jabe
            $this->data = array_map(fn($i) => [
                'subject'    => $this->availableSubjects[$i]['name'] ?? '',
                'teacher'    => $this->availableSubjects[$i]['default_teacher'] ?? '',
                'start_time' => '09:00',
                'end_time'   => '10:00',
                'class_room' => '',
            ], range(0, $rows - 1));

            $this->hasSchedule = true;
        }
    }

    /**
     * Subject change hole, oi subject er jonno assign kora teacher ke
     * automatically data row e boshiye dao
     */
    public function updatedData($value, $key)
    {
        if (str_ends_with($key, '.subject')) {
            $index = explode('.', $key)[0];

            $subject = collect($this->availableSubjects)->firstWhere('name', $value);

            if ($subject && !empty($subject['default_teacher'])) {
                $this->data[$index]['teacher'] = $subject['default_teacher'];
            }
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
        $this->filterDay         = 'Sunday';
        $this->data              = [];
        $this->hasSchedule       = false;
        $this->schedule_id       = null;
        $this->availableSubjects = [];
        $this->availableTeachers = [];
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate([
            'filterClass'       => 'required|exists:academic_classes,id',
            'filterSection'     => 'nullable',
            'filterDay'         => 'required|string|max:20',
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
        return view('livewire.admin.academic.class-schedule-create-component')
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->with('subjects', $this->availableSubjects)
            ->with('teachers', $this->availableTeachers)
            ->layout('layouts.admin.app', [
                'title' => 'Class Schedule | ' . institution()->name,
            ]);
    }
}