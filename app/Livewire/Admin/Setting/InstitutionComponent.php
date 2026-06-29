<?php

namespace App\Livewire\Admin\Setting;

use App\Models\Institution;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class InstitutionComponent extends Component
{
    use WithFileUploads;

    // Single-tenant: always id = 1
    protected const INSTITUTION_ID = 1;

    // General
    public string $name         = '';
    public ?string $eiin         = null;
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

    // Registration
    public bool $enable_registration_prefix = false;
    public ?string $institution_code_prefix = null;
    public int $register_start_from         = 1;
    public int $register_no_digit           = 4;

    // Fees
    public bool $offline_payment_enabled          = true;
    public int $due_days                          = 30;
    public bool $due_fees_calculation_with_fine   = false;

    // Online Exam ✅ (declare kora hoyni age, missing chilo)
    public bool $show_only_own_question = false;

    // Auto Login
    public bool $auto_generate_student_login  = false;
    public bool $auto_generate_guardian_login = false;

    // Logo paths (stored — raw relative path, e.g. "institutions/logos/xxx.webp")
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
            'eiin'              => 'nullable|string|max:255',
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

            'enable_registration_prefix' => 'boolean',
            'institution_code_prefix'    => 'nullable|string|max:50',
            'register_start_from'        => 'required|integer|min:1',
            'register_no_digit'          => 'required|integer|min:1|max:10',

            'offline_payment_enabled'          => 'boolean',
            'due_days'                         => 'required|integer|min:0',
            'due_fees_calculation_with_fine'   => 'boolean',

            'show_only_own_question' => 'boolean',

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
        $setting = Institution::withoutGlobalScope(\App\Models\Scopes\InstitutionScope::class)->find(auth()->user()->institution_id);

        if (! $setting) {
            return;
        }

        $this->name                         = $setting->name;
        $this->eiin                         = $setting->eiin;
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

        $this->enable_registration_prefix   = (bool) $setting->enable_registration_prefix;
        $this->institution_code_prefix      = $setting->institution_code_prefix;
        $this->register_start_from          = (int) ($setting->register_start_from ?? 1);
        $this->register_no_digit            = (int) ($setting->register_no_digit   ?? 4);

        $this->offline_payment_enabled          = (bool) $setting->offline_payment_enabled;
        $this->due_days                         = (int) ($setting->due_days ?? 30);
        $this->due_fees_calculation_with_fine   = (bool) $setting->due_fees_calculation_with_fine;

        $this->show_only_own_question = (bool) ($setting->show_only_own_question ?? false);

        $this->auto_generate_student_login  = (bool) $setting->auto_generate_student_login;
        $this->auto_generate_guardian_login = (bool) $setting->auto_generate_guardian_login;

        // raw path সরাসরি property-তে রাখা হলো (storage/ prefix নেই)
        $this->system_logo  = $setting->system_logo;
        $this->text_logo    = $setting->text_logo;
        $this->print_logo   = $setting->print_logo;
        $this->report_logo  = $setting->report_logo;
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save(): void
    {
        $this->validate();

        $setting = Institution::withoutGlobalScope(\App\Models\Scopes\InstitutionScope::class)->find(auth()->user()->institution_id);

        // চারটা logo field-এর জন্য একই pattern follow করা হলো
        foreach ([
            'system_logo' => $this->system_logo_upload,
            'text_logo'   => $this->text_logo_upload,
            'print_logo'  => $this->print_logo_upload,
            'report_logo' => $this->report_logo_upload,
        ] as $field => $upload) {

            $logoPath = $setting->{$field};   // আগের logo path

            if ($upload) {
                if ($logoPath) {
                    Storage::disk('public')->delete($logoPath);
                }
                $logoPath = $upload->store('institutions/logos', 'public');
            }

            $setting->{$field} = $logoPath;
            $this->{$field}    = $logoPath;
        }

        $setting->fill([
            'name'                           => $this->name,
            'eiin'                           => $this->eiin,
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

            'enable_registration_prefix'     => $this->enable_registration_prefix,
            'institution_code_prefix'        => $this->institution_code_prefix,
            'register_start_from'            => $this->register_start_from,
            'register_no_digit'              => $this->register_no_digit,

            'offline_payment_enabled'        => $this->offline_payment_enabled,
            'due_days'                       => $this->due_days,
            'due_fees_calculation_with_fine' => $this->due_fees_calculation_with_fine,

            // 'show_only_own_question'         => $this->show_only_own_question,

            'auto_generate_student_login'    => $this->auto_generate_student_login,
            'auto_generate_guardian_login'   => $this->auto_generate_guardian_login,
        ]);

        $setting->save();

        // Clear institution settings cache
        Cache::forget('institution_settings');

        // Reset upload fields after save
        $this->reset([
            'system_logo_upload', 'text_logo_upload',
            'print_logo_upload',  'report_logo_upload',
        ]);

        $this->redirect(request()->header('Referer'));
        $this->dispatch('toast', type: 'success', message: 'Institution settings saved successfully.');
    }

    public function render()
    {
        return view('livewire.admin.setting.institution-component')
            ->layout('layouts.admin.app', [
                'title' => 'Institution Settings | ' . institution()->name,
            ]);
    }
}