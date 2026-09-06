<?php

namespace App\Livewire\ITSupport\Employee;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeDepartment;
use Illuminate\Validation\Rule;

class DepartmentComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Sortable 
    protected const SORTABLE_FIELDS = ['id', 'name'];

    // Modal
    public bool $showModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    // Form
    public ?int $editId = null;
    public string $name = '';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employee_departments', 'name')
                    ->where('institution_id', auth()->user()->institution_id)
                    ->ignore($this->editId),
            ]
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    private function resolvedSortField(): string
    {
        return in_array($this->sortField, self::SORTABLE_FIELDS, true)
            ? $this->sortField
            : 'id';
    }

    private function resolvedSortDirection(): string
    {
        return $this->sortDirection === 'desc' ? 'desc' : 'asc';
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = EmployeeDepartment::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        $this->editId    = $id;
        $this->name      = $record->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $institutionId = auth()->user()->institution_id;

        if ($this->editId) {
            $record = EmployeeDepartment::where('institution_id', $institutionId)
                ->findOrFail($this->editId);

            $record->update([
                'name'      => $this->name,
            ]);

            $this->dispatch('toast', type: 'success', message: 'Department updated successfully!');
        } else {
            EmployeeDepartment::create([
                'name'      => $this->name,
            ]);

            $this->dispatch('toast', type: 'success', message: 'Department created successfully!');
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
        $department = EmployeeDepartment::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($this->deleteId);

        activity()
            ->performedOn($department)
            ->withProperties([
                'institution_id' => $department->institution_id,
                'icon' => 'delete',
                'type' => 'employee_department',
            ])
            ->log('Department deleted: ' . $department->name);

        $department->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Department deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $query = EmployeeDepartment::query()
            ->where('institution_id', auth()->user()->institution_id)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"));

        $departments = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.employee.department-component')
            ->with('departments', $departments)
            ->layout('layouts.itsupport.app', [
                'title' => 'Departments | ' . institution()->name,
            ]);
    }
}