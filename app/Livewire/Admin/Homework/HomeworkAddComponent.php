<?php

namespace App\Livewire\Admin\Homework;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use App\Models\AcademicClassAssign;
use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use App\Services\NotificationService;

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

    // ── Class changed → reload sections, clear rest ──
    public function updatedClassId($value): void
    {
        $this->section_id        = null;
        $this->subject_id        = null;
        $this->availableSections = [];
        $this->availableSubjects = [];

        if (!$value) return;

        $assigns = AcademicClassAssign::with('section')
            ->where('institution_id', institution()->id)
            ->where('class_id', $value)
            ->whereNotNull('section_id')
            ->get();

        $this->availableSections = $assigns
            ->filter(fn($a) => $a->section)
            ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();

        // No sections → load subjects directly
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

        $sectionId = ($this->section_id && $this->section_id !== 'all') ? $this->section_id : null;

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
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'class_id'        => [
                'required',
                Rule::exists('academic_classes', 'id')->where('institution_id', institution()->id),
            ],
            'section_id'      => 'nullable',
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

        $attachmentPath = null;

        try {
            // File upload happens outside the DB transaction (storage isn't transactional).
            $attachmentPath = $this->attachment
                ? $this->attachment->store('homeworks', 'public')
                : null;

            $sectionId = ($this->section_id && $this->section_id !== 'all')
                ? $this->section_id
                : null;

            $homework = DB::transaction(function () use ($sectionId, $attachmentPath) {
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
                    'status'          => $this->status,
                ]);

                activity()
                    ->performedOn($homework)
                    ->log('Homework "' . $homework->title . '" created');

                return $homework;
            });

            // ── Notification: শুধু published homework-এর ক্ষেত্রেই এখনই notify করব ──
            // draft হলে এখনো কেউ দেখবে না, closed practically create হয় না, তাই safe check।
            if ($homework->status === 'published' && !$homework->published_later) {
                $this->notifyStudentsAndGuardians($homework);
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

    // ──────────────────────────────────────────
    // Notification: homework-এর class/section-এর সব student + তাদের guardian-দের পাঠানো
    // (DB transaction commit হওয়ার পর কল হয়, যাতে notification fail হলেও homework save rollback না হয়)
    // ──────────────────────────────────────────
    private function notifyStudentsAndGuardians(Homework $homework): void
    {
        try {
            // class_id / section_id "students" টেবিলে থাকে, "users" টেবিলে না —
            // তাই Student model থেকে query শুরু করে, তারপর linked User account বের করতে হবে।
            $studentsQuery = Student::with(['user', 'guardians.user'])
                ->where('institution_id', institution()->id)
                ->where('class_id', $homework->class_id);

            if ($homework->section_id) {
                $studentsQuery->where('section_id', $homework->section_id);
            }

            $students = $studentsQuery->get();

            if ($students->isEmpty()) {
                return;
            }

            $subjectName = optional($homework->subject)->name ?? '';
            $dueDate     = $homework->submission_date instanceof \Carbon\Carbon
                ? $homework->submission_date->format('d M Y')
                : \Carbon\Carbon::parse($homework->submission_date)->format('d M Y');

            $title   = 'New Homework: ' . $homework->title;
            $message = ($subjectName ? "{$subjectName} বিষয়ে " : '')
                . "নতুন Homework দেওয়া হয়েছে। জমা দেওয়ার শেষ তারিখ: {$dueDate}।";

            $data = [
                'icon' => 'assignment',
                'url'  => '#',
                // 'url'  => route('admin.homework.index'),
            ];

            // ── প্রতিটা student-এর নিজস্ব login User account (থাকলে) ──
            $studentUsers = collect();

            foreach ($students as $student) {
                $studentUser = $student->user;

                if ($studentUser instanceof User && $studentUser->is_active) {
                    $studentUsers->push($studentUser);
                } else {
                    Log::warning('Homework notification skipped: student has no active linked User account.', [
                        'homework_id' => $homework->id,
                        'student_id'  => $student->id,
                    ]);
                }
            }

            $studentUsers = $studentUsers->unique('id');

            if ($studentUsers->isNotEmpty()) {
                NotificationService::sendToMany($studentUsers, 'homework', $title, $message, $data);
            }

            // ── প্রতিটা student-এর guardian(s)-কে notify করা ──
            $guardianUsers = collect();

            foreach ($students as $student) {
                foreach ($student->guardians as $guardian) {
                    // Guardian-এর সাথে যুক্ত login User account থাকলে সেটাই notify হবে।
                    // না থাকলে (শুধু contact info হিসেবে guardian থাকলে) ApplicationComponent-এর
                    // pattern অনুযায়ী skip করে log করি, ভুল model-এ notification পাঠানো ঠেকাতে।
                    $guardianUser = $guardian->user;

                    if ($guardianUser instanceof User) {
                        $guardianUsers->push($guardianUser);
                    } else {
                        Log::warning('Homework notification skipped: guardian has no linked User account.', [
                            'homework_id' => $homework->id,
                            'student_id'  => $student->id,
                            'guardian_id' => $guardian->id,
                        ]);
                    }
                }
            }

            $guardianUsers = $guardianUsers->unique('id');

            if ($guardianUsers->isNotEmpty()) {
                NotificationService::sendToMany($guardianUsers, 'homework', $title, $message, $data);
            }
        } catch (\Throwable $e) {
            // Notification ব্যর্থ হলেও homework save সফল থাকবে — শুধু log করে রাখি।
            Log::warning('Homework notification failed.', [
                'homework_id' => $homework->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $classes = AcademicClass::where('institution_id', institution()->id)
            ->whereIn('id', AcademicClassAssign::where('institution_id', institution()->id)->distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();

        $teachers = User::where('institution_id', institution()->id)
            ->where('role', User::ROLE_TEACHER)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.homework.homework-add-component')
            ->with('classes', $classes)
            ->with('teachers', $teachers)
            ->layout('layouts.admin.app', [
                'title' => 'Create Homework | ' . institution()->name,
            ]);
    }
}