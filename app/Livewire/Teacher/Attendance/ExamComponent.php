<?php

namespace App\Livewire\Teacher\Attendance;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use App\Models\AcademicClassAssign;
use App\Models\ExamSetup;

class ExamComponent extends Component
{
    public $filterExam = '';
    public $filterClass = '';
    public $filterSection = '';
    public $filterSubject = '';

    public $subjects = [];
    public $date;

    public $data = [];
    public $hasAttendance = false;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    // Exam 
    public function getExams()
    {
        return ExamSetup::where('is_published', 1)
            ->orderBy('name')
            ->get();
    }

    // Classes
    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
        ->orderBy('name')
        ->get();
    }

    // Sections
    public function getAvailableSections()
    {
        if (!$this->filterClass) return [];

        return AcademicSection::whereIn('id', AcademicClassAssign::where('class_id', $this->filterClass)->pluck('section_id'))
            ->orderBy('name')
            ->get();
    }

    public function updatedFilterSection()
{
    $this->subjects = [];

    if (!$this->filterClass || !$this->filterSection) {
        return;
    }

    $row = AcademicClassAssign::where('class_id', $this->filterClass)
        ->where('section_id', $this->filterSection)
        ->first();

    if ($row && $row->subjects) {

        $subjectIds = $row->subjects;
        $this->subjects = AcademicSubject::whereIn('name', $subjectIds)->get();
        // $this->subjects = AcademicSubject::whereIn('id', $subjectIds)->get();
    }
}

    public function filter()
    {
        if (
            !$this->filterExam ||
            !$this->filterClass ||
            !$this->filterSection ||
            !$this->filterSubject
        ) {
            return;
        }

        $students = Student::where('class_id', $this->filterClass)
            ->where('section_id', $this->filterSection)
            ->orderBy('roll_no')
            ->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No Exam found.');
            $this->hasAttendance = false;
            return;
        }

        $existing = Attendance::where('type', 'exam')
            ->where('exam_id', $this->filterExam)
            ->where('class_id', $this->filterClass)
            ->where('section_id', $this->filterSection)
            ->where('subject_id', $this->filterSubject)
            ->get()
            ->keyBy('attendable_id');

        $this->data = $students->map(function ($student) use ($existing) {

            $att = $existing[$student->id] ?? null;

            return [
                'student_id'  => $student->id,
                'name'        => $student->full_name,
                'roll_no'     => $student->roll_no,
                'register_no' => $student->register_no,

                'status'      => $att->status ?? 'present',
                'remarks'     => $att->remarks ?? '',
            ];
        })->toArray();

        $this->hasAttendance = true;
    }

    public function save()
    {
        $this->validate([
            'filterExam'   => 'required|exists:exam_setups,id',
            'filterClass'   => 'required|exists:academic_classes,id',
            'filterSection' => 'required|exists:academic_sections,id',
            'filterSubject' => 'required|exists:academic_subjects,id',
        ]);

        foreach ($this->data as $item) {

            Attendance::updateOrCreate(
                [
                    'attendable_id'   => $item['student_id'],
                    'attendable_type' => Student::class,
                    'type'            => 'exam',
                    'exam_id'   => $this->filterExam,
                    'class_id'        => $this->filterClass,
                    'section_id'      => $this->filterSection,
                    'subject_id'      => $this->filterSubject,
                ],
                [
                    'status'  => $item['status'],
                    'remarks' => $item['remarks'],
                ]
            );
        }

        $this->dispatch('toast', type: 'success', message: 'Attendance saved successfully!');
    }

    public function resetForm()
    {
        $this->filterExam = '';
        $this->filterClass = '';
        $this->filterSection = '';
        $this->subjects = [];

        $this->data = [];
        $this->hasAttendance = false;

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.teacher.attendance.exam-component')
            ->with('exams', $this->getExams())
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->with('subjects', $this->subjects)
            ->layout('layouts.teacher.app', [
                'title' => "Student Attendance | School SaaS",
            ]);
    }
}