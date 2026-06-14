<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;

class TeacherComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $teachers = Employee::with('user', 'designation', 'department')
        ->whereHas('user', fn($q) => $q->where('role', 'teacher'))
        ->when($this->search, fn($q) => $q
            ->where('name', 'like', "%{$this->search}%")
            ->orWhere('email', 'like', "%{$this->search}%")
            ->orWhere('phone', 'like', "%{$this->search}%")
            ->orWhereHas('designation', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orWhereHas('department', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
        )
        ->orderBy($this->sortField, $this->sortDirection)
        ->paginate($this->perPage);

        return view('livewire.student.teacher-component')
            ->with('teachers', $teachers)
            ->layout('layouts.student.app', [
                'title' => "Teachers | Monarchy School",
            ]);
    }
}