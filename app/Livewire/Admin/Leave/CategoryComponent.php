<?php

namespace App\Livewire\Admin\Leave;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LeaveCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class CategoryComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ── List / Filter ──
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

    // ── Modal flags ──
    public bool $showModal     = false;
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    // ── Form fields ──
    public ?int   $editId = null;
    public string $name   = '';
    public string $role   = '';
    public ?int   $days   = null;

    // Role অবশ্যই বাকি সব মডিউলের (ApplicationComponent) সাথে consistent থাকতে হবে।
    // 'librarian' এর বদলে 'staff' ব্যবহার করা হচ্ছে যাতে Leave Application module-এর
    // roleModelMap-এর সাথে ঠিকঠাক মিলে।
    public const ROLES = [
        'admin'      => 'Admin',
        'teacher'    => 'Teacher',
        'accountant' => 'Accountant',
        'staff'      => 'Staff',
        'student'    => 'Student',
    ];

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leave_categories', 'name')
                    ->where('institution_id', institution()->id)
                    ->ignore($this->editId),
            ],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique' => 'এই নামে একটি Leave Category ইতিমধ্যে বিদ্যমান।',
            'days.min'    => 'কমপক্ষে ১ দিন হতে হবে।',
        ];
    }

    // ──────────────────────────────────────────
    // Lifecycle
    // ──────────────────────────────────────────
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc')
            ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    // ──────────────────────────────────────────
    // Modal: Create
    // ──────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    // ──────────────────────────────────────────
    // Modal: Edit
    // ──────────────────────────────────────────
    public function openEdit(int $id): void
    {
        $record = LeaveCategory::findOrFail($id);

        $this->editId = $id;
        $this->name   = $record->name;
        $this->role   = $record->role;
        $this->days   = $record->days;

        $this->resetValidation();
        $this->showModal = true;
    }

    // ──────────────────────────────────────────
    // Save (Create / Update)
    // ──────────────────────────────────────────
    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            if ($this->editId) {
                $record = LeaveCategory::findOrFail($this->editId);
                $record->update([
                    'name' => $this->name,
                    'role' => $this->role,
                    'days' => $this->days,
                ]);

                activity()
                    ->performedOn($record)
                    ->causedBy(auth()->user())
                    ->log('Leave category updated');

                $message = 'Leave category updated successfully!';
            } else {
                $record = LeaveCategory::create([
                    'name' => $this->name,
                    'role' => $this->role,
                    'days' => $this->days,
                ]);

                activity()
                    ->performedOn($record)
                    ->causedBy(auth()->user())
                    ->log('Leave category created');

                $message = 'Leave category created successfully!';
            }

            DB::commit();

            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('toast', type: 'success', message: $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'কিছু একটা সমস্যা হয়েছে, আবার চেষ্টা করুন।');
            report($e);
        }
    }

    // ──────────────────────────────────────────
    // Delete
    // ──────────────────────────────────────────
    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        DB::beginTransaction();

        try {
            $record = LeaveCategory::findOrFail($this->deleteId);
            $record->delete();

            activity()
                ->performedOn($record)
                ->causedBy(auth()->user())
                ->log('Leave category deleted');

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId      = null;
            $this->dispatch('toast', type: 'success', message: 'Leave category deleted successfully!');
        } catch (QueryException $e) {
            // leave_applications টেবিলে onDelete('restrict') থাকায়,
            // ব্যবহৃত ক্যাটাগরি ডিলিট করতে গেলে এই এক্সসেপশন আসবে।
            DB::rollBack();
            $this->confirmDelete = false;
            $this->dispatch('toast', type: 'error', message: 'এই ক্যাটাগরিটি ইতিমধ্যে কোনো Leave Application-এ ব্যবহৃত হয়েছে, তাই ডিলিট করা যাবে না।');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->confirmDelete = false;
            $this->dispatch('toast', type: 'error', message: 'কিছু একটা সমস্যা হয়েছে, আবার চেষ্টা করুন।');
            report($e);
        }
    }

    // ──────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────
    private function resetForm(): void
    {
        $this->reset(['name', 'role', 'days', 'editId']);
        $this->resetValidation();
    }

    // ──────────────────────────────────────────
    // Render
    // ──────────────────────────────────────────
    public function render()
    {
        $categories = LeaveCategory::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.leave.category-component', [
            'categories' => $categories,
            'roles'      => self::ROLES,
        ])->layout('layouts.admin.app', [
            'title' => 'Leave Category | ' . institution()->name,
        ]);
    }
}