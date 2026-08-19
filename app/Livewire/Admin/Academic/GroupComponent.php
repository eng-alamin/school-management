<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicGroup;
use Illuminate\Validation\Rule;

class GroupComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Security: allowlist for sortBy() — never orderBy() a raw client value
    protected const SORTABLE_FIELDS = ['id', 'name', 'is_current'];

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
    public bool $is_current = true;

    public string $routePrefix = '';

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();
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

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_groups', 'name')
                    ->where(fn ($q) => $q->where('institution_id', institution()->id))
                    ->whereNull('deleted_at')
                    ->ignore($this->editId),
            ],
            'is_current' => 'boolean',
        ];
    }

    public function updatingSearch(): void
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

        $this->resetPage();
    }

    public function openCreate(): void
    {
        // abort_unless(auth()->user()->can('academic-group-create'), 403);

        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        // abort_unless(auth()->user()->can('academic-group-update'), 403);

        $record = AcademicGroup::where('institution_id', institution()->id)
            ->findOrFail($id);

        $this->editId     = $id;
        $this->name       = $record->name;
        $this->is_current = (bool) $record->is_current;
        $this->showModal  = true;
    }

    public function save(): void
    {
        // abort_unless($this->editId ? auth()->user()->can('academic-group-update') : auth()->user()->can('academic-group-create'),403);

        $this->validate();

        $data = [
            'name'       => $this->name,
            'is_current' => $this->is_current,
        ];

        if ($this->editId) {
            $record = AcademicGroup::where('institution_id', institution()->id)
                ->findOrFail($this->editId);

            $record->update($data);

            activity()
                ->performedOn($record)
                ->tap(function ($activity) {
                    $activity->institution_id = institution()->id;
                })
                ->log('Academic group updated');

            $savedId = $this->editId;
            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $data['institution_id'] = institution()->id;

            $record  = AcademicGroup::create($data);
            $savedId = $record->id;

            activity()
                ->performedOn($record)
                ->tap(function ($activity) {
                    $activity->institution_id = institution()->id;
                })
                ->log('Academic group created');

            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        // current = true হলে একই institution-এর বাকি সব group inactive হয়ে যাবে
        if ($this->is_current) {
            AcademicGroup::where('institution_id', institution()->id)
                ->where('id', '!=', $savedId)
                ->where('is_current', true)
                ->update(['is_current' => false]);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        // abort_unless(auth()->user()->can('academic-group-update'), 403);

        $record = AcademicGroup::where('institution_id', institution()->id)
            ->findOrFail($id);

        $record->update(['is_status' => ! $record->is_status]);

        activity()
            ->performedOn($record)
            ->tap(function ($activity) {
                $activity->institution_id = institution()->id;
            })
            ->log($record->is_status ? 'Academic group activated' : 'Academic group deactivated');

        $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');
    }

    public function confirmDeleteRecord(int $id): void
    {
        // abort_unless(auth()->user()->can('academic-group-delete'), 403);

        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        // abort_unless(auth()->user()->can('academic-group-delete'), 403);

        $record = AcademicGroup::where('institution_id', institution()->id)
            ->findOrFail($this->deleteId);

        // Log BEFORE delete, per project convention
        activity()
            ->performedOn($record)
            ->tap(function ($activity) {
                $activity->institution_id = institution()->id;
            })
            ->log('Academic group deleted');

        $record->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editId']);
        $this->is_current = true;
        $this->resetValidation();
    }

    public function render()
    {
        $groups = AcademicGroup::query()
            ->where('institution_id', institution()->id)
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.academic.group-component')
            ->with('groups', $groups)
            ->layout('layouts.admin.app', [
                'title' => 'Groups | ' . institution()->name,
            ]);
    }
}