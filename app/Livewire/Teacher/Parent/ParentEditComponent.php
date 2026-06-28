<?php

namespace App\Livewire\Teacher\Parent;

use Livewire\Component;
use App\Models\User;
use App\Models\Guardian;
use Illuminate\Support\Facades\DB;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

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

    public function mount($id)
    {
        $this->guardianId = $id;
        $this->guardian   = Guardian::findOrFail($id);
        $this->userId     = $this->guardian->user_id;

        $this->name        = $this->guardian->name;
        $this->relation    = $this->guardian->relation;
        $this->father_name = $this->guardian->father_name;
        $this->mother_name = $this->guardian->mother_name;
        $this->occupation  = $this->guardian->occupation;
        $this->income      = $this->guardian->income;
        $this->education   = $this->guardian->education;
        $this->mobile      = $this->guardian->mobile;
        $this->email       = $this->guardian->email;
        $this->address     = $this->guardian->address;

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

            'photo_upload' => 'nullable|image|max:2048',

            'username'    => ['required', Rule::unique('users', 'username')->ignore($this->userId)],
            'password'    => 'nullable',
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

    public function update()
    {
        DB::beginTransaction();

        try {

            $this->validate($this->rules());

            // ── User update ──────────────────────────────
            $userData = [
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
            ];

            if (!empty($this->password)) {
                $userData['password'] = $this->password;
            }

            $user = User::findOrFail($this->userId);
            $user->update($userData);

            // ── Guardian update ──────────────────────────
            $guardianData = [
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

                $oldPhoto = $this->guardian->photo;

                $guardianData['photo'] = $this->photo_upload->store('guardians', 'public');

                if ($oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }

            $this->guardian->update($guardianData);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Parent updated successfully!');

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.teacher.parent.parent-edit-component')
            ->layout('layouts.teacher.app', [
                'title' => 'Edit Parent | ' . institution()->name,
            ]);
    }
}