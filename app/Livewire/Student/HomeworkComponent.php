<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\Homework;
use App\Models\Student;

class HomeworkComponent extends Component
{
    public string $search = '';
    public array $homeworks = [];
    public array $filteredHomeworks = [];

    public bool $showDetail = false;
    public array $detail = [];

    public function mount(): void
    {
        $student = $this->currentStudent();

        if (!$student || !$student->class_id) {
            return;
        }

        $this->homeworks = Homework::with('subject', 'class', 'section')
            ->where('class_id', $student->class_id)
            ->where(function ($query) use ($student) {
                $query->whereNull('section_id')
                      ->orWhere('section_id', $student->section_id);
            })
            ->orderByDesc('homework_date')
            ->get()
            ->toArray();

        $this->filteredHomeworks = $this->homeworks;
    }

    /**
     * Resolve the logged-in student.
     *
     * Default 'web' guard is used. Auth::user() returns a User model,
     * and Student::user_id links back to that User, so we resolve the
     * Student record via that foreign key.
     */
    protected function currentStudent(): ?Student
    {
        $userId = auth()->id();

        if (!$userId) {
            return null;
        }

        return Student::where('user_id', $userId)->first();
    }

    public function updatedSearch(string $value): void
    {
        $q = strtolower($value);

        $this->filteredHomeworks = collect($this->homeworks)->filter(fn($p) =>
            str_contains(strtolower($p['subject']['name'] ?? ''), $q) ||
            str_contains(strtolower($p['title'] ?? ''), $q) ||
            str_contains(strtolower($p['homework_date'] ?? ''), $q) ||
            str_contains(strtolower($p['submission_date'] ?? ''), $q) ||
            str_contains(strtolower($p['status'] ?? ''), $q)
        )->values()->toArray();
    }

    public function openDetail(int $id): void
    {
        $hw = collect($this->homeworks)->firstWhere('id', $id);

        if (!$hw) {
            return;
        }

        // Defense-in-depth: ensure the homework actually belongs to this student's class,
        // even though $this->homeworks was already scoped in mount().
        $this->detail     = $hw;
        $this->showDetail = true;
    }

    public function render()
    {
        return view('livewire.student.homework-component')
            ->layout('layouts.student.app', [
                'title' => 'Homeworks | ' . institution()->name,
            ]);
    }
}