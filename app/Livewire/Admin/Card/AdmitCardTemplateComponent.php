<?php

namespace App\Livewire\Admin\Card;

use Livewire\Component;
use App\Models\AdmitCardTemplate;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class AdmitCardTemplateComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public string $filterExamType = '';
    public string $filterStatus = '';
    public int $perPage = 10;

    // Modal
    public bool $showModal = false;
    public bool $showViewModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;
    public ?AdmitCardTemplate $viewRecord = null;

    // Form
    public ?int $editId = null;
    public string $name = '';
    public string $exam_type = 'annual';
    public string $background_color = '#ffffff';
    public string $text_color = '#000000';
    public string $accent_color = '#dc3545';
    public $logo = null;
    public string $existingLogo = '';
    public string $header_text = '';
    public string $instructions = '';
    public string $footer_text = '';
    public bool $show_photo = true;
    public bool $show_signature = true;
    public bool $show_barcode = false;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'exam_type'        => 'required|string',
            'background_color' => 'required|string',
            'text_color'       => 'required|string',
            'accent_color'     => 'required|string',
            'logo'             => 'nullable',
            'header_text'      => 'nullable|string|max:500',
            'instructions'     => 'nullable|string|max:2000',
            'footer_text'      => 'nullable|string|max:500',
            'show_photo'       => 'boolean',
            'show_signature'   => 'boolean',
            'show_barcode'     => 'boolean',
            'is_active'        => 'boolean',
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterType(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = AdmitCardTemplate::findOrFail($id);
        $this->editId           = $id;
        $this->name             = $record->name;
        $this->exam_type        = $record->exam_type ?? 'annual';
        $this->background_color = $record->background_color;
        $this->text_color       = $record->text_color;
        $this->accent_color     = $record->accent_color;
        $this->existingLogo     = $record->logo_path ?? '';
        $this->header_text      = $record->header_text ?? '';
        $this->instructions     = $record->instructions ?? '';
        $this->footer_text      = $record->footer_text ?? '';
        $this->show_photo       = $record->show_photo;
        $this->show_signature   = $record->show_signature;
        $this->show_barcode     = $record->show_barcode;
        $this->is_active        = $record->is_active;
        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord = AdmitCardTemplate::findOrFail($id);
        $this->showViewModal = true;
    }

    private function deleteOldFile($path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = public_path($path);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    public function save(): void
    {
        $this->validate();

        $logoPath = $this->existingLogo;
        if ($this->logo) {
            if ($this->editId) {
                $record = AdmitCardTemplate::find($this->editId);
                if ($record?->logo_path) $this->deleteOldFile($record->logo_path);
            }
            $logoPath = \App\Helpers\TenantFileHelper::store($this->logo, 'cards');
        }

        $data = [
            'name'             => $this->name,
            'exam_type'        => $this->exam_type,
            'background_color' => $this->background_color,
            'text_color'       => $this->text_color,
            'accent_color'     => $this->accent_color,
            'logo_path'        => $logoPath,
            'header_text'      => $this->header_text,
            'instructions'     => $this->instructions,
            'footer_text'      => $this->footer_text,
            'show_photo'       => $this->show_photo,
            'show_signature'   => $this->show_signature,
            'show_barcode'     => $this->show_barcode,
            'is_active'        => $this->is_active,
        ];

        if ($this->editId) {
            AdmitCardTemplate::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Admit card template updated successfully!');
        } else {
            AdmitCardTemplate::create($data);
            session()->flash('success', 'Admit card template created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = AdmitCardTemplate::findOrFail($this->deleteId);
        if ($record->logo_path) Storage::disk('public')->delete($record->logo_path);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Template deleted successfully!');
    }

    public function toggleStatus(int $id): void
    {
        $record = AdmitCardTemplate::findOrFail($id);
        $record->update(['is_active' => !$record->is_active]);
        session()->flash('success', 'Status updated!');
    }

    private function resetForm(): void
    {
        $this->reset(['editId', 'name', 'logo', 'existingLogo', 'header_text',
            'instructions', 'footer_text', 'show_barcode']);
        $this->exam_type = 'annual';
        $this->background_color = '#ffffff';
        $this->text_color = '#000000';
        $this->accent_color = '#dc3545';
        $this->show_photo = true;
        $this->show_signature = true;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $templates = AdmitCardTemplate::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterExamType, fn($q) => $q->where('exam_type', $this->filterExamType))
            ->when($this->filterStatus !== '', fn($q) => $q->where('is_active', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.card.admit-card-template-component')
            ->with('templates', $templates)
            ->with('examTypes', AdmitCardTemplate::getExamTypes())
            ->layout('layouts.admin.app', [
                'title' => "Admit Card Templates | School SaaS",
            ]);
    }
}