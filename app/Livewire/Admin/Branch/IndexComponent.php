<?php

namespace App\Livewire\Admin\Branch;

use Livewire\Component;
use App\Models\Branch;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class IndexComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public string $filterStatus = '';
    public int $perPage = 10;

    // Modal
    public bool $showModal = false;
    public bool $showViewModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;
    public ?Branch $viewRecord = null;

    // Form
    public ?int $editId = null;
    public bool $editIsMain = false;
    public string $name = '';
    public string $code = '';
    public string $address = '';
    public string $phone = '';
    public string $email = '';
    public bool $is_active = true;

    protected function rules(): array
    {
        $institutionId = auth()->user()->institution_id;

        return [
            'name' => 'required|min:2|max:255',
            'code' => [
                'required',
                'alpha_num',
                'max:20',
                Rule::unique('branches', 'code')
                    ->where('institution_id', $institutionId)
                    ->ignore($this->editId),
            ],
            'address' => 'nullable|max:500',
            'phone' => 'nullable|max:20',
            'email' => 'nullable|email|max:150',
            'is_active' => 'boolean',
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = Branch::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        $this->editId = $id;
        $this->editIsMain = $record->is_main;
        $this->name = $record->name;
        $this->code = $record->code;
        $this->address = $record->address ?? '';
        $this->phone = $record->phone ?? '';
        $this->email = $record->email ?? '';
        $this->is_active = $record->is_active;
        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord = Branch::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $institutionId = auth()->user()->institution_id;

            $data = [
                'institution_id' => $institutionId,
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'address' => $this->address ?: null,
                'phone' => $this->phone ?: null,
                'email' => $this->email ?: null,
                'is_active' => $this->is_active,
            ];

            if ($this->editId) {
                $record = Branch::where('institution_id', $institutionId)
                    ->findOrFail($this->editId);

                // Main branch cannot be deactivated - it's the required fallback
                if ($record->is_main) {
                    $data['is_active'] = true;
                }

                $record->update($data);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'store', 'type' => 'branch'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                    })
                    ->log('Branch updated: ' . $record->name);
            } else {
                // institution_id NOT auto-filled by any global scope - explicit here
                $record = Branch::create($data);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'store', 'type' => 'branch'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                    })
                    ->log('New branch created: ' . $record->name);
            }

            DB::commit();

            $this->dispatch('toast', type: 'success', message: $this->editId ? 'Data updated successfully!' : 'Data created successfully!');

            $this->showModal = false;
            $this->resetForm();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function confirmDeleteRecord(int $id): void
    {
        $record = Branch::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        if ($record->is_main) {
            $this->dispatch('toast', type: 'error', message: 'Main Branch cannot be deleted!');
            return;
        }

        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        DB::beginTransaction();

        try {
            $record = Branch::where('institution_id', auth()->user()->institution_id)
                ->findOrFail($this->deleteId);

            if ($record->is_main) {
                DB::rollBack();
                $this->dispatch('toast', type: 'error', message: 'Main Branch cannot be deleted!');
                $this->confirmDelete = false;
                return;
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'store', 'type' => 'branch'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                })
                ->log('Branch deleted: ' . $record->name);

            $record->delete();

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId = null;

            $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function toggleStatus(int $id): void
    {
        DB::beginTransaction();

        try {
            $record = Branch::where('institution_id', auth()->user()->institution_id)
                ->findOrFail($id);

            if ($record->is_main) {
                DB::rollBack();
                $this->dispatch('toast', type: 'error', message: 'Main Branch cannot be deactivated!');
                return;
            }

            $newStatus = ! $record->is_active;
            $record->update(['is_active' => $newStatus]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'store', 'type' => 'branch'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                })
                ->log('Branch status changed to ' . ($newStatus ? 'active' : 'inactive') . ': ' . $record->name);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'code', 'address', 'phone', 'email', 'editId', 'editIsMain']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;

        $branches = Branch::where('institution_id', $institutionId)
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterStatus !== '', fn ($q) =>
                $q->where('is_active', $this->filterStatus === 'active')
            )
            ->orderByDesc('is_main')
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.branch.index-component')
            ->with('branches', $branches)
            ->layout('layouts.admin.app', [
                'title' => 'Branches | ' . institution()->name,
            ]);
    }
}