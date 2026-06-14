<?php

namespace App\Livewire\Accountant\Card;

use Livewire\Component;
use App\Models\IdCardTemplate;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class IdCardTemplateComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public string $filterType = '';
    public string $filterStatus = '';
    public int $perPage = 10;

    // Modal
    public bool $showModal = false;
    public bool $showViewModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;
    public ?IdCardTemplate $viewRecord = null;

    // Form
    public ?int $editId = null;
    public string $name = '';
    public string $type = 'general';
    public string $background_color = '#ffffff';
    public string $text_color = '#000000';
    public string $accent_color = '#007bff';
    public $logo = null;
    public string $existingLogo = '';
    public string $header_text = '';
    public string $footer_text = '';
    public string $card_width = '85.6mm';
    public string $card_height = '54mm';
    public bool $show_photo = true;
    public bool $show_barcode = false;
    public bool $show_qrcode = false;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:general,student,employee',
            'background_color' => 'required|string',
            'text_color'       => 'required|string',
            'accent_color'     => 'required|string',
            'logo'             => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
            'header_text'      => 'nullable|string|max:500',
            'footer_text'      => 'nullable|string|max:500',
            'card_width'       => 'required|string',
            'card_height'      => 'required|string',
            'show_photo'       => 'boolean',
            'show_barcode'     => 'boolean',
            'show_qrcode'      => 'boolean',
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
        $record = IdCardTemplate::findOrFail($id);
        $this->editId = $id;
        $this->name = $record->name;
        $this->type = $record->type;
        $this->background_color = $record->background_color;
        $this->text_color = $record->text_color;
        $this->accent_color = $record->accent_color;
        $this->existingLogo = $record->logo_path ?? '';
        $this->header_text = $record->header_text ?? '';
        $this->footer_text = $record->footer_text ?? '';
        $this->card_width = $record->card_width;
        $this->card_height = $record->card_height;
        $this->show_photo = $record->show_photo;
        $this->show_barcode = $record->show_barcode;
        $this->show_qrcode = $record->show_qrcode;
        $this->is_active = $record->is_active;
        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord = IdCardTemplate::findOrFail($id);
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $logoPath = $this->existingLogo;
        if ($this->logo) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $this->logo->store('templates/logos', 'public');
        }

        $data = [
            'name'             => $this->name,
            'type'             => $this->type,
            'background_color' => $this->background_color,
            'text_color'       => $this->text_color,
            'accent_color'     => $this->accent_color,
            'logo_path'        => $logoPath,
            'header_text'      => $this->header_text,
            'footer_text'      => $this->footer_text,
            'card_width'       => $this->card_width,
            'card_height'      => $this->card_height,
            'show_photo'       => $this->show_photo,
            'show_barcode'     => $this->show_barcode,
            'show_qrcode'      => $this->show_qrcode,
            'is_active'        => $this->is_active,
        ];

        if ($this->editId) {
            IdCardTemplate::findOrFail($this->editId)->update($data);
            session()->flash('success', 'Template updated successfully!');
        } else {
            IdCardTemplate::create($data);
            session()->flash('success', 'Template created successfully!');
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
        $record = IdCardTemplate::findOrFail($this->deleteId);
        if ($record->logo_path) {
            Storage::disk('public')->delete($record->logo_path);
        }
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Template deleted successfully!');
    }

    public function toggleStatus(int $id): void
    {
        $record = IdCardTemplate::findOrFail($id);
        $record->update(['is_active' => !$record->is_active]);
        session()->flash('success', 'Status updated!');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'type', 'background_color', 'text_color', 'accent_color',
            'logo', 'existingLogo', 'header_text', 'footer_text', 'show_barcode',
            'show_qrcode', 'editId']);
        $this->background_color = '#ffffff';
        $this->text_color = '#000000';
        $this->accent_color = '#007bff';
        $this->card_width = '85.6mm';
        $this->card_height = '54mm';
        $this->show_photo = true;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $templates = IdCardTemplate::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus !== '', fn($q) => $q->where('is_active', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.accountant.card.id-card-template-component')
            ->with('templates', $templates)
            ->with('types', IdCardTemplate::getTypes())
            ->layout('layouts.accountant.app', [
                'title' => "ID Card Templates | School SaaS",
            ]);
    }
}