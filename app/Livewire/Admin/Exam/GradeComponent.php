<?php

namespace App\Livewire\Admin\Exam;

use Livewire\Component;
use App\Models\ExamGrade;
use Livewire\WithPagination;

class GradeComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Modal
    public bool $showModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    // Form
    public ?int $editId = null;
    public string $name = '';
    public ?float $grade_point = null;
    public ?float $min_percentage = null;
    public ?float $max_percentage = null;
    public string $remarks = '';

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'grade_point' => 'required|numeric|min:0|max:5',
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage'   => 'required|numeric|min:0|max:100|gte:min_percentage',
        ];
    }

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

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = ExamGrade::findOrFail($id);
        $this->editId = $id;
        $this->name = $record->name;
        $this->grade_point = $record->grade_point;
        $this->min_percentage = $record->min_percentage;
        $this->max_percentage = $record->max_percentage;
        $this->remarks = $record->remarks;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'             => $this->name,
            'grade_point'      => $this->grade_point,
            'min_percentage'   => $this->min_percentage,
            'max_percentage'   => $this->max_percentage,
            'remarks'          => $this->remarks,
        ];

        if ($this->editId) {
            ExamGrade::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Data updated successfully!');
        } else {
            ExamGrade::create($data);
            session()->flash('success', 'Data created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'grade_point', 'min_percentage', 'max_percentage', 'remarks', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $grades = ExamGrade::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.exam.grade-component')
            ->with('grades', $grades)
            ->layout('layouts.admin.app', [
                'title' => "Grades Range | School SaaS",
            ]);

    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = ExamGrade::findOrFail($this->deleteId);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Data deleted successfully!');
    }
}