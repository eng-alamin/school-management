<?php

namespace App\Livewire\Admin\RolePermission;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Database\Seeders\InstitutionRolePermissionSeeder;
use App\Livewire\Admin\RolePermission\Concerns\InteractsWithPermissionMatrix;

class EditComponent extends Component
{
    use InteractsWithPermissionMatrix;

    public $roleId;
    public $name;

    // Read-only display value — the branch a role belongs to is fixed at
    // creation time and is never re-assigned through edit (no dropdown).
    public ?int $branch_id = null;

    private const SYSTEM_ROLES = ['admin', 'it_support', 'staff', 'accountant', 'teacher'];

    private function institutionId(): int
    {
        return auth()->user()->institution_id;
    }

    public function mount($id)
    {
        $institutionId = $this->institutionId();

        $role = Role::where('institution_id', $institutionId)
            ->where('guard_name', 'web')
            ->findOrFail($id);

        $this->roleId              = $role->id;
        $this->name                = $role->name;
        $this->branch_id           = $role->branch_id;
        $this->selectedPermissions = $role->permissions()->pluck('name')->toArray();
    }

    public function rules(): array
    {
        $institutionId = $this->institutionId();
        $branchId       = $this->branch_id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId)
                    ->ignore($this->roleId),
            ],
            'selectedPermissions'   => 'array',
            'selectedPermissions.*' => Rule::exists('permissions', 'name')
                ->where(function ($query) {
                    $query->where('guard_name', 'web')
                          ->where('name', 'like', InstitutionRolePermissionSeeder::PREFIX . '%');
                }),
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
        $branchId       = $this->branch_id;

        // Defense-in-depth: re-verify the permission names actually belong
        // to the fixed global 'institution.*' permission list (guard=web)
        // before persisting.
        $validPermissionNames = Permission::where('guard_name', 'web')
            ->where('name', 'like', InstitutionRolePermissionSeeder::PREFIX . '%')
            ->whereIn('name', $this->selectedPermissions)
            ->pluck('name')
            ->all();

        try {
            DB::transaction(function () use ($institutionId, $branchId, $validPermissionNames) {

                app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

                $role = Role::where('institution_id', $institutionId)
                    ->where('guard_name', 'web')
                    ->findOrFail($this->roleId);

                // System/panel role (users.role enum-এর সাথে bound) —
                // নাম পরিবর্তন করা যাবে না, শুধু permission customize করা যাবে।
                $isSystemRole = in_array(strtolower($role->name), self::SYSTEM_ROLES, true);

                if ($isSystemRole && strtolower($this->name) !== strtolower($role->name)) {
                    throw new \RuntimeException('System role name cannot be changed.');
                }

                $role->update([
                    'name'      => $isSystemRole ? $role->name : $this->name,
                    'branch_id' => $branchId,
                ]);
                $role->syncPermissions($validPermissionNames);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($role)
                    ->withProperties(['icon' => 'door_front', 'type' => 'role'])
                    ->tap(function ($activity) use ($role) {
                        $activity->institution_id = $role->institution_id;
                        $activity->branch_id = $role->branch_id;
                    })
                    ->log('Role updated: ' . $role->name);
            });

            session()->flash('toast_success', 'Role updated successfully!');
            $this->redirectRoute('admin.role-permission.index', navigate: true);

        } catch (\RuntimeException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
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