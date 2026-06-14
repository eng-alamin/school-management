<?php

namespace App\Livewire\Admin\Setting;

use App\Models\School;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SchoolComponent extends Component
{
    use WithFileUploads;

    // Single-tenant: always id = 1
    protected const SCHOOL_ID = 1;

    // General
    public string $name         = '';
    public ?string $email       = null;
    public ?string $phone       = null;
    public ?string $city        = null;
    public ?string $address     = null;
    public string $language     = 'en';
    public string $timezone     = 'Asia/Dhaka';
    public array $weekends      = [];
    public string $unique_roll  = 'class_wise';
    public bool $teacher_restricted = false;
    public ?string $academic_year   = null;

    // Currency
    public string $currency         = 'BDT';
    public string $currency_symbol  = '৳';
    public string $currency_format  = '1,00,000.00';
    public string $symbol_position  = 'prefix';

    // Registration
    public bool $enable_registration_prefix = false;
    public ?string $institution_code_prefix = null;
    public int $register_start_from         = 1;
    public int $register_no_digit           = 4;

    // Fees
    public bool $offline_payment_enabled          = true;
    public int $due_days                          = 30;
    public bool $due_fees_calculation_with_fine   = false;

    // Auto Login
    public bool $auto_generate_student_login  = false;
    public bool $auto_generate_guardian_login = false;

    // Logo paths (stored)
    public ?string $system_logo = null;
    public ?string $text_logo   = null;
    public ?string $print_logo  = null;
    public ?string $report_logo = null;

    // Temp uploads
    public $system_logo_upload = null;
    public $text_logo_upload   = null;
    public $print_logo_upload  = null;
    public $report_logo_upload = null;

    protected function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:30',
            'city'              => 'nullable|string|max:100',
            'address'           => 'nullable|string',
            'language'          => 'required|string|max:10',
            'timezone'          => 'required|string',
            'weekends'          => 'nullable|array',
            'weekends.*'        => 'string|in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'unique_roll'       => 'required|in:class_wise,section_wise,disabled',
            'teacher_restricted'=> 'boolean',
            'academic_year'     => 'nullable|string|max:20',

            'currency'          => 'required|string|max:20',
            'currency_symbol'   => 'required|string|max:10',
            'currency_format'   => 'required|string',
            'symbol_position'   => 'required|in:prefix,suffix',

            'enable_registration_prefix' => 'boolean',
            'institution_code_prefix'    => 'nullable|string|max:50',
            'register_start_from'        => 'required|integer|min:1',
            'register_no_digit'          => 'required|integer|min:1|max:10',

            'offline_payment_enabled'          => 'boolean',
            'due_days'                         => 'required|integer|min:0',
            'due_fees_calculation_with_fine'   => 'boolean',

            'auto_generate_student_login'  => 'boolean',
            'auto_generate_guardian_login' => 'boolean',

            'system_logo_upload' => 'nullable|image|max:2048',
            'text_logo_upload'   => 'nullable|image|max:2048',
            'print_logo_upload'  => 'nullable|image|max:2048',
            'report_logo_upload' => 'nullable|image|max:2048',
        ];
    }

    public function mount()
    {
        $setting = School::withoutGlobalScope(\App\Models\Scopes\SchoolScope::class)->find(auth()->user()->school_id);

        if (! $setting) {
            return;
        }

        $this->name                         = $setting->name;
        $this->email                        = $setting->email;
        $this->phone                        = $setting->phone;
        $this->city                         = $setting->city;
        $this->address                      = $setting->address;
        $this->language                     = $setting->language        ?? 'en';
        $this->timezone                     = $setting->timezone        ?? 'Asia/Dhaka';
        $this->weekends                     = $setting->weekends        ?? [];
        $this->unique_roll                  = $setting->unique_roll     ?? 'class_wise';
        $this->teacher_restricted           = (bool) $setting->teacher_restricted;
        $this->academic_year                = $setting->academic_year;

        $this->currency                     = $setting->currency        ?? 'BDT';
        $this->currency_symbol              = $setting->currency_symbol ?? '৳';
        $this->currency_format              = $setting->currency_format ?? '1,00,000.00';
        $this->symbol_position              = $setting->symbol_position ?? 'prefix';

        $this->enable_registration_prefix   = (bool) $setting->enable_registration_prefix;
        $this->institution_code_prefix      = $setting->institution_code_prefix;
        $this->register_start_from          = (int) ($setting->register_start_from ?? 1);
        $this->register_no_digit            = (int) ($setting->register_no_digit   ?? 4);

        $this->offline_payment_enabled          = (bool) $setting->offline_payment_enabled;
        $this->due_days                         = (int) ($setting->due_days ?? 30);
        $this->due_fees_calculation_with_fine   = (bool) $setting->due_fees_calculation_with_fine;

        $this->auto_generate_student_login  = (bool) $setting->auto_generate_student_login;
        $this->auto_generate_guardian_login = (bool) $setting->auto_generate_guardian_login;

        $this->system_logo  = $setting->system_logo;
        $this->text_logo    = $setting->text_logo;
        $this->print_logo   = $setting->print_logo;
        $this->report_logo  = $setting->report_logo;
    }

    // ── Logo helpers ──────────────────────────────────────────────────────────

    public function safePreviewUrl($upload): ?string
    {
        if (! $upload) return null;
        try {
            return $upload->temporaryUrl();
        } catch (\Throwable) {
            return null;
        }
    }

    private function deleteOldLogo(?string $path): void
    {
        if (! $path) return;
        $relative = ltrim(str_replace('storage/', '', $path), '/');
        if (Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
        }
    }

    private function storeLogo($upload, string $prefix): string
    {
        $ext  = $upload->getClientOriginalExtension();
        $path = $upload->storeAs('logos', time() . "_{$prefix}.{$ext}", 'public');
        return 'storage/' . $path;
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate();

        $setting = School::withoutGlobalScope(\App\Models\Scopes\SchoolScope::class)->find(auth()->user()->school_id);

        // Process logo uploads
        foreach ([
            'system_logo'  => 'system',
            'text_logo'    => 'text',
            'print_logo'   => 'print',
            'report_logo'  => 'report',
        ] as $field => $prefix) {
            $uploadProp = "{$field}_upload";
            if ($this->{$uploadProp}) {
                $this->deleteOldLogo($setting->{$field});
                $setting->{$field} = $this->storeLogo($this->{$uploadProp}, $prefix);
                $this->{$field}    = $setting->{$field};
            }
        }

        $setting->fill([
            'name'                           => $this->name,
            'email'                          => $this->email,
            'phone'                          => $this->phone,
            'city'                           => $this->city,
            'address'                        => $this->address,
            'language'                       => $this->language,
            'timezone'                       => $this->timezone,
            'weekends'                       => $this->weekends,
            'unique_roll'                    => $this->unique_roll,
            'teacher_restricted'             => $this->teacher_restricted,
            'academic_year'                  => $this->academic_year,

            'currency'                       => $this->currency,
            'currency_symbol'                => $this->currency_symbol,
            'currency_format'                => $this->currency_format,
            'symbol_position'                => $this->symbol_position,

            'enable_registration_prefix'     => $this->enable_registration_prefix,
            'institution_code_prefix'        => $this->institution_code_prefix,
            'register_start_from'            => $this->register_start_from,
            'register_no_digit'              => $this->register_no_digit,

            'offline_payment_enabled'        => $this->offline_payment_enabled,
            'due_days'                       => $this->due_days,
            'due_fees_calculation_with_fine' => $this->due_fees_calculation_with_fine,

            'auto_generate_student_login'    => $this->auto_generate_student_login,
            'auto_generate_guardian_login'   => $this->auto_generate_guardian_login,
        ]);

        $setting->save();

        // Clear school settings cache
        Cache::forget('school_settings');

        // Reset upload fields after save
        $this->reset([
            'system_logo_upload', 'text_logo_upload',
            'print_logo_upload',  'report_logo_upload',
        ]);

        $this->dispatch('toast', type: 'success', message: 'School settings saved successfully.');
    }

    public function render()
    {
        return view('livewire.admin.setting.school-component')
            ->layout('layouts.admin.app', [
                'title' => 'School Settings',
            ]);
    }
}
