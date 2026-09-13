<?php

namespace App\Livewire\Admin\Leave;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LeaveCategory;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class CategoryComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ── List / Filter ──
    public string $search        = '';
    public string $roleFilter    = 'all';
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
            'name.unique' => 'A leave category with this name already exists.',
            'days.min'    => 'It must be at least one day.',
        ];
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
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

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

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
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'exit_to_app', 'type' => 'leave'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                        $activity->branch_id = $record->branch_id;
                    })
                    ->log('Leave category updated: ' . $record->name);
            } else {
                $record = LeaveCategory::create([
                    'institution_id' => institution()->id,
                    'branch_id'      => $this->activeBranchId(),
                    'name'           => $this->name,
                    'role'           => $this->role,
                    'days'           => $this->days,
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'exit_to_app', 'type' => 'leave'])
                    ->tap(function ($activity) use ($record) {
                        $activity->institution_id = $record->institution_id;
                        $activity->branch_id = $record->branch_id;
                    })
                    ->log('Leave category created: ' . $record->name);
            }

            DB::commit();

            $this->showModal = false;
            $this->resetForm();
            $this->dispatch('toast', type: 'success', message: $this->editId ? 'Leave category updated successfully!' : 'Leave category created successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong; please try again.');
            report($e);
        }
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
            $record = LeaveCategory::findOrFail($this->deleteId);
            $record->delete();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'exit_to_app', 'type' => 'leave'])
                ->tap(function ($activity) use ($record) {
                    $activity->institution_id = $record->institution_id;
                    $activity->branch_id = $record->branch_id;
                })
                ->log('Leave category deleted: ' . $record->name);

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId      = null;
            $this->dispatch('toast', type: 'success', message: 'Leave category deleted successfully!');
        } catch (QueryException $e) {
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

    private function resetForm(): void
    {
        $this->reset(['name', 'role', 'days', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $categories = LeaveCategory::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->roleFilter !== 'all', fn ($q) => $q->where('role', $this->roleFilter))
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