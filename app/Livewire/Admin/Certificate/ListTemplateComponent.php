<?php

namespace App\Livewire\Admin\Certificate;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CertificateTemplate;

class ListTemplateComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

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
        // Whitelist to prevent arbitrary column sort injection
        if (! in_array($field, ['certificate_name', 'applicable_user'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = CertificateTemplate::findOrFail($this->deleteId);
        $name   = $record->certificate_name;

        // Delete associated images
        // Bug fix: files are stored directly under public_path() (not the
        // 'public' storage disk), so they must be removed the same way.
        foreach (['signature_image', 'logo_image', 'background_image'] as $field) {
            if ($record->$field) {
                $fullPath = public_path($record->$field);
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }
        }

        $record->delete();

        activity()
            ->withProperties(['certificate_name' => $name])
            ->log('Certificate template deleted: ' . $name);

        $this->confirmDelete = false;
        $this->deleteId      = null;

        $this->dispatch('toast', type: 'success', message: 'Certificate template deleted successfully!');
    }

    public function render()
    {
        $templates = CertificateTemplate::query()
            ->when($this->search, fn ($q) => $q->where('certificate_name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.certificate.list-template-component')
            ->with('templates', $templates)
            ->layout('layouts.admin.app', [
                'title' => 'Certificate Templates | ' . institution()->name,
            ]);
    }
}