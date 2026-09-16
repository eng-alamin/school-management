<?php

namespace App\Livewire\Admin\Branch;

use Livewire\Component;
use App\Models\Branch;
use Illuminate\Validation\Rule;

class AccountComponent extends Component
{
    public Branch $branch;
    public $user;

    // Account fields
    public $name;
    public $username;
    public $email;
    public $phone;

    // Password fields
    public $password;
    public $password_confirmation;

    public string $routePrefix = '';

    public function mount(int $id)
    {
        $this->routePrefix = $this->resolveRoutePrefix();

        $this->branch = Branch::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        // A branch's "account" is the manager user linked to it — a Branch
        // itself has no login of its own. Main Branch (auto-created by
        // Institution::booted()) is managed by the institution's 'admin';
        // any other (custom/random) branch is managed by a 'branch' user.
        $managerRole = $this->branch->is_main ? 'admin' : 'branch';

        $this->user = $this->branch->users()
            ->where('role', $managerRole)
            ->first();

        if (! $this->user) {
            abort(404, 'No ' . ($this->branch->is_main ? 'admin' : 'branch manager') . ' account found for this branch.');
        }

        $this->name     = $this->user->name;
        $this->username = $this->user->username;
        $this->email    = $this->user->email;
        $this->phone    = $this->user->phone;
    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
    }

    public function updateAccount()
    {
        $this->validate([
            'name'     => 'required|string|max:100',
            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'username')->ignore($this->user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('users', 'phone')->ignore($this->user->id),
            ],
        ]);

        $this->user->update([
            'name'     => $this->name,
            'username' => $this->username,
            'email'    => $this->email,
            'phone'    => $this->phone,
        ]);

        $record = $this->user;
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'store', 'type' => 'branch'])
            ->tap(function ($activity) use ($record) {
                $activity->institution_id = $record->institution_id;
                $activity->branch_id = $record->branch_id;
            })
            ->log('Branch account updated: ' . $record->name);

        $this->dispatch('toast', type: 'success', message: 'Branch account updated successfully!');
    }

    public function updatePassword()
    {
        $this->validate([
            'password' => ['required', 'confirmed', 'min:4'],
        ]);

        $this->user->update([
            'password' => $this->password,
        ]);

        $record = $this->user;
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'store', 'type' => 'branch'])
            ->tap(function ($activity) use ($record) {
                $activity->institution_id = $record->institution_id;
                $activity->branch_id = $record->branch_id;
            })
            ->log('Branch password updated: ' . $record->name);

        $this->reset(['password', 'password_confirmation']);
        $this->dispatch('toast', type: 'success', message: 'Password reset successfully!');
    }

    public function render()
    {
        return view('livewire.admin.branch.account-component')
            ->with('branch', $this->branch)
            ->layout('layouts.admin.app', [
                'title' => 'Branch Account | ' . institution()->name,
            ]);
    }
}