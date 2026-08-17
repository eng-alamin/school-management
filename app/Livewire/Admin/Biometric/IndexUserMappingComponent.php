<?php

namespace App\Livewire\Admin\Biometric;

use App\Models\BiometricDevice;
use App\Models\BiometricDeviceUserMapping;
use App\Services\BiometricCommandService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin.app')]
class IndexUserMappingComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['device_user_id', 'card_number', 'attendable_type', 'created_at'];

    // FIX: 'card_number' was missing from this map in the old component.
    // sortBy('card_number') passed the allowlist check but silently fell back
    // to sorting by created_at, so the "Card Number" column header was a dead click.
    protected array $sortColumnMap = [
        'device_user_id'  => 'biometric_device_user_mappings.device_user_id',
        'card_number'     => 'biometric_device_user_mappings.card_number',
        'attendable_type' => 'biometric_device_user_mappings.attendable_type',
        'created_at'      => 'biometric_device_user_mappings.created_at',
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Bound to the URL so Create/Edit pages can redirect back to the same
    // device context, and so the list is shareable/bookmarkable.
    #[Url(as: 'device_id')]
    public ?int $selectedDeviceId = null;

    public string $search = '';
    public int $perPage = 10;

    public ?int $deletingId = null;
    public bool $showDeleteModal = false;

    public function mount(): void
    {
        // Show a toast after redirect from Create/Edit save().
        if (session()->has('toast_success')) {
            $this->dispatch('toast', type: 'success', message: session('toast_success'));
        }

        if (session()->has('toast_error')) {
            $this->dispatch('toast', type: 'error', message: session('toast_error'));
        }

        // IDOR guard: if a device_id arrived via URL (bookmark, tampered link, etc.),
        // make sure it actually belongs to this institution before trusting it.
        if ($this->selectedDeviceId) {
            $owned = BiometricDevice::where('institution_id', institution()->id)
                ->where('id', $this->selectedDeviceId)
                ->exists();

            if (! $owned) {
                $this->selectedDeviceId = null;
            }
        }
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

    public function confirmDelete(int $id): void
    {
        // Ownership check before showing the confirm modal (IDOR prevention).
        BiometricDeviceUserMapping::where('institution_id', institution()->id)->findOrFail($id);

        $this->deletingId = $id;
        $this->showDeleteModal = true;
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

        return view('livewire.admin.biometric.index-user-mapping-component', [
            'devices'  => $devices,
            'mappings' => $mappings,
        ]);
    }
}