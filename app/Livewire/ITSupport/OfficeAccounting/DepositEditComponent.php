<?php

namespace App\Livewire\ITSupport\OfficeAccounting;

use Livewire\Component;
use App\Models\OfficeAccount;
use App\Models\OfficeHead;
use App\Models\OfficeDeposit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class DepositEditComponent extends Component
{
    use WithFileUploads;

    public OfficeDeposit $deposit;

    public $account_id = '';
    public $head_id = '';
    public $pay_via = '';
    public $reference = '';
    public $amount = '';
    public $date = '';
    public $description = '';
    public $attachment = '';

    public $existing_attachment = null;
    public $remove_attachment = false;

    public function mount($id)
    {
        $deposit = OfficeDeposit::findOrFail($id);

        $this->deposit = $deposit;
        $this->account_id = $deposit->account_id;
        $this->head_id = $deposit->head_id ?? '';
        $this->pay_via = $deposit->pay_via ?? '';
        $this->reference = $deposit->reference ?? '';
        $this->amount = $deposit->amount;
        $this->date = $deposit->date?->format('Y-m-d');
        $this->description = $deposit->description ?? '';
        $this->existing_attachment = $deposit->attachment;
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
            // ── attachment তখনই required, যখন existing_attachment নেই (বা remove করা হয়েছে)
            // এবং নতুন কোনো ফাইলও আপলোড করা হয়নি। পুরনো attachment থেকে গেলে নতুন ফাইল
            // না দিলেও validation pass করবে। ──
            'attachment' => [
                Rule::requiredIf(fn () => !$this->existing_attachment && !$this->attachment),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:2048',
            ],
            'remove_attachment' => 'boolean',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function removeAttachment()
    {
        $this->remove_attachment = true;
        $this->existing_attachment = null;
    }

    public function update()
    {
        $this->validate($this->rules());

        DB::beginTransaction();

        try {
            $attachmentPath = $this->deposit->attachment;

            if ($this->remove_attachment) {
                if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                    Storage::disk('public')->delete($attachmentPath);
                }
                $attachmentPath = null;
            }

            if ($this->attachment) {
                if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                    Storage::disk('public')->delete($attachmentPath);
                }
                $attachmentPath = $this->attachment->store('office-deposits', 'public');
            }

            $this->deposit->update([
                'account_id' => $this->account_id,
                'head_id' => $this->head_id ?: null,
                'pay_via' => $this->pay_via ?: null,
                'reference' => $this->reference ?: null,
                'amount' => $this->amount,
                'date' => $this->date,
                'description' => $this->description ?: null,
                'attachment' => $attachmentPath,
            ]);

            activity()
                ->performedOn($this->deposit)
                ->withProperties(['institution_id' => institution()->id])
                ->log('Office Deposit Updated');

            DB::commit();

            $this->existing_attachment = $attachmentPath;
            $this->attachment = null;
            $this->remove_attachment = false;

            $this->dispatch('toast', type: 'success', message: 'Deposit updated successfully!');

            $this->redirect(route('itsupport.office-accounting.deposit.list'), navigate: true);

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'An error occurred while updating the deposit.');
        }
    }

    public function render()
    {
        $accounts = OfficeAccount::where('is_active', true)->get();
        $heads = OfficeHead::where('type', 'deposit')->where('is_active', true)->get();

        return view('livewire.admin.office-accounting.deposit-edit-component')
            ->with('accounts', $accounts)
            ->with('heads', $heads)
            ->layout('layouts.itsupport.app', [
                'title' => 'Edit Deposit | ' . institution()->name,
            ]);
    }
}