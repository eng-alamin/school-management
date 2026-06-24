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
    public string $search    = '';
    public int    $perPage   = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

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

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = CertificateTemplate::findOrFail($this->deleteId);

        // Delete associated images
        foreach (['signature_image', 'logo_image', 'background_image'] as $field) {
            if ($record->$field) {
                \Storage::disk('public')->delete($record->$field);
            }
        }

        $record->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;

        session()->flash('success', 'Certificate template deleted successfully!');
    }

    public function render()
    {
        $templates = CertificateTemplate::query()
            ->when($this->search, fn($q) => $q->where('certificate_name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.certificate.list-template-component')
            ->with('templates', $templates)
            ->layout('layouts.admin.app', [
                'title' => 'Certificate Templates | ' . institution()->name,
            ]);
    }
}