<?php

namespace App\Livewire\SuperAdmin;

use App\Models\BiometricDevice;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * SuperAdmin-facing device registry.
 * Cross-institution: সব institution-এর device দেখা/manage করা যাবে।
 * institution_id dropdown দিয়ে user select করে (filter এবং form দুই জায়গাতেই)।
 *
 * SECURITY NOTE: এখানে per-query institution_id lock নেই — access control
 * সম্পূর্ণভাবে route middleware ('role:super-admin')-এর উপর নির্ভরশীল।
 * নিশ্চিত করো ওই middleware ঠিকভাবে কাজ করছে।
 */
#[Layout('layouts.superadmin.app')]
class BiometricDeviceComponent extends Component
{
    use WithPagination;

    // ---------- Sort ----------
    public const SORTABLE_FIELDS = ['device_name', 'device_serial', 'device_type', 'is_active', 'last_seen_at', 'created_at'];

    protected array $sortColumnMap = [
        'device_name' => 'biometric_devices.device_name',
        'device_serial' => 'biometric_devices.device_serial',
        'device_type' => 'biometric_devices.device_type',
        'is_active' => 'biometric_devices.is_active',
        'last_seen_at' => 'biometric_devices.last_seen_at',
        'created_at' => 'biometric_devices.created_at',
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // ---------- Filters ----------
    public string $search = '';
    public string $filterInstitution = 'all';
    public string $filterStatus = 'all';
    public int $perPage = 10;

    // ---------- Form ----------
    public ?int $editingId = null;
    public ?int $viewingId = null;
    public ?int $deletingId = null;

    public string $institution_id = '';
    public string $device_serial = '';
    public string $device_name = '';
    public string $device_type = 'attendance';
    public string $ip_address = '';
    public string $location = '';
    public bool $is_active = true;

    public bool $showFormModal = false;
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;

    protected function rules(): array
    {
        return [
            'institution_id' => ['required', 'exists:institutions,id'],
            'device_serial' => [
                'required', 'string', 'max:100',
                Rule::unique('biometric_devices', 'device_serial')
                    ->ignore($this->editingId)
                    ->whereNull('deleted_at'),
            ],
            'device_name' => ['required', 'string', 'max:150'],
            'device_type' => [Rule::in(['attendance', 'access_control'])],
            'ip_address' => ['nullable', 'ip'],
            'location' => ['nullable', 'string', 'max:150'],
            'is_active' => ['boolean'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterInstitution(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
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

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $id): void
    {
        $device = BiometricDevice::findOrFail($id);

        $this->editingId = $device->id;
        $this->institution_id = (string) $device->institution_id;
        $this->device_serial = $device->device_serial;
        $this->device_name = $device->device_name;
        $this->device_type = $device->device_type;
        $this->ip_address = (string) $device->ip_address;
        $this->location = (string) $device->location;
        $this->is_active = $device->is_active;

        $this->showFormModal = true;
    }

    public function openViewModal(int $id): void
    {
        BiometricDevice::findOrFail($id);

        $this->viewingId = $id;
        $this->showViewModal = true;
    }

    public function confirmDelete(int $id): void
    {
        BiometricDevice::findOrFail($id);

        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            if ($this->editingId) {
                $device = BiometricDevice::findOrFail($this->editingId);
                $device->update($validated);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($device)
                    ->withProperties(['icon' => 'edit', 'type' => 'biometric_device_update'])
                    ->log('Biometric device updated: ' . $device->device_name);
            } else {
                $device = BiometricDevice::create($validated);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($device)
                    ->withProperties(['icon' => 'add', 'type' => 'biometric_device_create'])
                    ->log('Biometric device created: ' . $device->device_name);
            }
        });

        $this->showFormModal = false;
        $this->resetForm();

        $this->dispatch('toast', type: 'success', message: $this->editingId ? 'Device updated successfully.' : 'Device added successfully.');
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $device = BiometricDevice::findOrFail($this->deletingId);

        DB::transaction(function () use ($device) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($device)
                ->withProperties(['icon' => 'delete', 'type' => 'biometric_device_delete'])
                ->log('Biometric device deleted: ' . $device->device_name);

            $device->delete();
        });

        $this->showDeleteModal = false;
        $this->deletingId = null;

        $this->dispatch('toast', type: 'success', message: 'Device deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'institution_id', 'device_serial', 'device_name',
            'device_type', 'ip_address', 'location', 'is_active',
        ]);
        $this->is_active = true;
        $this->device_type = 'attendance';
        $this->resetErrorBag();
    }

    public function render()
    {
        $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'biometric_devices.created_at';

        $query = BiometricDevice::query()
            ->select('biometric_devices.*')
            ->with('institution:id,name')
            ->withCount('userMappings')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('device_name', 'like', "%{$this->search}%")
                        ->orWhere('device_serial', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterInstitution !== 'all', fn ($q) => $q->where('institution_id', $this->filterInstitution))
            ->when($this->filterStatus !== 'all', fn ($q) => $q->where('is_active', $this->filterStatus === 'active'))
            ->orderBy($sortColumn, $this->sortDirection);

        $devices = $query->paginate($this->perPage);

        $viewingDevice = $this->viewingId
            ? BiometricDevice::with('institution:id,name')->withCount('userMappings')->find($this->viewingId)
            : null;

        return view('livewire.super-admin.biometric-device-component', [
            'devices' => $devices,
            'institutions' => Institution::select('id', 'name')->orderBy('name')->get(),
            'viewingDevice' => $viewingDevice,
        ]);
    }
}
