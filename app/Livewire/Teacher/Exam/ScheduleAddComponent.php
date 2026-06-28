<?php

namespace App\Livewire\Teacher\Exam;

use Livewire\Component;
use App\Models\ExamSchedule;
use App\Models\ExamSetup;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;

class ScheduleAddComponent extends Component
{
    public $filterExam    = '';
    public $filterClass   = '';
    public $filterSection = '';
    public $data          = [];

    public $hasSchedule = false;
    public $schedule_id;

    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    public function getAvailableSections()
    {
        if (!$this->filterClass) return [];

        return AcademicSection::whereIn('id',
            AcademicClassAssign::where('class_id', $this->filterClass)->pluck('section_id')
        )->orderBy('name')->get();
    }

    public function updatedFilterClass()
    {
        $this->filterSection = '';
        $this->hasSchedule   = false;
        $this->data          = [];

        if (!$this->filterClass) return;
    }

    public function updatedFilterSection()
    {
        $this->hasSchedule = false;
        $this->data        = [];

        if (!$this->filterClass) return;
    }

    public function filter()
    {
        if (!$this->filterExam) {
            $this->dispatch('toast', type: 'error', message: 'Please select an exam.');
            return;
        }

        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }

        $sectionId = ($this->filterSection && $this->filterSection !== 'all')
            ? $this->filterSection
            : null;

        $assign = AcademicClassAssign::where('class_id', $this->filterClass)
            ->where('section_id', $sectionId)
            ->first();

        $subjects = collect($assign?->subjects ?? []);

        if ($subjects->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No subjects found for this class & section.');
            return;
        }

        $schedule = ExamSchedule::where('exam_id', $this->filterExam)
            ->where('class_id', $this->filterClass)
            ->where('section_id', $sectionId)
            ->first();

        if ($schedule) {
            $savedData         = collect($schedule->data);
            $savedSubjectNames = $savedData->pluck('subject');

            $newRows = $subjects
                ->diff($savedSubjectNames)
                ->values()
                ->map(fn($subject) => $this->emptyRow($subject));

            $this->data        = $savedData->concat($newRows)->values()->toArray();
            $this->schedule_id = $schedule->id;
            $this->hasSchedule = true;
            return;
        }

        $this->schedule_id = null;
        $this->data        = $subjects->map(fn($subject) => $this->emptyRow($subject))->toArray();
        $this->hasSchedule = true;
    }

    private function emptyRow(string $subject): array
    {
        return [
            'subject'             => $subject,
            'exam_date'           => '',
            'start_time'          => '10:00',
            'end_time'            => '01:00',
            'hall_room'           => '',
            'practical_full_mark' => '',
            'practical_pass_mark' => '',
            'written_full_mark'   => '',
            'written_pass_mark'   => '',
        ];
    }

    public function save()
    {
        $this->validate([
            'filterExam'    => 'required|exists:exam_setups,id',
            'filterClass'   => 'required|exists:academic_classes,id',
            'filterSection' => 'nullable',

            'data.*.subject'             => 'required',
            'data.*.exam_date'           => 'required',
            'data.*.start_time'          => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->failIfSameDateTimeClash($attribute, $value, $fail, 'start_time', 'Starting Time');
                },
            ],
            'data.*.end_time'            => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->failIfSameDateTimeClash($attribute, $value, $fail, 'end_time', 'Ending Time');
                },
            ],
            'data.*.hall_room'           => 'required|string|max:100',
            'data.*.practical_full_mark' => 'required|numeric|min:0',
            'data.*.practical_pass_mark' => 'required|numeric|min:0',
            'data.*.written_full_mark'   => 'required|numeric|min:0',
            'data.*.written_pass_mark'   => 'required|numeric|min:0',
        ], [], [
            'data.*.subject'             => 'Subject',
            'data.*.exam_date'           => 'Date',
            'data.*.start_time'          => 'Starting Time',
            'data.*.end_time'            => 'Ending Time',
            'data.*.hall_room'           => 'Hall Room',
            'data.*.practical_full_mark' => 'Practical Full Mark',
            'data.*.practical_pass_mark' => 'Practical Pass Mark',
            'data.*.written_full_mark'   => 'Written Full Mark',
            'data.*.written_pass_mark'   => 'Written Pass Mark',
        ]);

        try {
            $sectionId = ($this->filterSection && $this->filterSection !== 'all')
                ? $this->filterSection
                : null;

            ExamSchedule::updateOrCreate(
                [
                    'exam_id'    => $this->filterExam,
                    'class_id'   => $this->filterClass,
                    'section_id' => $sectionId,
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

    private function failIfSameDateTimeClash($attribute, $value, $fail, string $field, string $label): void
    {
        if (!preg_match('/^data\.(\d+)\.' . $field . '$/', $attribute, $matches)) return;

        $currentIndex = (int) $matches[1];
        $currentDate  = $this->data[$currentIndex]['exam_date'] ?? null;

        if (!$currentDate || !$value) return;

        foreach ($this->data as $index => $row) {
            if ($index === $currentIndex) continue;
            if (($row['exam_date'] ?? null) === $currentDate && ($row[$field] ?? null) === $value) {
                $fail("Same date-e {$label} alada hote hobe.");
                return;
            }
        }
    }

    public function resetForm()
    {
        $this->filterExam    = '';
        $this->filterClass   = '';
        $this->filterSection = '';
        $this->data          = [];
        $this->hasSchedule   = false;
        $this->schedule_id   = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.teacher.exam.schedule-add-component')
            ->with('exams', ExamSetup::orderBy('name')->get())
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->layout('layouts.teacher.app', [
                'title' => 'Exam Schedule | ' . institution()->name,
            ]);
    }
}