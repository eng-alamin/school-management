<?php

namespace App\Livewire\Branch\Student;

use Livewire\Component;
use App\Models\Student;
use Illuminate\Validation\Rule;

class AccountComponent extends Component
{
    public Student $student;
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
        $this->student = Student::with('user')
            ->where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        $this->user = $this->student->user;

        if (! $this->user) {
            abort(404, 'No linked user account found for this student.');
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
                'type' => 'student_account_update',
            ])
            ->log("Student account updated: {$this->user->name}");

        $this->dispatch('toast', type: 'success', message: 'Student account updated successfully!');
    }

    public function updatePassword()
    {
        $this->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $this->user->update([
            'password' => $this->password,
        ]);

        activity()
            ->performedOn($this->user)
            ->causedBy(auth()->user())
            ->withProperties([
                'icon' => 'lock_reset',
                'type' => 'student_password_reset',
            ])
            ->log("Password reset for student: {$this->user->name}");

        $this->reset(['password', 'password_confirmation']);

        $this->dispatch('toast', type: 'success', message: 'Password reset successfully!');
    }

    public function render()
    {
        return view('livewire.branch.student.account-component')
            ->with('student', $this->student)
            ->layout('layouts.branch.app', [
                'title' => 'Student Account | ' . institution()->name,
            ]);
    }
}