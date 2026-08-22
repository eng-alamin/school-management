<?php

namespace App\Livewire\Accountant\Parent;

use Livewire\Component;
use App\Models\Guardian;
use Illuminate\Validation\Rule;

class ParentAccountComponent extends Component
{
    public Guardian $guardian;
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

        $this->guardian = Guardian::with('user')->findOrFail($id);
        $this->user     = $this->guardian->user;

        if (! $this->user) {
            abort(404, 'No linked user account found for this parent.');
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

        activity()
            ->performedOn($this->user)
            ->causedBy(auth()->user())
            ->withProperties([
                'icon' => 'person',
                'type' => 'parent_account_update',
            ])
            ->log("Parent account updated: {$this->user->name}");

        $this->dispatch('toast', type: 'success', message: 'Parent account updated successfully!');
    }

    public function updatePassword()
    {
        $this->validate([
            'password' => ['required', 'confirmed', 'min:4'],
        ]);

        $this->user->update([
            'password' => $this->password,
        ]);

        activity()
            ->performedOn($this->user)
            ->causedBy(auth()->user())
            ->withProperties([
                'icon' => 'lock_reset',
                'type' => 'parent_password_reset',
            ])
            ->log("Password reset for parent: {$this->user->name}");

        $this->reset(['password', 'password_confirmation']);

        $this->dispatch('toast', type: 'success', message: 'Password reset successfully!');
    }

    public function render()
    {
        return view('livewire.admin.parent.parent-account-component')
            ->with('guardian', $this->guardian)
            ->layout('layouts.accountant.app', [
                'title' => 'Parent Account | ' . institution()->name,
            ]);
    }
}