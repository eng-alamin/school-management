<?php

namespace App\Livewire\SuperAdmin;

use App\Models\BiometricDevice;
use App\Models\BiometricDeviceUserMapping;
use App\Models\Employee;
use App\Models\Institution;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class BiometricDeviceUserMappingComponent extends Component
{
    use WithPagination;

    protected const SORTABLE_FIELDS = ['device_user_id', 'created_at'];

    public string $search = '';
    public ?int $filterInstitutionId = null;
    public ?int $filterDeviceId = null;
    public string $filterAttendableType = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    public ?int $institution_id = null; // selected in the Create/Edit form
    public ?int $biometric_device_id = null;
    public ?string $device_user_id = null;
    public string $attendable_type = 'student';
    public ?int $attendable_id = null;
    public string $personSearch = '';

    public bool $showDeleteModal = false;
    public ?int $deletingId = null;

    protected function rules(): array
    {
        return [
            'institution_id' => ['required', 'exists:institutions,id'],
            'biometric_device_id' => [
                'required',
                Rule::exists('biometric_devices', 'id')->where('institution_id', $this->institution_id),
            ],
            'device_user_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('biometric_device_user_mappings', 'device_user_id')
                    ->where('biometric_device_id', $this->biometric_device_id)
                    ->whereNull('deleted_at')
                    ->ignore($this->editingId),
            ],
            'attendable_type' => ['required', 'in:student,employee'],
            'attendable_id' => [
                'required',
                'integer',
                Rule::exists($this->attendableTable(), 'id')->where('institution_id', $this->institution_id),
            ],
        ];
    }

    protected function attendableTable(): string
    {
        return $this->attendable_type === 'employee' ? 'employees' : 'students';
    }

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
        $this->resetPage();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterInstitutionId(): void { $this->resetPage(); }
    public function updatingFilterDeviceId(): void { $this->resetPage(); }
    public function updatingFilterAttendableType(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        // NOTE: SuperAdmin scope intentionally not institution-locked at query level;
        // access control relies on route middleware role:super-admin (per established pattern)
        $mapping = BiometricDeviceUserMapping::findOrFail($id);

        $this->editingId = $mapping->id;
        $this->institution_id = $mapping->institution_id;
        $this->biometric_device_id = $mapping->biometric_device_id;
        $this->device_user_id = $mapping->device_user_id;
        $this->attendable_type = $mapping->attendable_type === Employee::class ? 'employee' : 'student';
        $this->attendable_id = $mapping->attendable_id;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function updatedInstitutionId(): void
    {
        $this->biometric_device_id = null;
        $this->attendable_id = null;
        $this->personSearch = '';
    }

    public function updatedAttendableType(): void
    {
        $this->attendable_id = null;
        $this->personSearch = '';
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $data = [
                'institution_id' => $this->institution_id,
                'biometric_device_id' => $this->biometric_device_id,
                'device_user_id' => $this->device_user_id,
                'attendable_type' => $this->attendableClass(),
                'attendable_id' => $this->attendable_id,
            ];

            if ($this->editMode) {
                $mapping = BiometricDeviceUserMapping::findOrFail($this->editingId);
                $mapping->update($data);

                activity()->causedBy(auth()->user())->performedOn($mapping)
                    ->log('Biometric device-user mapping updated (super-admin)');
            } else {
                $mapping = BiometricDeviceUserMapping::create($data);

                activity()->causedBy(auth()->user())->performedOn($mapping)
                    ->log('Biometric device-user mapping created (super-admin)');
            }
        });

        $this->dispatch('toast', type: 'success', message: $this->editMode
            ? 'Mapping updated successfully.'
            : 'Mapping created successfully.');

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $mapping = BiometricDeviceUserMapping::findOrFail($this->deletingId);

        activity()->causedBy(auth()->user())->performedOn($mapping)
            ->log('Biometric device-user mapping deleted (super-admin)');

        $mapping->delete();

        $this->dispatch('toast', type: 'success', message: 'Mapping deleted successfully.');
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'institution_id', 'biometric_device_id', 'device_user_id',
            'attendable_type', 'attendable_id', 'personSearch',
        ]);
        $this->attendable_type = 'student';
        $this->resetErrorBag();
    }

    public function getDevicesForFormProperty()
    {
        if (! $this->institution_id) {
            return collect();
        }

        return BiometricDevice::query()
            ->where('institution_id', $this->institution_id)
            ->orderBy('device_name')
            ->get(['id', 'device_name', 'device_serial']);
    }

    public function getPersonOptionsProperty()
    {
        if (strlen($this->personSearch) < 2 || ! $this->institution_id) {
            return collect();
        }

        $model = $this->attendableClass();

        return $model::query()
            ->where('institution_id', $this->institution_id)
            ->where('name', 'like', "%{$this->personSearch}%")
            ->limit(15)
            ->get(['id', 'name']);
    }

    public function render()
    {
        $institutions = Institution::query()->orderBy('name')->get(['id', 'name']);

        $devices = $this->filterInstitutionId
            ? BiometricDevice::query()->where('institution_id', $this->filterInstitutionId)->orderBy('device_name')->get(['id', 'device_name', 'device_serial'])
            : collect();

        $mappings = BiometricDeviceUserMapping::query()
            ->with(['biometricDevice:id,device_name,device_serial', 'attendable', 'institution:id,name'])
            ->when($this->filterInstitutionId, fn ($q) => $q->where('institution_id', $this->filterInstitutionId))
            ->when($this->filterDeviceId, fn ($q) => $q->where('biometric_device_id', $this->filterDeviceId))
            ->when($this->filterAttendableType === 'student', fn ($q) => $q->where('attendable_type', Student::class))
            ->when($this->filterAttendableType === 'employee', fn ($q) => $q->where('attendable_type', Employee::class))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('device_user_id', 'like', "%{$this->search}%")
                        ->orWhereHas('attendable', fn ($aq) => $aq->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('livewire.super-admin.biometric-device-user-mapping-component', [
            'institutions' => $institutions,
            'devices' => $devices,
            'mappings' => $mappings,
        ]);
    }
}