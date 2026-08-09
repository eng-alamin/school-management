<?php

namespace App\Livewire\Ministry\Institution;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\Institution;

class IndexComponent extends Component
{
    use WithPagination;

    public string $search              = '';
    public string $division            = '';
    public string $district            = '';
    public string $type                = '';
    public string $medium              = '';
    public string $status              = ''; // '', '1', '0'
    public string $verificationStatus  = '';

    protected $queryString = [
        'search'             => ['except' => ''],
        'division'           => ['except' => ''],
        'district'           => ['except' => ''],
        'type'               => ['except' => ''],
        'medium'             => ['except' => ''],
        'status'             => ['except' => ''],
        'verificationStatus' => ['except' => ''],
    ];

    public function updatingSearch(): void             { $this->resetPage(); }
    public function updatingDivision(): void            { $this->district = ''; $this->resetPage(); }
    public function updatingDistrict(): void            { $this->resetPage(); }
    public function updatingType(): void                { $this->resetPage(); }
    public function updatingMedium(): void              { $this->resetPage(); }
    public function updatingStatus(): void              { $this->resetPage(); }
    public function updatingVerificationStatus(): void  { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->reset(['search', 'division', 'district', 'type', 'medium', 'status', 'verificationStatus']);
        $this->resetPage();
    }

    public function filterByDivision(string $division): void
    {
        $this->division = $this->division === $division ? '' : $division;
        $this->district = '';
        $this->resetPage();
    }

    #[Computed]
    public function divisions(): array
    {
        return Institution::DIVISIONS;
    }

    #[Computed]
    public function verificationStatuses(): array
    {
        return Institution::VERIFICATION_LABELS;
    }

    /**
     * District options for the filter dropdown.
     * - A division is selected: show that division's full district list
     *   from Institution::DISTRICTS (source of truth on the model),
     *   regardless of whether an institution currently exists there.
     * - No division selected: show every district across all divisions,
     *   sorted alphabetically, so "All" genuinely means all.
     */
    #[Computed]
    public function districts(): array
    {
        if ($this->division) {
            return Institution::districtsFor($this->division);
        }

        $all = collect(Institution::DISTRICTS)->flatten()->unique()->sort()->values();

        return $all->all();
    }

    #[Computed]
    public function types()
    {
        return Institution::query()
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');
    }

    #[Computed]
    public function mediums(): array
    {
        return Institution::MEDIUM_LABELS;
    }

    #[Computed]
    public function divisionCounts()
    {
        return Institution::query()
            ->selectRaw('division, count(*) as total')
            ->whereNotNull('division')
            ->groupBy('division')
            ->pluck('total', 'division');
    }

    public function render()
    {
        $institutions = Institution::query()
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('eiin', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->division, fn ($q) => $q->where('division', $this->division))
            ->when($this->district, fn ($q) => $q->where('district', $this->district))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->medium, fn ($q) => $q->where('medium', $this->medium))
            ->when($this->status !== '', fn ($q) => $q->where('status', (bool) $this->status))
            ->when($this->verificationStatus, fn ($q) => $q->where('verification_status', $this->verificationStatus))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.ministry.institution.index-component', [
            'institutions' => $institutions,
        ])->layout('layouts.ministry.app', [
            'title' => 'Institutions | ' . setting('app_name', 'EMS'),
        ]);
    }
}