<?php

namespace App\Livewire\Branch\Exam;

use Livewire\Component;
use App\Models\ExamSchedule;
use App\Models\ExamSetup;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'classAssign.academicClass',
            'classAssign.academicSection',
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

        $this->selectedClassLabel = $this->buildClassLabel($examSetup);

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
            // FIX (IDOR): plain 'exists' rule Eloquent global scope বাইপাস করে raw
            // DB query চালায় — তাই institution_id দিয়ে explicit scope করা হলো,
            // নাহলে অন্য institution-এর exam_setup id পাঠিয়েও validation পাশ হয়ে যেত।
            'filterExam' => [
                'required',
                Rule::exists('exam_setups', 'id')->where('institution_id', institution()->id),
            ],

            'rows.*.exam_date'   => 'required|date',
            'rows.*.start_time'  => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->failIfOverlap($attribute, $value, $fail);
                },
            ],
            // FIX: আগে end_time শুধু format check হতো, start_time এর চেয়ে পরে
            // আছে কিনা সেটা validate হতো না — এখন 'after:rows.*.start_time' দিয়ে
            // সেই ভুল ঠেকানো হচ্ছে।
            'rows.*.end_time'    => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $this->failIfEndBeforeStart($attribute, $value, $fail);
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
                        // FIX: institution_id মিসিং ছিল — established pattern অনুযায়ী
                        // এটা NOT NULL কলাম, explicit সেট করা লাগবে।
                        'institution_id' => institution()->id,
                        'exam_date'       => $row['exam_date'],
                        'start_time'      => $row['start_time'],
                        'end_time'        => $row['end_time'],
                        'class_room'      => $row['class_room'] ?: null,
                        'remarks'         => $row['remarks'] ?: null,
                        'is_published'    => $row['is_published'] ?? false,
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

    /**
     * একই তারিখে দুইটা subject-এর সময় overlap করছে কিনা চেক করে।
     *
     * FIX: আগে শুধু exact start_time == start_time বা end_time == end_time মিলিয়ে
     * clash ধরা হতো — তাই 10:00–12:00 আর 11:00–13:00 (আসলে overlap করছে)
     * ধরা পড়ত না, কারণ দুটোরই start/end আলাদা। এখন প্রকৃত সময়-পরিসীমা
     * (range) তুলনা করে overlap ধরা হচ্ছে।
     */
    private function failIfOverlap($attribute, $value, $fail): void
    {
        if (!preg_match('/^rows\.(\d+)\.start_time$/', $attribute, $matches)) return;

        $currentIndex = (int) $matches[1];
        $this->checkOverlapForIndex($currentIndex, $fail);
    }

    private function failIfEndBeforeStart($attribute, $value, $fail): void
    {
        if (!preg_match('/^rows\.(\d+)\.end_time$/', $attribute, $matches)) return;

        $currentIndex = (int) $matches[1];
        $start = $this->rows[$currentIndex]['start_time'] ?? null;

        if ($start && $value && $value <= $start) {
            $fail('Ending Time অবশ্যই Starting Time এর পরে হতে হবে।');
            return;
        }

        $this->checkOverlapForIndex($currentIndex, $fail);
    }

    private function checkOverlapForIndex(int $currentIndex, callable $fail): void
    {
        $current = $this->rows[$currentIndex] ?? null;
        $date    = $current['exam_date'] ?? null;
        $start   = $current['start_time'] ?? null;
        $end     = $current['end_time'] ?? null;

        if (!$date || !$start || !$end) return;

        foreach ($this->rows as $index => $row) {
            if ($index === $currentIndex) continue;
            if (($row['exam_date'] ?? null) !== $date) continue;

            $otherStart = $row['start_time'] ?? null;
            $otherEnd   = $row['end_time'] ?? null;
            if (!$otherStart || !$otherEnd) continue;

            // দুইটা time-range overlap করে যদি: start1 < end2 AND start2 < end1
            if ($start < $otherEnd && $otherStart < $end) {
                $fail("Same date-e {$row['subject_name']} এর সময়ের সাথে overlap করছে।");
                return;
            }
        }
    }

    /**
     * ExamSetup theke ClassAssign er human-readable label banay.
     *
     * FIX: class/section relation naam bhul chilo (model-e nai) — real relation
     * academicClass/academicSection babohar kora holo. Section na thakle
     * "All Section" er moto placeholder text na dekhiye shudhu class name dekhano hocche.
     */
    private function buildClassLabel(ExamSetup $examSetup): string
    {
        $className = $examSetup->classAssign->academicClass->name ?? '—';

        if ($examSetup->classAssign->academicSection) {
            return $className . ' - ' . $examSetup->classAssign->academicSection->name;
        }

        return $className;
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
            ->with('exams', ExamSetup::with('classAssign.academicClass', 'classAssign.academicSection')
                ->where('is_published', true)
                ->orderBy('name')
                ->get())
            ->layout('layouts.branch.app', [
                'title' => 'Exam Schedule | ' . institution()->name,
            ]);
    }
}