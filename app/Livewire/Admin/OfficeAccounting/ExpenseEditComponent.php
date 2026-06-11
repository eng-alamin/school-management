<?php

namespace App\Livewire\Admin\OfficeAccounting;

use Livewire\Component;
use App\Models\OfficeAccount;
use App\Models\OfficeHead;
use App\Models\OfficeExpense;

use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ExpenseEditComponent extends Component
{
    use WithFileUploads;

    public OfficeExpense $expense;

    public $account_id  = '';
    public $head_id     = '';
    public $pay_via     = '';
    public $reference   = '';
    public $amount      = '';
    public $date        = '';
    public $description = '';
    public $attachment  = null;

    public $existing_attachment = null;
    public $remove_attachment   = false;

    public function mount($id)
    {
        $expense = OfficeExpense::findOrFail($id);

        $this->expense            = $expense;
        $this->account_id         = $expense->account_id;
        $this->head_id            = $expense->head_id ?? '';
        $this->pay_via            = $expense->pay_via ?? '';
        $this->reference          = $expense->reference ?? '';
        $this->amount             = $expense->amount;
        $this->date               = $expense->date?->format('Y-m-d');
        $this->description        = $expense->description ?? '';
        $this->existing_attachment = $expense->attachment;
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function rules()
    {
        return [
            'account_id'        => 'required|exists:office_accounts,id',
            'head_id'           => 'nullable|exists:office_heads,id',
            'pay_via'           => 'nullable|string|max:100',
            'reference'         => 'nullable|string|max:255',
            'amount'            => 'required|numeric|min:0',
            'date'              => 'required|date',
            'description'       => 'nullable|string',
            'attachment'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'remove_attachment' => 'boolean',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function removeAttachment()
    {
        $this->remove_attachment   = true;
        $this->existing_attachment = null;
    }

    public function update()
    {
        try {
            $this->validate($this->rules());

            $attachmentPath = $this->expense->attachment;

            if ($this->remove_attachment) {
                if ($attachmentPath && \Storage::disk('public')->exists($attachmentPath)) {
                    \Storage::disk('public')->delete($attachmentPath);
                }
                $attachmentPath = null;
            }

            if ($this->attachment) {
                if ($attachmentPath && \Storage::disk('public')->exists($attachmentPath)) {
                    \Storage::disk('public')->delete($attachmentPath);
                }
                $attachmentPath = $this->attachment->store('office-expenses', 'public');
            }

            $this->expense->update([
                'account_id'  => $this->account_id,
                'head_id'     => $this->head_id ?: null,
                'pay_via'     => $this->pay_via ?: null,
                'reference'   => $this->reference ?: null,
                'amount'      => $this->amount,
                'date'        => $this->date,
                'description' => $this->description ?: null,
                'attachment'  => $attachmentPath,
            ]);

            $this->existing_attachment = $attachmentPath;
            $this->attachment          = null;
            $this->remove_attachment   = false;

            $this->dispatch('toast', type: 'success', message: 'Expense updated successfully!');

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'An error occurred while updating the expense.');
            throw $e;
        }
    }

    public function render()
    {
        $accounts = OfficeAccount::all();
        $heads    = OfficeHead::all();

        return view('livewire.admin.office-accounting.expense-edit-component')
            ->with('accounts', $accounts)
            ->with('heads', $heads)
            ->layout('layouts.admin.app', [
                'title' => 'Edit Expense | Monarchy School',
            ]);
    }
}