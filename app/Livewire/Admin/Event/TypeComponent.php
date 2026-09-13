<?php

namespace App\Livewire\Admin\Event;

use Livewire\Component;
use App\Models\EventType;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class TypeComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Modal
    public bool $showModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    // Form
    public ?int $editId = null;
    public string $name = '';

    protected function rules(): array
    {
        $institutionId = auth()->user()->institution_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('event_types', 'name')
                    ->where(fn($q) => $q->where('institution_id', $institutionId))
                    ->ignore($this->editId),
            ],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = EventType::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        $this->editId = $id;
        $this->name = $record->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            if ($this->editId) {
                $record = EventType::findOrFail($this->editId);
                $record->update(['name' => $this->name]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'event', 'type' => 'event'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                        $activity->branch_id = $record->branch_id;
                    })
                    ->log('Event type updated: ' . $record->name);
            } else {
                $record = EventType::create([
                    'name'           => $this->name,
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'event', 'type' => 'event'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                        $activity->branch_id = $record->branch_id;
                    })
                    ->log('Event type created: ' . $record->name);
            }

            DB::commit();

            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('toast', type: 'success', message: $this->editId ? 'Event type updated successfully!' : 'Event type created successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong; please try again.');
            report($e);
            return;
        }

    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;

        $types = EventType::query()
            ->where('institution_id', $institutionId)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.event.type-component')
            ->with('types', $types)
            ->layout('layouts.admin.app', [
                'title' => 'Event Type | ' . institution()->name,
            ]);
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = EventType::where('institution_id', auth()->user()->institution_id)->findOrFail($this->deleteId);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;

        activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'event', 'type' => 'event'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Event type deleted: ' . $record->name);

        $this->dispatch('toast', type: 'success', message: 'Event type deleted successfully!');
    }
}