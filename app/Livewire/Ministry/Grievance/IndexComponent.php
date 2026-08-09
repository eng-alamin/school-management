<?php

namespace App\Livewire\Ministry\Grievance;

use App\Models\Grievance;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class IndexComponent extends Component
{
    use WithPagination;

    private const SORTABLE_FIELDS = ['created_at', 'status', 'category'];

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $division = '';

    #[Url(history: true)]
    public string $complainantType = '';

    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingDivision(): void { $this->resetPage(); }
    public function updatingComplainantType(): void { $this->resetPage(); }

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
        return Grievance::STATUS_LABELS;
    }

    public function getComplainantTypesProperty(): array
    {
        return Grievance::TYPE_LABELS;
    }

    public function getDivisionsProperty(): array
    {
        return Institution::DIVISIONS;
    }

    public function assignToMe(int $id): void
    {
        $grievance = Grievance::findOrFail($id);
        $grievance->update(['assigned_to' => Auth::id()]);
        $this->dispatch('toast', type: 'success', message: 'Assigned to you.');
    }

    public function render()
    {
        $query = Grievance::query()->with(['institution', 'assignedTo']);

        if ($this->search !== '') {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhereHas('institution', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->complainantType !== '') {
            $query->where('complainant_type', $this->complainantType);
        }

        if ($this->division !== '') {
            $division = $this->division;
            $query->whereHas('institution', function ($q) use ($division) {
                $q->where('division', $division);
            });
        }

        $grievances = $query->orderBy($this->sortField, $this->sortDirection)->paginate(15);

        return view('livewire.ministry.grievance.index-component', [
            'grievances' => $grievances,
        ])->layout('layouts.ministry.app', [
            'title' => 'Compliances | ' . setting('app_name', 'EMS'),
        ]);
    }
}