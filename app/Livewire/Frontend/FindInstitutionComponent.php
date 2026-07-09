<?php

namespace App\Livewire\Frontend;

use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class FindInstitutionComponent extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $filterCity = '';

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCity(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterType', 'filterCity']);
        $this->resetPage();
    }

    public function getStatsProperty(): array
    {
        return [
            'high_schools' => Institution::withoutGlobalScope(\App\Models\Scopes\InstitutionScope::class)
                ->where('status', true)
                ->where('type', 'high_school')
                ->count(),

            'colleges' => Institution::withoutGlobalScope(\App\Models\Scopes\InstitutionScope::class)
                ->where('status', true)
                ->where('type', 'college')
                ->count(),

            'school_college' => Institution::withoutGlobalScope(\App\Models\Scopes\InstitutionScope::class)
                ->where('status', true)
                ->where('type', 'school_college')
                ->count(),

            'total_teachers' => DB::table('users')
                ->where('role', 'teacher')
                ->count(),

            'total_students' => DB::table('students')
                ->count(),
        ];
    }

    public function getInstitutionTypesProperty()
    {
        return Institution::withoutGlobalScope(\App\Models\Scopes\InstitutionScope::class)
            ->where('status', true)
            ->whereNotNull('type')
            ->distinct()
            ->pluck('type');
    }

    public function getInstitutionCitiesProperty()
    {
        return Institution::withoutGlobalScope(\App\Models\Scopes\InstitutionScope::class)
            ->where('status', true)
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');
    }

    public function render()
    {
        $institutions = Institution::withoutGlobalScope(\App\Models\Scopes\InstitutionScope::class)
            ->where('status', true)
            ->withCount([
                // ensure these relation names exist on the Institution model
                // 'students', 'teachers'
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('eiin', 'like', "%{$this->search}%")
                        ->orWhere('city', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterType, function ($query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->filterCity, function ($query) {
                $query->where('city', $this->filterCity);
            })
            ->orderBy('name')
            ->paginate(9);

        return view('livewire.frontend.find-institution-component', [
            'institutions' => $institutions,
            'stats' => $this->stats,
            'institutionTypes' => $this->institutionTypes,
            'institutionCities' => $this->institutionCities,
        ])->layout('layouts.frontend.app', [
            'title' => 'Find Institution',
        ]);
    }
}