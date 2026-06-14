<?php

namespace App\Livewire\Student;

use Livewire\Component;
use App\Models\Homework;

class HomeworkComponent extends Component
{
    public string $search = '';
    public array $homeworks = [];
    public array $filteredHomeworks = [];

    public bool $showDetail = false;
    public array $detail = [];

    public function mount(): void
    {
        $student = auth()->user()->student;

        if (!$student || !$student->class_id || !$student->section_id) {
            return;
        }

        $this->homeworks = Homework::with('subject', 'class', 'section')
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->orderByDesc('homework_date')
            ->get()
            ->toArray();

        $this->filteredHomeworks = $this->homeworks;
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

        if (!$hw) return;

        $this->detail    = $hw;
        $this->showDetail = true;
    }

    public function render()
    {
        return view('livewire.student.homework-component')
            ->layout('layouts.student.app', [
                'title' => "Homeworks | Monarchy School",
            ]);
    }
}