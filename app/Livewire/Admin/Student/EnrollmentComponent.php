<?php

namespace App\Livewire\Admin\Student;

use Livewire\Component;
use App\Models\Student;
use App\Models\StudentEnrollment;

class EnrollmentComponent extends Component
{
    public Student $student;

    public string $routePrefix = '';

    public function mount(int $id)
    {
        $this->routePrefix = $this->resolveRoutePrefix();
        
        $this->student = Student::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);
    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;

        $enrollments = StudentEnrollment::with(['class', 'section', 'group'])
            ->where('institution_id', $institutionId)
            ->where('student_id', $this->student->id)
            ->orderByDesc('id')
            ->get();

        return view('livewire.admin.student.enrollment-component')
            ->with('student', $this->student)
            ->with('enrollments', $enrollments)
            ->layout('layouts.admin.app', [
                'title' => 'Enrollment History | ' . institution()->name,
            ]);
    }
}