<?php

namespace App\Livewire\Admin\Card;

use Livewire\Component;
use App\Models\AdmitCardTemplate;
use App\Models\AdmitCard;

use App\Models\Student;
use App\Models\AcademicClassAssign;
use App\Models\ExamSetup;
use App\Models\ExamSetupDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Throwable;

class GenerateAdmitCardComponent extends Component
{
    // Filter / Ground
    public string $filterClass = '';
    public string $filterSection = '';
    public ?int $filterExam = null;
    public ?int $filterTemplate = null;
    public bool $filtered = false;

    // Date fields
    public string $print_date = '';
    public string $expiry_date = '';

    // Selection
    public array $selectedIds = [];
    public bool $selectAll = false;

    // Print
    public bool $showPrintPreview = false;
    public array $printCards = [];

    public function mount(): void
    {
        $this->print_date  = now()->format('Y-m-d');
        $this->expiry_date = now()->addYear()->format('Y-m-d');
    }

    public function applyFilter(): void
    {
        $this->validate([
            'filterClass'    => 'required|string',
            'filterExam'     => 'required',
            'filterTemplate' => 'required|exists:admit_card_templates,id',
        ], [
            'filterClass.required'    => 'Class is required.',
            'filterExam.required'     => 'Exam is required.',
            'filterTemplate.required' => 'Template is required.',
            'filterTemplate.exists'   => 'Selected template is invalid.',
        ]);

        $this->filtered    = true;
        $this->selectedIds = [];
        $this->selectAll   = false;
    }

    public function resetFilter(): void
    {
        $this->filtered       = false;
        $this->filterClass    = '';
        $this->filterSection  = '';
        $this->filterExam     = null;
        $this->filterTemplate = null;
        $this->selectedIds    = [];
        $this->selectAll      = false;
        $this->resetValidation();
    }

    public function updatedFilterClass(): void
    {
        $this->filterSection = '';
        $this->selectedIds   = [];
        $this->selectAll     = false;
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIds = $this->getStudents()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds(): void
    {
        $total = $this->getStudents()->count();
        $this->selectAll = count($this->selectedIds) === $total && $total > 0;
    }

    public function generateCards(): void
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('toast', type: 'error', message: 'Please select at least one student.');
            return;
        }

        if (!$this->filterExam || !$this->filterTemplate) {
            $this->dispatch('toast', type: 'error', message: 'Please select an exam and a template.');
            return;
        }

        $this->validate([
            'print_date'  => 'required|date',
            'expiry_date' => 'required|date|after_or_equal:print_date',
        ]);

        $students = Student::with(['class', 'section', 'group'])
            ->whereIn('id', $this->selectedIds)
            ->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'Selected students could not be found.');
            return;
        }

        $scheduleData  = $this->buildExamScheduleData();
        $institutionId = institution()->id;
        $data = [];

        foreach ($students as $student) {
            $data[] = [
                'institution_id' => $institutionId,
                'student_id'     => $student->id,

                'issue_date'  => $this->print_date,
                'expiry_date' => $this->expiry_date,
                'template_id' => $this->filterTemplate,

                'name'        => $student->name,
                'gender'      => $student->gender,
                'blood_group' => $student->full_blood_group,
                'dob'         => $student->dob,
                'religion'    => $student->religion,
                'mobile'      => $student->mobile,
                'address'     => $student->present_address,
                'photo'       => $student->photo,
                'session'     => $student->academic_year,
                'register_no' => $student->register_no,
                'roll_no'     => $student->roll_no,

                'class'       => $student->class?->name,
                'section'     => $student->section?->name,
                'group'       => $student->group?->name,

                'exam_schedules' => json_encode($scheduleData),

                'updated_at'  => now(),
                'created_at'  => now(),
            ];
        }

        DB::beginTransaction();
        try {
            AdmitCard::upsert(
                $data,
                ['student_id'],
                [
                    'institution_id',
                    'issue_date',
                    'expiry_date',
                    'template_id',
                    'name',
                    'gender',
                    'blood_group',
                    'dob',
                    'religion',
                    'mobile',
                    'address',
                    'photo',
                    'session',
                    'register_no',
                    'roll_no',
                    'class',
                    'section',
                    'exam_schedules',
                    'group',
                    'updated_at',
                ]
            );

            $cards = AdmitCard::with('template')
                ->where('institution_id', $institutionId)
                ->whereIn('student_id', $this->selectedIds)
                ->get();

            activity()->log('Generated '.$cards->count().' admit card(s)');

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Admit cards could not be generated. Please try again.');
            return;
        }

        $this->printCards       = $cards->toArray();
        $this->showPrintPreview = true;

        $this->dispatch('toast', type: 'success', message: count($this->printCards).' admit card(s) generated successfully!');
    }

    /**
     * Build exam schedule rows (subject-wise) for the selected exam + class + section.
     *
     * Chain: ExamSetupDetail -> AcademicClassAssignDetail -> AcademicClassAssign (class_id/section_id)
     *                        -> AcademicClassAssignDetail -> subject (subject name)
     *                        -> hasOne ExamSchedule (date/start_time/end_time)
     *
     * NOTE: exam_schedules table has NO class_id/section_id/data columns,
     * so filtering must happen through the classAssignDetail.classAssign relation.
     */
    private function buildExamScheduleData(): array
    {
        if (!$this->filterExam) {
            return [];
        }

        $details = ExamSetupDetail::with(['classAssignDetail.subject', 'schedule'])
            ->where('exam_setup_id', $this->filterExam)
            ->whereHas('classAssignDetail.classAssign', function ($query) {
                $query
                    ->when($this->filterClass, fn ($q) => $q->where('class_id', $this->filterClass))
                    ->when(
                        $this->filterSection && $this->filterSection !== 'all',
                        fn ($q) => $q->where('section_id', $this->filterSection)
                    );
            })
            ->orderBy('serial')
            ->get();

        return $details->map(function (ExamSetupDetail $detail) {
            $start = $detail->schedule?->start_time;
            $end   = $detail->schedule?->end_time;

            $duration = null;
            if ($start && $end) {
                $duration = Carbon::parse($start)->diff(Carbon::parse($end))->format('%H:%I').' hrs';
            }

            return [
                'subject'    => $detail->classAssignDetail?->subject?->name,
                'exam_date'  => $detail->schedule?->exam_date?->format('Y-m-d'),
                'start_time' => $start ? Carbon::parse($start)->format('h:i A') : null,
                'duration'   => $duration,
                'full_marks' => $detail->full_mark,
            ];
        })->toArray();
    }

    /**
     * Student list based on the selected class/section.
     * Student table already has normalized class_id/section_id FKs,
     * so filtering directly on Student is correct.
     */
    private function getStudents()
    {
        if (!$this->filtered) {
            return collect();
        }

        return Student::query()
            ->with(['class', 'section', 'group'])
            ->when($this->filterClass, fn ($q) => $q->where('class_id', $this->filterClass))
            ->when(
                $this->filterSection && $this->filterSection !== 'all',
                fn ($q) => $q->where('section_id', $this->filterSection)
            )
            ->orderBy('roll_no')
            ->get();
    }

    /**
     * Class dropdown source = AcademicClassAssign (single source of truth
     * for "which classes are assigned in this school"), NOT Student table.
     */
    public function getAvailableClasses(): array
    {
        return AcademicClassAssign::with('class')
            ->whereHas('class')
            ->get()
            ->unique('class_id')
            ->pluck('class')
            ->filter()
            ->sortBy('name')
            ->values()
            ->map(fn ($class) => [
                'id'   => $class->id,
                'name' => $class->name,
            ])
            ->toArray();
    }

    /**
     * Section dropdown source = AcademicClassAssign, scoped to the
     * currently selected class.
     */
    public function getAvailableSections(): array
    {
        if (!$this->filterClass) {
            return [];
        }

        return AcademicClassAssign::with('section')
            ->where('class_id', $this->filterClass)
            ->whereHas('section')
            ->get()
            ->unique('section_id')
            ->pluck('section')
            ->filter()
            ->sortBy('name')
            ->values()
            ->map(fn ($section) => [
                'id'   => $section->id,
                'name' => $section->name,
            ])
            ->toArray();
    }

    public function render()
    {
        $templates = AdmitCardTemplate::where('is_active', true)->get();

        $students = $this->filtered ? $this->getStudents() : collect();
        $sections = $this->getAvailableSections();
        $classes  = $this->getAvailableClasses();

        $selectedTemplate = $this->filterTemplate
            ? AdmitCardTemplate::find($this->filterTemplate)
            : null;

        $exams = ExamSetup::get();

        return view('livewire.admin.card.generate-admit-card-component')
            ->with('templates', $templates)
            ->with('students', $students)
            ->with('sections', $sections)
            ->with('classes', $classes)
            ->with('exams', $exams)
            ->with('selectedTemplate', $selectedTemplate)
            ->layout('layouts.admin.app', [
                'title' => 'Admit Cards | ' . institution()->name,
            ]);
    }
}