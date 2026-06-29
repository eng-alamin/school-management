<?php

namespace App\Livewire\Teacher\Homework;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use App\Models\AcademicClassAssign;

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

    // ── Class changed → reload sections, clear rest ──
    public function updatedClassId($value): void
    {
        $this->section_id        = null;
        $this->subject_id        = null;
        $this->availableSections = [];
        $this->availableSubjects = [];

        if (!$value) return;

        $assigns = AcademicClassAssign::with('section')
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
        $query = AcademicClassAssign::where('class_id', $class_id);

        if ($section_id) {
            $query->where('section_id', $section_id);
        } else {
            $query->whereNull('section_id');
        }

        $assign = $query->first();

        if ($assign && !empty($assign->subjects)) {
            $this->availableSubjects = AcademicSubject::whereIn('name', $assign->subjects)
                ->orderBy('name')
                ->get()
                ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                ->toArray();
        } else {
            $this->availableSubjects = [];
        }
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
        $this->status = 'published';
        $this->resetValidation();
    }

    public function save(): void
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
            'attachment'      => 'nullable|file|max:10240',
            'send_sms'        => 'boolean',
            'status'          => ['required', Rule::in(['draft', 'published', 'closed'])],
        ]);

        try {
            $attachmentPath = $this->attachment
                ? $this->attachment->store('homeworks', 'public')
                : null;

            $sectionId = ($this->section_id && $this->section_id !== 'all')
                ? $this->section_id
                : null;

            Homework::create([
                'teacher_id'      => auth()->user()->employee->id,
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

            $this->dispatch('toast', type: 'success', message: 'Homework created successfully!');
            $this->resetForm();

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Creation failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $classes = AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();

        return view('livewire.teacher.homework.homework-add-component')
            ->with('classes', $classes)
            ->layout('layouts.teacher.app', [
                'title' => 'Create Homework | ' . institution()->name,
            ]);
    }
}