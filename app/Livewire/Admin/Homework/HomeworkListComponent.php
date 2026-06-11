<?php

namespace App\Livewire\Admin\Homework;

use Livewire\Component;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;

class HomeworkListComponent extends Component
{
    public $class_id;
    public $section_id;
    public $subject_id;

    public $homeworks = [];
    public $hasHomework = false;
    public $homework_id;

    public function render()
    {
        return view('livewire.admin.homework.homework-list-component')
            ->with('classes', AcademicClass::all())
            ->with('sections', AcademicSection::all())
            ->with('subjects', AcademicSubject::all())
            ->with('homeworks', $this->homeworks ?? [])
            ->layout('layouts.admin.app', [
                'title' => "Homework List | Monarchy School",
            ]);
    }

    public function filter()
    {
        $this->validate([
            'class_id'   => 'required|exists:academic_classes,id',
            'section_id' => 'required|exists:academic_sections,id',
            'subject_id' => 'required|exists:academic_subjects,id',
        ]);

        $homeworks = Homework::with('subject', 'class', 'section')
            ->where('class_id', $this->class_id)
            ->where('section_id', $this->section_id)
            ->where('subject_id', $this->subject_id)
            ->get();

        $this->homeworks = $homeworks->toArray();
        $this->hasHomework = $homeworks->count() > 0;

        $this->dispatch('homeworkFiltered', $this->homeworks);
    }

}
