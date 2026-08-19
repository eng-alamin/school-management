<?php

namespace App\Livewire\Branch\RolePermission;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class IndexComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    public bool $showViewModal = false;
    public ?int $viewingRoleId = null;

    protected array $sortableFields = ['name', 'created_at'];

    public function mount(): void
    {
        // Surface a success toast after redirect back from Create/Edit pages.
        if (session()->has('toast_success')) {
            $this->dispatch('toast', type: 'success', message: session()->pull('toast_success'));
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
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

    private function institutionId(): int
    {
        return auth()->user()->institution_id;
    }

    public function openViewModal(int $roleId): void
    {
        $institutionId = $this->institutionId();

        abort_unless(
            Role::where('institution_id', $institutionId)->where('id', $roleId)->exists(),
            403
        );

        $this->viewingRoleId = $roleId;
        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewingRoleId = null;
    }

    public function confirmDeleteRecord(int $id): void
    {
        $institutionId = $this->institutionId();

        abort_unless(
            Role::where('institution_id', $institutionId)->where('id', $id)->exists(),
            403
        );

        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $institutionId = $this->institutionId();

        abort_unless($this->deleteId, 404);

        try {
            DB::transaction(function () use ($institutionId) {
                $role = Role::where('institution_id', $institutionId)
                    ->where('guard_name', 'web')
                    ->findOrFail($this->deleteId);

                abort_if($role->users()->exists(), 422, 'This role is assigned to users and cannot be deleted.');

                activity()
                    ->performedOn($role)
                    ->causedBy(auth()->user())
                    ->tap(fn($activity) => $activity->institution_id = $institutionId)
                    ->withProperties(['icon' => 'delete', 'type' => 'role_deleted'])
                    ->log('Role deleted: ' . $role->name);

                $role->delete();
            });

            $this->confirmDelete = false;
            $this->deleteId      = null;

            $this->dispatch('toast', type: 'success', message: 'Role deleted successfully!');

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage() ?: 'Something went wrong!');
        }
    }

    public function render()
    {
        $institutionId = $this->institutionId();

        $roles = Role::query()
            ->where('institution_id', $institutionId)
            ->where('guard_name', 'web')
            ->withCount('permissions')
            ->when($this->search, function ($q) {
                $q->where(fn($sub) => $sub->where('name', 'like', "%{$this->search}%"));
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $viewingRole = $this->viewingRoleId
            ? Role::where('institution_id', $institutionId)
                ->with('permissions')
                ->find($this->viewingRoleId)
            : null;

        return view('livewire.admin.role-permission.index-component')
            ->with('roles', $roles)
            ->with('viewingRole', $viewingRole)
            ->layout('layouts.branch.app', [
                'title' => 'Roles & Permissions | ' . institution()->name,
            ]);
    }
}