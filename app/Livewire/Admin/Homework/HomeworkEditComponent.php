<?php

namespace App\Livewire\Admin\Homework;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicSubject;
use App\Models\AcademicClassAssign;

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
        $homework = Homework::findOrFail($id);

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
        $this->status           = $homework->status;

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

    protected function loadSections($class_id): void
    {
        $assigns = AcademicClassAssign::with('section')
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
        $query = AcademicClassAssign::where('class_id', $class_id);

        if ($section_id) {
            $query->where('section_id', $section_id);
        } else {
            $query->whereNull('section_id');
        }

        // ✅ Ekhon subjects asbe details -> subject relation theke
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

    public function update(): void
    {
        $this->validate([
            'class_id'        => 'required|exists:academic_classes,id',
            'section_id'      => 'nullable|exists:academic_sections,id',
            'subject_id'      => 'required|exists:academic_subjects,id',
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
            $homework = Homework::findOrFail($this->homework_id);

            $sectionId = ($this->section_id && $this->section_id !== 'all')
                ? $this->section_id
                : null;

            $attachmentPath = $this->attachment
                ? $this->attachment->store('homeworks', 'public')
                : $homework->attachment;

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

            $this->dispatch('toast', type: 'success', message: 'Homework updated successfully!');

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Update failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $classes = AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();

        return view('livewire.admin.homework.homework-edit-component')
            ->with('classes', $classes)
            ->with('availableSections', $this->availableSections)
            ->with('availableSubjects', $this->availableSubjects)
            ->layout('layouts.admin.app', [
                'title' => 'Edit Homework | ' . institution()->name,
            ]);
    }
}