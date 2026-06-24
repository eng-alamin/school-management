<?php

namespace App\Livewire\Admin\Attendance;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;

class StudentComponent extends Component
{
    public $filterClass = '';
    public $filterSection = '';
    public $date;

    public $data = [];
    public $hasAttendance = false;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()
            ->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    public function getAvailableSections()
    {
        if (!$this->filterClass) return [];

        return AcademicSection::whereIn('id', AcademicClassAssign::where('class_id', $this->filterClass)->pluck('section_id'))
            ->orderBy('name')
            ->get();
    }

    public function filter()
    {
        if (!$this->filterClass || !$this->filterSection || !$this->date) {
            return;
        }

        // 1️⃣ Get students of selected class (+ section, jodi specific section select kora hoy)
        $studentsQuery = Student::where('class_id', $this->filterClass)
            ->orderBy('section_id')
            ->orderBy('roll_no');

        // "All Section" hole section filter lagbe na - class-er shob student ashbe
        if ($this->filterSection !== 'all') {
            $studentsQuery->where('section_id', $this->filterSection);
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No students found.');
            $this->hasAttendance = false;
            return;
        }

        // Table-e section name dekhanor jonno (jokhon All Section select kora hoy)
        $sectionNames = AcademicSection::whereIn('id', $students->pluck('section_id')->unique())
            ->pluck('name', 'id');

        // 2️⃣ Check existing attendance for this date
        $existingQuery = Attendance::where('date', $this->date)
            ->where('type', 'student')
            ->where('class_id', $this->filterClass);

        if ($this->filterSection !== 'all') {
            $existingQuery->where('section_id', $this->filterSection);
        }

        $existing = $existingQuery->get()->keyBy('attendable_id');

        // 3️⃣ Build table data (like your image)
        $this->data = $students->map(function ($student) use ($existing, $sectionNames) {

            $att = $existing[$student->id] ?? null;

            return [
                'student_id'    => $student->id,
                'section_id'    => $student->section_id,
                'section_name'  => $sectionNames[$student->section_id] ?? '',
                'name'          => $student->name,
                'roll_no'       => $student->roll_no,
                'register_no'   => $student->register_no,

                // default status = present
                'status'        => $att->status ?? 'present',

                'remarks'       => $att->remarks ?? '',
            ];
        })->toArray();

        $this->hasAttendance = true;
    }

    public function save()
    {
        $this->validate([
            'filterClass' => 'required|exists:academic_classes,id',
            'filterSection' => ['required', function ($attribute, $value, $fail) {
                if ($value !== 'all' && !AcademicSection::where('id', $value)->exists()) {
                    $fail('Invalid section selected.');
                }
            }],
            'date' => 'required|date',
        ]);

         try {
            foreach ($this->data as $item) {
                // filterSection "all" hote pare, tai item['section_id'] (student-er actual section) use kora hocche
                Attendance::updateOrCreate(
                    [
                        'attendable_id'   => $item['student_id'],
                        'attendable_type' => Student::class,
                        'date'            => $this->date,
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('toast', type: 'error', message: 'Please fill all required fields correctly.');
            return;
        }
        
    }
        

    public function resetForm()
    {
        $this->filterClass = '';
        $this->filterSection = '';
        $this->date = now()->format('Y-m-d');
        $this->data = [];
        $this->hasAttendance = false;
        $this->resetValidation();
    }

    public function render()
    {
        $classes  = $this->getAvailableClasses();
        $sections = $this->getAvailableSections();

        return view('livewire.admin.attendance.student-component')
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->layout('layouts.admin.app', [
                'title' => 'Student Attendance | ' . institution()->name,
            ]);
    }
}