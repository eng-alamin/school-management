<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicSession;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SessionComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    private const SORTABLE_FIELDS = ['id', 'name', 'start_date', 'end_date', 'is_current'];

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
    public string $start_date = '';
    public string $end_date = '';
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

    private function institutionId(): int
    {
        return (int) auth()->user()->institution_id;
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId($this->institutionId());
    }

    protected function rules(): array
    {
        $institutionId = $this->institutionId();
        $branchId      = $this->activeBranchId();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_sessions', 'name')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId)
                    ->whereNull('deleted_at')
                    ->ignore($this->editId),
            ],
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
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
        // abort_unless(auth()->user()->can('academic-session.create'), 403);

        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        // abort_unless(auth()->user()->can('academic-session.edit'), 403);

        $record = AcademicSession::where('institution_id', $this->institutionId())
            ->where('branch_id', $this->activeBranchId())
            ->findOrFail($id);

        $this->editId      = $record->id;
        $this->name        = $record->name;
        $this->start_date  = $record->start_date?->format('Y-m-d') ?? '';
        $this->end_date    = $record->end_date?->format('Y-m-d') ?? '';
        $this->is_current  = (bool) $record->is_current;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $action = $this->editId ? 'academic-session.edit' : 'academic-session.create';
        // abort_unless(auth()->user()->can($action), 403);

        $this->validate();

        $institutionId = $this->institutionId();
        $branchId      = $this->activeBranchId();

        $data = [
            'institution_id' => $institutionId,
            'branch_id'      => $branchId,
            'name'           => $this->name,
            'start_date'     => $this->start_date ?: null,
            'end_date'       => $this->end_date ?: null,
            'is_current'     => $this->is_current,
        ];

        DB::transaction(function () use ($data, $institutionId, $branchId) {
            if ($this->editId) {
                $record = AcademicSession::where('institution_id', $institutionId)
                    ->where('branch_id', $branchId)
                    ->findOrFail($this->editId);

                $record->update($data);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->tap(fn ($a) => $a->institution_id = $institutionId)
                    ->withProperties([
                        'icon' => 'edit_calendar',
                        'type' => 'academic_session_updated',
                        'name' => $record->name,
                    ])
                    ->log('Academic session updated');
            } else {
                $record = AcademicSession::create($data);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->tap(fn ($a) => $a->institution_id = $institutionId)
                    ->withProperties([
                        'icon' => 'event_available',
                        'type' => 'academic_session_created',
                        'name' => $record->name,
                    ])
                    ->log('Academic session created');
            }
        });

        $this->dispatch('toast', type: 'success', message: $this->editId
            ? 'Data updated successfully!'
            : 'Data created successfully!');

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'start_date', 'end_date', 'editId']);
        $this->is_current = true;
        $this->resetValidation();
    }

    public function render()
    {
        $institutionId = $this->institutionId();
        $branchId      = $this->activeBranchId();

        $sessions = AcademicSession::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->when($this->search, function ($q) {
                // OR condition সবসময় closure এ wrap — global scope bypass রোধে
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%");
                });
            })
            ->when(
                in_array($this->sortField, self::SORTABLE_FIELDS, true),
                fn ($q) => $q->orderBy($this->sortField, $this->sortDirection === 'desc' ? 'desc' : 'asc'),
                fn ($q) => $q->orderBy('id', 'asc')
            )
            ->paginate($this->perPage);

        return view('livewire.admin.academic.session-component')
            ->with('sessions', $sessions)
            ->layout('layouts.admin.app', [
                'title' => 'Academic Session | ' . institution()->name,
            ]);
    }

    public function confirmDeleteRecord(int $id): void
    {
        // abort_unless(auth()->user()->can('academic-session.delete'), 403);

        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        // abort_unless(auth()->user()->can('academic-session.delete'), 403);

        $institutionId = $this->institutionId();
        $branchId      = $this->activeBranchId();

        $record = AcademicSession::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->findOrFail($this->deleteId);

        // Server-side hard block — আগের কোডে এটা শুধু UI তে disabled attribute
        // দিয়ে আটকানো ছিল, direct Livewire call দিয়ে bypass করা সম্ভব ছিল।
        if ($record->is_current) {
            $this->dispatch('toast', type: 'error', message: 'Current session cannot be deleted!');
            $this->confirmDelete = false;
            $this->deleteId = null;
            return;
        }

        DB::transaction(function () use ($record, $institutionId) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->tap(fn ($a) => $a->institution_id = $institutionId)
                ->withProperties([
                    'icon' => 'event_busy',
                    'type' => 'academic_session_deleted',
                    'name' => $record->name,
                ])
                ->log('Academic session deleted');

            $record->delete();
        });

        $this->confirmDelete = false;
        $this->deleteId = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }
}