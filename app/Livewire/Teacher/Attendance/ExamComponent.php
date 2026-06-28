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

    public $data = [];
    public $hasAttendance = false;

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


    public function updatedFilterClass()
    {
        $this->filterSection = '';
        $this->filterSubject = '';
        $this->subjects      = [];
        $this->data          = [];
        $this->hasAttendance = false;

        if (!$this->filterClass) return;

        $rows = AcademicClassAssign::where('class_id', $this->filterClass)->get();

        $subjectNames = $rows->flatMap(function ($row) {
            $subjects = $row->subjects;
            if (is_string($subjects)) {
                $subjects = json_decode($subjects, true) ?? [];
            }
            return $subjects ?: [];
        })
        ->filter()
        ->unique()
        ->values();

        if ($subjectNames->isNotEmpty()) {
            $this->subjects = AcademicSubject::whereIn('name', $subjectNames)->get();
        }
    }

    public function updatedFilterSection()
    {
        $this->filterSubject = '';
        $this->data          = [];
        $this->hasAttendance = false;

        if (!$this->filterClass || !$this->filterSection) return;

        if ($this->filterSection === 'all') {
            $rows = AcademicClassAssign::where('class_id', $this->filterClass)->get();
        } else {
            $rows = AcademicClassAssign::where('class_id', $this->filterClass)
                ->where('section_id', $this->filterSection)
                ->get();
        }

        $subjectNames = $rows->flatMap(function ($row) {
            $subjects = $row->subjects;
            if (is_string($subjects)) {
                $subjects = json_decode($subjects, true) ?? [];
            }
            return $subjects ?: [];
        })
        ->filter()
        ->unique()
        ->values();

        $this->subjects = $subjectNames->isNotEmpty()
            ? AcademicSubject::whereIn('name', $subjectNames)->get()
            : [];
    }

    public function filter()
    {
        if (!$this->filterExam) {
            $this->dispatch('toast', type: 'error', message: 'Please select a exam.');
            return;
        }
        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }
        if (!$this->filterSubject) {
            $this->dispatch('toast', type: 'error', message: 'Please select a subject.');
            return;
        }

        $studentsQuery = Student::where('class_id', $this->filterClass)
            ->orderBy('section_id')
            ->orderBy('roll_no');

        if ($this->filterSection && $this->filterSection !== 'all') {
            $studentsQuery->where('section_id', $this->filterSection);
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No Exam found.');
            $this->hasAttendance = false;
            return;
        }

        $sectionNames = AcademicSection::whereIn('id', $students->pluck('section_id')->unique())
            ->pluck('name', 'id');

        $existingQuery = Attendance::where('type', 'exam')
            ->where('exam_id', $this->filterExam)
            ->where('class_id', $this->filterClass)
            ->where('subject_id', $this->filterSubject);

        if ($this->filterSection && $this->filterSection !== 'all') {
            $existingQuery->where('section_id', $this->filterSection);
        }

        $existing = $existingQuery->get()->keyBy('attendable_id');

        $this->data = $students->map(function ($student) use ($existing, $sectionNames) {

            $att = $existing[$student->id] ?? null;

            return [
                'student_id'   => $student->id,
                'section_id'   => $student->section_id,
                'section_name' => $sectionNames[$student->section_id] ?? '',
                'name'         => $student->name,
                'roll_no'      => $student->roll_no,
                'register_no'  => $student->register_no,

                'status'       => $att->status ?? 'present',
                'remarks'      => $att->remarks ?? '',
            ];
        })->toArray();

        $this->hasAttendance = true;
    }

    public function save()
    {
        $this->validate([
            'filterExam'    => 'required',
            'filterClass'   => 'required',
            'filterSubject' => 'required',
        ]);

        foreach ($this->data as $item) {

            Attendance::updateOrCreate(
                [
                    'attendable_id'   => $item['student_id'],
                    'attendable_type' => Student::class,
                    'type'            => 'exam',
                    'exam_id'         => $this->filterExam,
                    'class_id'        => $this->filterClass,
                    'section_id'      => $item['section_id'],
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
                'title' => 'Exam Attendance | ' . institution()->name,
            ]);
    }
}