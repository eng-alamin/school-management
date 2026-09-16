<?php

namespace App\Livewire\Admin\Branch;

use Livewire\Component;
use App\Models\Branch;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EditComponent extends Component
{
    public $branchId;
    public $branch;

    // Branch Details
    public $name;
    public $code;
    public $address;
    public $phone;
    public $email;
    public $is_active;

    // Read-only display only — is_main can't be toggled from this form to
    // avoid ending up with zero/multiple main branches for an institution.
    public $is_main;

    public function mount($id)
    {
        $institutionId = auth()->user()->institution_id;

        $this->branch = Branch::where('institution_id', $institutionId)
            ->findOrFail($id);

        $this->branchId = $this->branch->id;

        $this->name      = $this->branch->name;
        $this->code      = $this->branch->code;
        $this->address   = $this->branch->address;
        $this->phone     = $this->branch->phone;
        $this->email     = $this->branch->email;
        $this->is_active = $this->branch->is_active;
        $this->is_main   = $this->branch->is_main;
    }

    public function rules(): array
    {
        $institutionId = auth()->user()->institution_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId))
                    ->ignore($this->branchId),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('branches', 'code')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId))
                    ->ignore($this->branchId),
            ],
            'address'   => 'nullable|string',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:255',
            'is_active' => 'boolean',
        ];
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');

        parent::failedValidation($validator);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function update(): void
    {
        $this->validate($this->rules());

        $institutionId = auth()->user()->institution_id;

        try {
            DB::transaction(function () use ($institutionId) {

                abort_unless($this->branch->institution_id === $institutionId, 403);

                $branch = Branch::where('institution_id', $institutionId)
                    ->lockForUpdate()
                    ->findOrFail($this->branchId);

                // Main branch must always stay active — deactivating it would
                // orphan every branch-scoped user who defaults to it.
                if ($branch->is_main) {
                    $this->is_active = true;
                }

                $branch->update([
                    'name'      => $this->name,
                    'code'      => $this->code,
                    'address'   => $this->address,
                    'phone'     => $this->phone,
                    'email'     => $this->email,
                    'is_active' => $this->is_active,
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($branch)
                    ->withProperties(['icon' => 'store', 'type' => 'branch'])
                    ->tap(function ($activity) use ($branch) {
                        $activity->institution_id = $branch->institution_id;
                    })
                    ->log('Branch updated: ' . $branch->name);
            });

            session()->flash('toast_success', 'Branch updated successfully!');
            $this->redirectRoute('admin.branch.view', ['id' => $this->branchId], navigate: true);

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.admin.branch.edit-component')
            ->layout('layouts.admin.app', [
                'title' => 'Edit Branch | ' . institution()->name,
            ]);
    }
}