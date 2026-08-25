<?php

namespace App\Livewire\Admin\Biometric;

use App\Models\AcademicClass;
use App\Models\BiometricDevice;
use App\Models\BiometricDeviceUserMapping;
use App\Models\Employee;
use App\Models\Student;
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

    protected array $sortColumnMap = [
        'device_user_id'  => 'biometric_device_user_mappings.device_user_id',
        'card_number'     => 'biometric_device_user_mappings.card_number',
        'attendable_type' => 'biometric_device_user_mappings.attendable_type',
        'created_at'      => 'biometric_device_user_mappings.created_at',
    ];

    public string $sortField = 'created_at';
    public string $sortDirection = 'asc';

    // "" (empty) = All Devices. No longer required to view the list.
    #[Url(as: 'device_id')]
    public string $selectedDeviceId = '';

    // ── Filter Ground: Type / Class / Section ───────────────────────────────
    #[Url(as: 'type')]
    public string $filterType = ''; // '' = All, 'student', 'employee'

    public string $filterClassId   = '';
    public string $filterSectionId = '';
    public bool   $filterClassHasSection = true;

    public string $search = '';
    public int $perPage = 10;

    public ?int $deletingId = null;
    public bool $showDeleteModal = false;

    public string $routePrefix = '';

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();

        if (session()->has('toast_success')) {
            $this->dispatch('toast', type: 'success', message: session('toast_success'));
        }

        if (session()->has('toast_error')) {
            $this->dispatch('toast', type: 'error', message: session('toast_error'));
        }

        // IDOR guard: if a device_id arrived via URL (bookmark, tampered link, etc.),
        // make sure it actually belongs to this institution before trusting it.
        if ($this->selectedDeviceId !== '') {
            $owned = BiometricDevice::where('institution_id', institution()->id)
                ->where('id', $this->selectedDeviceId)
                ->exists();

            if (! $owned) {
                $this->selectedDeviceId = '';
            }
        }

        // If a class id arrived via URL/state but type isn't student, ignore it.
        if ($this->filterType !== 'student') {
            $this->filterClassId = '';
            $this->filterSectionId = '';
        } elseif ($this->filterClassId !== '') {
            $this->filterClassHasSection = $this->resolveClassHasSection($this->filterClassId);
        }
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

    public function updatedFilterType(): void
    {
        $this->filterClassId = '';
        $this->filterSectionId = '';
        $this->filterClassHasSection = true;
        $this->resetPage();
    }

    public function updatedFilterClassId(): void
    {
        $this->filterSectionId = '';
        $this->filterClassHasSection = $this->resolveClassHasSection($this->filterClassId);
        $this->resetPage();
    }

    public function updatedFilterSectionId(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    private function resolveClassHasSection(?string $classId): bool
    {
        if (!$classId) {
            return true;
        }

        $class = AcademicClass::where('institution_id', institution()->id)->find($classId);

        return $class ? (bool) $class->has_section : true;
    }

    public function getAvailableFilterClasses()
    {
        return AcademicClass::where('institution_id', institution()->id)
            ->orderBy('id')
            ->get(['id', 'name', 'has_section']);
    }

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
            \Log::error('IndexUserMapping delete failed: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        $devices = BiometricDevice::where('institution_id', institution()->id)
            ->where('is_active', true)
            ->orderBy('device_name')
            ->get(['id', 'device_name', 'device_serial']);

        // Ownership verify only when a specific device is chosen.
        if ($this->selectedDeviceId !== '') {
            BiometricDevice::where('institution_id', institution()->id)
                ->findOrFail($this->selectedDeviceId);
        }

        $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'biometric_device_user_mappings.created_at';

        $mappings = BiometricDeviceUserMapping::query()
            ->where('institution_id', institution()->id)
            ->when($this->selectedDeviceId !== '', fn ($q) => $q->where('biometric_device_id', $this->selectedDeviceId))
            ->when($this->filterType !== '', function ($q) {
                $type = $this->filterType === 'employee' ? Employee::class : Student::class;
                $q->where('attendable_type', $type);
            })
            ->when($this->filterType === 'student' && $this->filterClassId !== '', function ($q) {
                $q->whereHasMorph('attendable', [Student::class], function ($sq) {
                    $sq->where('class_id', $this->filterClassId);

                    if ($this->filterClassHasSection && $this->filterSectionId !== '' && $this->filterSectionId !== 'all') {
                        $sq->where('section_id', $this->filterSectionId);
                    }
                });
            })
            ->with('attendable', 'biometricDevice:id,device_name,device_serial')
            ->when($this->search !== '', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('device_user_id', 'like', "%{$this->search}%")
                        ->orWhere('card_number', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.biometric.index-user-mapping-component', [
            'devices'        => $devices,
            'mappings'       => $mappings,
            'filterClasses'  => $this->filterType === 'student' ? $this->getAvailableFilterClasses() : collect(),
            'filterSections' => $this->filterType === 'student' ? $this->getAvailableFilterSections($this->filterClassId) : collect(),
        ])
        ->layout('layouts.admin.app', [
            'title' => 'User Mapping | ' . institution()->name,
        ]);
    }
}