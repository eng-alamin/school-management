<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClass;
use App\Models\AcademicSubject;
use App\Models\Employee;

class ClassScheduleCreateComponent extends Component
{
    public $class_id;
    public $section_id;
    public $day;
    public $data = [];

    public $schedule = null;
    public $hasSchedule = false;
    public $schedule_id;

    public array $availableSections = [];

    public function updatedClassId($value): void
    {
        $this->section_id;
        $this->availableSections = [];
        $this->hasSchedule       = false;
        $this->data              = [];
        $this->day = "Sunday";

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
        if (!$this->class_id || !$this->day) return;

        $schedule = AcademicClassSchedule::where('class_id', $this->class_id)
            ->where('section_id', $this->section_id)
            ->where('day', $this->day)
            ->first();

        if ($schedule) {
            $this->data        = $schedule->data;
            $this->schedule_id = $schedule->id;
            $this->hasSchedule = true;
        } else {
            $this->schedule_id = null;
            $this->data        = [
                [
                    'subject'    => '',
                    'teacher'    => '',
                    'start_time' => '09:00',
                    'end_time'   => '10:00',
                    'class_room' => '',
                ]
            ];
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
        $this->reset(['class_id', 'section_id', 'day', 'data', 'hasSchedule', 'schedule_id', 'availableSections']);
    }

    public function save()
    {
        $this->validate([
            'class_id'            => 'required|exists:academic_classes,id',
            'section_id'          => 'nullable|exists:academic_sections,id',
            'day'                 => 'required|string|max:20',
            'data.*.subject'      => 'required',
            'data.*.teacher'      => 'required',
            'data.*.start_time'   => 'required|date_format:H:i',
            'data.*.end_time'     => 'required|date_format:H:i|after:data.*.start_time',
            'data.*.class_room'   => 'nullable|string|max:100',
        ]);

        try {
            AcademicClassSchedule::updateOrCreate(
                [
                    'class_id'   => $this->class_id,
                    'section_id' => $this->section_id,
                    'day'        => $this->day,
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
        $classes  = AcademicClass::orderBy('id')->get();
        $subjects = AcademicSubject::orderBy('name')->get();
        $teachers = Employee::with(['designation', 'department', 'user'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'teacher');
            })
            ->orderBy('id', 'asc')
            ->get();

        return view('livewire.admin.academic.class-schedule-create-component')
            ->with('classes', $classes)
            ->with('subjects', $subjects)
            ->with('teachers', $teachers)
            ->layout('layouts.admin.app', [
                'title' => "Class Schedule | School SaaS",
            ]);
    }
}