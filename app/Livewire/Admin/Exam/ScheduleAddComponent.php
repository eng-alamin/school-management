<?php

namespace App\Livewire\Admin\Exam;

use Livewire\Component;
use App\Models\ExamSchedule;
use App\Models\ExamSetup;
use Illuminate\Support\Facades\DB;

class ScheduleAddComponent extends Component
{
    public $filterExam = '';

    // নির্বাচিত exam এর class info দেখানোর জন্য (read-only)
    public ?string $selectedClassLabel = null;

    // rows[index] = ['exam_setup_detail_id'=>, 'subject_name'=>, 'full_mark'=>, 'pass_mark'=>, 'exam_date'=>, 'start_time'=>, 'end_time'=>, 'class_room'=>, 'remarks'=>, 'is_published'=>]
    public array $rows = [];

    public bool $hasSchedule = false;

    public function updatedFilterExam(): void
    {
        $this->hasSchedule        = false;
        $this->rows               = [];
        $this->selectedClassLabel = null;
    }

    public function filter(): void
    {
        if (!$this->filterExam) {
            $this->dispatch('toast', type: 'error', message: 'Please select an exam.');
            return;
        }

        $examSetup = ExamSetup::with([
            'classAssign.class',
            'classAssign.section',
            'details.classAssignDetail.subject',
        ])->find($this->filterExam);

        if (!$examSetup) {
            $this->dispatch('toast', type: 'error', message: 'Exam setup পাওয়া যায়নি।');
            return;
        }

        if ($examSetup->details->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'এই Exam এ কোনো Subject পাওয়া যায়নি।');
            return;
        }

        $this->selectedClassLabel = ($examSetup->classAssign->class->name ?? '—')
            . ($examSetup->classAssign->section ? ' - ' . $examSetup->classAssign->section->name : '');

        // existing schedules load করো (edit case)
        $existingSchedules = ExamSchedule::where('exam_setup_id', $examSetup->id)
            ->get()
            ->keyBy('exam_setup_detail_id');

        $this->rows = [];
        foreach ($examSetup->details as $detail) {
            $existing = $existingSchedules->get($detail->id);

            $this->rows[] = [
                'exam_setup_detail_id' => $detail->id,
                'subject_name'         => $detail->classAssignDetail->subject->name ?? '—',
                'full_mark'            => $detail->full_mark,
                'pass_mark'            => $detail->pass_mark,
                'exam_date'            => $existing?->exam_date?->format('Y-m-d') ?? '',
                'start_time'           => $existing?->start_time ? substr($existing->start_time, 0, 5) : '10:00',
                'end_time'             => $existing?->end_time ? substr($existing->end_time, 0, 5) : '13:00',
                'class_room'           => $existing?->class_room ?? '',
                'remarks'              => $existing?->remarks ?? '',
                'is_published'         => $existing?->is_published ?? false,
            ];
        }

        $this->hasSchedule = true;
    }

    public function save(): void
    {
        $this->validate([
            'filterExam' => 'required|exists:exam_setups,id',

            'rows.*.exam_date'   => 'required|date',
            'rows.*.start_time'  => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->failIfSameDateTimeClash($attribute, $value, $fail, 'start_time', 'Starting Time');
                },
            ],
            'rows.*.end_time'    => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->failIfSameDateTimeClash($attribute, $value, $fail, 'end_time', 'Ending Time');
                },
            ],
            'rows.*.class_room'  => 'nullable|string|max:100',
            'rows.*.remarks'     => 'nullable|string',
        ], [], [
            'rows.*.exam_date'  => 'Date',
            'rows.*.start_time' => 'Starting Time',
            'rows.*.end_time'   => 'Ending Time',
            'rows.*.class_room' => 'Class Room',
        ]);

        DB::beginTransaction();
        try {
            foreach ($this->rows as $row) {
                ExamSchedule::updateOrCreate(
                    [
                        'exam_setup_id'        => $this->filterExam,
                        'exam_setup_detail_id' => $row['exam_setup_detail_id'],
                    ],
                    [
                        'exam_date'    => $row['exam_date'],
                        'start_time'   => $row['start_time'],
                        'end_time'     => $row['end_time'],
                        'class_room'   => $row['class_room'] ?: null,
                        'remarks'      => $row['remarks'] ?: null,
                        'is_published' => $row['is_published'] ?? false,
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            return;
        }

        $this->dispatch('toast', type: 'success', message: 'Exam schedule saved successfully!');
    }

    // একই Exam এর মধ্যে একই তারিখ ও একই সময়ে দুইটা subject clash করছে কিনা চেক করে
    private function failIfSameDateTimeClash($attribute, $value, $fail, string $field, string $label): void
    {
        if (!preg_match('/^rows\.(\d+)\.' . $field . '$/', $attribute, $matches)) return;

        $currentIndex = (int) $matches[1];
        $currentDate  = $this->rows[$currentIndex]['exam_date'] ?? null;

        if (!$currentDate || !$value) return;

        foreach ($this->rows as $index => $row) {
            if ($index === $currentIndex) continue;
            if (($row['exam_date'] ?? null) === $currentDate && ($row[$field] ?? null) === $value) {
                $fail("Same date-e {$label} alada hote hobe.");
                return;
            }
        }
    }

    public function resetForm(): void
    {
        $this->filterExam         = '';
        $this->rows               = [];
        $this->hasSchedule        = false;
        $this->selectedClassLabel = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.exam.schedule-add-component')
            ->with('exams', ExamSetup::with('classAssign.class', 'classAssign.section')
                ->orderBy('name')
                ->get())
            ->layout('layouts.admin.app', [
                'title' => 'Exam Schedule | ' . institution()->name,
            ]);
    }
}