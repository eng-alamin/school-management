<?php

namespace App\Livewire\Admin\Biometric;

use App\Models\BiometricDevice;
use App\Models\BiometricDeviceUserMapping;
use App\Models\Employee;
use App\Models\Student;
use App\Services\BiometricCommandService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin.app')]
class BiometricDeviceUserMappingComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['device_user_id', 'card_number', 'attendable_type', 'created_at'];

    protected array $sortColumnMap = [
        'device_user_id' => 'biometric_device_user_mappings.device_user_id',
        'attendable_type' => 'biometric_device_user_mappings.attendable_type',
        'created_at' => 'biometric_device_user_mappings.created_at',
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // ---------- Device selection ----------
    public ?int $selectedDeviceId = null;

    // ---------- Filters ----------
    public string $search = '';
    public int $perPage = 10;

    // ---------- Add/Edit Mapping Form ----------
    public bool $showFormModal = false;
    public bool $isEditMode = false;
    public ?int $editingMappingId = null;

    public string $device_user_id = '';
    public string $card_number = '';
    public string $attendable_type = 'student'; // 'student' | 'employee'
    public string $personSearch = '';
    public ?int $selectedPersonId = null;
    public array $personResults = [];

    // ---------- Delete ----------
    public ?int $deletingId = null;
    public bool $showDeleteModal = false;

    protected function attendableClass(): string
    {
        return $this->attendable_type === 'employee' ? Employee::class : Student::class;
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSelectedDeviceId(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPersonSearch(): void
    {
        $this->searchPeople();
    }

    public function updatedAttendableType(): void
    {
        // Edit মোডে Type বদলানো যাবে না (person mismatch এড়াতে)
        if ($this->isEditMode) {
            return;
        }

        $this->personSearch = '';
        $this->personResults = [];
        $this->selectedPersonId = null;
    }

    public function searchPeople(): void
    {
        if (mb_strlen($this->personSearch) < 2) {
            $this->personResults = [];
            return;
        }

        if ($this->attendable_type === 'employee') {
            $this->personResults = Employee::query()
                ->where('institution_id', institution()->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->personSearch}%")
                        ->orWhere('employee_id', 'like', "%{$this->personSearch}%");
                })
                ->with(['designation:id,name'])
                ->limit(10)
                ->get(['id', 'name', 'employee_id', 'designation_id'])
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'label' => $e->name . ' (' . $e->employee_id . ')' . ($e->designation ? ' - ' . $e->designation->name : ''),
                ])
                ->toArray();
        } else {
            $this->personResults = Student::query()
                ->where('institution_id', institution()->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->personSearch}%")
                        ->orWhere('student_id', 'like', "%{$this->personSearch}%")
                        ->orWhere('roll_no', 'like', "%{$this->personSearch}%");
                })
                ->with(['academicClass:id,name', 'academicSection:id,name'])
                ->limit(10)
                ->get(['id', 'name', 'student_id', 'roll_no', 'class_id', 'section_id'])
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'label' => $s->name . ' (' . $s->student_id . ')'
                        . ($s->academicClass ? ' - ' . $s->academicClass->name : '')
                        . ($s->academicSection ? ' ' . $s->academicSection->name : ''),
                ])
                ->toArray();
        }
    }

    public function selectPerson(int $id): void
    {
        // Edit মোডে person পরিবর্তনের সুযোগ দিচ্ছি না
        if ($this->isEditMode) {
            return;
        }

        $this->selectedPersonId = $id;
        $match = collect($this->personResults)->firstWhere('id', $id);
        $this->personSearch = $match['label'] ?? '';
        $this->personResults = [];
    }

    public function openCreateModal(): void
    {
        if (! $this->selectedDeviceId) {
            $this->dispatch('toast', type: 'error', message: 'আগে একটা Device সিলেক্ট করো।');
            return;
        }

        $this->resetForm();
        $this->isEditMode = false;
        $this->editingMappingId = null;
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $mapping = BiometricDeviceUserMapping::where('institution_id', institution()->id)
            ->where('biometric_device_id', $this->selectedDeviceId)
            ->with('attendable')
            ->findOrFail($id);

        $this->resetValidation();

        $this->isEditMode = true;
        $this->editingMappingId = $mapping->id;

        $this->device_user_id = $mapping->device_user_id;
        $this->card_number = $mapping->card_number ?? '';
        $this->attendable_type = str_contains($mapping->attendable_type, 'Employee') ? 'employee' : 'student';
        $this->selectedPersonId = $mapping->attendable_id;
        $this->personSearch = $mapping->attendable?->name
            . ' (' . ($this->attendable_type === 'employee' ? $mapping->attendable?->employee_id : $mapping->attendable?->student_id) . ')';
        $this->personResults = [];

        $this->showFormModal = true;
    }

    public function confirmDelete(int $id): void
    {
        BiometricDeviceUserMapping::where('institution_id', institution()->id)->findOrFail($id);

        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function save(): void
    {
        $device = BiometricDevice::where('institution_id', institution()->id)
            ->findOrFail($this->selectedDeviceId);

        $this->validate([
            'device_user_id' => [
                'required', 'string', 'max:50',
                Rule::unique('biometric_device_user_mappings', 'device_user_id')
                    ->where('biometric_device_id', $device->id)
                    ->whereNull('deleted_at')
                    ->when($this->isEditMode, fn ($rule) => $rule->ignore($this->editingMappingId)),
            ],

            'card_number' => [
                'nullable', 'digits_between:1,10',
                Rule::unique('biometric_device_user_mappings', 'card_number')
                    ->where('biometric_device_id', $device->id)
                    ->whereNotNull('card_number')
                    ->whereNot('card_number', '')
                    ->whereNull('deleted_at')
                    ->when($this->isEditMode, fn ($rule) => $rule->ignore($this->editingMappingId)),
            ],
            'attendable_type' => [Rule::in(['student', 'employee'])],
            'selectedPersonId' => ['required', 'integer'],
        ], [
            'selectedPersonId.required' => 'Student/Employee সিলেক্ট করা আবশ্যক।',
            'device_user_id.unique' => 'এই Device User ID ইতিমধ্যে এই Device-এ অন্য কারো সাথে যুক্ত আছে।',
            'card_number.unique' => 'এই Card Number ইতিমধ্যে এই Device-এ অন্য কারো সাথে যুক্ত আছে।',
            'card_number.digits_between' => 'Card Number শুধু সংখ্যা (numeric) হতে হবে, সর্বোচ্চ ১০ ডিজিট।',
        ]);

        $attendableClass = $this->attendableClass();

        $person = $attendableClass::where('institution_id', institution()->id)
            ->findOrFail($this->selectedPersonId);

        DB::beginTransaction();

        try {
            if ($this->isEditMode) {
                $mapping = BiometricDeviceUserMapping::where('institution_id', institution()->id)
                    ->where('biometric_device_id', $device->id)
                    ->findOrFail($this->editingMappingId);

                $oldDeviceUserId = $mapping->device_user_id;
                $deviceUserIdChanged = $oldDeviceUserId !== $this->device_user_id;

                $mapping->update([
                    'device_user_id' => $this->device_user_id,
                    'card_number' => $this->card_number !== '' ? $this->card_number : null,
                ]);

                if ($deviceUserIdChanged) {
                    // পুরোনো PIN ডিভাইস থেকে সরিয়ে নতুন PIN নতুন করে push করা হচ্ছে
                    BiometricCommandService::queueUserDelete($device, $oldDeviceUserId);
                }

                BiometricCommandService::queueUserUpsert(
                    $device,
                    $this->device_user_id,
                    $person->name,
                    $this->card_number !== '' ? $this->card_number : null
                );

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($mapping)
                    ->withProperties(['icon' => 'edit', 'type' => 'biometric_mapping_update'])
                    ->tap(function ($activity) use ($mapping) {
                        $activity->institution_id = $mapping->institution_id;
                    })
                    ->log("Device user mapping updated: {$device->device_name} -> {$person->name}");

                $successMessage = 'Mapping আপডেট হয়েছে, device-এ sync হওয়ার জন্য কিউতে রাখা হয়েছে।';
            } else {
                $mapping = BiometricDeviceUserMapping::create([
                    'institution_id' => institution()->id,
                    'biometric_device_id' => $device->id,
                    'device_user_id' => $this->device_user_id,
                    'card_number' => $this->card_number !== '' ? $this->card_number : null,
                    'attendable_type' => $attendableClass,
                    'attendable_id' => $person->id,
                ]);

                BiometricCommandService::queueUserUpsert(
                    $device,
                    $this->device_user_id,
                    $person->name,
                    $this->card_number !== '' ? $this->card_number : null
                );

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($mapping)
                    ->withProperties(['icon' => 'add', 'type' => 'biometric_mapping_create'])
                    ->tap(function ($activity) use ($mapping) {
                        $activity->institution_id = $mapping->institution_id;
                    })
                    ->log("Device user mapping created: {$device->device_name} -> {$person->name}");

                $successMessage = 'Mapping যোগ হয়েছে, device-এ push হওয়ার জন্য কিউতে রাখা হয়েছে।';
            }

            DB::commit();

            $this->showFormModal = false;
            $this->resetForm();

            $this->dispatch('toast', type: 'success', message: $successMessage);

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        DB::beginTransaction();

        try {
            $mapping = BiometricDeviceUserMapping::where('institution_id', institution()->id)
                ->findOrFail($this->deletingId);

            $device = BiometricDevice::find($mapping->biometric_device_id);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($mapping)
                ->withProperties(['icon' => 'delete', 'type' => 'biometric_mapping_delete'])
                ->tap(function ($activity) use ($mapping) {
                    $activity->institution_id = $mapping->institution_id;
                })
                ->log('Device user mapping deleted: device_user_id ' . $mapping->device_user_id);

            $deviceUserId = $mapping->device_user_id;

            $mapping->delete();

            // Device থেকেও user delete করার command queue করা
            if ($device) {
                BiometricCommandService::queueUserDelete($device, $deviceUserId);
            }

            DB::commit();

            $this->showDeleteModal = false;
            $this->deletingId = null;

            $this->dispatch('toast', type: 'success', message: 'Mapping ডিলিট হয়েছে, device থেকেও সরানোর জন্য কিউতে রাখা হয়েছে।');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function resetForm(): void
    {
        $this->reset(['device_user_id', 'card_number', 'personSearch', 'selectedPersonId', 'personResults', 'isEditMode', 'editingMappingId']);
        $this->attendable_type = 'student';
        $this->resetValidation();
    }

    public function render()
    {
        $devices = BiometricDevice::where('institution_id', institution()->id)
            ->where('is_active', true)
            ->orderBy('device_name')
            ->get(['id', 'device_name', 'device_serial']);

        $mappings = collect();

        if ($this->selectedDeviceId) {
            // ownership verify
            BiometricDevice::where('institution_id', institution()->id)->findOrFail($this->selectedDeviceId);

            $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'biometric_device_user_mappings.created_at';

            $mappings = BiometricDeviceUserMapping::query()
                ->where('institution_id', institution()->id)
                ->where('biometric_device_id', $this->selectedDeviceId)
                ->with('attendable')
                ->when($this->search !== '', function ($q) {
                    $q->where('device_user_id', 'like', "%{$this->search}%")
                        ->orWhere('card_number', 'like', "%{$this->search}%");
                })
                ->orderBy($sortColumn, $this->sortDirection)
                ->paginate($this->perPage);
        }

        return view('livewire.admin.biometric.biometric-device-user-mapping-component', [
            'devices' => $devices,
            'mappings' => $mappings,
        ]);
    }
}