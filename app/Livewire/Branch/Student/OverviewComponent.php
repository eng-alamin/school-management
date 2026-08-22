<?php

namespace App\Livewire\Branch\Student;

use Livewire\Component;
use App\Models\Student;

class OverviewComponent extends Component
{
    public $student;

    public string $routePrefix = '';

    public function mount(int $id)
    {
        $this->routePrefix = $this->resolveRoutePrefix();

        $this->student = Student::with([
            'session',
            'class',
            'section',
            'group',
            'guardians.user',
            'user',
        ])
            ->where('institution_id', auth()->user()->institution_id)
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
        return view('livewire.admin.student.overview-component')
            ->with('student', $this->student)
            ->layout('layouts.branch.app', [
                'title' => 'Student Overview | ' . institution()->name,
            ]);
    }
}