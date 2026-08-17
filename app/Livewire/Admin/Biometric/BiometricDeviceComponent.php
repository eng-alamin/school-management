<?php

namespace App\Livewire\Admin\Biometric;

use App\Models\BiometricDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;


class BiometricDeviceComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

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
    public string $filterStatus = 'all';
    public int $perPage = 10;

    // ---------- Form ----------
    public ?int $editingId = null;
    public ?int $viewingId = null;
    public ?int $deletingId = null;

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
        // institution_id explicit check — defense-in-depth against IDOR
        $device = BiometricDevice::where('institution_id', institution()->id)->findOrFail($id);

        $this->editingId = $device->id;
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
        BiometricDevice::where('institution_id', institution()->id)->findOrFail($id);

        $this->viewingId = $id;
        $this->showViewModal = true;
    }

    public function confirmDelete(int $id): void
    {
        BiometricDevice::where('institution_id', institution()->id)->findOrFail($id);

        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();
        $validated['institution_id'] = institution()->id;

        DB::beginTransaction();

        try {
            $isNew = ! $this->editingId;

            if ($this->editingId) {
                $device = BiometricDevice::where('institution_id', institution()->id)
                    ->findOrFail($this->editingId);

                $device->update($validated);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($device)
                    ->withProperties(['icon' => 'edit', 'type' => 'biometric_device_update'])
                    ->tap(function ($activity) use ($device) {
                        $activity->institution_id = $device->institution_id;
                    })
                    ->log('Biometric device updated: ' . $device->device_name);
            } else {
                $device = BiometricDevice::create($validated);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($device)
                    ->withProperties(['icon' => 'add', 'type' => 'biometric_device_create'])
                    ->tap(function ($activity) use ($device) {
                        $activity->institution_id = $device->institution_id;
                    })
                    ->log('Biometric device created: ' . $device->device_name);
            }

            DB::commit();

            $this->showFormModal = false;
            $this->resetForm();

            $this->dispatch('toast', type: 'success', message: $isNew ? 'Device added successfully!' : 'Device updated successfully!');

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
            $device = BiometricDevice::where('institution_id', institution()->id)
                ->findOrFail($this->deletingId);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($device)
                ->withProperties(['icon' => 'delete', 'type' => 'biometric_device_delete'])
                ->tap(function ($activity) use ($device) {
                    $activity->institution_id = $device->institution_id;
                })
                ->log('Biometric device deleted: ' . $device->device_name);

            $device->delete();

            DB::commit();

            $this->showDeleteModal = false;
            $this->deletingId = null;

            $this->dispatch('toast', type: 'success', message: 'Device deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'device_serial', 'device_name',
            'device_type', 'ip_address', 'location', 'is_active',
        ]);
        $this->is_active = true;
        $this->device_type = 'attendance';
        $this->resetValidation();
    }

    public function render()
    {
        $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'biometric_devices.created_at';

        $query = BiometricDevice::query()
            ->select('biometric_devices.*')
            ->where('institution_id', institution()->id) // explicit, defense-in-depth
            ->withCount('userMappings')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('device_name', 'like', "%{$this->search}%")
                        ->orWhere('device_serial', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus !== 'all', fn ($q) => $q->where('is_active', $this->filterStatus === 'active'))
            ->orderBy($sortColumn, $this->sortDirection);

        $devices = $query->paginate($this->perPage);

        $viewingDevice = $this->viewingId
            ? BiometricDevice::where('institution_id', institution()->id)
                ->withCount('userMappings')
                ->find($this->viewingId)
            : null;

        return view('livewire.admin.biometric.biometric-device-component', [
            'devices' => $devices,
            'viewingDevice' => $viewingDevice,
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Biometric Device | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Biometric Device', 'url' => route('admin.biometric.devices')],
            ],
        ]);
    }
}