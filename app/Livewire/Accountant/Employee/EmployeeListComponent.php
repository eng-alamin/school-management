<?php

namespace App\Livewire\Accountant\Employee;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;

class EmployeeListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'desc';

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    // Status Update
    public bool $showStatusModal = false;
    public ?int $statusId = null;
    public string $newStatus = '';

    /**
     * Whitelist — must match the enum values defined in the employees
     * migration exactly (active, inactive, resigned, terminated).
     */
    protected array $statusOptions = ['active', 'inactive', 'resigned', 'terminated'];

    /**
     * Only these columns are allowed to be sorted on.
     * Prevents SQL errors / column-injection from arbitrary wire:click values.
     */
    protected array $sortableFields = ['id', 'name', 'email', 'mobile'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        // BUG FIX: guard against unknown/invalid columns (previously "phone" was
        // passed here, but the actual DB column is "mobile" — sorting by it
        // caused an "Unknown column" SQL error).
        if (!in_array($field, $this->sortableFields, true)) {
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

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $employee = Employee::findOrFail($this->deleteId);

        // Activity logging before delete, not after — row must still exist for the log.
        activity()
            ->performedOn($employee)
            ->withProperties([
                'institution_id' => $employee->institution_id,
                'icon' => 'delete',
                'type' => 'employee',
            ])
            ->log('Employee deleted: ' . $employee->name);

        $employee->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Employee deleted successfully!');
    }

    /**
     * Status icon click korle eta call hobe — modal open kore, current
     * status ta preselect kore rakhe.
     */
    public function openStatusModal(int $id): void
    {
        $employee = Employee::findOrFail($id);

        $this->statusId        = $employee->id;
        $this->newStatus       = $employee->status;
        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->statusId        = null;
        $this->newStatus       = '';
    }

    public function updateStatus(): void
    {
        $this->validate([
            'statusId'  => 'required|integer|exists:employees,id',
            'newStatus' => 'required|string|in:' . implode(',', $this->statusOptions),
        ]);

        $employee = Employee::findOrFail($this->statusId);
        $oldStatus = $employee->status;

        $employee->update(['status' => $this->newStatus]);

        activity()
            ->performedOn($employee)
            ->withProperties([
                'institution_id' => $employee->institution_id,
                'icon' => 'toggle_on',
                'type' => 'employee',
                'old_status' => $oldStatus,
                'new_status' => $this->newStatus,
            ])
            ->log('Employee status changed: ' . $employee->name . ' (' . $oldStatus . ' → ' . $this->newStatus . ')');

        $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');

        $this->closeStatusModal();
    }

    public function render()
    {
        $employees = Employee::with('designation', 'department', 'user')
            // BUG FIX: OR conditions wrapped inside a single where() closure so
            // they cannot leak past the BelongsToInstitution global scope.
            // Previously the orWhere() chain was applied directly on the query
            // builder, which could bypass institution_id scoping and expose
            // other institutions' employee data during search.
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('employee_id', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('mobile', 'like', "%{$this->search}%")
                        ->orWhereHas('designation', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                        ->orWhereHas('department', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.accountant.employee.employee-list-component')
            ->with('employees', $employees)
            ->with('statusOptions', $this->statusOptions)
            ->layout('layouts.accountant.app', [
                'title' => 'Employee List | ' . institution()->name,
            ]);
    }
}