<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\Institution;
use App\Models\User;
use App\Models\Invoice;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InstitutionRegistrationComponent extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;

    // Step 1 — Institution Information
    public string $institution_name = '';
    public string $institution_type = '';
    public string $institution_medium = '';
    public string $institution_division = '';
    public string $institution_district = '';
    public string $phone = '';
    public string $email = '';
    public $logo;
    public ?string $existing_logo_path = null;

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

        $this->institution_name     = $pending['institution_name'] ?? '';
        $this->institution_type     = $pending['institution_type'] ?? '';
        $this->institution_medium   = $pending['institution_medium'] ?? '';
        $this->institution_division = $pending['institution_division'] ?? '';
        $this->institution_district = $pending['institution_district'] ?? '';
        $this->email                = $pending['email'] ?? '';
        $this->phone                = $pending['phone'] ?? '';
        $this->admin_name           = $pending['admin_name'] ?? '';
        $this->admin_email          = $pending['admin_email'] ?? '';
        $this->password              = $pending['password'] ?? '1234';
        $this->password_confirmation = $pending['password'] ?? '1234';

        $this->existing_logo_path = session('pending_logo');

        $this->currentStep = 3;

        session()->flash('info', 'Ager fill kora tothyo restore kora hoyeche. Password ta abar diye continue korun.');
    }

    /**
     * Livewire computed property — re-evaluated automatically whenever
     * institution_division changes. Used in Blade as $this->districts.
     * Source of truth lives on the Institution model.
     */
    public function getDistrictsProperty(): array
    {
        return Institution::districtsFor($this->institution_division);
    }

    /**
     * Livewire lifecycle hook — fires automatically whenever
     * institution_division is updated via wire:model.live.
     * Resets the district selection so a stale district from a
     * previously selected division can never be submitted.
     */
    public function updatedInstitutionDivision(): void
    {
        $this->institution_district = '';
    }

    public function stepOneValidation(): void
    {
        $this->validate([
            'institution_name'     => 'required|min:3|max:255',
            'institution_type'     => 'required|string',
            'institution_medium'   => ['required', 'string', Rule::in(Institution::MEDIUMS)],
            'institution_division' => ['required', 'string', Rule::in(array_keys(Institution::DIVISIONS))],
            'institution_district' => ['required', 'string', Rule::in($this->districts)],
            'phone'                => 'required|string|max:30',
            'email'                => 'required|email|max:255',
            'logo'                 => 'nullable|image|max:2048',
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
                'institution_name'     => $this->institution_name,
                'institution_type'     => $this->institution_type,
                'institution_medium'   => $this->institution_medium,
                'institution_division' => $this->institution_division,
                'institution_district' => $this->institution_district,
                'email'       => $this->email,
                'phone'       => $this->phone,
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
                'medium'   => $this->institution_medium,
                'division' => $this->institution_division,
                'district' => $this->institution_district,
                'email'    => $this->email,
                'phone'    => $this->phone,
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
                'name'            => $this->admin_name,
                'email'           => $this->admin_email,
                'password'        => $this->password,
                'role'            => 'admin',
                'institution_id'  => $institution->id,
            ]);

            // 4. Create Invoice
            Invoice::create([
                'institution_id' => $institution->id,
                'type'           => 'registration',
                'invoice_no'     => 'REG_' . strtoupper(uniqid()),
                'total_amount'   => (float) setting('register_fee', 0),
                'status'         => 'free',
            ]);

            Auth::login($user);
            session()->regenerate();

        });

        return redirect()->route('admin.dashboard')->with('success', 'Institution setup complete!!');
    }

    public function render()
    {
        return view('livewire.frontend.institution-registration-component')
            ->layout('layouts.frontend.app', [
                'title' => 'Institution Setup | ' . setting('app_name', 'EMS'),
            ]);
    }
}