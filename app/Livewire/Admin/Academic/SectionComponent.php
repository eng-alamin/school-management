<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicSection;

class SectionComponent extends Component
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
    public $capacity;

    public string $routePrefix = '';

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();
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

    protected function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'capacity' => 'nullable|integer',
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
        $record = AcademicSection::findOrFail($id);
        $this->editId    = $id;
        $this->name      = $record->name;
        $this->capacity  = $record->capacity;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'     => $this->name,
            'capacity' => $this->capacity,
        ];

        if ($this->editId) {
            $record = AcademicSection::findOrFail($this->editId);
            $record->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'menu_book', 'type' => 'academic'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Section updated: ' . $record->name);

            $this->dispatch('toast', type: 'success', message: 'Section updated successfully!');
        } else {
            $record = AcademicSection::create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'menu_book', 'type' => 'academic'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Section created: ' . $record->name);

            $this->dispatch('toast', type: 'success', message: 'Section created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = AcademicSection::findOrFail($this->deleteId);
        $record->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'menu_book', 'type' => 'academic'])
            ->tap(function ($activity) use ($record) {
                $activity->institution_id = $record->institution_id;
                $activity->branch_id = $record->branch_id;
            })
            ->log('Section deleted: ' . $record->name);

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Section deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'capacity', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $sections = AcademicSection::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.academic.section-component')
            ->with('sections', $sections)
            ->layout('layouts.admin.app', [
                'title' => 'Sections | ' . institution()->name,
            ]);
    }
}