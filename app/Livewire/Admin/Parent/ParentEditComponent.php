<?php

namespace App\Livewire\Admin\Parent;

use Livewire\Component;
use App\Models\User;
use App\Models\Guardian;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class ParentEditComponent extends Component
{
    use WithFileUploads;

    public $guardianId;
    public $userId;
    public $guardian;

    public $name;
    public $relation;
    public $father_name;
    public $mother_name;
    public $occupation;
    public $income;
    public $education;
    public $mobile;
    public $email;
    public $address;

    public $photo;
    public $photo_upload;

    public $username;
    public $password;
    public $password_confirmation;

    public function mount($id)
    {
        $this->guardianId = $id;
        $this->guardian = Guardian::findOrFail($id);
        $this->userId = $this->guardian->user_id;

        $this->name = $this->guardian->name;
        $this->relation = $this->guardian->relation;
        $this->father_name = $this->guardian->father_name;
        $this->mother_name = $this->guardian->mother_name;
        $this->occupation = $this->guardian->occupation;
        $this->income = $this->guardian->income;
        $this->education = $this->guardian->education;
        $this->mobile = $this->guardian->mobile;
        $this->email = $this->guardian->email;
        $this->address = $this->guardian->address;

        $this->photo = $this->guardian->photo;

        $this->username = $this->guardian->user->username;
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'relation' => 'nullable|string|max:50',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:255',
            'income' => 'nullable|numeric',
            'education' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'nullable|email',

            'photo_upload'       => 'nullable',

            'username'    => ['required', Rule::unique('users', 'username')->ignore($this->userId)],
            'password'    => 'nullable|confirmed|min:4',
        ];
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
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

    public function update()
    {
        try {
            $this->validate($this->rules());

            $userData = [
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
            ];

            if (!empty($this->password)) {
                $userData['password'] = $this->password;
            }

            $user = User::with('guardian')->findOrFail($this->userId);
            $user->update($userData);

            $guardian = [
                'name'        => $this->name,
                'relation'    => $this->relation,
                'father_name' => $this->father_name,
                'mother_name' => $this->mother_name,
                'occupation'  => $this->occupation,
                'income'      => $this->income,
                'education'   => $this->education,
                'mobile'      => $this->mobile,
                'email'       => $this->email,
                'address'     => $this->address,
            ];

            if ($this->photo_upload) {
                $this->deleteOldFile($this->student->photo);
                $guardian['photo'] = \App\Helpers\TenantFileHelper::store($this->photo_upload, 'guardians');
            }

            $this->guardian->update($guardian);

            $this->dispatch('toast', type: 'success', message: 'Parent updated successfully!');
        } catch (\Exception $e) {
            $this->dispatch('validation-failed');
            $this->dispatch('toast', type: 'error', message: 'An error occurred while creating the parent.');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.admin.parent.parent-edit-component')
            ->layout('layouts.admin.app', [
                'title' => "Edit Parent | Monarchy School",
            ]);
    }
}
