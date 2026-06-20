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

    public $homeworks   = [];
    public $hasHomework = false;
    public $homework_id;

    public array $availableSections = [];

    public function updatedClassId($value): void
    {
        $this->section_id        = null; // ✅ fix: আগে এই লাইন কিছুই করত না
        $this->availableSections = [];
        $this->hasHomework       = false;
        $this->homeworks         = [];

        if ($value) {
            $class = AcademicClass::with('sections')->find($value);
            if ($class) {
                $this->availableSections = $class->sections
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->toArray();
            }
        }
    }

    public function filter()
    {
        $this->validate([
            'class_id'   => 'required|exists:academic_classes,id',
            'section_id' => 'nullable|exists:academic_sections,id',
            'subject_id' => 'required|exists:academic_subjects,id',
        ]);

        $query = Homework::with('subject', 'class', 'section')
            ->where('class_id', $this->class_id)
            ->where('subject_id', $this->subject_id);

        // ✅ fix: section select না করলেও যেন রেজাল্ট আসে
        if ($this->section_id) {
            $query->where('section_id', $this->section_id);
        }

        $this->homeworks   = $query->latest()->get()->toArray();
        $this->hasHomework = true;
    }

    // ✅ নতুন: blade থেকে delete dispatch হতো কিন্তু এই method-ই ছিল না
    public function deleteConfirmed($id)
    {
        Homework::find($id)?->delete();

        $this->homeworks = collect($this->homeworks)
            ->reject(fn($h) => $h['id'] == $id)
            ->values()
            ->toArray();

        $this->dispatch('toast', type: 'success', message: 'Homework deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.homework.homework-list-component')
            ->with('classes', AcademicClass::orderBy('id')->get())
            ->with('sections', AcademicSection::orderBy('name')->get())
            ->with('subjects', AcademicSubject::orderBy('name')->get())
            ->with('homeworks', $this->homeworks ?? [])
            ->layout('layouts.admin.app', [
                'title' => "Homework List | School SaaS",
            ]);
    }
}