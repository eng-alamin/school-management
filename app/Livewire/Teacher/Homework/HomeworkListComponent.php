<?php

namespace App\Livewire\Teacher\Homework;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;

class HomeworkListComponent extends Component
{
    use WithPagination;

    public $search    = '';
    public $perPage   = 10;
    public $sortField = 'created_at';
    public $sortDir   = 'desc';

    public $filterClass   = '';
    public $filterSection = '';

    public array $availableSections = [];

    public $confirmDelete = false;
    public $deleteId;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClass($value): void
    {
        $this->filterSection     = '';
        $this->availableSections = [];
        $this->resetPage();

        if (!$value) return;

        $assigns = AcademicClassAssign::with('section')
            ->where('class_id', $value)
            ->whereNotNull('section_id')
            ->get();

        $this->availableSections = $assigns
            ->filter(fn($a) => $a->section)
            ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();
    }

    public function updatedFilterSection(): void
    {
        $this->resetPage();
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }
    }

    public function confirmDeleteRecord($id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        Homework::find($this->deleteId)?->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;

        $this->dispatch('toast', type: 'success', message: 'Homework deleted successfully.');
    }

    public function render()
    {
        $classes = AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();

        $homeworks = Homework::with(['class', 'section', 'subject'])
            ->when($this->search, fn($q) =>
                $q->where('title', 'like', '%' . $this->search . '%')
            )
            ->when($this->filterClass, fn($q) =>
                $q->where('class_id', $this->filterClass)
            )
            ->when($this->filterSection && $this->filterSection !== 'all', fn($q) =>
                $q->where('section_id', $this->filterSection)
            )
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.teacher.homework.homework-list-component')
            ->with('classes', $classes)
            ->with('homeworks', $homeworks)
            ->layout('layouts.teacher.app', [
                'title' => 'Homework List | ' . institution()->name,
            ]);
    }
}