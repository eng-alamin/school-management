<?php

namespace App\Livewire\Admin\Homework;

use Livewire\Component;
use App\Models\Homework;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class HomeworkAddComponent extends Component
{
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

    public function render()
    {
        $classes = \App\Models\AcademicClass::all();
        $sections = \App\Models\AcademicSection::all();
        $subjects = \App\Models\AcademicSubject::all();
        $teachers = \App\Models\Employee::all();

        return view('livewire.admin.homework.homework-add-component')
        ->with('classes', $classes)
        ->with('sections', $sections)
        ->with('subjects', $subjects)
        ->with('teachers', $teachers)
            ->layout('layouts.admin.app', [
                'title' => "Create Homework | Monarchy School",
            ]);
    }

    public function resetForm()
    {
        $this->reset();
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function rules()
    {
        return [
            'class_id' => 'required|exists:academic_classes,id',
            'section_id' => 'nullable|exists:academic_sections,id',
            'subject_id' => 'required|exists:academic_subjects,id',
            'teacher_id' => 'nullable|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'homework_date' => 'required|date',
            'submission_date' => 'required|date|after_or_equal:homework_date',
            'published_later' => 'boolean',
            'schedule_date' => 'nullable|date|after:now',
            'attachment' => 'nullable|file|max:10240', // Max 10MB
            'send_sms' => 'boolean',
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function save()
    {

        try {
            $data = $this->validate($this->rules());

            $attachmentPath = $this->attachment?->store('homeworks', 'public');

            Homework::create([
                'class_id' => $this->class_id,
                'section_id' => $this->section_id,
                'subject_id' => $this->subject_id,
                'teacher_id' => $this->teacher_id,
                'title' => $this->title,
                'description' => $this->description,
                'homework_date' => $this->homework_date,
                'submission_date' => $this->submission_date,
                'published_later' => $this->published_later,
                'schedule_date' => $this->schedule_date,
                'attachment' => $attachmentPath,
                'send_sms' => $this->send_sms,
                'status' => $this->status,
            ]);

            $this->dispatch('toast', type: 'success', message: 'Homework created successfully!');
            $this->resetForm();
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'An error occurred while creating the homework.');
            throw $e;
        }
    }

}
