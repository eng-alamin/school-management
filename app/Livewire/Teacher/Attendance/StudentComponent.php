<?php

namespace App\Livewire\Tenant\Teacher\Attendance;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Student  ;
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

        // 1️⃣ Get students of selected class + section
        $students = Student::where('class_id', $this->filterClass)
            ->where('section_id', $this->filterSection)
            ->orderBy('roll_no')
            ->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No students found.');
            $this->hasAttendance = false;
            return;
        }

        // 2️⃣ Check existing attendance for this date
        $existing = Attendance::where('date', $this->date)
            ->where('type', 'student')
            ->where('class_id', $this->filterClass)
            ->where('section_id', $this->filterSection)
            ->get()
            ->keyBy('attendable_id');

        // 3️⃣ Build table data (like your image)
        $this->data = $students->map(function ($student) use ($existing) {

            $att = $existing[$student->id] ?? null;

            return [
                'student_id'    => $student->id,
                'name'          => $student->full_name,
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
            'filterSection' => 'required|exists:academic_sections,id',
            'date' => 'required|date',
        ]);

         try {
            foreach ($this->data as $item) {
                Attendance::updateOrCreate(
                    [
                        'attendable_id'   => $item['student_id'],
                        'attendable_type' => Student::class,
                        'date'            => $this->date,
                        'type'            => 'student',
                        'class_id'        => $this->filterClass,
                        'section_id'      => $this->filterSection,
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

        return view('livewire.tenant.teacher.attendance.student-component')
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->layout('layouts.teacher.app', [
                'title' => "Student Attendance | School SaaS",
            ]);
    }
}
