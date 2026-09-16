<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use App\Models\Institution;
use App\Models\User;
use App\Models\Invoice;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\InstitutionDefaultsService;
use Spatie\Permission\PermissionRegistrar;

class InstitutionRegistrationComponent extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;

    // Step 1 — Institution Information
    public string $institution_name = '';
    public string $institution_eiin = '';
    public string $institution_type = '';
    public string $institution_medium = '';
    public string $institution_division = '';
    public string $institution_district = '';
    public string $phone = '';
    public string $email = '';
    public $is_branch = false;
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
        $this->institution_eiin     = $pending['institution_eiin'] ?? '';
        $this->institution_type     = $pending['institution_type'] ?? '';
        $this->institution_medium   = $pending['institution_medium'] ?? '';
        $this->institution_division = $pending['institution_division'] ?? '';
        $this->institution_district = $pending['institution_district'] ?? '';
        $this->email                = $pending['email'] ?? '';
        $this->phone                = $pending['phone'] ?? '';
        // ✅ Fix: is_branch আগে restore হতো না — payment flow থেকে ফিরে এলে
        // এই মান হারিয়ে যেত এবং সবসময় default false ধরা হতো।
        $this->is_branch            = $pending['is_branch'] ?? false;
        $this->admin_name           = $pending['admin_name'] ?? '';
        $this->admin_email          = $pending['admin_email'] ?? '';

        if (!empty($pending['password'])) {
            try {
                $this->password              = $pending['password'];
                $this->password_confirmation = $this->password;
            } catch (\Throwable $e) {
                $this->password              = '';
                $this->password_confirmation = '';
            }
        }

        $this->existing_logo_path = session('pending_logo');

        $this->currentStep = 3;

        session()->flash('info', 'Previously filled-in information has been restored. If necessary, re-enter your password and continue.');
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
            'institution_eiin'     => 'required|min:6|max:255',
            'institution_type'     => 'required|string',
            'institution_medium'   => ['required', 'string', Rule::in(Institution::MEDIUMS)],
            'institution_division' => ['required', 'string', Rule::in(array_keys(Institution::DIVISIONS))],
            'institution_district' => ['required', 'string', Rule::in($this->districts)],
            'phone'                => 'required|string|max:30',
            'email'                => 'required|email|max:255',
            'is_branch'            => ['required', Rule::in(['0', '1', 0, 1, true, false])],
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
        $this->stepOneValidation();
        $this->stepTwoValidation();

        $logoPath = $this->logo
            ? $this->logo->store('institution/system/logo', 'public')
            : $this->existing_logo_path;

        session([
            'pending_registration' => [
                'institution_name'     => $this->institution_name,
                'institution_eiin'     => $this->institution_eiin,
                'institution_type'     => $this->institution_type,
                'institution_medium'   => $this->institution_medium,
                'institution_division' => $this->institution_division,
                'institution_district' => $this->institution_district,
                'email'       => $this->email,
                'phone'       => $this->phone,
                'is_branch'   => (bool) $this->is_branch,
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
        $this->stepOneValidation();
        $this->stepTwoValidation();

        try {
            $institution = null;

            DB::transaction(function () use (&$institution) {
                $institution = Institution::create([
                    'name'      => $this->institution_name,
                    'eiin'      => $this->institution_eiin,
                    'type'      => $this->institution_type,
                    'medium'    => $this->institution_medium,
                    'division'  => $this->institution_division,
                    'district'  => $this->institution_district,
                    'email'     => $this->email,
                    'phone'     => $this->phone,
                    'status'    => true,
                    'is_branch' => (bool) $this->is_branch,
                ]);

                $branch = InstitutionDefaultsService::create($institution);

                $user = User::create([
                    'name'           => $this->admin_name,
                    'email'          => $this->admin_email,
                    'password'       => $this->password,
                    'role'           => User::ROLE_ADMIN,
                    'institution_id' => $institution->id,
                    'branch_id'      => $branch->id,
                ]);

                app(PermissionRegistrar::class)->setPermissionsTeamId($institution->id);
                $user->assignRole('admin');

                Invoice::create([
                    'institution_id' => $institution->id,
                    'branch_id'      => $branch->id,
                    'type'           => 'registration',
                    'invoice_no'     => 'REG_' . strtoupper(Str::random(12)),
                    'total_amount'   => (float) setting('register_fee', 0),
                    'status'         => 'free',
                ]);

                Auth::login($user);
                session()->regenerate();
            });

            if ($this->logo) {
                $path = $this->logo->storeAs(
                    'logos',
                    time() . '_system.' . $this->logo->getClientOriginalExtension(),
                    'public'
                );
                $institution->update(['system_logo' => 'storage/' . $path]);
            } elseif ($this->existing_logo_path) {
                $institution->update(['system_logo' => 'storage/' . $this->existing_logo_path]);
            }

            session()->forget(['pending_registration', 'pending_logo']);

            return redirect()->route('admin.dashboard')->with('success', 'Institution setup complete!!');

        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'Registration could not be completed; please try again.');
            return back();
        }
    }

    public function render()
    {
        return view('livewire.frontend.institution-registration-component')
            ->layout('layouts.frontend.app', [
                'title' => 'Institution Setup | ' . setting('app_name', 'EMS'),
            ]);
    }
}