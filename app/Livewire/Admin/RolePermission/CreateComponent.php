<?php

namespace App\Livewire\Admin\RolePermission;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\Branch;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Database\Seeders\InstitutionRolePermissionSeeder;
use App\Livewire\Admin\RolePermission\Concerns\InteractsWithPermissionMatrix;

class CreateComponent extends Component
{
    use InteractsWithPermissionMatrix;

    public string $name = '';

    private const SYSTEM_ROLES = ['admin', 'it_support', 'staff', 'accountant', 'teacher'];

    private function institutionId(): int
    {
        return auth()->user()->institution_id;
    }

    private function activeBranchId(): ?int
    {
        $institutionId = $this->institutionId();

        return auth()->user()->branch_id ?? Branch::resolveMainBranchId($institutionId);
    }

    public function rules(): array
    {
        $institutionId = $this->institutionId();
        $branchId       = $this->activeBranchId();

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (in_array(strtolower(trim($value)), self::SYSTEM_ROLES, true)) {
                        $fail('This role name is reserved for the system and cannot be used.');
                    }
                },
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId),
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

    public function resetForm(): void
    {
        $this->reset('name');
        $this->resetPermissionMatrix();
        $this->dispatch('form-reset');
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $institutionId = $this->institutionId();
        $branchId       = $this->activeBranchId();

        $validPermissionNames = Permission::where('guard_name', 'web')
            ->where('name', 'like', InstitutionRolePermissionSeeder::PREFIX . '%')
            ->whereIn('name', $this->selectedPermissions)
            ->pluck('name')
            ->all();

        try {
            DB::transaction(function () use ($institutionId, $branchId, $validPermissionNames) {

                app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

                $role = Role::create([
                    'institution_id' => $institutionId,
                    'branch_id'      => $branchId,
                    'name'           => $this->name,
                    'guard_name'     => 'web',
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
                    ->log('Role created: ' . $role->name);
            });

            session()->flash('toast_success', 'Role created successfully!');
            $this->redirectRoute('admin.role-permission.index', navigate: true);

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.admin.role-permission.create-component', [
            'matrix'  => $this->buildPermissionMatrix(),
            'actions' => self::ACTIONS,
        ])->layout('layouts.admin.app', [
            'title' => 'Create Role | ' . institution()->name,
        ]);
    }
}