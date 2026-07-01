<?php

namespace App\Livewire\Admin\Exam;

use Livewire\Component;
use App\Models\ExamSetup;
use App\Models\ExamSetupDetail;
use App\Models\ExamEntry;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class EntryComponent extends Component
{
    // Filter
    public ?int $exam_setup_id        = null;
    public ?int $exam_setup_detail_id = null;

    // Selected exam এর class/section দেখানোর জন্য (read-only)
    public ?string $selectedClassLabel = null;

    // Selected subject এর full/pass mark দেখানোর জন্য
    public ?ExamSetupDetail $selectedDetail = null;

    // entries[student_id] = ['is_absent'=>, 'practical_obtained'=>, 'written_obtained'=>, 'mcq_obtained'=>]
    public array $entries = [];

    // students[] = Student models, শুধু display এর জন্য
    public $students = [];

    public bool $hasResults = false;

    public function updatedExamSetupId(): void
    {
        $this->exam_setup_detail_id = null;
        $this->resetResults();

        if (!$this->exam_setup_id) {
            $this->selectedClassLabel = null;
            return;
        }

        $examSetup = ExamSetup::with('classAssign.class', 'classAssign.section')
            ->find($this->exam_setup_id);

        $this->selectedClassLabel = $examSetup
            ? ($examSetup->classAssign->class->name ?? '—') .
              ($examSetup->classAssign->section ? ' - ' . $examSetup->classAssign->section->name : '')
            : null;
    }

    public function updatedExamSetupDetailId(): void
    {
        $this->resetResults();
    }

    private function resetResults(): void
    {
        $this->hasResults     = false;
        $this->entries        = [];
        $this->students       = [];
        $this->selectedDetail = null;
    }

    public function filter(): void
    {
        if (!$this->exam_setup_id) {
            $this->dispatch('toast', type: 'error', message: 'Please select an exam.');
            return;
        }

        if (!$this->exam_setup_detail_id) {
            $this->dispatch('toast', type: 'error', message: 'Please select a subject.');
            return;
        }

        $this->selectedDetail = ExamSetupDetail::with('classAssignDetail.subject')
            ->find($this->exam_setup_detail_id);

        $examSetup = ExamSetup::with('classAssign')->find($this->exam_setup_id);

        if (!$examSetup || !$examSetup->classAssign) {
            $this->dispatch('toast', type: 'error', message: 'Class information পাওয়া যায়নি।');
            return;
        }

        // ── এই class/section এর সব student লোড করো ──
        // Assumption: Student model এ class_id, section_id column আছে
        $this->students = Student::where('class_id', $examSetup->classAssign->class_id)
            ->when($examSetup->classAssign->section_id, function ($q) use ($examSetup) {
                $q->where('section_id', $examSetup->classAssign->section_id);
            })
            ->orderBy('roll_no')
            ->get();

        if ($this->students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'এই class এ কোনো student পাওয়া যায়নি।');
            return;
        }

        // existing entries লোড করো (edit case)
        $existingEntries = ExamEntry::where('exam_setup_detail_id', $this->exam_setup_detail_id)
            ->get()
            ->keyBy('student_id');

        $this->entries = [];
        foreach ($this->students as $student) {
            $existing = $existingEntries->get($student->id);

            $this->entries[$student->id] = [
                'is_absent'          => $existing?->is_absent ?? false,
                'practical_obtained' => $existing?->practical_obtained,
                'written_obtained'   => $existing?->written_obtained,
                'mcq_obtained'       => $existing?->mcq_obtained,
            ];
        }

        $this->hasResults = true;
    }

    public function save(): void
    {
        $detail = $this->selectedDetail;

        $rules = [];
        foreach ($this->entries as $studentId => $row) {
            $isAbsent = $row['is_absent'] ?? false;

            if (!$isAbsent) {
                if ($detail->practical_mark > 0) {
                    $rules["entries.{$studentId}.practical_obtained"] = "required|numeric|min:0|max:{$detail->practical_mark}";
                }
                if ($detail->written_mark > 0) {
                    $rules["entries.{$studentId}.written_obtained"] = "required|numeric|min:0|max:{$detail->written_mark}";
                }
                if ($detail->mcq_mark > 0) {
                    $rules["entries.{$studentId}.mcq_obtained"] = "required|numeric|min:0|max:{$detail->mcq_mark}";
                }
            }
        }

        $this->validate($rules, [], [
            'entries.*.practical_obtained' => 'Practical Mark',
            'entries.*.written_obtained'   => 'Written Mark',
            'entries.*.mcq_obtained'       => 'MCQ Mark',
        ]);

        DB::beginTransaction();
        try {
            foreach ($this->entries as $studentId => $row) {
                $isAbsent = $row['is_absent'] ?? false;

                $practical = $isAbsent ? null : ($row['practical_obtained'] ?? null);
                $written   = $isAbsent ? null : ($row['written_obtained'] ?? null);
                $mcq       = $isAbsent ? null : ($row['mcq_obtained'] ?? null);

                $total = $isAbsent ? null : (($practical ?? 0) + ($written ?? 0) + ($mcq ?? 0));

                ExamEntry::updateOrCreate(
                    [
                        'exam_setup_detail_id' => $this->exam_setup_detail_id,
                        'student_id'            => $studentId,
                    ],
                    [
                        'exam_setup_id'       => $this->exam_setup_id,
                        'is_absent'           => $isAbsent,
                        'practical_obtained'  => $practical,
                        'written_obtained'    => $written,
                        'mcq_obtained'        => $mcq,
                        'total_obtained'      => $total,
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            return;
        }

        $this->dispatch('toast', type: 'success', message: 'Marks saved successfully!');
    }

    public function render()
    {
        // শুধু সেই Exam Setup গুলো দেখাবো যাদের subject আছে
        $exams = ExamSetup::with('classAssign.class', 'classAssign.section')
            ->whereHas('details')
            ->orderBy('name')
            ->get();

        // নির্বাচিত exam এর subjects
        $subjects = $this->exam_setup_id
            ? ExamSetupDetail::with('classAssignDetail.subject')
                ->where('exam_setup_id', $this->exam_setup_id)
                ->get()
            : collect();

        return view('livewire.admin.exam.entry-component')
            ->with('exams', $exams)
            ->with('subjects', $subjects)
            ->layout('layouts.admin.app', [
                'title' => 'Mark Entries | ' . institution()->name,
            ]);
    }
}