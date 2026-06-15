<?php

namespace App\Livewire\SuperAdmin\Settings;

use Livewire\Component;
use App\Models\Setting;

class SystemSettingsComponent extends Component
{
    public $app_name;
    public $logo;
    
    public $smtp_host;
    public $smtp_port;
    public $smtp_user;
    public $smtp_pass;

    public $sms_gateway;
    public $payment_gateway;

    public $maintenance_mode = false;

    public $feature_student = true;
    public $feature_teacher = true;
    public $feature_fee = true;

    public function mount()
    {
        $this->app_name = setting('app_name');
        $this->smtp_host = setting('smtp_host');
        $this->smtp_port = setting('smtp_port');
        $this->smtp_user = setting('smtp_user');
        $this->smtp_pass = setting('smtp_pass');

        $this->sms_gateway = setting('sms_gateway');
        $this->payment_gateway = setting('payment_gateway');

        $this->maintenance_mode = setting('maintenance_mode') == '1';

        $this->feature_student = setting('feature_student') == '1';
        $this->feature_teacher = setting('feature_teacher') == '1';
        $this->feature_fee = setting('feature_fee') == '1';
    }

    public function save()
    {
        Setting::updateOrCreate(['key' => 'app_name'], ['value' => $this->app_name]);

        Setting::updateOrCreate(['key' => 'smtp_host'], ['value' => $this->smtp_host]);
        Setting::updateOrCreate(['key' => 'smtp_port'], ['value' => $this->smtp_port]);
        Setting::updateOrCreate(['key' => 'smtp_user'], ['value' => $this->smtp_user]);
        Setting::updateOrCreate(['key' => 'smtp_pass'], ['value' => $this->smtp_pass]);

        Setting::updateOrCreate(['key' => 'sms_gateway'], ['value' => $this->sms_gateway]);
        Setting::updateOrCreate(['key' => 'payment_gateway'], ['value' => $this->payment_gateway]);

        Setting::updateOrCreate(['key' => 'maintenance_mode'], ['value' => $this->maintenance_mode ? 1 : 0]);

        Setting::updateOrCreate(['key' => 'feature_student'], ['value' => $this->feature_student ? 1 : 0]);
        Setting::updateOrCreate(['key' => 'feature_teacher'], ['value' => $this->feature_teacher ? 1 : 0]);
        Setting::updateOrCreate(['key' => 'feature_fee'], ['value' => $this->feature_fee ? 1 : 0]);

        session()->flash('success', 'Settings Updated Successfully!');
    }

    public function render()
    {
        return view('livewire.super-admin.settings.system-settings-component')
            ->layout('layouts.superadmin.app');
    }
}