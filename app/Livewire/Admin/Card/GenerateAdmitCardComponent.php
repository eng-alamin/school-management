<?php

namespace App\Livewire\Admin\Card;

use Livewire\Component;
use App\Models\AdmitCardTemplate;
use App\Models\AdmitCard;

use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\ExamSetup;
use App\Models\ExamSetupDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Throwable;

class GenerateAdmitCardComponent extends Component
{
    // Filter / Ground
    public string $filterClass = '';
    public string $filterSection = '';
    public ?int $filterExam = null;
    public ?int $filterTemplate = null;
    public bool $filtered = false;

    // ── Class-level section-support flag (academic_classes.has_section) ──
    public bool $classHasSection = true;

    // Date fields
    public string $print_date = '';
    public string $expiry_date = '';

    // Selection
    // IMPORTANT: selectedIds holds Student::$student_id values (business key),
    // NOT the Student::$id primary key. This must stay consistent with the
    // checkbox `value` in the Blade view.
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
            'filterSection'  => [
                Rule::requiredIf($this->classHasSection),
                'nullable',
            ],
            'filterExam'     => 'required',
            'filterTemplate' => 'required|exists:admit_card_templates,id',
        ], [
            'filterClass.required'    => 'Class is required.',
            'filterSection.required'  => 'Please select a section for this class.',
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
        $this->filtered        = false;
        $this->filterClass     = '';
        $this->filterSection   = '';
        $this->filterExam      = null;
        $this->filterTemplate  = null;
        $this->selectedIds     = [];
        $this->selectAll       = false;
        $this->classHasSection = true;
        $this->resetValidation();
    }

    public function updatedFilterClass(): void
    {
        $this->filterSection   = '';
        $this->selectedIds     = [];
        $this->selectAll       = false;
        $this->classHasSection = $this->resolveClassHasSection($this->filterClass);
    }

    /**
     * Resolves academic_classes.has_section for a given class id, scoped to
     * the current institution. Defaults to true (section required) when the
     * class can't be found, to avoid silently widening the student query.
     */
    private function resolveClassHasSection(?string $classId): bool
    {
        if (!$classId) {
            return true;
        }

        $institutionId = auth()->user()->institution_id;
        $class = AcademicClass::where('institution_id', $institutionId)->find($classId);

        return $class ? (bool) $class->has_section : true;
    }

    public function updatedSelectAll(bool $value): void
    {
        // FIX: must pluck 'student_id' (business key), not 'id' (primary key),
        // to stay consistent with the checkbox value used for manual selection.
        if ($value) {
            $this->selectedIds = $this->getStudents()
                ->pluck('student_id')
                ->map(fn ($sid) => (string) $sid)
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

        $institutionId = auth()->user()->institution_id;

        // FIX: filter by 'student_id' (business key) to match $this->selectedIds,
        // instead of the previous 'id' mismatch which silently broke selection.
        $students = Student::with(['class', 'section', 'group'])
            ->where('institution_id', $institutionId)
            ->whereIn('student_id', $this->selectedIds)
            ->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'Selected students could not be found.');
            return;
        }

        $scheduleData  = $this->buildExamScheduleData();
        $institution   = institution();

        DB::beginTransaction();
        try {
            $cards = collect();

            foreach ($students as $student) {
                $payload = [
                    'institution_id' => $institutionId,
                    'template_id'    => $this->filterTemplate,
                    'issue_date'     => $this->print_date,
                    'expiry_date'    => $this->expiry_date,

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
                ];

                // FIX: query WITH trashed records so an existing soft-deleted
                // admit card for this student is found and properly RESTORED
                // instead of being silently overwritten at the DB layer, which
                // is what the previous raw upsert() did (it bypasses any
                // SoftDeletes scope and leaves deleted_at set on stale data).
                $card = AdmitCard::withTrashed()
                    ->where('institution_id', $institutionId)
                    ->where('student_id', $student->student_id)
                    ->first();

                if ($card) {
                    $card->fill($payload);
                    if (method_exists($card, 'trashed') && $card->trashed()) {
                        $card->restore();
                    }
                    $card->save();
                } else {
                    $card = AdmitCard::create(array_merge($payload, [
                        'student_id' => $student->student_id,
                    ]));
                }

                // FIX: activity log now targets the actual model and records
                // the causing user, per project audit-trail convention.
                activity()
                    ->performedOn($card)
                    ->causedBy(auth()->user())
                    ->log('Generated/updated admit card for student: '.$student->name);

                $cards->push($card->load('template'));
            }

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

        // Class-e section support na thakle, section filter kokhono
        // exam-schedule query-te apply kora jabe na.
        $sectionFilterActive = $this->classHasSection
            && $this->filterSection
            && $this->filterSection !== 'all';

        $details = ExamSetupDetail::with(['classAssignDetail.subject', 'schedule'])
            ->where('exam_setup_id', $this->filterExam)
            ->whereHas('classAssignDetail.classAssign', function ($query) use ($sectionFilterActive) {
                $query
                    ->when($this->filterClass, fn ($q) => $q->where('class_id', $this->filterClass))
                    ->when($sectionFilterActive, fn ($q) => $q->where('section_id', $this->filterSection));
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

        $institutionId = auth()->user()->institution_id;

        // Class-e section support na thakle, section filter kokhono
        // apply kora jabe na — even if a stray filterSection value exists.
        $sectionFilterActive = $this->classHasSection
            && $this->filterSection
            && $this->filterSection !== 'all';

        return Student::query()
            ->with(['class', 'section', 'group'])
            ->where('institution_id', $institutionId)
            ->when($this->filterClass, fn ($q) => $q->where('class_id', $this->filterClass))
            ->when($sectionFilterActive, fn ($q) => $q->where('section_id', $this->filterSection))
            ->orderBy('roll_no')
            ->get();
    }

    /**
     * Class dropdown source = AcademicClassAssign (single source of truth
     * for "which classes are assigned in this school"), NOT Student table.
     */
    public function getAvailableClasses(): array
    {
        $institutionId = auth()->user()->institution_id;

        return AcademicClassAssign::with('class')
            ->where('institution_id', $institutionId)
            ->whereHas('class')
            ->get()
            ->unique('class_id')
            ->pluck('class')
            ->filter()
            ->values()
            ->map(fn ($class) => [
                'id'   => $class->id,
                'name' => $class->name,
            ])
            ->toArray();
    }

    /**
     * Section dropdown source = AcademicClassAssign, scoped to the
     * currently selected class — gated by academic_classes.has_section
     * so a section-less class never surfaces a section list at all.
     */
    public function getAvailableSections(): array
    {
        if (!$this->filterClass || !$this->classHasSection) {
            return [];
        }

        $institutionId = auth()->user()->institution_id;

        return AcademicClassAssign::with('section')
            ->where('institution_id', $institutionId)
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
        $institutionId = auth()->user()->institution_id;

        $templates = AdmitCardTemplate::where('institution_id', $institutionId)
            ->where('is_active', true)
            ->get();

        $students = $this->filtered ? $this->getStudents() : collect();
        $sections = $this->getAvailableSections();
        $classes  = $this->getAvailableClasses();

        $selectedTemplate = $this->filterTemplate
            ? AdmitCardTemplate::where('institution_id', $institutionId)->find($this->filterTemplate)
            : null;

        $exams = ExamSetup::with('classAssign.academicClass', 'classAssign.academicSection')
            ->where('institution_id', $institutionId)
            ->whereHas('details')
            ->orderBy('name')
            ->get();

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