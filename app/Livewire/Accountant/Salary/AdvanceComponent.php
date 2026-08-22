<?php

namespace App\Livewire\Accountant\Salary;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\SalaryAdvance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class AdvanceComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ── List ──────────────────────────────────────────────────────
    public string $search  = '';
    public int    $perPage = 10;

    // ── Modal ─────────────────────────────────────────────────────
    public bool $showModal     = false;
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    // ── Form fields ───────────────────────────────────────────────
    public ?int   $employee_id         = null;
    public string $amount              = '';
    public string $installment_amount  = '';
    public string $advance_date        = '';
    public string $reason              = '';

    public string $routePrefix = '';

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();

        $this->advance_date = now()->format('Y-m-d');
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

    protected function failedValidation(Validator $validator)
    {
        $this->dispatch('toast', type: 'error', message: $validator->errors()->first());

        throw new ValidationException($validator);
    }

    public function rules(): array
    {
        return [
            'employee_id'         => [
                'required',
                'exists:employees,id',
                // Business rule: one employee can only have ONE active (unsettled)
                // advance at a time — this keeps the auto-deduction logic in
                // AddPaymentComponent simple and unambiguous.
                Rule::unique('salary_advances', 'employee_id')
                    ->where(fn ($q) => $q->where('institution_id', institution()->id)->where('status', 'active')),
            ],
            'amount'              => 'required|numeric|min:1',
            'installment_amount'  => 'nullable|numeric|min:0.01|lte:amount',
            'advance_date'        => 'required|date',
            'reason'              => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.unique' => 'This employee already has an active (unsettled) advance.',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function resetForm(): void
    {
        $this->reset(['employee_id', 'amount', 'installment_amount', 'reason']);
        $this->advance_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate($this->rules());

        DB::beginTransaction();

        try {
            $advance = SalaryAdvance::create([
                'institution_id'      => institution()->id,
                'employee_id'         => $this->employee_id,
                'amount'              => $this->amount,
                'remaining_amount'    => $this->amount, // fully outstanding at creation
                'installment_amount'  => $this->installment_amount ?: null,
                'advance_date'        => $this->advance_date,
                'reason'              => $this->reason ?: null,
                'status'              => 'active',
                'created_by'          => auth()->id(),
            ]);

            activity()
                ->performedOn($advance)
                ->withProperties([
                    'institution_id' => institution()->id,
                    'employee_id'    => $this->employee_id,
                    'amount'         => $this->amount,
                ])
                ->log('Salary Advance Issued');

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Advance issued successfully!');
            $this->showModal = false;
            $this->resetForm();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'An error occurred while issuing the advance.');
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
            $advance = SalaryAdvance::withCount('repayments')->findOrFail($this->deleteId);

            // Guard: once any repayment has been deducted, deleting the advance
            // would break the audit trail (repayments referencing a missing
            // parent). Only un-touched advances (no repayments yet) can be deleted.
            if ($advance->repayments_count > 0) {
                DB::rollBack();
                $this->dispatch('toast', type: 'error', message: 'Cannot delete: this advance already has repayment history.');
                $this->confirmDelete = false;
                return;
            }

            activity()
                ->performedOn($advance)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Salary Advance Deleted');

            $advance->delete();

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId      = null;
            $this->dispatch('toast', type: 'success', message: 'Advance deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'An error occurred while deleting the advance.');
        }
    }

    public function render()
    {
        $employees = Employee::orderBy('name')->get(['id', 'name', 'employee_id']);

        $advances = SalaryAdvance::with('employee')
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->whereHas('employee', fn ($eq) => $eq->where('name', 'like', $s)->orWhere('employee_id', 'like', $s));
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.admin.salary.advance-component', [
            'employees' => $employees,
            'advances'  => $advances,
        ])->layout('layouts.accountant.app', [
            'title' => 'Salary Advance | ' . institution()->name,
        ]);
    }
}