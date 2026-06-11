<?php

namespace App\Livewire;

use App\Models\School;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterComponent extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;

    /*
    |--------------------------------------------------------------------------
    | Step 1 — School Information
    |--------------------------------------------------------------------------
    */

    public string $school_name = '';
    public string $school_type = '';
    public string $phone = '';
    public string $email = '';
    public string $timezone = 'Asia/Dhaka';
    public $logo;

    /*
    |--------------------------------------------------------------------------
    | Step 2 — Admin Account
    |--------------------------------------------------------------------------
    */

    public string $admin_name = '';
    public string $admin_email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */

    protected function rules(): array
    {
        return [
            'school_name' => 'required|min:3|max:255',
            'school_type' => 'required|string',
            'phone'       => 'required|string|max:30',
            'email'       => 'required|email|max:255',
            'logo'        => 'nullable|image|max:2048',

            'admin_name'  => 'required|min:3|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'password'    => 'required|min:8|confirmed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Step Validation
    |--------------------------------------------------------------------------
    */

    public function stepOneValidation(): void
    {
        $this->validate([
            'school_name' => 'required|min:3|max:255',
            'school_type' => 'required|string',
            'phone'       => 'required|string|max:30',
            'email'       => 'required|email|max:255',
            'logo'        => 'nullable|image|max:2048',
        ]);
    }

    public function stepTwoValidation(): void
    {
        $this->validate([
            'admin_name'  => 'required|min:3|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'password'    => 'required|min:8|confirmed',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    public function nextStep(): void
    {
        match ($this->currentStep) {
            1 => $this->stepOneValidation(),
            2 => $this->stepTwoValidation(),
        };

        $this->currentStep++;
    }

    public function previousStep(): void
    {
        $this->currentStep--;
    }

    /*
    |--------------------------------------------------------------------------
    | Final Setup
    |--------------------------------------------------------------------------
    */

    public function register(): mixed
    {
        $this->stepTwoValidation();

        DB::transaction(function () {
            // 1. Create the school record
            $school = School::create([
                'name'     => $this->school_name,
                'email'    => $this->email,
                'phone'    => $this->phone,
                'timezone' => $this->timezone,
                'status'   => true,
            ]);

            // 2. Store logo if uploaded
            if ($this->logo) {
                $path = $this->logo->storeAs(
                    'logos',
                    time() . '_system.' . $this->logo->getClientOriginalExtension(),
                    'public'
                );
                $school->update(['system_logo' => 'storage/' . $path]);
            }

            // 3. Create the super-admin user
            User::create([
                'name'      => $this->admin_name,
                'email'     => $this->admin_email,
                'password'  => Hash::make($this->password),
                'role'      => 'admin',
                'school_id' => $school->id,
            ]);
        });

        session()->flash('success', 'School setup complete! You can now log in.');

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.register-component')
            ->layout('layouts.app', [
                'title' => 'School Setup',
            ]);
    }
}
