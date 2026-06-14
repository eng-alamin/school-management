<?php

namespace App\Livewire\Accountant\OfficeAccounting;

use Livewire\Component;
use App\Models\OfficeAccount;
use App\Models\OfficeHead;
use App\Models\OfficeExpense;

use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ExpenseAddComponent extends Component
{
    use WithFileUploads;

    public $account_id  = '';
    public $head_id     = '';
    public $pay_via     = '';
    public $reference   = '';
    public $amount      = '';
    public $date        = '';
    public $description = '';
    public $attachment  = null;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function resetForm()
    {
        $this->reset();
        $this->date = now()->format('Y-m-d');
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function rules()
    {
        return [
            'account_id'  => 'required|exists:office_accounts,id',
            'head_id'     => 'required|exists:office_heads,id',
            'pay_via'     => 'nullable|string|max:100',
            'reference'   => 'nullable|string|max:255',
            'amount'      => 'required|numeric|min:0',
            'date'        => 'required|date',
            'description' => 'nullable|string',
            'attachment'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function save()
    {
        try {
            $this->validate($this->rules());

            $attachmentPath = $this->attachment?->store('office-expenses', 'public');

            OfficeExpense::create([
                'account_id'  => $this->account_id,
                'head_id'     => $this->head_id ?: null,
                'pay_via'     => $this->pay_via ?: null,
                'reference'   => $this->reference ?: null,
                'amount'      => $this->amount,
                'date'        => $this->date,
                'description' => $this->description ?: null,
                'attachment'  => $attachmentPath,
            ]);

            $this->dispatch('toast', type: 'success', message: 'Expense created successfully!');
            $this->resetForm();

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'An error occurred while creating the expense.');
            throw $e;
        }
    }

    public function render()
    {
        $accounts = OfficeAccount::all();
        $heads    = OfficeHead::all();

        return view('livewire.accountant.office-accounting.expense-add-component')
            ->with('accounts', $accounts)
            ->with('heads', $heads)
            ->layout('layouts.accountant.app', [
                'title' => 'Add Expense | Monarchy School',
            ]);
    }
}