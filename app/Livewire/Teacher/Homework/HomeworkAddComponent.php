<?php

namespace App\Livewire\Teacher\Homework;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;
use App\Services\HomeworkNotificationService;

class HomeworkAddComponent extends Component
{
    use WithFileUploads;

    public $class_id;
    public $section_id;
    public $subject_id;

    public $title;
    public $description;

    public $homework_date;
    public $submission_date;

    public $published_later = false;
    public $schedule_date;

    public $attachment;

    public $send_sms = false;

    public $status = 'published';

    // ── Dynamic dropdowns ──
    public array $availableSections = [];
    public array $availableSubjects = [];

    // ── Class-level section-support flag (academic_classes.has_section) ──
    public bool $classHasSection = true;

    // ── Class changed → reload sections, clear rest ──
    public function updatedClassId($value): void
    {
        $this->section_id        = null;
        $this->subject_id        = null;
        $this->availableSections = [];
        $this->availableSubjects = [];
        $this->classHasSection   = true;

        if (!$value) return;

        $institutionId = institution()->id;

        $class = AcademicClass::where('institution_id', $institutionId)->find($value);
        $this->classHasSection = $class ? (bool) $class->has_section : true;

        if (!$this->classHasSection) {
            $this->loadSubjects($value, null);
            return;
        }

        $this->loadSections($value);

        // No sections found (in the teacher's own assignments) → load subjects directly
        if (empty($this->availableSections)) {
            $this->loadSubjects($value, null);
        }
    }

    // ── Section changed → reload subjects ──
    public function updatedSectionId($value): void
    {
        $this->subject_id        = null;
        $this->availableSubjects = [];

        if (!$this->class_id) return;

        // "all" selected → load subjects without section filter
        $sectionId = ($value && $value !== 'all') ? $value : null;

        $this->loadSubjects($this->class_id, $sectionId);
    }

    // ── "Publish Later" টগল হলে status ফিল্ড সাথে সাথে sync রাখা ──
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

    /**
     * Only load the sections a class has where THIS teacher is actually
     * assigned to teach a subject (via AcademicClassAssignDetail.teacher_id),
     * scoped to the current institution.
     */
    protected function loadSections($class_id): void
    {
        $institutionId = institution()->id;

        $myAssignIds = AcademicClassAssignDetail::where('institution_id', $institutionId)
            ->where('teacher_id', auth()->id())
            ->pluck('academic_class_assign_id');

        $assigns = AcademicClassAssign::with('section')
            ->where('institution_id', $institutionId)
            ->where('class_id', $class_id)
            ->whereNotNull('section_id')
            ->whereIn('id', $myAssignIds)
            ->get();

        $this->availableSections = $assigns
            ->filter(fn($a) => $a->section)
            ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();
    }

    /**
     * Subjects the logged-in teacher is assigned to teach for this class
     * (and section, when a specific one is picked), scoped to institution.
     */
    protected function loadSubjects($class_id, $section_id = null): void
    {
        $institutionId = institution()->id;

        $assignQuery = AcademicClassAssign::where('institution_id', $institutionId)
            ->where('class_id', $class_id);

        if ($section_id) {
            $assignQuery->where('section_id', $section_id);
        } else {
            $assignQuery->whereNull('section_id');
        }

        $assignIds = $assignQuery->pluck('id');

        $this->availableSubjects = AcademicClassAssignDetail::where('institution_id', $institutionId)
            ->whereIn('academic_class_assign_id', $assignIds)
            ->where('teacher_id', auth()->id())
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
            ->values()
            ->toArray();
    }

    /**
     * Resolve the valid subject_id list for the selected class/section,
     * honoring classHasSection just like the Admin panel. Used both for
     * "All Section" unions and tamper-proof server-side validation.
     */
    protected function validSubjectIdsForSelection(): array
    {
        if (!$this->class_id) {
            return [];
        }

        $institutionId = institution()->id;

        $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        $assignQuery = AcademicClassAssign::where('institution_id', $institutionId)
            ->where('class_id', $this->class_id);

        if ($sectionId) {
            $assignQuery->where('section_id', $sectionId);
        } else {
            $assignQuery->whereNull('section_id');
        }

        $assignIds = $assignQuery->pluck('id');

        return AcademicClassAssignDetail::where('institution_id', $institutionId)
            ->whereIn('academic_class_assign_id', $assignIds)
            ->where('teacher_id', auth()->id())
            ->pluck('subject_id')
            ->toArray();
    }

    /**
     * Server-side re-check that the logged-in teacher is actually assigned
     * to teach the submitted class/section/subject combo. class_id,
     * section_id and subject_id are public Livewire properties, so without
     * this check a tampered request could submit any ids and bypass the
     * dropdown restrictions entirely.
     */
    protected function authorizedForSelection($sectionId): bool
    {
        return in_array($this->subject_id, $this->validSubjectIdsForSelection());
    }

    public function resetForm(): void
    {
        $this->reset([
            'class_id', 'section_id', 'subject_id',
            'title', 'description',
            'homework_date', 'submission_date',
            'published_later', 'schedule_date',
            'attachment', 'send_sms',
            'availableSections', 'availableSubjects',
        ]);
        $this->status          = 'published';
        $this->classHasSection = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        $institutionId = institution()->id;

        $this->validate([
            'class_id'        => [
                'required',
                Rule::exists('academic_classes', 'id')->where('institution_id', $institutionId),
            ],
            'section_id'      => [
                Rule::requiredIf($this->classHasSection),
                'nullable',
            ],
            'subject_id'      => [
                'required',
                Rule::exists('academic_subjects', 'id')->where('institution_id', $institutionId),
            ],
            'title'           => 'required|string|max:255',
            'description'     => 'required|string',
            'homework_date'   => 'required|date',
            'submission_date' => 'required|date|after_or_equal:homework_date',
            'published_later' => 'boolean',
            'schedule_date'   => 'nullable|required_if:published_later,true|date|after:now',
            'attachment'      => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            'send_sms'        => 'boolean',
            'status'          => ['required', Rule::in(['draft', 'published', 'closed'])],
        ]);

        $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        if (!$this->authorizedForSelection($sectionId)) {
            $this->dispatch('toast', type: 'error', message: 'You are not assigned to teach this class/section/subject.');
            return;
        }

        // ── Tamper-proof guard: "Publish Later" চেক করা থাকলে status অবশ্যই draft হবে,
        // client-side থেকে যাই আসুক না কেন। ──
        $status = $this->published_later ? 'draft' : $this->status;

        $attachmentPath = null;

        try {
            // File upload হয় transaction-এর বাইরে (storage transactional না)
            $attachmentPath = $this->attachment
                ? $this->attachment->store('homeworks', 'public')
                : null;

            $homework = DB::transaction(function () use ($sectionId, $attachmentPath, $status) {
                $homework = Homework::create([
                    'institution_id'  => institution()->id,
                    'teacher_id'      => auth()->id(),
                    'class_id'        => $this->class_id,
                    'section_id'      => $sectionId,
                    'subject_id'      => $this->subject_id,
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
                    ->performedOn($homework)
                    ->causedBy(auth()->user())
                    ->tap(fn($a) => $a->institution_id = institution()->id)
                    ->log('Homework "' . $homework->title . '" created');

                return $homework;
            });

            // শুধু published homework-এই এখনই notify করা হবে; draft/scheduled
            // পরে ProcessScheduledHomeworks command থেকে publish+notify হবে।
            if ($homework->status === 'published' && !$homework->published_later) {
                HomeworkNotificationService::notifyStudentsAndGuardians($homework);
            }

            $this->dispatch('toast', type: 'success', message: 'Homework created successfully!');
            $this->resetForm();

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

        $myAssignIds = AcademicClassAssignDetail::where('institution_id', $institutionId)
            ->where('teacher_id', auth()->id())
            ->pluck('academic_class_assign_id');

        $classIds = AcademicClassAssign::where('institution_id', $institutionId)
            ->whereIn('id', $myAssignIds)
            ->distinct()
            ->pluck('class_id');

        $classes = AcademicClass::where('institution_id', $institutionId)
            ->whereIn('id', $classIds)
            ->orderBy('name')
            ->get();

        return view('livewire.teacher.homework.homework-add-component')
            ->with('classes', $classes)
            ->layout('layouts.teacher.app', [
                'title' => 'Create Homework | ' . institution()->name,
            ]);
    }
}