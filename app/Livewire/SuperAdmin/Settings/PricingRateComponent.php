<?php

namespace App\Livewire\SuperAdmin\Settings;

use App\Models\PricingRate;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

class PricingRateComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search       = '';
    public string $filterStatus = '';
    public int    $perPage      = 10;

    // Modal
    public bool          $showModal     = false;
    public bool          $showViewModal = false;
    public bool          $confirmDelete = false;
    public ?int           $deleteId      = null;
    public ?PricingRate   $viewRecord    = null;

    // Form
    public ?int    $editId    = null;
    public string  $type      = '';
    public string  $label     = '';
    public         $rate      = '';
    public bool    $is_active = true;

    protected function rules(): array
    {
        return [
            'type'      => ['required', 'string', 'max:50', Rule::unique('pricing_rates', 'type')->ignore($this->editId)],
            'label'     => 'required|string|max:255',
            'rate'      => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId    = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = PricingRate::findOrFail($id);
        $this->editId    = $id;
        $this->type      = $record->type;
        $this->label     = $record->label;
        $this->rate      = $record->rate;
        $this->is_active = $record->is_active;
        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord    = PricingRate::findOrFail($id);
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'type'      => $this->type,
            'label'     => $this->label,
            'rate'      => $this->rate,
            'is_active' => $this->is_active,
        ];

        if ($this->editId) {
            $record = PricingRate::findOrFail($this->editId);
            $record->update($data);

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'price_change', 'type' => 'pricing_rate'])
                ->log('Pricing rate updated: ' . $record->label);

            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $record = PricingRate::create($data);

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'price_change', 'type' => 'pricing_rate'])
                ->log('New pricing rate created: ' . $record->label);

            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = PricingRate::findOrFail($this->deleteId);

        // ── Activity Log (before delete) ─────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'price_change', 'type' => 'pricing_rate'])
            ->log('Pricing rate deleted: ' . $record->label);

        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    public function toggleStatus(int $id): void
    {
        $record    = PricingRate::findOrFail($id);
        $newStatus = ! $record->is_active;
        $record->update(['is_active' => $newStatus]);

        // ── Activity Log ───────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'price_change', 'type' => 'pricing_rate'])
            ->log('Pricing rate status changed to ' . ($newStatus ? 'active' : 'inactive') . ': ' . $record->label);

        $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['type', 'label', 'rate', 'editId']);
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $rates = PricingRate::query()
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('type', 'like', "%{$this->search}%")
                       ->orWhere('label', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterStatus !== '', fn ($q) => $q->where('is_active', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.super-admin.settings.pricing-rate-component')
            ->with('rates', $rates)
            ->layout('layouts.superadmin.app', [
                'title' => "Pricing Rates | School SaaS",
            ]);
    }
}