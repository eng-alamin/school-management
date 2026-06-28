<?php

namespace App\Livewire\Teacher\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class SettingComponent extends Component
{
    use WithFileUploads;

    public $user;

    // Profile Details
    public $name;
    public $email;
    public $phone;
    public $avatar;     // raw relative path, e.g. "avatars/xxx.jpg"
    public $newAvatar;

    // Password
    public $current_password;
    public $password;
    public $password_confirmation;

    public function mount()
    {
        $this->user = Auth::user();

        $this->name   = $this->user->name;
        $this->email  = $this->user->email;
        $this->phone  = $this->user->phone;
        $this->avatar = $this->user->avatar;
    }

    public function updateProfile()
    {
        $this->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $this->user->id,
            'phone'     => 'nullable|string|max:20',
            'newAvatar' => 'nullable|image|max:2048',
        ]);

        $avatarPath = $this->avatar;

        if ($this->newAvatar) {
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }
            $avatarPath = $this->newAvatar->store('avatars', 'public');
        }

        $data = [
            'name'   => $this->name,
            'email'  => $this->email,
            'phone'  => $this->phone,
            'avatar' => $avatarPath,
        ];

        $this->user->update($data);
        $this->avatar = $avatarPath;

        $this->dispatch('toast', type: 'success', message: 'Profile updated successfully!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:4'],
        ]);

        if (! Hash::check($this->current_password, $this->user->password)) {

            $this->addError(
                'current_password',
                'Current password is incorrect.'
            );

            return;
        }

        $this->user->update([
            'password' => $this->password,
        ]);

        $this->reset([
            'current_password',
            'password',
            'password_confirmation',
        ]);

        $this->dispatch('toast', type: 'success', message: 'Password updated successfully!');
    }

    public function render()
    {
        return view('livewire.teacher.profile.setting-component')
            ->with('user', $this->user)
            ->layout('layouts.teacher.app', [
                'title' => 'Profile Setting | ' . institution()->name,
            ]);
    }
}