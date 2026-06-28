<?php

namespace App\Livewire\Teacher\Exam;

use Livewire\Component;
use App\Models\ExamTerm;
use App\Models\ExamHall;
use App\Models\ExamMark;
use App\Models\ExamType;
use App\Models\ExamSetup;
use Livewire\WithPagination;

class ExamSetupComponent extends Component
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
    public ?int $exam_term_id = null;
    public ?int $exam_type_id = null;
    public array $mark_distribution = [];
    public string $remarks = '';
    public bool $is_published = false;

    protected function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'exam_term_id'        => 'nullable|exists:exam_terms,id',
            'exam_type_id'        => 'nullable|exists:exam_types,id',
            'mark_distribution'   => 'nullable|array',
            'mark_distribution.*' => 'nullable|string',
            'remarks'             => 'nullable|string',
            'is_published'        => 'boolean',
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
        $this->dispatch('showModalChanged', selected: []);
    }

    public function openEdit(int $id): void
    {
        $record = ExamSetup::findOrFail($id);
        $this->editId = $id;
        $this->name = $record->name;
        $this->exam_term_id = $record->exam_term_id;
        $this->exam_type_id = $record->exam_type_id;
        $this->mark_distribution = $record->marks ?? [];
        $this->remarks = $record->remarks ?? '';
        $this->is_published = $record->is_published;
        $this->showModal = true;

        $this->dispatch('showModalChanged', selected: $this->mark_distribution);
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'         => $this->name,
            'exam_term_id' => $this->exam_term_id,
            'exam_type_id' => $this->exam_type_id,
            'marks'        => $this->mark_distribution,
            'remarks'      => $this->remarks,
            'is_published' => $this->is_published,
        ];

        if ($this->editId) {
            ExamSetup::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Data updated successfully!');
        } else {
            ExamSetup::create($data);
            session()->flash('success', 'Data created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'name', 'exam_term_id', 'exam_type_id', 'mark_distribution', 'remarks', 'is_published']);
        $this->mark_distribution = [];
        $this->resetValidation();
    }

    // ✅ নতুন: list page থেকে এক ক্লিকে Publish টগল করার জন্য
    public function togglePublished(int $id): void
    {
        $setup = ExamSetup::findOrFail($id);
        $setup->update(['is_published' => ! $setup->is_published]);
    }

    // ✅ নতুন: list page থেকে এক ক্লিকে Publish Result টগল করার জন্য
    public function toggleResultPublished(int $id): void
    {
        $setup = ExamSetup::findOrFail($id);
        $setup->update(['is_result_published' => ! $setup->is_result_published]);
    }

    public function render()
    {
        $terms = ExamTerm::pluck('name', 'id');
        $types = ExamType::pluck('name', 'id');
        $marks = ExamMark::pluck('name', 'id');

        $setups = ExamSetup::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.teacher.exam.exam-setup-component')
            ->with('terms', $terms)
            ->with('types', $types)
            ->with('marks', $marks)
            ->with('setups', $setups)
            ->layout('layouts.teacher.app', [
                'title' => 'Exam Setup | ' . institution()->name,
            ]);
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = ExamSetup::findOrFail($this->deleteId);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Data deleted successfully!');
    }
}