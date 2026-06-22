<?php

namespace App\Livewire\Admin\Attendance;

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
        $this->data = [];

        if (!$this->filterClass || !$this->filterSection) {
            return;
        }

        // "All Section" select kora hoyeche - class-er shob section-er subject ekshathe (union) dekhabe
        if ($this->filterSection === 'all') {
            $rows = AcademicClassAssign::where('class_id', $this->filterClass)->get();

            $subjectNames = $rows->flatMap(function ($row) {
                $subjects = $row->subjects;

                // 'subjects' column model-e array cast na thakle JSON string hishebe ashte pare,
                // tai string hole nijei decode kore nicchi
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

        $studentsQuery = Student::where('class_id', $this->filterClass)
            ->orderBy('section_id')
            ->orderBy('roll_no');

        // "All Section" hole section filter lagbe na - class-er shob student ashbe
        if ($this->filterSection !== 'all') {
            $studentsQuery->where('section_id', $this->filterSection);
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No Exam found.');
            $this->hasAttendance = false;
            return;
        }

        // Table-e section name dekhanor jonno (jokhon All Section select kora hoy)
        $sectionNames = AcademicSection::whereIn('id', $students->pluck('section_id')->unique())
            ->pluck('name', 'id');

        $existingQuery = Attendance::where('type', 'exam')
            ->where('exam_id', $this->filterExam)
            ->where('class_id', $this->filterClass)
            ->where('subject_id', $this->filterSubject);

        if ($this->filterSection !== 'all') {
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
            'filterExam'    => 'required|exists:exam_setups,id',
            'filterClass'   => 'required|exists:academic_classes,id',
            'filterSection' => ['required', function ($attribute, $value, $fail) {
                if ($value !== 'all' && !AcademicSection::where('id', $value)->exists()) {
                    $fail('Invalid section selected.');
                }
            }],
            'filterSubject' => 'required|exists:academic_subjects,id',
        ]);

        foreach ($this->data as $item) {

            // filterSection "all" hote pare, tai item['section_id'] (student-er actual section) use kora hocche
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
        return view('livewire.admin.attendance.exam-component')
            ->with('exams', $this->getExams())
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->with('subjects', $this->subjects)
            ->layout('layouts.admin.app', [
                'title' => "Exam Attendance | School SaaS",
            ]);
    }
}