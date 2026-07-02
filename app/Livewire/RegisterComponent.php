<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Institution;
use App\Models\User;
use App\Models\Invoice;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RegisterComponent extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;

    // Step 1 — Institution Information
    public string $institution_name = '';
    public string $institution_type = '';
    public string $phone = '';
    public string $email = '';
    public string $timezone = 'Asia/Dhaka';
    public $logo;

    // Step 2 — Admin Account
    public string $admin_name = '';
    public string $admin_email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        $pending = session('pending_registration');
 
        if (!$pending) {
            return;
        }
 
        $this->institution_name = $pending['institution_name'] ?? '';
        $this->institution_type = $pending['institution_type'] ?? '';
        $this->email            = $pending['email'] ?? '';
        $this->phone            = $pending['phone'] ?? '';
        $this->timezone         = $pending['timezone'] ?? 'Asia/Dhaka';
        $this->admin_name       = $pending['admin_name'] ?? '';
        $this->admin_email      = $pending['admin_email'] ?? '';
        $this->password         = $pending['password'] ?? 1234;
        $this->password_confirmation         = $pending['password'] ?? 1234;
 
        $this->existing_logo_path = session('pending_logo');

        $this->currentStep = 3;
 
        session()->flash('info', 'Ager fill kora tothyo restore kora hoyeche. Password ta abar diye continue korun.');
    }

    protected function rules(): array
    {
        return [
            'institution_name' => 'required|min:3|max:255',
            'institution_type' => 'required|string',
            'phone'       => 'required|string|max:30',
            'email'       => 'required|email|max:255',
            'logo'        => 'nullable|image|max:2048',
            'admin_name'  => 'required|min:3|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'password'    => 'required|min:8|confirmed',
        ];
    }

    public function stepOneValidation(): void
    {
        $this->validate([
            'institution_name' => 'required|min:3|max:255',
            'institution_type' => 'required|string',
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

    public function nextStep(): void
    {
        match ($this->currentStep) {
            1 => $this->stepOneValidation(),
            2 => $this->stepTwoValidation(),
            3 => null,
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

    public function initiatePayment(): mixed
    {
        $this->stepTwoValidation();

        $logoPath = null;
        if ($this->logo) {
            $logoPath = $this->logo->store('institution/system/logo', 'public');
        }

        session([
            'pending_registration' => [
                'institution_name' => $this->institution_name,
                'institution_type' => $this->institution_type,
                'email'       => $this->email,
                'phone'       => $this->phone,
                'timezone'    => $this->timezone,
                'admin_name'  => $this->admin_name,
                'admin_email' => $this->admin_email,
                'password'    => $this->password,
                'tran_id'     => '',
            ],
            'pending_logo' => $logoPath,
        ]);

        return redirect()->route('registration.payment.pay');
    }

    public function initiateFree(): mixed
    {
        $this->stepTwoValidation();

        DB::transaction(function () {
            // 1. Create the institution record
            $institution = Institution::create([
                'name'     => $this->institution_name,
                'type'     => $this->institution_type,
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
                $institution->update(['system_logo' => 'storage/' . $path]);
            }

            // 3. Create the super-admin user
            $user = User::create([
                'name'      => $this->admin_name,
                'email'     => $this->admin_email,
                'password'  => $this->password,
                'role'      => 'admin',
                'institution_id' => $institution->id,
            ]);

            // 4. Create Invoice
            Invoice::create([
                'institution_id'      => $institution->id,
                'type'           => 'registration',
                'invoice_no'     => 'REG_' . strtoupper(uniqid()),
                'total_amount'     => number_format(setting('register_fee', 0), 0),
                'status'         => 'free',
            ]);

            Auth::login($user);
            session()->regenerate();

        });

        return redirect()->route('admin.dashboard')->with('success', 'Institution setup complete!!');
    }

    public function render()
    {
        return view('livewire.register-component')
            ->layout('layouts.app', [
                'title' => 'Institution Setup',
            ]);
    }
}