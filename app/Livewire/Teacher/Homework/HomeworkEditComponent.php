<?php

namespace App\Livewire\Teacher\Homework;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;

class HomeworkEditComponent extends Component
{
    use WithFileUploads;

    public $homework_id;

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

    public function mount($id): void
    {
        // Ownership check: a teacher must only be able to open/edit their own homework.
        // Without this, any authenticated teacher could edit another teacher's
        // homework simply by changing {id} in the URL (IDOR).
        $homework = Homework::where('id', $id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $this->homework_id      = $homework->id;
        $this->class_id         = $homework->class_id;
        $this->section_id       = $homework->section_id;
        $this->subject_id       = $homework->subject_id;
        $this->title            = $homework->title;
        $this->description      = $homework->description;
        $this->homework_date    = $homework->homework_date;
        $this->submission_date  = $homework->submission_date;
        $this->published_later  = (bool) $homework->published_later;
        $this->schedule_date    = $homework->schedule_date;
        $this->send_sms         = (bool) $homework->send_sms;
        $this->status            = $homework->status;

        // Load sections & subjects for existing class/section
        if ($this->class_id) {
            $this->loadSections($this->class_id);
            $this->loadSubjects($this->class_id, $this->section_id);
        }
    }

    // ── Class changed → reload sections, clear rest ──
    public function updatedClassId($value): void
    {
        $this->section_id        = null;
        $this->subject_id        = null;
        $this->availableSections = [];
        $this->availableSubjects = [];

        if (!$value) return;

        $this->loadSections($value);

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

        $sectionId = ($value && $value !== 'all') ? $value : null;
        $this->loadSubjects($this->class_id, $sectionId);
    }

    /**
     * Only load the sections a class has where THIS teacher is actually
     * assigned to teach a subject (via AcademicClassAssignDetail.teacher_id).
     * Previously this pulled every section in the institution, letting a
     * teacher re-target homework to classes/sections they don't teach.
     */
    protected function loadSections($class_id): void
    {
        $myAssignIds = AcademicClassAssignDetail::where('teacher_id', auth()->id())
            ->pluck('academic_class_assign_id');

        $assigns = AcademicClassAssign::with('section')
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
     * (and section, when a specific one is picked).
     *
     * NOTE (bug fix): the old code read `$assign->subjects`, a column that
     * does not exist on `academic_class_assigns` — it was always null, so
     * the subject dropdown could never be filled. Real subject assignments
     * live in `academic_class_assign_details` (subject_id + teacher_id).
     *
     * When $section_id is null (either "All Section" was chosen, or the
     * class has no sections at all) we union subjects across every
     * section-assign row of the class so "All Section" reflects everything
     * this teacher teaches in that class.
     */
    protected function loadSubjects($class_id, $section_id = null): void
    {
        $assignQuery = AcademicClassAssign::where('class_id', $class_id);

        if ($section_id) {
            $assignQuery->where('section_id', $section_id);
        }

        $assignIds = $assignQuery->pluck('id');

        $this->availableSubjects = AcademicClassAssignDetail::whereIn('academic_class_assign_id', $assignIds)
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
     * Server-side re-check that the logged-in teacher is actually assigned
     * to teach the submitted class/section/subject combo. class_id,
     * section_id and subject_id are public Livewire properties, so without
     * this check a tampered request could re-target the homework to any
     * class/subject, bypassing the dropdown restrictions entirely.
     */
    protected function authorizedForSelection($sectionId): bool
    {
        $assignQuery = AcademicClassAssign::where('class_id', $this->class_id);

        if ($sectionId) {
            $assignQuery->where('section_id', $sectionId);
        }

        $assignIds = $assignQuery->pluck('id');

        return AcademicClassAssignDetail::whereIn('academic_class_assign_id', $assignIds)
            ->where('teacher_id', auth()->id())
            ->where('subject_id', $this->subject_id)
            ->exists();
    }

    public function update(): void
    {
        $this->validate([
            'class_id'        => 'required',
            'section_id'      => 'nullable',
            'subject_id'      => 'required',
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

        $sectionId = ($this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        if (!$this->authorizedForSelection($sectionId)) {
            $this->dispatch('toast', type: 'error', message: 'You are not assigned to teach this class/section/subject.');
            return;
        }

        try {
            // Re-check ownership on update as well — mount() only runs once per
            // request lifecycle, so this is the real guard against a tampered
            // hidden/wire property being used to update someone else's record.
            $homework = Homework::where('id', $this->homework_id)
                ->where('teacher_id', auth()->id())
                ->firstOrFail();

            $oldAttachment = $homework->attachment;

            $attachmentPath = $this->attachment
                ? $this->attachment->store('homeworks', 'public')
                : $oldAttachment;

            $homework->update([
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
                'status'          => $this->status,
            ]);

            // Clean up the old file only after a new one was actually uploaded,
            // and only after the update succeeded, to avoid deleting a file
            // that's still referenced if something above throws.
            if ($this->attachment && $oldAttachment && $oldAttachment !== $attachmentPath) {
                Storage::disk('public')->delete($oldAttachment);
            }

            $this->dispatch('toast', type: 'success', message: 'Homework updated successfully!');

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Update failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $myAssignIds = AcademicClassAssignDetail::where('teacher_id', auth()->id())
            ->pluck('academic_class_assign_id');

        $classIds = AcademicClassAssign::whereIn('id', $myAssignIds)
            ->distinct()
            ->pluck('class_id');

        $classes = AcademicClass::whereIn('id', $classIds)
            ->orderBy('name')
            ->get();

        return view('livewire.teacher.homework.homework-edit-component')
            ->with('classes', $classes)
            ->with('availableSections', $this->availableSections)
            ->with('availableSubjects', $this->availableSubjects)
            ->layout('layouts.teacher.app', [
                'title' => 'Edit Homework | ' . institution()->name,
            ]);
    }
}