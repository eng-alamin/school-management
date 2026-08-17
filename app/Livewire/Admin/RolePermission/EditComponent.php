<?php

namespace App\Livewire\Admin\RolePermission;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Livewire\Admin\RolePermission\Concerns\InteractsWithPermissionMatrix;

class EditComponent extends Component
{
    use InteractsWithPermissionMatrix;

    public int $roleId;
    public string $name = '';

    private function institutionId(): int
    {
        return auth()->user()->institution_id;
    }

    public function mount(int $id): void
    {
        $institutionId = $this->institutionId();

        // IDOR guard: role must belong to the current institution.
        $role = Role::where('institution_id', $institutionId)
            ->where('guard_name', 'web')
            ->findOrFail($id);

        $this->roleId              = $role->id;
        $this->name                = $role->name;
        $this->selectedPermissions = $role->permissions()->pluck('name')->toArray();
    }

    public function rules(): array
    {
        $institutionId = $this->institutionId();

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->where('institution_id', $institutionId)
                    ->ignore($this->roleId),
            ],
            'selectedPermissions'   => 'array',
            'selectedPermissions.*' => Rule::exists('permissions', 'name')->where('guard_name', 'web'),
        ];
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');

        parent::failedValidation($validator);
    }

    public function updated($propertyName): void
    {
        if ($propertyName === 'name') {
            $this->validateOnly($propertyName, $this->rules());
        }
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $institutionId = $this->institutionId();

        // Defense-in-depth: re-verify the permission names actually belong
        // to the fixed global permission list (guard=web) before persisting.
        $validPermissionNames = Permission::where('guard_name', 'web')
            ->whereIn('name', $this->selectedPermissions)
            ->pluck('name')
            ->all();

        try {
            DB::transaction(function () use ($institutionId, $validPermissionNames) {

                app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

                // Re-verify ownership server-side (IDOR guard) even though
                // mount() already checked — public property $roleId could
                // theoretically be tampered with client-side between requests.
                $role = Role::where('institution_id', $institutionId)
                    ->where('guard_name', 'web')
                    ->findOrFail($this->roleId);

                $role->update(['name' => $this->name]);
                $role->syncPermissions($validPermissionNames);

                activity()
                    ->performedOn($role)
                    ->causedBy(auth()->user())
                    ->tap(fn($activity) => $activity->institution_id = $institutionId)
                    ->withProperties([
                        'icon' => 'edit',
                        'type' => 'role_updated',
                    ])
                    ->log('Role updated: ' . $role->name);
            });

            session()->flash('toast_success', 'Role updated successfully!');
            $this->redirectRoute('admin.role-permission.index', navigate: true);

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.admin.role-permission.edit-component', [
            'matrix'  => $this->buildPermissionMatrix(),
            'actions' => self::ACTIONS,
        ])->layout('layouts.admin.app', [
            'title' => 'Edit Role | ' . institution()->name,
        ]);
    }
}