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

    public string $activeTab = 'general';

    // General
    public string $name         = '';
    public ?string $eiin         = null;
    public ?string $email       = null;
    public ?string $phone       = null;
    public ?string $city        = null;
    public ?string $address     = null;
    public array $weekends      = [];
    public string $unique_roll  = 'class_wise';
    public ?string $academic_year   = null;

    // Registration
    public bool $enable_registration_prefix = false;
    public ?string $registration_code_prefix = null;
    public int $registration_start_from         = 1;
    public int $registration_digit_length           = 4;

    // Student ID
    public bool $enable_student_id_prefix = false;
    public ?string $student_id_code_prefix = null;
    public int $student_id_start_from         = 1;
    public int $student_id_digit_length           = 6;

    // Employee ID
    public bool $enable_employee_id_prefix = false;
    public ?string $employee_id_code_prefix = null;
    public int $employee_id_start_from         = 1;
    public int $employee_id_digit_length           = 6;

    // Fees
    public int $due_days                          = 30;
    public bool $due_fees_calculation_with_fine   = false;

    // Facilities (key => bool)
    public array $facilities = [];

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

    /**
     * Master list of available facilities: key => [label, icon].
     * Adding a new facility only requires adding an entry here (Open/Closed Principle).
     * Icons use Bootstrap Icons (bi-*), already used across the project.
     */
    public static function facilityDefinitions(): array
    {
        return [
            'library'       => ['label' => 'Library',        'icon' => 'bi-book'],
            'science_lab'   => ['label' => 'Science Lab',    'icon' => 'bi-flask'],
            'computer_lab'  => ['label' => 'Computer Lab',   'icon' => 'bi-pc-display'],
            'playground'    => ['label' => 'Playground',     'icon' => 'bi-tree'],
            'transport'     => ['label' => 'Transport',      'icon' => 'bi-bus-front'],
            'hostel'        => ['label' => 'Hostel',         'icon' => 'bi-house-door'],
            'canteen'       => ['label' => 'Canteen',        'icon' => 'bi-cup-hot'],
        ];
    }

    /**
     * Default facilities array (all disabled) — used when institution has no saved value yet.
     */
    protected function defaultFacilities(): array
    {
        return array_fill_keys(array_keys(self::facilityDefinitions()), false);
    }

    protected function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'eiin'              => 'nullable|string|max:255',
            'email'             => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:30',
            'city'              => 'nullable|string|max:100',
            'address'           => 'nullable|string',
            'weekends'          => 'nullable|array',
            'weekends.*'        => 'string|in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'unique_roll'       => 'required|in:class_wise,section_wise,disabled',
            'academic_year'     => 'nullable|string|max:20',

            'enable_registration_prefix' => 'boolean',
            'registration_code_prefix'    => 'nullable|string|max:50',
            'registration_start_from'        => 'required|integer|min:1',
            'registration_digit_length'          => 'required|integer|min:1|max:10',

            'enable_student_id_prefix' => 'boolean',
            'student_id_code_prefix'    => 'nullable|string|max:50',
            'student_id_start_from'        => 'required|integer|min:1',
            'student_id_digit_length'          => 'required|integer|min:1|max:10',

            'enable_employee_id_prefix' => 'boolean',
            'employee_id_code_prefix'    => 'nullable|string|max:50',
            'employee_id_start_from'        => 'required|integer|min:1',
            'employee_id_digit_length'          => 'required|integer|min:1|max:10',

            'due_days'                         => 'required|integer|min:0',
            'due_fees_calculation_with_fine'   => 'boolean',

            'facilities'    => 'nullable|array',
            'facilities.*'  => 'boolean',

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
        $this->weekends                     = $setting->weekends        ?? [];
        $this->unique_roll                  = $setting->unique_roll     ?? 'class_wise';
        $this->academic_year                = $setting->academic_year;

        $this->enable_registration_prefix   = (bool) $setting->enable_registration_prefix;
        $this->registration_code_prefix      = $setting->registration_code_prefix;
        $this->registration_start_from          = (int) ($setting->registration_start_from ?? 1);
        $this->registration_digit_length            = (int) ($setting->registration_digit_length   ?? 4);

        $this->enable_student_id_prefix   = (bool) $setting->enable_student_id_prefix;
        $this->student_id_code_prefix      = $setting->student_id_code_prefix;
        $this->student_id_start_from          = (int) ($setting->student_id_start_from ?? 1);
        $this->student_id_digit_length            = (int) ($setting->student_id_digit_length   ?? 6);

        $this->enable_employee_id_prefix   = (bool) $setting->enable_employee_id_prefix;
        $this->employee_id_code_prefix      = $setting->employee_id_code_prefix;
        $this->employee_id_start_from          = (int) ($setting->employee_id_start_from ?? 1);
        $this->employee_id_digit_length            = (int) ($setting->employee_id_digit_length   ?? 6);

        $this->due_days                         = (int) ($setting->due_days ?? 30);
        $this->due_fees_calculation_with_fine   = (bool) $setting->due_fees_calculation_with_fine;

        // Merge saved facilities with default definitions so newly added
        // facility keys (added later in facilityDefinitions()) always appear,
        // even if the institution's saved JSON doesn't have them yet.
        $saved = $setting->facilities ?? [];
        $this->facilities = array_merge($this->defaultFacilities(), array_intersect_key(
            $saved,
            $this->defaultFacilities()
        ));

        // raw path সরাসরি property-তে রাখা হলো (storage/ prefix নেই)
        $this->system_logo  = $setting->system_logo;
        $this->text_logo    = $setting->text_logo;
        $this->print_logo   = $setting->print_logo;
        $this->report_logo  = $setting->report_logo;
    }

    /**
     * Toggle a single facility flag on/off. Called from the Facilities tab UI.
     */
    public function toggleFacility(string $key): void
    {
        if (! array_key_exists($key, self::facilityDefinitions())) {
            return; // ignore unknown keys — protects against tampered wire:click payloads
        }

        $this->facilities[$key] = ! ($this->facilities[$key] ?? false);
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
            'weekends'                       => $this->weekends,
            'unique_roll'                    => $this->unique_roll,
            'academic_year'                  => $this->academic_year,

            'enable_registration_prefix'     => $this->enable_registration_prefix,
            'registration_code_prefix'        => $this->registration_code_prefix,
            'registration_start_from'            => $this->registration_start_from,
            'registration_digit_length'              => $this->registration_digit_length,

            'enable_student_id_prefix'       => $this->enable_student_id_prefix,
            'student_id_code_prefix'          => $this->student_id_code_prefix,
            'student_id_start_from'              => $this->student_id_start_from,
            'student_id_digit_length'                => $this->student_id_digit_length,

            'enable_employee_id_prefix'      => $this->enable_employee_id_prefix,
            'employee_id_code_prefix'         => $this->employee_id_code_prefix,
            'employee_id_start_from'             => $this->employee_id_start_from,
            'employee_id_digit_length'               => $this->employee_id_digit_length,

            'due_days'                       => $this->due_days,
            'due_fees_calculation_with_fine' => $this->due_fees_calculation_with_fine,

            'facilities'                     => $this->facilities,
        ]);

        $setting->save();

        // Clear institution settings cache
        Cache::forget("institution_settings_{$setting->id}");

        // Reset upload fields after save
        $this->reset([
            'system_logo_upload', 'text_logo_upload',
            'print_logo_upload',  'report_logo_upload',
        ]);

        $this->dispatch('toast', type: 'success', message: 'Institution settings saved successfully.');
    }

    public function render()
    {
        return view('livewire.admin.setting.institution-component', [
            'facilityDefinitions' => self::facilityDefinitions(),
        ])
            ->layout('layouts.admin.app', [
                'title' => 'Institution Settings | ' . institution()->name,
            ]);
    }
}