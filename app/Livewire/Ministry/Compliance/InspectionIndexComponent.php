<?php

namespace App\Livewire\Ministry\Compliance;

use App\Models\Inspection;
use App\Models\Institution;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InspectionIndexComponent extends Component
{
    use WithPagination;

    private const SORTABLE_FIELDS = ['scheduled_at', 'conducted_at', 'status', 'overall_score', 'created_at'];

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $division = '';

    public string $sortField = 'scheduled_at';
    public string $sortDirection = 'desc';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingDivision(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if (!in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getStatusesProperty(): array
    {
        return Inspection::STATUS_LABELS;
    }

    public function getDivisionsProperty(): array
    {
        return Institution::DIVISIONS;
    }

    public function render()
    {
        $query = Inspection::query()->with(['institution', 'createdBy']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->whereHas('institution', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('eiin', 'like', "%{$search}%");
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->division !== '') {
            $division = $this->division;
            $query->whereHas('institution', function ($q) use ($division) {
                $q->where('division', $division);
            });
        }

        $inspections = $query->orderBy($this->sortField, $this->sortDirection)->paginate(15);

        return view('livewire.ministry.compliance.inspection-index-component', [
            'inspections' => $inspections,
        ])->layout('layouts.ministry.app', [
            'title' => 'Compliances | ' . setting('app_name', 'EMS'),
        ]);
    }
}