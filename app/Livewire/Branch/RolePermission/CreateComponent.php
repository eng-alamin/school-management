<?php

namespace App\Livewire\Branch\RolePermission;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Livewire\Admin\RolePermission\Concerns\InteractsWithPermissionMatrix;

class CreateComponent extends Component
{
    use InteractsWithPermissionMatrix;

    public string $name = '';

    private function institutionId(): int
    {
        return auth()->user()->institution_id;
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
                    ->where('institution_id', $institutionId),
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

        // Defense-in-depth: re-verify the permission names actually belong
        // to the fixed global permission list (guard=web) before persisting.
        $validPermissionNames = Permission::where('guard_name', 'web')
            ->whereIn('name', $this->selectedPermissions)
            ->pluck('name')
            ->all();

        try {
            DB::transaction(function () use ($institutionId, $validPermissionNames) {

                // Ensure Spatie's team context matches the current institution
                app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

                // institution_id is auto-filled by Spatie's team scoping
                $role = Role::create([
                    'name'       => $this->name,
                    'guard_name' => 'web',
                ]);

                $role->syncPermissions($validPermissionNames);

                activity()
                    ->performedOn($role)
                    ->causedBy(auth()->user())
                    ->tap(fn($activity) => $activity->institution_id = $institutionId)
                    ->withProperties([
                        'icon' => 'add_moderator',
                        'type' => 'role_created',
                    ])
                    ->log('Role created: ' . $role->name);
            });

            session()->flash('toast_success', 'Role created successfully!');
            $this->redirectRoute('branch.role-permission.index', navigate: true);

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
        ])->layout('layouts.branch.app', [
            'title' => 'Create Role | ' . institution()->name,
        ]);
    }
}