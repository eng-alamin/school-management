<?php

namespace App\Livewire\Admin\Homework;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicSubject;
use App\Models\AcademicClassAssign;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;

class HomeworkEditComponent extends Component
{
    use WithFileUploads;

    public $homework_id;

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

    public bool $classHasSection = true;

    public ?int $homeworkBranchId  = null;
    public ?int $homeworkSessionId = null;

    public function mount($id): void
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $homework = Homework::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->findOrFail($id);

        $this->homework_id       = $homework->id;
        $this->homeworkBranchId  = $homework->branch_id;
        $this->homeworkSessionId = $homework->session_id;

        $this->class_id         = $homework->class_id;
        $this->section_id       = $homework->section_id;
        $this->subject_id       = $homework->subject_id;
        $this->teacher_id       = $homework->teacher_id;
        $this->title            = $homework->title;
        $this->description      = $homework->description;
        $this->homework_date    = $homework->homework_date;
        $this->submission_date  = $homework->submission_date;
        $this->published_later  = (bool) $homework->published_later;
        $this->schedule_date    = $homework->schedule_date;
        $this->send_sms         = (bool) $homework->send_sms;
        $this->status            = $homework->status;

        if ($this->class_id) {
            $this->classHasSection = $this->resolveClassHasSection($this->class_id);

            if ($this->classHasSection) {
                $this->loadSections($this->class_id);
            }

            $this->loadSubjects($this->class_id, $this->section_id);
        }
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    protected function resolveClassHasSection($classId): bool
    {
        if (!$classId) {
            return true;
        }

        $class = AcademicClass::where('institution_id', institution()->id)
            ->where('branch_id', $this->homeworkBranchId)
            ->find($classId);

        return $class ? (bool) $class->has_section : true;
    }

    public function updatedClassId($value): void
    {
        $this->section_id        = null;
        $this->subject_id        = null;
        $this->availableSections = [];
        $this->availableSubjects = [];
        $this->classHasSection   = true;

        if (!$value) return;

        $this->classHasSection = $this->resolveClassHasSection($value);

        if (!$this->classHasSection) {
            $this->loadSubjects($value, null);
            return;
        }

        $this->loadSections($value);

        if (empty($this->availableSections)) {
            $this->loadSubjects($value, null);
        }
    }

    public function updatedSectionId($value): void
    {
        $this->subject_id        = null;
        $this->availableSubjects = [];

        if (!$this->class_id) return;

        $sectionId = ($value && $value !== 'all') ? $value : null;
        $this->loadSubjects($this->class_id, $sectionId);
    }

    protected function loadSections($class_id): void
    {
        $assigns = AcademicClassAssign::with('section')
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->homeworkBranchId)
            ->where('session_id', $this->homeworkSessionId)
            ->where('class_id', $class_id)
            ->whereNotNull('section_id')
            ->get();

        $this->availableSections = $assigns
            ->filter(fn($a) => $a->section)
            ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();
    }

    protected function loadSubjects($class_id, $section_id = null): void
    {
        $query = AcademicClassAssign::where('institution_id', institution()->id)
            ->where('branch_id', $this->homeworkBranchId)
            ->where('session_id', $this->homeworkSessionId)
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

    protected function validSubjectIdsForSelection(): array
    {
        if (!$this->class_id) {
            return [];
        }

        $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        $query = AcademicClassAssign::where('institution_id', institution()->id)
            ->where('branch_id', $this->homeworkBranchId)
            ->where('session_id', $this->homeworkSessionId)
            ->where('class_id', $this->class_id);

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        } else {
            $query->whereNull('section_id');
        }

        $assign = $query->with('details')->first();

        return $assign ? $assign->details->pluck('subject_id')->toArray() : [];
    }

    public function update(): void
    {
        $institutionId = institution()->id;

        $this->validate([
            'class_id'        => [
                'required',
                Rule::exists('academic_classes', 'id')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $this->homeworkBranchId),
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
                    ->where('branch_id', $this->homeworkBranchId)
                    ->where('role', User::ROLE_TEACHER),
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

        $newAttachmentPath = null;
        $oldAttachmentPath = null;

        try {
            $homework = Homework::where('institution_id', $institutionId)
                ->where('branch_id', $this->homeworkBranchId)
                ->findOrFail($this->homework_id);

            $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
                ? $this->section_id
                : null;

            if ($this->attachment) {
                $newAttachmentPath = $this->attachment->store('homeworks', 'public');
                $oldAttachmentPath = $homework->attachment;
            }

            $attachmentPath = $newAttachmentPath ?: $homework->attachment;

            DB::transaction(function () use ($homework, $sectionId, $attachmentPath, $institutionId) {
                $homework->update([
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
                    'status'          => $this->status,
                ]);

                activity()
                    ->performedOn($homework)
                    ->tap(fn ($a) => $a->institution_id = $institutionId)
                    ->log('Homework "' . $homework->title . '" updated');
            });

            if ($oldAttachmentPath) {
                Storage::disk('public')->delete($oldAttachmentPath);
            }

            $this->dispatch('toast', type: 'success', message: 'Homework updated successfully!');

        } catch (\Exception $e) {
            if ($newAttachmentPath) {
                Storage::disk('public')->delete($newAttachmentPath);
            }

            $this->dispatch('toast', type: 'error', message: 'Update failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $institutionId = institution()->id;

        $classes = AcademicClass::where('institution_id', $institutionId)
            ->where('branch_id', $this->homeworkBranchId)
            ->whereIn('id', AcademicClassAssign::where('institution_id', $institutionId)
                ->where('branch_id', $this->homeworkBranchId)
                ->where('session_id', $this->homeworkSessionId)
                ->distinct()
                ->pluck('class_id'))
            ->orderBy('name')
            ->get();

        $teachers = User::where('institution_id', $institutionId)
            ->where('branch_id', $this->homeworkBranchId)
            ->where('role', User::ROLE_TEACHER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.homework.homework-edit-component')
            ->with('classes', $classes)
            ->with('teachers', $teachers)
            ->with('availableSections', $this->availableSections)
            ->with('availableSubjects', $this->availableSubjects)
            ->layout('layouts.admin.app', [
                'title' => 'Edit Homework | ' . institution()->name,
            ]);
    }
}