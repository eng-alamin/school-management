<?php

namespace App\Livewire\ITSupport\Biometric;

use App\Models\AcademicClass;
use App\Models\BiometricDevice;
use App\Models\BiometricDeviceUserMapping;
use App\Models\Employee;
use App\Models\Student;
use App\Services\BiometricCommandService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;


class CreateUserMappingComponent extends Component
{
    // Device is now chosen from the Filter Ground itself (in addition to
    // arriving via URL for shareable/bookmarkable links from the list page).
    // #[Url(as: 'device_id')]
    public ?int $deviceId = null;

    public ?BiometricDevice $device = null;

    // ── Filter Ground: Device / Type / Class / Section ──────────────────────
    public string $attendable_type = 'student'; // 'student' | 'employee'

    // Class/Section only apply to Student (Employee has neither).
    public string $filterClassId   = '';
    public string $filterSectionId = '';
    public bool   $filterClassHasSection = true;

    // Employee has no class/section, so it keeps a plain name search instead.
    public string $employeeSearch = '';

    // ── Filter results table (bulk mapping) ─────────────────────────────────
    // Each row: id, name, idNo, extra, device_user_id (text, required), card_number (input)
    public array $people      = [];
    public bool  $hasResults  = false;

    public array $selectedStudents = [];
    public bool  $selectAll        = false;

    public string $routePrefix = '';

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();

        // if ($this->deviceId) {
        //     $this->loadDevice($this->deviceId);
        // }
    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
    }

    /**
     * Scoped device lookup — an unowned/forged device_id silently clears
     * back to "no device selected" instead of leaking another institution's
     * device or 404-ing the whole page.
     */
    protected function loadDevice(?int $id): void
    {
        if (!$id) {
            $this->device = null;
            $this->deviceId = null;
            return;
        }

        $this->device = BiometricDevice::where('institution_id', institution()->id)
            ->find($id);

        $this->deviceId = $this->device?->id;
    }

    public function updatedDeviceId($value): void
    {
        $this->loadDevice($value ? (int) $value : null);
        $this->resetFilterGround();
    }

    protected function attendableClass(): string
    {
        return $this->attendable_type === 'employee' ? Employee::class : Student::class;
    }

    public function updatedAttendableType(): void
    {
        $this->resetFilterGround();
    }

    public function updatedFilterClassId(): void
    {
        $this->filterSectionId = '';
        $this->filterClassHasSection = $this->resolveClassHasSection($this->filterClassId);
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedStudents = $value ? array_column($this->people, 'id') : [];
    }

    /**
     * Resolves academic_classes.has_section for a given class id, scoped to
     * the current institution. Defaults to true (section required) when the
     * class can't be found, to avoid silently dropping a section requirement.
     */
    private function resolveClassHasSection(?string $classId): bool
    {
        if (!$classId) {
            return true;
        }

        $class = AcademicClass::where('institution_id', institution()->id)->find($classId);

        return $class ? (bool) $class->has_section : true;
    }

    public function getAvailableDevices()
    {
        return BiometricDevice::where('institution_id', institution()->id)
            ->where('is_active', true)
            ->orderBy('device_name')
            ->get(['id', 'device_name', 'device_serial']);
    }

    public function getAvailableFilterClasses()
    {
        return AcademicClass::where('institution_id', institution()->id)
            ->orderBy('id')
            ->get(['id', 'name', 'has_section']);
    }

    /**
     * Returns the valid sections for a class per the static
     * academic_class_sections mapping. Returns an empty collection when the
     * class has has_section = false.
     */
    public function getAvailableFilterSections(?string $classId)
    {
        if (!$classId) {
            return collect();
        }

        $class = AcademicClass::with('sections')
            ->where('institution_id', institution()->id)
            ->find($classId);

        if (!$class || !$class->has_section) {
            return collect();
        }

        return $class->sections->sortBy('name')->values();
    }

    public function resetFilterGround(): void
    {
        $this->filterClassId = '';
        $this->filterSectionId = '';
        $this->filterClassHasSection = true;
        $this->employeeSearch = '';
        $this->people = [];
        $this->hasResults = false;
        $this->selectedStudents = [];
        $this->selectAll = false;
    }

    /**
     * Loads the filter-ground result table. For Student: Class is required
     * (Section optional). For Employee: an optional name/ID search.
     *
     * NOTE: The employee query filters `where('status', 'active')`. If this
     * ever returns an empty list unexpectedly, check the actual casing/value
     * stored in employees.status (e.g. 'Active' vs 'active') via tinker —
     * this was flagged as an open verification item.
     */
    public function filter(): void
    {
        if (!$this->device) {
            $this->dispatch('toast', type: 'error', message: 'প্রথমে একটা Device সিলেক্ট করো।');
            return;
        }

        $this->selectedStudents = [];
        $this->selectAll = false;

        if ($this->attendable_type === 'employee') {
            $employees = Employee::query()
                ->where('institution_id', institution()->id)
                ->where('status', 'active')
                ->when($this->employeeSearch !== '', function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('name', 'like', "%{$this->employeeSearch}%")
                            ->orWhere('employee_id', 'like', "%{$this->employeeSearch}%");
                    });
                })
                ->with(['designation:id,name'])
                ->orderBy('name')
                ->limit(100)
                ->get(['id', 'name', 'employee_id', 'designation_id']);

            // Existing mappings for these employees on THIS device, so
            // already-mapped people show their current device_user_id /
            // card_number pre-filled instead of blank.
            $existingMappings = BiometricDeviceUserMapping::where('biometric_device_id', $this->device->id)
                ->where('attendable_type', Employee::class)
                ->whereIn('attendable_id', $employees->pluck('id'))
                ->whereNull('deleted_at')
                ->get()
                ->keyBy('attendable_id');

            $this->people = $employees->map(fn ($e) => [
                'id'             => $e->id,
                'name'           => $e->name,
                'idNo'           => $e->employee_id,
                'extra'          => $e->designation?->name ?? '—',
                'device_user_id' => (string) ($existingMappings->get($e->id)?->device_user_id ?? ''),
                'card_number'    => (string) ($existingMappings->get($e->id)?->card_number ?? ''),
            ])->toArray();
        } else {
            $this->validate([
                'filterClassId' => 'required|exists:academic_classes,id',
                'filterSectionId' => [
                    Rule::requiredIf($this->filterClassHasSection),
                    'nullable',
                ],
            ], [], [
                'filterClassId' => 'Class',
                'filterSectionId' => 'Section',
            ]);

            $sectionId = ($this->filterClassHasSection && $this->filterSectionId && $this->filterSectionId !== 'all')
                ? $this->filterSectionId
                : null;

            $students = Student::where('institution_id', institution()->id)
                ->where('class_id', $this->filterClassId)
                ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
                ->with(['academicClass:id,name', 'academicSection:id,name'])
                ->orderBy('roll_no')
                ->get(['id', 'name', 'student_id', 'roll_no', 'class_id', 'section_id']);

            $existingMappings = BiometricDeviceUserMapping::where('biometric_device_id', $this->device->id)
                ->where('attendable_type', Student::class)
                ->whereIn('attendable_id', $students->pluck('id'))
                ->whereNull('deleted_at')
                ->get()
                ->keyBy('attendable_id');

            $this->people = $students->map(fn ($s) => [
                'id'             => $s->id,
                'name'           => $s->name,
                'idNo'           => $s->student_id,
                'extra'          => trim(($s->academicClass?->name ?? '') . ' ' . ($s->academicSection?->name ?? '')) ?: '—',
                'device_user_id' => (string) ($existingMappings->get($s->id)?->device_user_id ?? ''),
                'card_number'    => (string) ($existingMappings->get($s->id)?->card_number ?? ''),
            ])->toArray();

            // dd($this->people);
        }

        if (empty($this->people)) {
            $this->dispatch('toast', type: 'error', message: 'No matching student/employee found.');
        }

        $this->hasResults = true;
    }

    public function save()
    {
        if (!$this->device) {
            $this->dispatch('toast', type: 'error', message: 'Device সিলেক্ট করা নেই।');
            return;
        }

        if (empty($this->selectedStudents)) {
            $this->dispatch('toast', type: 'error', message: 'কমপক্ষে একজন Student/Employee সিলেক্ট করো!');
            return;
        }

        $peopleById = collect($this->people)->keyBy('id');
        $rows = [];
        $deviceUserIdsUsedInRequest = [];

        // ── Row-level validation before touching the DB ────────────────────
        foreach ($this->selectedStudents as $personId) {
            $row = $peopleById->get($personId);
            if (!$row) {
                continue;
            }

            $deviceUserId = trim((string) $row['device_user_id']);

            if ($deviceUserId === '') {
                $this->dispatch('toast', type: 'error', message: "{$row['name']} এর জন্য Device User ID দেওয়া হয়নি (আবশ্যক)।");
                return;
            }

            if (in_array($deviceUserId, $deviceUserIdsUsedInRequest, true)) {
                $this->dispatch('toast', type: 'error', message: "Device User ID {$deviceUserId} একাধিক row-এ ব্যবহার করা হয়েছে।");
                return;
            }

            if ($row['card_number'] !== '' && !ctype_digit((string) $row['card_number'])) {
                $this->dispatch('toast', type: 'error', message: "{$row['name']} এর Card Number শুধু সংখ্যা হতে হবে।");
                return;
            }

            $row['device_user_id'] = $deviceUserId;
            $deviceUserIdsUsedInRequest[] = $deviceUserId;
            $rows[] = $row;
        }

        $attendableClass = $this->attendableClass();

        DB::beginTransaction();

        $createdCount = 0;
        $updatedCount = 0;

        try {
            foreach ($rows as $row) {
                // Server-side re-verification of ownership (IDOR prevention) —
                // never trust the client-side row id alone.
                $person = $attendableClass::where('institution_id', institution()->id)
                    ->findOrFail($row['id']);

                // If this person already has a (non-deleted) mapping on this
                // device, treat this as an UPDATE/replace of that row rather
                // than a duplicate-conflict error.
                $existingForPerson = BiometricDeviceUserMapping::where('biometric_device_id', $this->device->id)
                    ->where('attendable_type', $attendableClass)
                    ->where('attendable_id', $person->id)
                    ->whereNull('deleted_at')
                    ->first();

                // DB-level uniqueness re-check (defense-in-depth against
                // races), excluding the person's own existing row so
                // re-saving/replacing the same person's mapping doesn't
                // false-positive against themselves.
                $deviceUserIdTaken = BiometricDeviceUserMapping::where('biometric_device_id', $this->device->id)
                    ->where('device_user_id', $row['device_user_id'])
                    ->whereNull('deleted_at')
                    ->when($existingForPerson, fn ($q) => $q->where('id', '!=', $existingForPerson->id))
                    ->exists();

                if ($deviceUserIdTaken) {
                    throw new \RuntimeException("Device User ID {$row['device_user_id']} ইতিমধ্যে এই Device-এ অন্য একজনের জন্য ব্যবহৃত হয়েছে।");
                }

                if ($row['card_number'] !== '') {
                    $cardTaken = BiometricDeviceUserMapping::where('biometric_device_id', $this->device->id)
                        ->where('card_number', $row['card_number'])
                        ->whereNull('deleted_at')
                        ->when($existingForPerson, fn ($q) => $q->where('id', '!=', $existingForPerson->id))
                        ->exists();

                    if ($cardTaken) {
                        throw new \RuntimeException("Card Number {$row['card_number']} ইতিমধ্যে এই Device-এ অন্য একজনের জন্য ব্যবহৃত হয়েছে।");
                    }
                }

                if ($existingForPerson) {
                    $existingForPerson->update([
                        'device_user_id' => $row['device_user_id'],
                        'card_number'    => $row['card_number'] !== '' ? $row['card_number'] : null,
                    ]);

                    $mapping = $existingForPerson;
                    $updatedCount++;
                    $activityMessage = "Device user mapping updated: {$this->device->device_name} -> {$person->name}";
                    $activityType = 'biometric_mapping_update';
                    $activityIcon = 'edit';
                } else {
                    $mapping = BiometricDeviceUserMapping::create([
                        'institution_id'       => institution()->id,
                        'biometric_device_id'  => $this->device->id,
                        'device_user_id'       => $row['device_user_id'],
                        'card_number'          => $row['card_number'] !== '' ? $row['card_number'] : null,
                        'attendable_type'      => $attendableClass,
                        'attendable_id'        => $person->id,
                    ]);

                    $createdCount++;
                    $activityMessage = "Device user mapping created: {$this->device->device_name} -> {$person->name}";
                    $activityType = 'biometric_mapping_create';
                    $activityIcon = 'add';
                }

                BiometricCommandService::queueUserUpsert(
                    $this->device,
                    $row['device_user_id'],
                    $person->name,
                    $row['card_number'] !== '' ? $row['card_number'] : null
                );

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($mapping)
                    ->withProperties(['icon' => $activityIcon, 'type' => $activityType])
                    ->tap(function ($activity) use ($mapping) {
                        $activity->institution_id = $mapping->institution_id;
                    })
                    ->log($activityMessage);
            }

            DB::commit();

            $parts = [];
            if ($createdCount) {
                $parts[] = "{$createdCount} টি নতুন Mapping যোগ হয়েছে";
            }
            if ($updatedCount) {
                $parts[] = "{$updatedCount} টি Mapping আপডেট (replace) হয়েছে";
            }

            session()->flash('toast_success', implode(', ', $parts) . ', device-এ push হওয়ার জন্য কিউতে রাখা হয়েছে।');

            $this->redirectRoute('admin.biometric.mapping.index', ['device_id' => $this->device->id]);

            return;

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('CreateUserMapping (bulk) failed: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: $e->getMessage() ?: 'Something went wrong!');
        }
    }

    public function render()
    {
        return view('livewire.admin.biometric.create-user-mapping-component', [
            'devices'         => $this->getAvailableDevices(),
            'filterClasses'   => $this->attendable_type === 'student' ? $this->getAvailableFilterClasses() : collect(),
            'filterSections'  => $this->attendable_type === 'student' ? $this->getAvailableFilterSections($this->filterClassId) : collect(),
        ])
        ->layout('layouts.itsupport.app', [
            'title' => 'User Mapping | ' . institution()->name,
        ]);
    }
}