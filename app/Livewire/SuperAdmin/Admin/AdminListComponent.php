<?php

namespace App\Livewire\SuperAdmin\Admin;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class AdminListComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search       = '';
    public string $filterInstitution = '';
    public string $filterStatus = '';
    public int    $perPage      = 10;

    // Modal
    public bool   $showModal     = false;
    public bool   $showViewModal = false;
    public bool   $confirmDelete = false;
    public ?int   $deleteId      = null;
    public ?User  $viewRecord    = null;

    // Form
    public ?int    $editId                 = null;
    public string  $name                   = '';
    public string  $username               = '';
    public string  $phone                  = '';
    public string  $email                  = '';
    public ?int    $institution_id              = null;
    public string  $password               = '';
    public string  $password_confirmation  = '';
    public string  $is_active              = '1';
    public         $avatar                 = null;
    public string  $existingAvatar         = '';

    protected function rules(): array
    {
        return [
            'name'      => 'required|min:3|max:100',
            'username'  => 'nullable|max:100|unique:users,username,' . $this->editId,
            'phone'     => 'nullable|max:15|unique:users,phone,' . $this->editId,
            'email'     => 'nullable|email|max:150|unique:users,email,' . $this->editId,
            'institution_id' => 'required|exists:institutions,id',
            'password'  => $this->editId ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
            'avatar'    => 'nullable|image|max:2048',
            'is_active' => 'required|in:0,1',
        ];
    }

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterInstitution(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId    = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = User::where('role', 'admin')->findOrFail($id);

        $this->editId         = $id;
        $this->name           = $record->name;
        $this->username       = $record->username ?? '';
        $this->phone          = $record->phone ?? '';
        $this->email          = $record->email ?? '';
        $this->institution_id      = $record->institution_id;
        $this->is_active      = (string) (int) $record->is_active;
        $this->existingAvatar = $record->avatar ?? '';
        $this->password               = '';
        $this->password_confirmation  = '';
        $this->avatar          = null;
        $this->showModal       = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord    = User::where('role', 'admin')->with('institution')->findOrFail($id);
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $avatarPath = $this->existingAvatar;

        if ($this->avatar) {
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }
            $avatarPath = $this->avatar->store('admins/avatars', 'public');
        }

        $data = [
            'name'      => $this->name,
            'username'  => $this->username ?: null,
            'phone'     => $this->phone ?: null,
            'email'     => $this->email ?: null,
            'institution_id' => $this->institution_id,
            'is_active' => $this->is_active,
            'avatar'    => $avatarPath ?: null,
        ];

        if ($this->password) {
            $data['password'] = $this->password; // User model হয়তো 'hashed' cast ব্যবহার করছে
        }

        if ($this->editId) {
            $record = User::where('role', 'admin')->findOrFail($this->editId);
            $record->update($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'admin_panel_settings', 'type' => 'admin'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                })
                ->log('Admin updated: ' . $record->name);

            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $data['role']        = 'admin';
            $data['is_verified'] = true;

            $record = User::create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'admin_panel_settings', 'type' => 'admin'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                })
                ->log('New admin created: ' . $record->name);

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
        $record = User::where('role', 'admin')->findOrFail($this->deleteId);

        // ── Activity Log (before delete) ─────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'admin_panel_settings', 'type' => 'admin'])
            ->tap(function ($activity) use ($record) {
                $activity->institution_id = $record->institution_id;
            })
            ->log('Admin deleted: ' . $record->name);

        if ($record->avatar) {
            Storage::disk('public')->delete($record->avatar);
        }

        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    public function toggleStatus(int $id): void
    {
        $record    = User::where('role', 'admin')->findOrFail($id);
        $newStatus = $record->is_active ? 0 : 1;

        $record->update(['is_active' => $newStatus]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'admin_panel_settings', 'type' => 'admin'])
            ->tap(function ($activity) use ($record) {
                $activity->institution_id = $record->institution_id;
            })
            ->log('Admin status changed to ' . ($newStatus ? 'Active' : 'Inactive') . ': ' . $record->name);

        $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
    }

    public function removeAvatar(): void
    {
        if ($this->editId && $this->existingAvatar) {
            Storage::disk('public')->delete($this->existingAvatar);

            $record = User::where('role', 'admin')->findOrFail($this->editId);
            $record->update(['avatar' => null]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'admin_panel_settings', 'type' => 'admin'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                })
                ->log('Avatar removed from admin: ' . $record->name);

            $this->existingAvatar = '';
            $this->dispatch('toast', type: 'success', message: 'Data removed successfully!');
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'name', 'username', 'phone', 'email', 'institution_id',
            'password', 'password_confirmation', 'avatar', 'existingAvatar', 'editId',
        ]);
        $this->is_active = '1';
        $this->resetValidation();
    }

    public function render()
    {
        $admins = User::where('role', 'admin')
            ->with('institution')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('name', 'like', "%{$this->search}%")
                       ->orWhere('email', 'like', "%{$this->search}%")
                       ->orWhere('phone', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterInstitution, fn ($q) => $q->where('institution_id', $this->filterInstitution))
            ->when($this->filterStatus !== '', fn ($q) => $q->where('is_active', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);

        $institutions = Institution::orderBy('name')->get(['id', 'name']);

        return view('livewire.super-admin.admin.admin-list-component')
            ->with('admins', $admins)
            ->with('institutions', $institutions)
            ->layout('layouts.superadmin.app', [
                'title' => 'Admins | ' . setting('app_name', 'EMS'),
            ]);
    }
}