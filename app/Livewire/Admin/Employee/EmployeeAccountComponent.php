<?php

namespace App\Livewire\Admin\Employee;

use Livewire\Component;
use App\Models\Employee;
use Illuminate\Validation\Rule;

class EmployeeAccountComponent extends Component
{
    public Employee $employee;
    public $user;

    // Account fields
    public $name;
    public $username;
    public $email;
    public $phone;

    // Password fields
    public $password;
    public $password_confirmation;

    public function mount(int $id)
    {
        $this->employee = Employee::with('user')
            ->where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        $this->user = $this->employee->user;

        if (! $this->user) {
            abort(404, 'No linked user account found for this employee.');
        }

        $this->name     = $this->user->name;
        $this->username = $this->user->username;
        $this->email    = $this->user->email;
        $this->phone    = $this->user->phone;
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
                'type' => 'employee_account_update',
            ])
            ->log("Employee account updated: {$this->user->name}");

        $this->dispatch('toast', type: 'success', message: 'Employee account updated successfully!');
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
                'type' => 'employee_password_reset',
            ])
            ->log("Password reset for employee: {$this->user->name}");

        $this->reset(['password', 'password_confirmation']);

        $this->dispatch('toast', type: 'success', message: 'Password reset successfully!');
    }

    public function render()
    {
        return view('livewire.admin.employee.employee-account-component')
            ->with('employee', $this->employee)
            ->layout('layouts.admin.app', [
                'title' => 'Employee Account | ' . institution()->name,
            ]);
    }
}