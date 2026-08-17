<?php

namespace App\Livewire\Admin\Information;

use App\Models\InstitutionFacility;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class InstitutionFacilityComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public string $filterStatus = '';
    public int $perPage = 10;

    // Modal
    public bool $showModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    // Form
    public ?int $editId = null;
    public string $name = '';
    public string $status = InstitutionFacility::STATUS_ACTIVE;

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:150',
                Rule::unique('institution_facilities', 'name')
                    ->where(fn ($query) => $query->where('institution_id', institution()->id))
                    ->ignore($this->editId),
            ],
            'status' => 'required|in:' . InstitutionFacility::STATUS_ACTIVE . ',' . InstitutionFacility::STATUS_INACTIVE,
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique' => 'This facility name already exists.',
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = InstitutionFacility::findOrFail($id);

        $this->editId = $id;
        $this->name = $record->name;
        $this->status = $record->status;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $data = [
                'name' => $this->name,
                'status' => $this->status,
            ];

            $isNew = ! $this->editId;

            if ($this->editId) {
                $record = InstitutionFacility::findOrFail($this->editId);
                $record->update($data);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'domain', 'type' => 'facility'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                    })
                    ->log('Facility updated: ' . $record->name);
            } else {
                $record = InstitutionFacility::create($data);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'domain', 'type' => 'facility'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                    })
                    ->log('New facility created: ' . $record->name);
            }

            DB::commit();

            $this->dispatch('toast', type: 'success', message: $isNew ? 'Data created successfully!' : 'Data updated successfully!');

            $this->showModal = false;
            $this->resetForm();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        DB::beginTransaction();

        try {
            $record = InstitutionFacility::findOrFail($this->deleteId);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'domain', 'type' => 'facility'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                })
                ->log('Facility deleted: ' . $record->name);

            $record->delete();

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId = null;

            $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function toggleStatus(int $id): void
    {
        DB::beginTransaction();

        try {
            $record = InstitutionFacility::findOrFail($id);
            $newStatus = $record->status === InstitutionFacility::STATUS_ACTIVE
                ? InstitutionFacility::STATUS_INACTIVE
                : InstitutionFacility::STATUS_ACTIVE;

            $record->update(['status' => $newStatus]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'domain', 'type' => 'facility'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                })
                ->log('Facility status changed to ' . $newStatus . ': ' . $record->name);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editId']);
        $this->status = InstitutionFacility::STATUS_ACTIVE;
        $this->resetValidation();
    }

    public function render()
    {
        $facilities = InstitutionFacility::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.information.institution-facility-component')
            ->with('facilities', $facilities)
            ->layout('layouts.admin.app', [
                'title' => 'Institution Facilities | ' . institution()->name,
            ]);
    }
}