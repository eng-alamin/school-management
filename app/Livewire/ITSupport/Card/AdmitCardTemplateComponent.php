<?php

namespace App\Livewire\ITSupport\Card;

use Livewire\Component;
use App\Models\AdmitCardTemplate;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Throwable;

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
            'logo'             => 'nullable|image|mimes:jpg,jpeg,png,svg|max:2048',
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
    public function updatingFilterExamType(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = AdmitCardTemplate::findOrFail($id);

        $this->resetValidation();

        $this->editId           = $id;
        $this->name              = $record->name;
        $this->exam_type         = $record->exam_type ?? 'annual';
        $this->background_color  = $record->background_color;
        $this->text_color        = $record->text_color;
        $this->accent_color      = $record->accent_color;
        $this->logo               = null;
        $this->existingLogo      = $record->logo_path ?? '';
        $this->header_text       = $record->header_text ?? '';
        $this->instructions      = $record->instructions ?? '';
        $this->footer_text       = $record->footer_text ?? '';
        $this->show_photo        = $record->show_photo;
        $this->show_signature    = $record->show_signature;
        $this->show_barcode      = $record->show_barcode;
        $this->is_active         = $record->is_active;
        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord = AdmitCardTemplate::findOrFail($id);
        $this->showViewModal = true;
    }

    private function deleteOldFile(?string $path): void
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

        $newLogoPath = null;
        if ($this->logo) {
            $newLogoPath = $this->logo->store('templates/logos', 'public');
        }

        $oldLogoPath = $this->existingLogo;
        $logoPath    = $newLogoPath ?? $oldLogoPath;

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

        DB::beginTransaction();
        try {
            if ($this->editId) {
                $record = AdmitCardTemplate::findOrFail($this->editId);
                $record->update($data);
                activity()->performedOn($record)->log('Admit card template updated');
                $message = 'Admit card template updated successfully!';
            } else {
                $record = AdmitCardTemplate::create($data);
                activity()->performedOn($record)->log('Admit card template created');
                $message = 'Admit card template created successfully!';
            }

            DB::commit();

            // Only remove the old logo AFTER a successful commit, and only if it was replaced.
            if ($newLogoPath && $oldLogoPath) {
                $this->deleteOldFile($oldLogoPath);
            }
        } catch (Throwable $e) {
            DB::rollBack();

            // Roll back the newly uploaded file since DB save failed.
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Template could not be saved.');
            return;
        }

        $this->showModal = false;
        $this->resetForm();

        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record   = AdmitCardTemplate::findOrFail($this->deleteId);
        $logoPath = $record->logo_path;

        DB::beginTransaction();
        try {
            activity()->performedOn($record)->log('Admit card template deleted');
            $record->delete();
            DB::commit();

            // Use the same deletion helper as save() so file location logic never diverges.
            $this->deleteOldFile($logoPath);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->confirmDelete = false;
            $this->deleteId = null;
            $this->dispatch('toast', type: 'error', message: 'Template could not be deleted.');
            return;
        }

        $this->confirmDelete = false;
        $this->deleteId = null;

        $this->dispatch('toast', type: 'success', message: 'Template deleted successfully!');
    }

    public function toggleStatus(int $id): void
    {
        DB::beginTransaction();
        try {
            $record = AdmitCardTemplate::findOrFail($id);
            $record->update(['is_active' => !$record->is_active]);
            activity()->performedOn($record)->log('Admit card template status toggled');
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Status could not be updated.');
            return;
        }

        $this->dispatch('toast', type: 'success', message: 'Status updated!');
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
            ->layout('layouts.itsupport.app', [
                'title' => 'Admit Card Templates | ' . institution()->name,
            ]);
    }
}