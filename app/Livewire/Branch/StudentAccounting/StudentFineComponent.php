<?php

namespace App\Livewire\Branch\StudentAccounting;

use App\Models\Student;
use App\Models\StudentFine;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class StudentFineComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public string $filterStatus = 'all';
    public string $filterReason = 'all';
    public int $perPage = 10;
    public string $sortField = 'fine_date';
    public string $sortDirection = 'desc';

    // Modal
    public bool $showModal = false;
    public bool $showViewModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;
    public ?int $viewId = null;

    // Form
    public ?int $editId = null;
    public ?int $student_id = null;
    public string $reason = 'absent';
    public string $amount = '';
    public string $fine_date = '';
    public string $remarks = '';

    public array $reasonOptions = [
        'absent'         => 'Absent',
        'indiscipline'   => 'Indiscipline',
        'library_damage' => 'Library Damage',
        'uniform'        => 'Uniform',
        'other'          => 'Other',
    ];

    public function mount(): void
    {
        $this->fine_date = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'reason'     => 'required|in:absent,indiscipline,library_damage,uniform,other',
            'amount'     => 'required|numeric|min:0|max:999999.99',
            'fine_date'  => 'required|date',
            'remarks'    => 'nullable|string|max:500',
        ];
    }

    protected function messages(): array
    {
        return [
            'student_id.required' => 'Student সিলেক্ট করা আবশ্যক।',
            'amount.min'           => 'Amount ঋণাত্মক (negative) হতে পারবে না।',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $this->dispatch('toast', type: 'error', message: $validator->errors()->first());

        throw new ValidationException($validator);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterReason(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

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
        $record = StudentFine::findOrFail($id);

        if ($record->status !== 'pending') {
            $this->dispatch('toast', type: 'error', message: 'এই Fine ইতিমধ্যে Invoice-এ যুক্ত হয়ে গেছে, তাই এডিট করা যাবে না।');
            return;
        }

        $this->editId     = $id;
        $this->student_id = $record->student_id;
        $this->reason     = $record->reason;
        $this->amount     = (string) $record->amount;
        $this->fine_date  = $record->fine_date->toDateString();
        $this->remarks    = (string) $record->remarks;

        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewId = $id;
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $data = [
                'institution_id' => institution()->id,
                'student_id'     => $this->student_id,
                'reason'         => $this->reason,
                'amount'         => $this->amount,
                'fine_date'      => $this->fine_date,
                'remarks'        => $this->remarks,
            ];

            if ($this->editId) {
                $fine = StudentFine::findOrFail($this->editId);

                if ($fine->status !== 'pending') {
                    $this->dispatch('toast', type: 'error', message: 'এই Fine ইতিমধ্যে Invoice-এ যুক্ত হয়ে গেছে, তাই এডিট করা যাবে না।');
                    DB::rollBack();
                    return;
                }

                $fine->update($data);
                $activityMessage = 'Student Fine Updated for: ' . $fine->student->name;
            } else {
                $data['status']     = 'pending';
                $data['created_by'] = Auth::id();

                $fine = StudentFine::create($data);
                $activityMessage = 'Student Fine Created for: ' . $fine->student->name;
            }

            activity()
                ->performedOn($fine)
                ->withProperties([
                    'icon'           => 'gavel',
                    'type'           => $this->editId ? 'update' : 'create',
                    'institution_id' => institution()->id,
                ])
                ->log($activityMessage);

            DB::commit();

            $this->showModal = false;
            $this->resetForm();

            $this->dispatch('toast', type: 'success', message: $this->editId ? 'Fine updated successfully!' : 'Fine created successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    private function resetForm(): void
    {
        $this->reset(['student_id', 'amount', 'remarks', 'editId']);
        $this->reason    = 'absent';
        $this->fine_date = now()->toDateString();
        $this->resetValidation();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $record = StudentFine::findOrFail($id);

        if ($record->status !== 'pending') {
            $this->dispatch('toast', type: 'error', message: 'ইতিমধ্যে Invoice-এ যুক্ত Fine ডিলিট করা যাবে না।');
            return;
        }

        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        DB::beginTransaction();

        try {
            $fine = StudentFine::with('student')->findOrFail($this->deleteId);

            if ($fine->status !== 'pending') {
                $this->dispatch('toast', type: 'error', message: 'ইতিমধ্যে Invoice-এ যুক্ত Fine ডিলিট করা যাবে না।');
                DB::rollBack();
                $this->confirmDelete = false;
                return;
            }

            activity()
                ->performedOn($fine)
                ->withProperties([
                    'icon'           => 'delete',
                    'type'           => 'delete',
                    'institution_id' => institution()->id,
                ])
                ->log('Student Fine Deleted for: ' . ($fine->student->name ?? '—'));

            $fine->delete();

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId = null;

            $this->dispatch('toast', type: 'success', message: 'Fine deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        $studentFines = StudentFine::query()
            ->with(['student', 'feeInvoice'])
            ->when($this->search, fn ($q) => $q->whereHas('student', fn ($q2) =>
                $q2->where('name', 'like', "%{$this->search}%")
                   ->orWhere('student_id', 'like', "%{$this->search}%")
            ))
            ->when($this->filterStatus !== 'all', fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterReason !== 'all', fn ($q) => $q->where('reason', $this->filterReason))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $students = Student::orderBy('name')->get();

        $viewFine = $this->viewId
            ? StudentFine::with('student', 'creator', 'feeInvoice')->find($this->viewId)
            : null;

        return view('livewire.admin.student-accounting.student-fine-component')
            ->with([
                'studentFines' => $studentFines,
                'students'     => $students,
                'viewFine'     => $viewFine,
            ])
            ->layout('layouts.branch.app', [
                'title' => 'Student Fine | ' . institution()->name,
            ]);
    }
}