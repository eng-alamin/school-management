<?php

namespace App\Livewire\Tenant\Teacher\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class SettingComponent extends Component
{
    use WithFileUploads;

    public $user;

    // Profile Details
    public $name;
    public $email;
    public $phone;
    public $avatar;
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
        $this->phone = $this->user->phone;
        $this->avatar = $this->user->avatar;
    }

    public function safePreviewUrl($upload): ?string
    {
        if (!$upload) return null;
        try {
            return $upload->temporaryUrl();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function deleteOldFile($path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = public_path($path);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    public function updateProfile()
    {
        $this->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email,' . $this->user->id,
            'phone'     => 'nullable|string|max:20',
            'newAvatar'  => 'nullable',
        ]);

        $data = [
            'name'   => $this->name,
            'email'  => $this->email,
            'phone' => $this->phone,
        ];

        if ($this->newAvatar) {
            $this->deleteOldFile($this->user->avatar);
            $data['avatar'] = \App\Helpers\TenantFileHelper::store($this->newAvatar, 'avatars');
        }

        $this->user->update($data);

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
        return view('livewire.tenant.teacher.profile.setting-component')
            ->with('user', $this->user)
            ->layout('layouts.teacher.app', [
                'title' => "Profile Setting | School SaaS",
            ]);
    }
}