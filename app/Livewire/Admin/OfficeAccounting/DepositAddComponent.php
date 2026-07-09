<?php

namespace App\Livewire\Admin\OfficeAccounting;

use Livewire\Component;
use App\Models\OfficeAccount;
use App\Models\OfficeHead;
use App\Models\OfficeDeposit;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class DepositAddComponent extends Component
{
    use WithFileUploads;

    public $account_id = '';
    public $head_id = '';
    public $pay_via = '';
    public $reference = '';
    public $amount = '';
    public $date = '';
    public $description = '';
    public $attachment = null;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function resetForm()
    {
        $this->reset();
        $this->date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    protected function failedValidation(Validator $validator)
    {
        $this->dispatch('toast', type: 'error', message: $validator->errors()->first());

        throw new ValidationException($validator);
    }

    public function rules()
    {
        return [
            'account_id' => 'required|exists:office_accounts,id',
            'head_id' => 'nullable|exists:office_heads,id',
            'pay_via' => 'nullable|string|max:100',
            'reference' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function save()
    {
        $this->validate($this->rules());

        DB::beginTransaction();

        try {
            $attachmentPath = $this->attachment?->store('office-deposits', 'public');

            $deposit = OfficeDeposit::create([
                'institution_id' => institution()->id,
                'account_id' => $this->account_id,
                'head_id' => $this->head_id ?: null,
                'voucher_no' => OfficeDeposit::generateVoucherNo(institution()->id),
                'pay_via' => $this->pay_via ?: null,
                'reference' => $this->reference ?: null,
                'amount' => $this->amount,
                'date' => $this->date,
                'description' => $this->description ?: null,
                'attachment' => $attachmentPath,
                'created_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($deposit)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Office Deposit Created');

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Deposit created successfully!');
            $this->resetForm();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'An error occurred while creating the deposit.');
        }
    }

    public function render()
    {
        $accounts = OfficeAccount::where('is_active', true)->get();
        $heads = OfficeHead::where('type', 'deposit')->where('is_active', true)->get();

        return view('livewire.admin.office-accounting.deposit-add-component')
            ->with('accounts', $accounts)
            ->with('heads', $heads)
            ->layout('layouts.admin.app', [
                'title' => 'Add Deposit | ' . institution()->name,
            ]);
    }
}