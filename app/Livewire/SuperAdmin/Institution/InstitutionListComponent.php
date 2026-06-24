<?php

namespace App\Livewire\SuperAdmin\Institution;

use Livewire\Component;
use App\Models\Institution;
use App\Models\User;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class InstitutionListComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search       = '';
    public string $filterStatus = '';
    public int    $perPage      = 10;

    // Modal
    public bool    $showModal     = false;
    public bool    $showViewModal = false;
    public bool    $confirmDelete = false;
    public ?int    $deleteId      = null;
    public ?Institution $viewRecord    = null;

    // Form
    public ?int   $editId    = null;
    public string $name      = '';
    public string $email     = '';
    public string $phone     = '';
    public string $address   = '';
    public string $status = '1';
    public         $logo     = null;
    public string  $existingLogo = '';

    protected function rules(): array
    {
        $rules = [
            'name'      => 'required|min:3|max:255',
            'email'     => 'required|email|unique:institutions,email,' . $this->editId,
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:500',
            'status' => 'required|in:0,1',
            'logo'      => 'nullable|image|max:2048',
        ];

        return $rules;
    }

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId    = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = Institution::findOrFail($id);

        $this->editId       = $id;
        $this->name         = $record->name;
        $this->email        = $record->email;
        $this->phone        = $record->phone ?? '';
        $this->address      = $record->address ?? '';
        $this->status    = (string) $record->status;
        $this->existingLogo = $record->system_logo ?? '';
        $this->logo         = null;
        $this->showModal    = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord    = Institution::findOrFail($id);
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $logoPath = $this->existingLogo;

        if ($this->logo) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $this->logo->store('institutions/logos', 'public');
        }

        $data = [
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone ?: null,
            'address'   => $this->address ?: null,
            'status'    => $this->status,
            'system_logo'      => $logoPath ?: null,
        ];

        if ($this->editId) {
            $record = Institution::findOrFail($this->editId);
            $record->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'institution', 'type' => 'institution'])
                ->log('Institution updated: ' . $record->name);

            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $record = Institution::create($data);

            $user = User::create([
                'name'      => $this->name,
                'email'     => $this->email,
                'password'  => $this->email,
                'role'      => 'admin',
                'institution_id' => $record->id,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'institution', 'type' => 'institution'])
                ->log('New institution created: ' . $record->name);

            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
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
        $record = Institution::findOrFail($this->deleteId);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'institution', 'type' => 'institution'])
            ->log('Institution deleted: ' . $record->name);

        if ($record->logo) {
            Storage::disk('public')->delete($record->logo);
        }

        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    public function toggleStatus(int $id): void
    {
        $record    = Institution::findOrFail($id);
        $newStatus = $record->status ? 0 : 1;

        $record->update(['status' => $newStatus]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'institution', 'type' => 'institution'])
            ->log('Institution status changed to ' . ($newStatus ? 'Active' : 'Inactive') . ': ' . $record->name);

        $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
    }

    public function removeLogo(): void
    {
        if ($this->editId && $this->existingLogo) {
            Storage::disk('public')->delete($this->existingLogo);

            $record = Institution::findOrFail($this->editId);
            $record->update(['logo' => null]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'institution', 'type' => 'institution'])
                ->log('Logo removed from institution: ' . $record->name);

            $this->existingLogo = '';
            $this->dispatch('toast', type: 'success', message: 'Data removed successfully!');
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'name', 'email', 'phone', 'address', 'logo', 'existingLogo', 'editId',
        ]);
        $this->status = '1';
        $this->resetValidation();
    }

    public function render()
    {
        $institutions = Institution::query()
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('name', 'like', "%{$this->search}%")
                       ->orWhere('email', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.super-admin.institution.institution-list-component')
            ->with('institutions', $institutions)
            ->layout('layouts.superadmin.app', [
                'title' => 'Institutions | ' . setting('app_name', 'EMS'),
            ]);
    }
}