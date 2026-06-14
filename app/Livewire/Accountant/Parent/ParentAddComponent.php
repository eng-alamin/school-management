<?php

namespace App\Livewire\Accountant\Parent;

use Livewire\Component;
use App\Models\User;
use App\Models\Guardian;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class ParentAddComponent extends Component
{
    use WithFileUploads;

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
    public $photo_upload;

    public $username;
    public $password;
    public $password_confirmation;

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

            'username' => 'required|unique:users,username',
            'password' => 'nullable|confirmed|min:4',
        ];
    }

    public function resetForm()
    {
        $this->reset();
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

    public function save()
    {
        try {
            $this->validate($this->rules());

            $userPassword = !empty($this->password)
                ? $this->password
                : '1234';

            $userData = [
                'role'     => 'parent',
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
                'password' => $userPassword,
            ];

            $user = User::create($userData);

            // Upload photo
            $photoPath = $this->photo_upload 
            ? \App\Helpers\TenantFileHelper::store($this->photo_upload, 'guardians') 
            : null;

            Guardian::create([
                'user_id'     => $user->id,
                'name' => $this->name,
                'relation' => $this->relation,
                'father_name' => $this->father_name,
                'mother_name' => $this->mother_name,
                'occupation' => $this->occupation,
                'income' => $this->income,
                'education' => $this->education,
                'mobile' => $this->mobile,
                'email' => $this->email,
                'address' => $this->address,
                'photo' => $photoPath,
            ]);

            $this->dispatch('toast', type: 'success', message: 'Parent created successfully!');
            $this->resetForm();
        } catch (\Exception $e) {
            $this->dispatch('validation-failed');
            $this->dispatch('toast', type: 'error', message: 'An error occurred while creating the parent.');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.accountant.parent.parent-add-component')
            ->layout('layouts.accountant.app', [
                'title' => "Create Parent | Monarchy School",
            ]);
    }

}
