<?php

namespace App\Livewire\Teacher\Attendance;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;

class StudentComponent extends Component
{
    public $filterClass   = '';
    public $filterSection = '';
    public $filterDate;

    public $data          = [];
    public $hasAttendance = false;

    public function mount()
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    public function getAvailableSections()
    {
        if (!$this->filterClass) return [];

        return AcademicSection::whereIn('id',
            AcademicClassAssign::where('class_id', $this->filterClass)->pluck('section_id')
        )->orderBy('name')->get();
    }

    public function updatedFilterClass()
    {
        $this->filterSection = '';
        $this->data          = [];
        $this->hasAttendance = false;
    
        if (!$this->filterClass) return;
    }

    public function updatedFilterSection()
    {
        $this->data          = [];
        $this->hasAttendance = false;

        if (!$this->filterClass) return;
    }


    public function filter()
    {
        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
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
            $this->dispatch('toast', type: 'error', message: 'No students found.');
            $this->hasAttendance = false;
            return;
        }

        $sectionNames = AcademicSection::whereIn('id', $students->pluck('section_id')->unique())
            ->pluck('name', 'id');

        $existingQuery = Attendance::where('type', 'student')
            ->where('date', $this->filterDate)
            ->where('class_id', $this->filterClass);

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
            'filterClass' => 'required',
            'filterDate'  => 'required|date',
        ]);

        try {
            foreach ($this->data as $item) {
                Attendance::updateOrCreate(
                    [
                        'attendable_id'   => $item['student_id'],
                        'attendable_type' => Student::class,
                        'date'            => $this->filterDate,
                        'type'            => 'student',
                        'class_id'        => $this->filterClass,
                        'section_id'      => $item['section_id'],
                    ],
                    [
                        'status'  => $item['status'],
                        'remarks' => $item['remarks'],
                    ]
                );
            }

            $this->dispatch('toast', type: 'success', message: 'Attendance saved successfully!');

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Failed: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->filterClass   = '';
        $this->filterSection = '';
        $this->date          = now()->format('Y-m-d');
        $this->data          = [];
        $this->hasAttendance = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.teacher.attendance.student-component')
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->layout('layouts.teacher.app', [
                'title' => 'Student Attendance | ' . institution()->name,
            ]);
    }
}