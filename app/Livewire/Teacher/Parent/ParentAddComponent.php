<?php

namespace App\Livewire\Teacher\Parent;

use Livewire\Component;
use App\Models\User;
use App\Models\Guardian;
use Illuminate\Support\Facades\DB;

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

            // BUG FIX: users.email has a UNIQUE constraint. Without this rule,
            // typing an email that already belongs to another user threw a
            // raw, unhandled QueryException at save() time instead of a
            // friendly validation message.
            'email' => 'nullable|email|unique:users,email',

            'photo_upload'       => 'nullable|image|max:2048',

            'username' => 'required|unique:users,username',
            'password' => 'nullable',
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

    public function save()
    {
        DB::beginTransaction();

        try {

            $this->validate($this->rules());

            $institutionId = auth()->user()->institution_id;

            // ── Guardian identity resolve: Guardian.user_id is a GLOBAL login
            // (one real person can be a guardian for children in several
            // institutions — see App\Services\AdmissionService), so before
            // creating a brand new global User we check whether this same
            // mobile/email already belongs to a guardian somewhere (any
            // institution). If so, we reuse that login and only create a new
            // institution-scoped Guardian row for THIS institution, instead
            // of failing on the users.mobile/email unique constraint.
            $existingGuardian = Guardian::withoutGlobalScopes()
                ->where(function ($q) {
                    $q->where('mobile', $this->mobile);
                    if ($this->email) {
                        $q->orWhere('email', $this->email);
                    }
                })
                ->first();

            if ($existingGuardian) {

                // Already has a Guardian row in THIS institution — nothing to add.
                $alreadyHere = Guardian::withoutGlobalScopes()
                    ->where('institution_id', $institutionId)
                    ->where('user_id', $existingGuardian->user_id)
                    ->exists();

                if ($alreadyHere) {
                    DB::rollBack();
                    $this->addError('mobile', 'A parent with this mobile/email already exists in this institution.');
                    return;
                }

                Guardian::create([
                    'institution_id' => $institutionId,
                    'user_id'        => $existingGuardian->user_id,
                    'name'           => $this->name,
                    'relation'       => $this->relation,
                    'father_name'    => $this->father_name,
                    'mother_name'    => $this->mother_name,
                    'occupation'     => $this->occupation,
                    'income'         => $this->income,
                    'education'      => $this->education,
                    'mobile'         => $this->mobile,
                    'email'          => $this->email,
                    'address'        => $this->address,
                    'photo'          => $existingGuardian->photo,
                ]);

                DB::commit();

                $this->resetForm();

                $this->dispatch('toast', type: 'success', message: 'Existing parent linked to this institution!');

                return;
            }

            $userPassword = !empty($this->password)
                ? $this->password
                : '1234';

            // BUG FIX (critical): institution_id was completely missing here.
            // User is a GLOBAL model (no BelongsToInstitution trait), so it
            // is never auto-filled — every new parent login was being saved
            // with a null institution_id.
            $user = User::create([
                'institution_id' => $institutionId,
                'role'           => 'parent',
                'name'           => $this->name,
                'username'       => $this->username,
                'email'          => $this->email,
                'password'       => $userPassword,
                'is_verified'    => true,
            ]);

            // ── Upload Photo
            $photoPath = $this->photo_upload
                ? $this->photo_upload->store('guardians', 'public')
                : null;

            Guardian::create([
                'institution_id' => $institutionId,
                'user_id'     => $user->id,
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
                'photo'       => $photoPath,
            ]);

            DB::commit();

            $this->resetForm();

            $this->dispatch('toast', type: 'success', message: 'Parent created successfully!');

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.teacher.parent.parent-add-component')
            ->layout('layouts.teacher.app', [
                'title' => 'Create Parent | ' . institution()->name,
            ]);
    }
}