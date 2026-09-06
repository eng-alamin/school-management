<?php

namespace App\Livewire\Branch\Academic;

use Livewire\Component;
use App\Models\AcademicClassSchedule;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSession;
use App\Models\Branch;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ClassScheduleCreateComponent extends Component
{
    public $filterClass   = '';
    public $filterSection = '';
    public $filterDay     = 'Sunday';
    public $data          = [];

    public $hasSchedule = false;
    public $schedule_id;

    // subject list, each entry: id, name, default_teacher_id
    public array $availableSubjects = [];

    // class+section er shob possible teacher (dropdown er jonno)
    public array $availableTeachers = [];

    // Selected class-er has_section flag. True hole section select kora required.
    public bool $selectedClassHasSection = true;

    public ?int $currentSessionId = null;

    public function mount()
    {
        $this->filterDay = 'Sunday';
        $this->currentSessionId = $this->resolveCurrentSessionId();
    }

    private function resolveCurrentSessionId(): ?int
    {
        return AcademicSession::query()
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->active() // scopeActive() -> is_current = true
            ->value('id');
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    public function getAvailableClasses()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        return AcademicClass::where('institution_id', $institutionId)
            ->whereIn('id', AcademicClassAssign::where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->where('session_id', $this->currentSessionId)
                ->distinct()
                ->pluck('class_id'))
            ->orderBy('id')
            ->get();
    }

    public function getAvailableSections()
    {
        if (!$this->filterClass) return collect();

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        return AcademicSection::where('institution_id', $institutionId)
            ->whereIn('id',
                AcademicClassAssign::where('institution_id', $institutionId)
                    ->where('branch_id', $branchId)
                    ->where('session_id', $this->currentSessionId)
                    ->where('class_id', $this->filterClass)
                    ->whereNotNull('section_id')
                    ->pluck('section_id')
            )->orderBy('name')->get();
    }

    public function updatedFilterClass()
    {
        $this->filterSection           = '';
        $this->availableSubjects       = [];
        $this->availableTeachers       = [];
        $this->hasSchedule             = false;
        $this->data                    = [];
        $this->selectedClassHasSection = false;

        if (!$this->filterClass) return;

        $institutionId = institution()->id;

        // Defense-in-depth: institution_id explicitly check kora holo (IDOR protection)
        $class = AcademicClass::where('institution_id', $institutionId)
            ->find($this->filterClass);

        if (!$class) {
            $this->filterClass = '';
            return;
        }

        $this->selectedClassHasSection = (bool) $class->has_section;

        if (!$this->selectedClassHasSection) {
            $this->loadSubjects($this->filterClass, null);
        }
    }

    public function updatedFilterSection()
    {
        $this->availableSubjects = [];
        $this->availableTeachers = [];
        $this->hasSchedule       = false;
        $this->data              = [];

        if (!$this->filterClass || !$this->filterSection) return;

        $this->loadSubjects($this->filterClass, $this->filterSection);
    }

    protected function loadSubjects($class_id, $section_id = null): void
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $query = AcademicClassAssign::with('details.subject', 'details.teacher')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
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
     * AcademicClassAssignDetail collection theke availableSubjects (with default teacher_id)
     * ar availableTeachers (unique list) build kore. Sob id-based (name-based na),
     * jate future e subject/teacher rename hole purono schedule bhul na dekhay.
     */
    protected function buildSubjectsAndTeachers($details): void
    {
        $this->availableSubjects = $details
            ->filter(fn($d) => $d->subject)
            ->unique('subject_id')
            ->map(fn($d) => [
                'id'                 => $d->subject->id,
                'name'               => $d->subject->name,
                'default_teacher_id' => $d->teacher->id ?? null,
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

        // abort_unless((bool) $this->currentSessionId, 422, 'No active academic session found. Please set a current session first.');

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $allowedSectionIds = $this->getAvailableSections()->pluck('id')->toArray();

        $this->validate([
            'filterClass' => [
                'required',
                Rule::exists('academic_classes', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'filterSection' => [
                Rule::requiredIf($this->selectedClassHasSection),
                Rule::in($allowedSectionIds),
            ],
            'filterDay' => 'required|string|max:20',
        ], [
            'filterSection.required' => 'This class has sections. Please select a section.',
            'filterSection.in'       => 'Selected section is not valid for this class.',
        ]);

        $sectionId = ($this->filterSection && $this->filterSection !== 'all')
            ? $this->filterSection
            : null;

        $schedule = AcademicClassSchedule::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
            ->where('class_id', $this->filterClass)
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

            // subject select korar shathe shathei tar default teacher_id o auto-fill hoye jabe
            $this->data = array_map(fn($i) => [
                'subject_id' => $this->availableSubjects[$i]['id'] ?? '',
                'teacher_id' => $this->availableSubjects[$i]['default_teacher_id'] ?? '',
                'start_time' => '09:00',
                'end_time'   => '10:00',
                'class_room' => '',
            ], range(0, $rows - 1));

            $this->hasSchedule = true;
        }
    }

    /**
     * Subject change hole, oi subject er jonno assign kora teacher_id ke
     * automatically data row e boshiye dao
     */
    public function updatedData($value, $key)
    {
        if (str_ends_with($key, '.subject_id')) {
            $index = explode('.', $key)[0];

            $subject = collect($this->availableSubjects)->firstWhere('id', (int) $value);

            if ($subject && !empty($subject['default_teacher_id'])) {
                $this->data[$index]['teacher_id'] = $subject['default_teacher_id'];
            }
        }
    }

    public function addRow()
    {
        $this->data[] = [
            'subject_id' => '',
            'teacher_id' => '',
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
        $this->filterClass             = '';
        $this->filterSection           = '';
        $this->filterDay               = 'Sunday';
        $this->data                    = [];
        $this->hasSchedule             = false;
        $this->schedule_id             = null;
        $this->availableSubjects       = [];
        $this->availableTeachers       = [];
        $this->selectedClassHasSection = false;
        $this->resetValidation();
    }

    public function save()
    {
        // abort_unless((bool) $this->currentSessionId, 422, 'No active academic session found. Please set a current session first.');

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $allowedSubjectIds = collect($this->availableSubjects)->pluck('id')->toArray();
        $allowedTeacherIds = collect($this->availableTeachers)->pluck('id')->toArray();
        $allowedSectionIds = $this->getAvailableSections()->pluck('id')->toArray();

        $this->validate([
            'filterClass' => [
                'required',
                Rule::exists('academic_classes', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],
            'filterSection' => [
                Rule::requiredIf($this->selectedClassHasSection),
                Rule::in($allowedSectionIds),
            ],
            'filterDay'         => 'required|string|max:20',
            'data.*.subject_id' => ['required', Rule::in($allowedSubjectIds)],
            'data.*.teacher_id' => ['required', Rule::in($allowedTeacherIds)],
            'data.*.start_time' => 'required|date_format:H:i',
            'data.*.end_time'   => 'required|date_format:H:i|after:data.*.start_time',
            'data.*.class_room' => 'nullable|string|max:100',
        ], [
            'filterSection.required' => 'This class has sections. Please select a section.',
            'filterSection.in'       => 'Selected section is not valid for this class.',
            'data.*.subject_id.in'   => 'Selected subject is not assigned to this class.',
            'data.*.teacher_id.in'   => 'Selected teacher is not assigned to this class.',
        ]);

        try {
            $sectionId = ($this->filterSection && $this->filterSection !== 'all')
                ? $this->filterSection
                : null;

            AcademicClassSchedule::updateOrCreate(
                [
                    'institution_id' => $institutionId,
                    'branch_id'      => $branchId,
                    'session_id'     => $this->currentSessionId,
                    'class_id'       => $this->filterClass,
                    'section_id'     => $sectionId,
                    'day'            => $this->filterDay,
                ],
                [
                    'data' => $this->data,
                ]
            );

            $this->dispatch('toast', type: 'success', message: 'Class schedule saved successfully!');

        } catch (\Exception $e) {
            Log::error('Class schedule save failed', [
                'institution_id' => $institutionId,
                'branch_id'      => $branchId,
                'session_id'     => $this->currentSessionId,
                'class_id'       => $this->filterClass,
                'error'          => $e->getMessage(),
            ]);

            $this->dispatch('toast', type: 'error', message: 'Something went wrong while saving the schedule. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.admin.academic.class-schedule-create-component')
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->with('subjects', $this->availableSubjects)
            ->with('teachers', $this->availableTeachers)
            ->layout('layouts.branch.app', [
                'title' => 'Class Schedule | ' . institution()->name,
            ]);
    }
}