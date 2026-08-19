<?php

namespace App\Livewire\Admin\Salary;

use Livewire\Component;
use App\Models\SalaryTemplate;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ListTemplateComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

    // View modal
    public bool       $showViewModal = false;
    public ?SalaryTemplate $viewRecord    = null;

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openView(int $id): void
    {
        $this->viewRecord    = SalaryTemplate::with([
            'allowances', 'deductions',
        ])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        DB::beginTransaction();

        try {
            $record = SalaryTemplate::findOrFail($this->deleteId);

            activity()
                ->performedOn($record)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Salary Template Deleted');

            $record->delete();

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId      = null;

            $this->dispatch('toast', type: 'success', message: 'Salary template deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'An error occurred while deleting the template.');
        }
    }

    public function render()
    {
        $templates = SalaryTemplate::query()
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('name', 'like', "%{$this->search}%")
                        ->orWhere('salary_grade', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.salary.list-template-component')
            ->with('templates', $templates)
            ->layout('layouts.admin.app', [
                'title' => 'Salary Templates | ' . institution()->name,
            ]);
    }
}