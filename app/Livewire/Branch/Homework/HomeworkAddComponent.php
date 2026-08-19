<?php

namespace App\Livewire\Branch\Homework;

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

    // ── Dynamic dropdowns ──
    public array $availableSections = [];
    public array $availableSubjects = [];

    // ── Class-level section-support flag (academic_classes.has_section).
    // Gates whether the Section dropdown / "All Section" option can appear
    // at all — independent from whether the session-wise assign happens to
    // list any sections.
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

        // Class-e section support na thakle, assign-section query e na giye
        // sরাসরি section-less subjects load kora hocche.
        if (!$this->classHasSection) {
            $this->loadSubjects($value, null);
            return;
        }

        $assigns = AcademicClassAssign::with('section')
            ->where('institution_id', $institutionId)
            ->where('class_id', $value)
            ->whereNotNull('section_id')
            ->get();

        $this->availableSections = $assigns
            ->filter(fn($a) => $a->section)
            ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();

        // No sections found in the current session's assignment → load subjects directly
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
    // Checked হলে জোর করে draft; unchecked হলে ইউজার আগে যা সিলেক্ট করেছিল সেটাই থাকবে
    // (draft ছিল যদি, তাহলে published-এ ফিরিয়ে দিই যাতে ফর্ম বিভ্রান্তিকর না হয়)।
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

    /**
     * Resolve the currently valid subject_id list for the selected class/section.
     * Used both for rendering and for tamper-proof server-side validation.
     */
    protected function validSubjectIdsForSelection(): array
    {
        if (!$this->class_id) {
            return [];
        }

        $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        $query = AcademicClassAssign::where('institution_id', institution()->id)
            ->where('class_id', $this->class_id);

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        } else {
            $query->whereNull('section_id');
        }

        $assign = $query->with('details')->first();

        return $assign ? $assign->details->pluck('subject_id')->toArray() : [];
    }

    public function resetForm(): void
    {
        $this->reset([
            'class_id', 'section_id', 'subject_id', 'teacher_id',
            'title', 'description',
            'homework_date', 'submission_date',
            'published_later', 'schedule_date',
            'attachment', 'send_sms',
            'availableSections', 'availableSubjects',
        ]);
        $this->status = 'published';
        $this->classHasSection = true;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'class_id'        => [
                'required',
                Rule::exists('academic_classes', 'id')->where('institution_id', institution()->id),
            ],
            'section_id'      => [
                Rule::requiredIf($this->classHasSection),
                'nullable',
            ],
            'subject_id'      => [
                'required',
                Rule::exists('academic_subjects', 'id')->where('institution_id', institution()->id),
                function ($attribute, $value, $fail) {
                    if (!in_array($value, $this->validSubjectIdsForSelection())) {
                        $fail('Selected subject is not assigned to the selected class/section.');
                    }
                },
            ],
            'teacher_id'      => [
                'nullable',
                Rule::exists('users', 'id')
                    ->where('institution_id', institution()->id)
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

        // ── Tamper-proof guard: "Publish Later" চেক করা থাকলে status অবশ্যই draft হবে,
        // যতই client-side থেকে অন্য কিছু আসুক। এটাই আসল বাগ ফিক্স —
        // আগে এই guard না থাকায় status='published' থেকে গেলে Homework সাথে সাথেই
        // "published" দেখাত অথচ notification স্কিপ হয়ে যেত, এবং schedule_date-এ
        // কখনো actual publish হতো না (কারণ তা প্রসেস করার কোনো job ছিল না)। ──
        $status = $this->published_later ? 'draft' : $this->status;

        $attachmentPath = null;

        try {
            // File upload happens outside the DB transaction (storage isn't transactional).
            $attachmentPath = $this->attachment
                ? $this->attachment->store('homeworks', 'public')
                : null;

            // ── Data integrity: class-e section na thakle (has_section = false),
            // section_id kokhono persist kora jabe na, client-side state jai hok na keno ──
            $sectionId = ($this->classHasSection && $this->section_id && $this->section_id !== 'all')
                ? $this->section_id
                : null;

            $homework = DB::transaction(function () use ($sectionId, $attachmentPath, $status) {
                $homework = Homework::create([
                    'institution_id'  => institution()->id,
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
                    ->performedOn($homework)
                    ->log('Homework "' . $homework->title . '" created');

                return $homework;
            });

            // ── Notification: শুধু published homework-এর ক্ষেত্রেই এখনই notify করব ──
            // draft/scheduled হলে এখনো কেউ দেখবে না; সময় হলে ProcessScheduledHomeworks
            // command (প্রতি মিনিটে scheduler দিয়ে চলবে) সেটাকে publish করে notify করবে।
            if ($homework->status === 'published' && !$homework->published_later) {
                HomeworkNotificationService::notifyStudentsAndGuardians($homework);
            }

            $this->dispatch('toast', type: 'success', message: 'Homework created successfully!');
            $this->resetForm();

        } catch (\Throwable $e) {
            // Roll back the uploaded file if DB insert failed, to avoid orphan files.
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            $this->dispatch('toast', type: 'error', message: 'Creation failed: ' . $e->getMessage());
            report($e);
        }
    }

    public function render()
    {
        $classes = AcademicClass::where('institution_id', institution()->id)
            ->whereIn('id', AcademicClassAssign::where('institution_id', institution()->id)->distinct()->pluck('class_id'))
            ->orderBy('id')
            ->get();

        $teachers = User::where('institution_id', institution()->id)
            ->where('role', User::ROLE_TEACHER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.homework.homework-add-component')
            ->with('classes', $classes)
            ->with('teachers', $teachers)
            ->layout('layouts.branch.app', [
                'title' => 'Create Homework | ' . institution()->name,
            ]);
    }
}