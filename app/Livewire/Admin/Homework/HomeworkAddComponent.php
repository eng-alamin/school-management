<?php

namespace App\Livewire\Admin\Homework;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use App\Models\AcademicClassAssign;
use App\Models\AcademicSession;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use App\Services\HomeworkNotificationService;

class HomeworkAddComponent extends Component
{
    use WithFileUploads;

    public $class_id;
    public $section_id;
    public $subject_id;
    public $teacher_id;

    public $title;
    public $description;

    public $homework_date;
    public $submission_date;

    public $published_later = false;
    public $schedule_date;

    public $attachment;

    public $send_sms = false;

    public $status = 'published';

    public array $availableSections = [];
    public array $availableSubjects = [];
    public array $availableTeachers = [];

    public bool $classHasSection = true;

    public ?int $currentSessionId = null;

    public function mount(): void
    {
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

    public function updatedClassId($value): void
    {
        $this->section_id        = null;
        $this->subject_id        = null;
        $this->teacher_id        = null;
        $this->availableSections = [];
        $this->availableSubjects = [];
        $this->availableTeachers = [];
        $this->classHasSection   = true;

        if (!$value) return;

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $class = AcademicClass::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->find($value);
        $this->classHasSection = $class ? (bool) $class->has_section : true;

        if (!$this->classHasSection) {
            $this->loadSubjects($value, null);
            return;
        }

        $assigns = AcademicClassAssign::with('section')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
            ->where('class_id', $value)
            ->whereNotNull('section_id')
            ->get();

        $this->availableSections = $assigns
            ->filter(fn($a) => $a->section)
            ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();

        if (empty($this->availableSections)) {
            $this->loadSubjects($value, null);
        }
    }

    public function updatedSectionId($value): void
    {
        $this->subject_id        = null;
        $this->teacher_id        = null;
        $this->availableSubjects = [];
        $this->availableTeachers = [];

        if (!$this->class_id) return;

        $sectionId = ($value && $value !== 'all') ? $value : null;

        $this->loadSubjects($this->class_id, $sectionId);
    }

    public function updatedSubjectId($value): void
    {
        $this->teacher_id        = null;
        $this->availableTeachers = [];

        if (!$value || !$this->class_id) return;

        $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        $this->loadTeachers($this->class_id, $sectionId, $value);

        // যদি এই Subject-এর জন্য শুধু একজনই Teacher assigned থাকে, auto-select করে দেওয়া হচ্ছে
        if (count($this->availableTeachers) === 1) {
            $this->teacher_id = $this->availableTeachers[0]['id'];
        }
    }

    public function updatedPublishedLater($value): void
    {
        if ($value) {
            $this->status = 'draft';
        } else {
            $this->schedule_date = null;
            if ($this->status === 'draft') {
                $this->status = 'published';
            }
        }
    }

    protected function loadSubjects($class_id, $section_id = null): void
    {
        $query = AcademicClassAssign::where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->where('session_id', $this->currentSessionId)
            ->where('class_id', $class_id);

        if ($section_id) {
            $query->where('section_id', $section_id);
        } else {
            $query->whereNull('section_id');
        }

        $assign = $query->with('details.subject')->first();

        if ($assign && $assign->details->isNotEmpty()) {
            $this->availableSubjects = $assign->details
                ->filter(fn($detail) => $detail->subject)
                ->map(fn($detail) => [
                    'id'   => $detail->subject->id,
                    'name' => $detail->subject->name,
                ])
                ->unique('id')
                ->sortBy('name')
                ->values()
                ->toArray();
        } else {
            $this->availableSubjects = [];
        }
    }

    // ---------------------------------------------------------------
    // Selected Class + Section + Subject অনুযায়ী assigned Teacher(দের) লোড করা
    // ---------------------------------------------------------------
    protected function loadTeachers($class_id, $section_id, $subject_id): void
    {
        if (!$subject_id) {
            $this->availableTeachers = [];
            return;
        }

        $query = AcademicClassAssign::where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->where('session_id', $this->currentSessionId)
            ->where('class_id', $class_id);

        if ($section_id) {
            $query->where('section_id', $section_id);
        } else {
            $query->whereNull('section_id');
        }

        $assign = $query->with(['details' => function ($q) use ($subject_id) {
            $q->where('subject_id', $subject_id)->whereNotNull('teacher_id');
        }])->first();

        $teacherIds = $assign
            ? $assign->details->pluck('teacher_id')->unique()->values()->toArray()
            : [];

        if (empty($teacherIds)) {
            $this->availableTeachers = [];
            return;
        }

        $this->availableTeachers = User::where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->where('role', User::ROLE_TEACHER)
            ->where('is_active', true)
            ->whereIn('id', $teacherIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($teacher) => ['id' => $teacher->id, 'name' => $teacher->name])
            ->toArray();
    }

    protected function validSubjectIdsForSelection(): array
    {
        if (!$this->class_id) {
            return [];
        }

        $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        $query = AcademicClassAssign::where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->where('session_id', $this->currentSessionId)
            ->where('class_id', $this->class_id);

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        } else {
            $query->whereNull('section_id');
        }

        $assign = $query->with('details')->first();

        return $assign ? $assign->details->pluck('subject_id')->toArray() : [];
    }

    // ---------------------------------------------------------------
    // Selected Class + Section + Subject অনুযায়ী assigned Teacher ID গুলো (Validation এর জন্য)
    // ---------------------------------------------------------------
    protected function validTeacherIdsForSelection(): array
    {
        if (!$this->class_id || !$this->subject_id) {
            return [];
        }

        $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        $query = AcademicClassAssign::where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->where('session_id', $this->currentSessionId)
            ->where('class_id', $this->class_id);

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        } else {
            $query->whereNull('section_id');
        }

        $assign = $query->with(['details' => function ($q) {
            $q->where('subject_id', $this->subject_id)->whereNotNull('teacher_id');
        }])->first();

        return $assign ? $assign->details->pluck('teacher_id')->unique()->toArray() : [];
    }

    public function resetForm(): void
    {
        $this->reset([
            'class_id', 'section_id', 'subject_id', 'teacher_id',
            'title', 'description',
            'homework_date', 'submission_date',
            'published_later', 'schedule_date',
            'attachment', 'send_sms',
            'availableSections', 'availableSubjects', 'availableTeachers',
        ]);
        $this->status = 'published';
        $this->classHasSection = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        abort_unless((bool) $this->currentSessionId, 422, 'No active academic session found. Please set a current session first.');

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();
        $sessionId     = $this->currentSessionId;

        $this->validate([
            'class_id'        => [
                'required',
                Rule::exists('academic_classes', 'id')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId),
            ],
            'section_id'      => [
                Rule::requiredIf($this->classHasSection),
                'nullable',
            ],
            'subject_id'      => [
                'required',
                Rule::exists('academic_subjects', 'id')->where('institution_id', $institutionId),
                function ($attribute, $value, $fail) {
                    if (!in_array($value, $this->validSubjectIdsForSelection())) {
                        $fail('Selected subject is not assigned to the selected class/section.');
                    }
                },
            ],
            'teacher_id'      => [
                'nullable',
                Rule::exists('users', 'id')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId)
                    ->where('role', User::ROLE_TEACHER),
                function ($attribute, $value, $fail) {
                    if ($value && !in_array($value, $this->validTeacherIdsForSelection())) {
                        $fail('Selected teacher is not assigned to the selected class/section/subject.');
                    }
                },
            ],
            'title'           => 'required|string|max:255',
            'description'     => 'required|string',
            'homework_date'   => 'required|date',
            'submission_date' => 'required|date|after_or_equal:homework_date',
            'published_later' => 'boolean',
            'schedule_date'   => 'nullable|required_if:published_later,true|date|after:now',
            'attachment'      => 'nullable|file|max:10240',
            'send_sms'        => 'boolean',
            'status'          => ['required', Rule::in(['draft', 'published', 'closed'])],
        ]);

        $status = $this->published_later ? 'draft' : $this->status;

        $attachmentPath = null;

        try {
            $attachmentPath = $this->attachment
                ? $this->attachment->store('homeworks', 'public')
                : null;

            $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
                ? $this->section_id
                : null;

            $record = DB::transaction(function () use ($sectionId, $attachmentPath, $status, $institutionId, $branchId, $sessionId) {
                $record = Homework::create([
                    'institution_id'  => $institutionId,
                    'branch_id'       => $branchId,
                    'session_id'      => $sessionId,
                    'class_id'        => $this->class_id,
                    'section_id'      => $sectionId,
                    'subject_id'      => $this->subject_id,
                    'teacher_id'      => $this->teacher_id,
                    'title'           => $this->title,
                    'description'     => $this->description,
                    'homework_date'   => $this->homework_date,
                    'submission_date' => $this->submission_date,
                    'published_later' => $this->published_later,
                    'schedule_date'   => $this->schedule_date,
                    'attachment'      => $attachmentPath,
                    'send_sms'        => $this->send_sms,
                    'status'          => $status,
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'assignment', 'type' => 'homework'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                        $activity->branch_id = $record->branch_id;
                    })
                    ->log('Homework created: ' . $record->title);

                return $record;
            });

            if ($record->status === 'published' && !$record->published_later) {
                HomeworkNotificationService::notifyStudentsAndGuardians($record);
            }

            session()->flash('toast_success', 'Homework created successfully!');
            $this->redirectRoute('admin.homework.list', navigate: true);

        } catch (\Throwable $e) {

            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            $this->dispatch('toast', type: 'error', message: 'Creation failed: ' . $e->getMessage());
            report($e);
        }
    }

    public function render()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $classes = AcademicClass::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->whereIn('id', AcademicClassAssign::where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->where('session_id', $this->currentSessionId)
                ->distinct()
                ->pluck('class_id'))
            ->orderBy('id')
            ->get();

        return view('livewire.admin.homework.homework-add-component')
            ->with('classes', $classes)
            ->layout('layouts.admin.app', [
                'title' => 'Create Homework | ' . institution()->name,
            ]);
    }
}