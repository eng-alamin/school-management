<?php
// app/Livewire/Admin/Information/InstitutionCommitteeComponent.php

namespace App\Livewire\Admin\Information;

use Livewire\Component;
use App\Models\InstitutionCommittee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class InstitutionCommitteeComponent extends Component
{
    use WithPagination, WithFileUploads;

    private const SORTABLE_FIELDS = ['name', 'designation', 'display_order', 'status', 'created_at'];

    public string $search = '';
    public string $filterStatus = '';
    public string $sortField = 'display_order';
    public string $sortDirection = 'asc';
    public int $perPage = 15;

    // Form state
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $designation = '';
    public $photo = null;
    public ?string $existingPhoto = null;
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public ?string $term_start_date = null;
    public ?string $term_end_date = null;
    public string $status = 'active';

    // View modal
    public bool $showViewModal = false;
    public ?InstitutionCommittee $viewRecord = null;

    // Delete
    public bool $confirmDelete = false;
    public ?int $deletingId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'designation' => ['required', 'string', 'max:100'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:1000'],
            'term_start_date' => ['nullable', 'date'],
            'term_end_date' => ['nullable', 'date', 'after_or_equal:term_start_date'],
            'status' => ['required', 'in:active,former'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
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
    }

    public function openAddModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        // Defense-in-depth: explicit institution scope, global scope-er upor bhorsa na kore
        $committee = InstitutionCommittee::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->findOrFail($id);

        $this->editingId = $committee->id;
        $this->name = $committee->name;
        $this->designation = $committee->designation;
        $this->existingPhoto = $committee->photo;
        $this->phone = (string) $committee->phone;
        $this->email = (string) $committee->email;
        $this->address = (string) $committee->address;
        $this->term_start_date = $committee->term_start_date?->format('Y-m-d');
        $this->term_end_date = $committee->term_end_date?->format('Y-m-d');
        $this->status = $committee->status;
        $this->photo = null;

        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord = InstitutionCommittee::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->with('creator')
            ->findOrFail($id);

        $this->showViewModal = true;
    }

    public function toggleStatus(int $id): void
    {
        $committee = InstitutionCommittee::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->findOrFail($id);

        $newStatus = $committee->status === 'active' ? 'former' : 'active';

        $committee->update([
            'status' => $newStatus,
            'updated_by' => Auth::id(),
        ]);

        activity()
            ->performedOn($committee)
            ->causedBy(Auth::user())
            ->tap(fn ($activity) => $activity->institution_id = $committee->institution_id)
            ->log('Committee member status changed to ' . $newStatus);

        $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $photoPath = $this->existingPhoto;

            if ($this->photo) {
                $photoPath = $this->photo->store('committees', 'public');
            }

            if ($this->editingId) {
                $committee = InstitutionCommittee::query()
                    ->where('institution_id', Auth::user()->institution_id)
                    ->findOrFail($this->editingId);

                if ($this->photo && $committee->photo) {
                    Storage::disk('public')->delete($committee->photo);
                }

                $committee->update([
                    'name' => $this->name,
                    'designation' => $this->designation,
                    'photo' => $photoPath,
                    'phone' => $this->phone ?: null,
                    'email' => $this->email ?: null,
                    'address' => $this->address ?: null,
                    'term_start_date' => $this->term_start_date,
                    'term_end_date' => $this->term_end_date,
                    'status' => $this->status,
                    'updated_by' => Auth::id(),
                ]);

                activity()
                    ->performedOn($committee)
                    ->causedBy(Auth::user())
                    ->tap(fn ($activity) => $activity->institution_id = $committee->institution_id)
                    ->log('Committee member updated');
            } else {
                // institution-scoped max order, na hoile onno institution-er order-e collide korte pare
                $maxOrder = InstitutionCommittee::query()
                    ->where('institution_id', Auth::user()->institution_id)
                    ->max('display_order') ?? 0;

                // institution_id ekhane explicit rakha holo — BelongsToInstitution trait
                // thakleo write-time e ata safe-explicit approach, ??= diye override korbe na
                $committee = InstitutionCommittee::create([
                    'institution_id' => Auth::user()->institution_id,
                    'name' => $this->name,
                    'designation' => $this->designation,
                    'photo' => $photoPath,
                    'phone' => $this->phone ?: null,
                    'email' => $this->email ?: null,
                    'address' => $this->address ?: null,
                    'term_start_date' => $this->term_start_date,
                    'term_end_date' => $this->term_end_date,
                    'display_order' => $maxOrder + 1,
                    'status' => $this->status,
                    'created_by' => Auth::id(),
                ]);

                activity()
                    ->performedOn($committee)
                    ->causedBy(Auth::user())
                    ->tap(fn ($activity) => $activity->institution_id = $committee->institution_id)
                    ->log('Committee member added');
            }

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Committee member saved successfully.');
            $this->showModal = false;
            $this->resetForm();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deletingId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        if (! $this->deletingId) {
            return;
        }

        DB::beginTransaction();

        try {
            $committee = InstitutionCommittee::query()
                ->where('institution_id', Auth::user()->institution_id)
                ->findOrFail($this->deletingId);

            activity()
                ->performedOn($committee)
                ->causedBy(Auth::user())
                ->tap(fn ($activity) => $activity->institution_id = $committee->institution_id)
                ->log('Committee member deleted');

            $committee->delete(); // soft delete, photo file kept intentionally for audit/restore

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Committee member deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Delete failed. Please try again.');
        } finally {
            $this->confirmDelete = false;
            $this->deletingId = null;
        }
    }

    public function moveUp(int $id): void
    {
        $this->reorder($id, -1);
    }

    public function moveDown(int $id): void
    {
        $this->reorder($id, 1);
    }

    private function reorder(int $id, int $direction): void
    {
        $institutionId = Auth::user()->institution_id;

        DB::beginTransaction();

        try {
            $current = InstitutionCommittee::query()
                ->where('institution_id', $institutionId)
                ->lockForUpdate()
                ->findOrFail($id);

            $neighbor = InstitutionCommittee::query()
                ->where('institution_id', $institutionId)
                ->where('id', '!=', $current->id)
                ->when($direction < 0,
                    fn ($q) => $q->where('display_order', '<=', $current->display_order)->orderByDesc('display_order'),
                    fn ($q) => $q->where('display_order', '>=', $current->display_order)->orderBy('display_order')
                )
                ->lockForUpdate()
                ->first();

            if ($neighbor) {
                $currentOrder = $current->display_order;
                $current->update(['display_order' => $neighbor->display_order]);
                $neighbor->update(['display_order' => $currentOrder]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Reorder failed.');
        }
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'name', 'designation', 'photo', 'existingPhoto',
            'phone', 'email', 'address', 'term_start_date', 'term_end_date',
        ]);
        $this->status = 'active';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $query = InstitutionCommittee::query()
            // Defense-in-depth: global scope thakleo explicit where rakha holo
            ->where('institution_id', Auth::user()->institution_id)
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('designation', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy($this->sortField, $this->sortDirection);

        $committees = $query->paginate($this->perPage);

        return view('livewire.admin.information.institution-committee-component', [
            'committees' => $committees,
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Committee Member | ' . institution()->name,
        ]);
    }
}