<?php

namespace App\Livewire\Admin\Exam;

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

    public array $availableSections = [];

    public function updatedClassId($value): void
    {
        $this->section_id = null;
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
            'exam_id'    => 'required|exists:exam_setups,id',
            'class_id'   => 'required|exists:academic_classes,id',
            'section_id' => 'nullable|exists:academic_sections,id',
        ]);

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
            'section_id' => 'nullable|exists:academic_sections,id',

            'data.*.subject'                => 'required',
            'data.*.exam_date'              => 'required',
            'data.*.start_time'             => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->failIfSameDateTimeClash($attribute, $value, $fail, 'start_time', 'Starting Time');
                },
            ],
            'data.*.end_time'               => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->failIfSameDateTimeClash($attribute, $value, $fail, 'end_time', 'Ending Time');
                },
            ],
            // 'data.*.end_time'               => 'required|date_format:H:i|after:data.*.start_time',
            'data.*.hall_room'              => 'required|string|max:100',
            'data.*.practical_full_mark'    => 'required|numeric|min:0',
            'data.*.practical_pass_mark'    => 'required|numeric|min:0',
            'data.*.written_full_mark'      => 'required|numeric|min:0',
            'data.*.written_pass_mark'      => 'required|numeric|min:0',
        ], [], [
            // Custom attribute names - eta diye message-e "data.0.hall_room" er jaygay "Hall Room" dekhabe
            'data.*.subject'                => 'Subject',
            'data.*.exam_date'              => 'Date',
            'data.*.start_time'             => 'Starting Time',
            'data.*.end_time'               => 'Ending Time',
            'data.*.hall_room'              => 'Hall Room',
            'data.*.practical_full_mark'    => 'Practical Full Mark',
            'data.*.practical_pass_mark'    => 'Practical Pass Mark',
            'data.*.written_full_mark'      => 'Written Full Mark',
            'data.*.written_pass_mark'      => 'Written Pass Mark',
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

    /**
     * Same exam_date-e duita subject-er Starting Time ba Ending Time ekই hoye gele
     * error dekhabe, jate akoi diner exam-gulor somoy clash na kore.
     */
    private function failIfSameDateTimeClash($attribute, $value, $fail, string $field, string $label): void
    {
        if (!preg_match('/^data\.(\d+)\.' . $field . '$/', $attribute, $matches)) {
            return;
        }

        $currentIndex = (int) $matches[1];
        $currentDate  = $this->data[$currentIndex]['exam_date'] ?? null;

        if (!$currentDate || !$value) {
            return;
        }

        foreach ($this->data as $index => $row) {
            if ($index === $currentIndex) {
                continue;
            }

            if (($row['exam_date'] ?? null) === $currentDate && ($row[$field] ?? null) === $value) {
                $fail("Same date-e {$label} alada hote hobe, dui subject-er somoy ekই rakha jabe na.");
                return;
            }
        }
    }

    public function render()
    {
        $exams = ExamSetup::all();
        $classes = AcademicClass::all();
        $sections = AcademicSection::all();

        return view('livewire.admin.exam.schedule-add-component')
            ->with('exams', $exams)
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->layout('layouts.admin.app', [
                'title' => 'Exam Schedule | ' . institution()->name,
            ]);
    }
}