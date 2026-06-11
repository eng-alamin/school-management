<?php

namespace App\Livewire\Tenant\Teacher\Exam;

use Livewire\Component;
use App\Models\ExamSchedule;
use App\Models\ExamSetup;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;

class ScheduleAddComponent extends Component
{
    public $exam_id;
    public $class_id;
    public $section_id;
    public $data = [];

    public $schedule = null;
    public $hasSchedule = false;
    public $schedule_id;

    public function render()
    {
        $exams = ExamSetup::all();
        $classes = AcademicClass::all();
        $sections = AcademicSection::all();

        return view('livewire.tenant.teacher.exam.schedule-add-component')
            ->with('exams', $exams)
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->layout('layouts.teacher.app', [
                'title' => "Exam Schedule | School SaaS",
            ]);
    }


    public function filter()
    {
        if (!$this->exam_id || !$this->class_id || !$this->section_id) return;

        $assign   = AcademicClassAssign::where('class_id',   $this->class_id)
            ->where('section_id', $this->section_id)
            ->first();

        $subjects = collect($assign?->subjects ?? []);

        if ($subjects->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No subjects found for this class & section.');
            return;
        }

        $schedule = ExamSchedule::where('exam_id',    $this->exam_id)
            ->where('class_id',   $this->class_id)
            ->where('section_id', $this->section_id)
            ->first();

        if ($schedule) {
            $savedData         = collect($schedule->data);
            $savedSubjectNames = $savedData->pluck('subject');

            $newRows = $subjects
                ->diff($savedSubjectNames)
                ->values()
                ->map(fn($subject) => [
                    'subject'             => $subject,
                    'exam_date'           => '',
                    'start_time'          => '10:00',
                    'end_time'            => '01:00',
                    'hall_room'           => '',
                    'practical_full_mark' => '',
                    'practical_pass_mark' => '',
                    'written_full_mark'   => '',
                    'written_pass_mark'   => '',
                ]);

            $this->data        = $savedData->concat($newRows)->values()->toArray();
            $this->schedule_id = $schedule->id;
            $this->hasSchedule = true;
            return;
        }

        $this->schedule_id = null;
        $this->data        = $subjects->map(fn($subject) => [
            'subject'             => $subject,
            'exam_date'           => '',
            'start_time'          => '10:00',
            'end_time'            => '01:00',
            'hall_room'           => '',
            'practical_full_mark' => '',
            'practical_pass_mark' => '',
            'written_full_mark'   => '',
            'written_pass_mark'   => '',
        ])->toArray();

        $this->hasSchedule = true;
    }

    public function save()
    {
        $this->validate([
            'exam_id' => 'required|exists:exam_setups,id',
            'class_id' => 'required|exists:academic_classes,id',
            'section_id' => 'required|exists:academic_sections,id',

            'data.*.subject'                => 'required',
            'data.*.exam_date'              => 'required',
            'data.*.start_time'             => 'required|date_format:H:i',
            'data.*.end_time'               => 'required|date_format:H:i',
            // 'data.*.end_time'               => 'required|date_format:H:i|after:data.*.start_time',
            'data.*.hall_room'              => 'required|string|max:100',
            'data.*.practical_full_mark'    => 'required|numeric|min:0',
            'data.*.practical_pass_mark'    => 'required|numeric|min:0',
            'data.*.written_full_mark'      => 'required|numeric|min:0',
            'data.*.written_pass_mark'      => 'required|numeric|min:0',
        ]);

        try {
            ExamSchedule::updateOrCreate(
                [
                    'exam_id'   => $this->exam_id,
                    'class_id'   => $this->class_id,
                    'section_id' => $this->section_id,
                ],
                [
                    'data' => $this->data,
                ]
            );
            
            $this->dispatch('toast', type: 'success', message: 'Exam schedule saved successfully!');

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Creation failed: ' . $e->getMessage());
        }
    }
}

