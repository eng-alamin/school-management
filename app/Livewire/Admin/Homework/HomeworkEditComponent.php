<?php

namespace App\Livewire\Admin\Homework;

use Livewire\Component;
use App\Models\Homework;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class HomeworkEditComponent extends Component
{
    public $class_id;
    public $section_id;
    public $subject_id;
    public $teacher_id;
    public $title;
    public $description;
    public $homework_date;
    public $submission_date;
    public $published_later;
    public $schedule_date;
    public $attachment;
    public $send_sms;
    public $status;

    public $homework_id;

    public function mount($id)
    {
        $homework = Homework::findOrFail($id);

        $this->homework_id = $homework->id;
        $this->class_id = $homework->class_id;
        $this->section_id = $homework->section_id;
        $this->subject_id = $homework->subject_id;
        $this->teacher_id = $homework->teacher_id;
        $this->title = $homework->title;
        $this->description = $homework->description;
        $this->homework_date = $homework->homework_date;
        $this->submission_date = $homework->submission_date;
        $this->published_later = $homework->published_later;
        $this->schedule_date = $homework->schedule_date;
        $this->send_sms = $homework->send_sms;
        $this->status = $homework->status;
    }

    public function render()
    {
        return view('livewire.admin.homework.homework-edit-component')
        ->with('classes', \App\Models\AcademicClass::all())
        ->with('sections', \App\Models\AcademicSection::all())
        ->with('subjects', \App\Models\AcademicSubject::all())
        ->with('teachers', \App\Models\Employee::all())
            ->layout('layouts.admin.app', [
                'title' => 'Edit Homework | ' . institution()->name,
            ]);
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

    public function update()
    {
        try {
            $data = $this->validate($this->rules());

            $homework = Homework::findOrFail($this->homework_id);

            if ($this->attachment) {
                $data['attachment'] = $this->attachment->store('homeworks', 'public');
            }

            $homework->update($data);

            $this->dispatch('toast', type: 'success', message: 'Homework updated successfully!');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'An error occurred while updating the homework.');
            throw $e;
        }
    }

}
