<?php

namespace App\Livewire\Admin\Exam;

use Livewire\Component;
use App\Models\ExamHall;
use Livewire\WithPagination;

class HallComponent extends Component
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
    public string $hall_no = '';
    public string $no_of_seat = '';

    protected function rules(): array
    {
        return [
            'hall_no'          => 'required|string|max:255',
            'no_of_seat'       => 'required|integer|min:1',
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
        $record = ExamHall::findOrFail($id);
        $this->editId = $id;
        $this->hall_no = $record->hall_no;
        $this->no_of_seat = $record->no_of_seat;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'hall_no'          => $this->hall_no,
            'no_of_seat'       => $this->no_of_seat,
        ];

        if ($this->editId) {
            ExamHall::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Data updated successfully!');
        } else {
            ExamHall::create($data);
            session()->flash('success', 'Data created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['hall_no', 'no_of_seat', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $halls = ExamHall::query()
            ->when($this->search, fn($q) => $q->where('hall_no', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.exam.hall-component')
            ->with('halls', $halls)
            ->layout('layouts.admin.app', [
                'title' => "Exam Term | School SaaS",
            ]);

    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = ExamHall::findOrFail($this->deleteId);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Data deleted successfully!');
    }
}
