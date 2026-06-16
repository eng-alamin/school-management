<?php

namespace App\Livewire\SuperAdmin\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemSettingsComponent extends Component
{
    use WithFileUploads;

    public string $activeTab = 'general';

    // App
    public ?string $app_name = null;
    public $logo = null;                  // new upload (TemporaryUploadedFile)
    public ?string $existing_logo = null;  // currently stored path

    // SMTP
    public ?string $smtp_host = null;
    public ?string $smtp_port = null;
    public ?string $smtp_user = null;
    public ?string $smtp_pass = null;

    // Gateway
    public ?string $sms_gateway = null;
    public ?string $payment_gateway = null;

    // Register
    public ?string $register_type = null;
    public ?string $register_fee = null;

    // Feature Toggle
    public bool $feature_student = true;
    public bool $feature_teacher = true;
    public bool $feature_fee = true;

    // Maintenance
    public bool $maintenance_mode = false;

    protected array $defaults = [
        'app_name'         => 'My App',
        'logo'              => null,
        'smtp_host'         => null,
        'smtp_port'         => null,
        'smtp_user'         => null,
        'smtp_pass'         => null,
        'sms_gateway'       => null,
        'payment_gateway'   => null,
        'register_type'     => 'free',
        'register_fee'      => 0,
        'feature_student'   => 1,
        'feature_teacher'   => 1,
        'feature_fee'       => 1,
        'maintenance_mode'  => 0,
    ];

    public function mount(): void
    {
        $this->app_name      = setting('app_name');
        $this->existing_logo = setting('logo');

        $this->smtp_host = setting('smtp_host');
        $this->smtp_port = setting('smtp_port');
        $this->smtp_user = setting('smtp_user');
        $this->smtp_pass = setting('smtp_pass');

        $this->sms_gateway     = setting('sms_gateway');
        $this->payment_gateway = setting('payment_gateway');

        $this->register_type = setting('register_type');
        $this->register_fee  = setting('register_fee');

        $this->feature_student = setting('feature_student') == '1';
        $this->feature_teacher = setting('feature_teacher') == '1';
        $this->feature_fee     = setting('feature_fee') == '1';

        $this->maintenance_mode = setting('maintenance_mode') == '1';
    }

    protected function rules(): array
    {
        return [
            'app_name'        => 'required|string|max:255',
            'logo'            => 'nullable|image|max:2048',
            'smtp_host'       => 'nullable|string|max:255',
            'smtp_port'       => 'nullable|numeric',
            'smtp_user'       => 'nullable|string|max:255',
            'smtp_pass'       => 'nullable|string|max:255',
            'sms_gateway'     => 'nullable|string|max:255',
            'payment_gateway' => 'nullable|string|max:255',
            'register_type'   => 'nullable|in:free,paid',
            'register_fee'    => 'nullable|numeric|min:0',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $values = [
            'app_name'         => $this->app_name,
            'smtp_host'        => $this->smtp_host,
            'smtp_port'        => $this->smtp_port,
            'smtp_user'        => $this->smtp_user,
            'smtp_pass'        => $this->smtp_pass,
            'sms_gateway'      => $this->sms_gateway,
            'payment_gateway'  => $this->payment_gateway,
            'register_type'    => $this->register_type,
            'register_fee'     => $this->register_fee,
            'maintenance_mode' => $this->maintenance_mode ? 1 : 0,
            'feature_student'  => $this->feature_student ? 1 : 0,
            'feature_teacher'  => $this->feature_teacher ? 1 : 0,
            'feature_fee'      => $this->feature_fee ? 1 : 0,
        ];

        // Only touch the logo if a new file was actually chosen
        if ($this->logo) {
            if ($this->existing_logo) {
                Storage::disk('public')->delete($this->existing_logo);
            }

            $path = $this->logo->store('logos', 'public');
            $values['logo']      = $path;
            $this->existing_logo = $path;
            $this->logo          = null;
        }

        foreach ($values as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::forget("setting_{$key}");
        }

        $this->dispatch('toast', type: 'success', message: 'Settings updated successfully!');
    }

    public function resetToDefault(): void
    {
        if ($this->existing_logo) {
            Storage::disk('public')->delete($this->existing_logo);
        }

        foreach ($this->defaults as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::forget("setting_{$key}");
        }

        $this->mount();

        $this->dispatch('toast', type: 'success', message: 'Settings reset to default!');
    }

    public function render()
    {
        return view('livewire.super-admin.settings.system-settings-component')
            ->layout('layouts.superadmin.app');
    }
}