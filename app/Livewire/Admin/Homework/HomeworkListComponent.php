<?php

namespace App\Livewire\Admin\Homework;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\AcademicSession;
use App\Models\Branch;

class HomeworkListComponent extends Component
{
    use WithPagination;

    private const SORTABLE_FIELDS = ['created_at', 'title', 'homework_date', 'submission_date', 'status'];

    public $search    = '';
    public $perPage   = 10;
    public $sortField = 'created_at';
    public $sortDir   = 'desc';

    public $filterClass   = '';
    public $filterSection = '';

    public array $availableSections = [];

    public $confirmDelete = false;
    public $deleteId;

    public string $routePrefix = '';

    public ?int $currentSessionId = null;

    public function mount(): void
    {
        $this->routePrefix     = $this->resolveRoutePrefix();
        $this->currentSessionId = $this->resolveCurrentSessionId();
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

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    private function resolveCurrentSessionId(): ?int
    {
        return AcademicSession::query()
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->active() // scopeActive() -> is_current = true
            ->value('id');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClass($value): void
    {
        $this->filterSection     = '';
        $this->availableSections = [];
        $this->resetPage();

        if (!$value) return;

        $assigns = AcademicClassAssign::with('section')
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->where('session_id', $this->currentSessionId)
            ->where('class_id', $value)
            ->whereNotNull('section_id')
            ->get();

        $this->availableSections = $assigns
            ->filter(fn($a) => $a->section)
            ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();
    }

    public function updatedFilterSection(): void
    {
        $this->resetPage();
    }

    public function sortBy($field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }
    }

    public function confirmDeleteRecord($id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $homework = Homework::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->find($this->deleteId);

        if (!$homework) {
            $this->confirmDelete = false;
            $this->deleteId      = null;
            $this->dispatch('toast', type: 'error', message: 'Homework not found.');
            return;
        }

        $attachmentPath = $homework->attachment;
        $title          = $homework->title;

        try {
            DB::transaction(function () use ($homework, $title, $institutionId) {
                activity()
                    ->performedOn($homework)
                    ->tap(fn ($a) => $a->institution_id = $institutionId)
                    ->log('Homework "' . $title . '" deleted');

                $homework->delete();
            });

            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            $this->dispatch('toast', type: 'success', message: 'Homework deleted successfully.');

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Delete failed: ' . $e->getMessage());
        }

        $this->confirmDelete = false;
        $this->deleteId      = null;
    }

    public function render()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $classes = AcademicClass::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->whereIn('id', AcademicClassAssign::where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->where('session_id', $this->currentSessionId)
                ->distinct()
                ->pluck('class_id'))
            ->orderBy('name')
            ->get();

        $homeworks = Homework::with(['class', 'section', 'subject', 'teacher'])
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
            ->when($this->search, fn($q) =>
                $q->where('title', 'like', '%' . $this->search . '%')
            )
            ->when($this->filterClass, fn($q) =>
                $q->where('class_id', $this->filterClass)
            )
            ->when($this->filterSection && $this->filterSection !== 'all', fn($q) =>
                $q->where('section_id', $this->filterSection)
            )
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.admin.homework.homework-list-component')
            ->with('classes', $classes)
            ->with('homeworks', $homeworks)
            ->layout('layouts.admin.app', [
                'title' => 'Homework List | ' . institution()->name,
            ]);
    }
}