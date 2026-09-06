<?php

namespace App\Livewire\Admin\StudentAccounting;

use App\Models\AcademicClassAssign;
use App\Models\Branch;
use App\Models\FeeSetup;
use App\Models\FeeType;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FeeSetupComponent extends Component
{
    public array $grid = [];

    public $classes = [];
    public $feeTypes = [];

    public array $frequencyOptions = [
        'monthly'  => 'Monthly',
        'yearly'   => 'Yearly',
        'one_time' => 'One Time',
    ];

    public function mount()
    {
        $this->loadData();

          if (session()->has('toast_success')) {
            $this->dispatch('toast', type: 'success', message: session()->pull('toast_success'));
        }
    }

    /**
     * বর্তমান Request-এ যেই Branch Context-এ কাজ হচ্ছে সেটা resolve করে।
     * institution() হেল্পার থেকে institution_id নিয়ে Branch::resolveMainBranchId()
     * ফলব্যাক হিসেবে ব্যবহার করা হচ্ছে, প্রজেক্টের প্রতিষ্ঠিত Pattern অনুযায়ী।
     */
    protected function activeBranchId(): int
    {
        $institutionId = institution()->id;

        return session('active_branch_id')
            ?? Branch::resolveMainBranchId($institutionId);
    }

    public function loadData()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $this->classes = AcademicClassAssign::with('academicClass')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->whereHas('session', function ($q) {
                $q->where('is_current', true);
            })
            ->get()
            ->pluck('academicClass')
            ->filter()
            ->unique('id')
            ->sortBy('numeric')
            ->values();

        $this->feeTypes = FeeType::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('status', true)
            ->orderBy('id')
            ->get();

        $existing = FeeSetup::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->get()
            ->keyBy(fn ($row) => $row->class_id . '_' . $row->fee_type_id);

        $grid = [];
        foreach ($this->classes as $class) {
            foreach ($this->feeTypes as $feeType) {
                $key = $class->id . '_' . $feeType->id;
                $row = $existing->get($key);

                $grid[$class->id][$feeType->id] = [
                    'amount'        => $row?->amount !== null ? number_format($row->amount, 0, '.', '') : '',
                    'frequency'     => $row?->frequency ?? 'monthly',
                    'billing_month' => $row?->billing_month ?? null,
                ];
            }
        }

        $this->grid = $grid;
    }

    public function rules(): array
    {
        $rules = [];

        foreach ($this->classes as $class) {
            foreach ($this->feeTypes as $feeType) {
                $amountField = "grid.{$class->id}.{$feeType->id}.amount";
                $freqField   = "grid.{$class->id}.{$feeType->id}.frequency";
                $monthField  = "grid.{$class->id}.{$feeType->id}.billing_month";

                $rules[$amountField] = ['nullable', 'numeric', 'min:0', 'max:9999999.99'];
                $rules[$freqField]   = ['required', 'in:monthly,yearly,one_time'];
                $rules[$monthField]  = ['nullable', "required_if:{$freqField},yearly", 'integer', 'between:1,12'];
            }
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            '*.amount.numeric'             => 'শুধু সংখ্যা দিতে হবে।',
            '*.amount.min'                 => 'Amount ঋণাত্মক (negative) হতে পারবে না।',
            '*.frequency.required'         => 'Frequency সিলেক্ট করতে হবে।',
            '*.billing_month.required_if'  => 'Yearly হলে Billing Month সিলেক্ট করতে হবে।',
            '*.billing_month.between'      => 'Billing Month ১ থেকে ১২ এর মধ্যে হতে হবে।',
        ];
    }

    public function failedValidation(\Illuminate\Validation\Validator $validator)
    {
        $this->dispatch('toast', type: 'error', message: 'ফর্মে কিছু ভুল আছে, দয়া করে চেক করুন।');

        throw new \Illuminate\Validation\ValidationException($validator);
    }

    public function save()
    {
        $this->validate();

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        DB::beginTransaction();

        try {
            foreach ($this->grid as $classId => $feeTypeRow) {
                foreach ($feeTypeRow as $feeTypeId => $data) {

                    if ($data['amount'] === '' || $data['amount'] === null) {
                        continue;
                    }

                    $frequency = $data['frequency'] ?? 'monthly';

                    FeeSetup::updateOrCreate(
                        [
                            'institution_id' => $institutionId,
                            'branch_id'      => $branchId,
                            'class_id'       => $classId,
                            'fee_type_id'    => $feeTypeId,
                        ],
                        [
                            'amount'        => $data['amount'],
                            'frequency'     => $frequency,
                            // yearly না হলে billing_month সবসময় null থাকবে (backend enforced, frontend এর উপর নির্ভর নয়)
                            'billing_month' => $frequency === 'yearly' ? ($data['billing_month'] ?? null) : null,
                        ]
                    );
                }
            }

            activity()
                ->tap(function ($activity) use ($institutionId) {
                    $activity->institution_id = $institutionId;
                })
                ->log('Fee Setup updated');

            DB::commit();

            session()->flash('toast_success', 'Data saved successfully!');
            $this->redirectRoute('admin.student-accounting.fee.setups');
            // $this->redirectRoute('admin.student-accounting.fee.setups', navigate: true);

            // $this->dispatch('toast', type: 'success', message: 'Fee Setup সফলভাবে সেভ হয়েছে।');
            // $this->loadData();

        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            $this->dispatch('toast', type: 'error', message: 'সেভ করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        }
    }

    public function render()
    {
        return view('livewire.admin.student-accounting.fee-setup-component')
            ->layout('layouts.admin.app', [
                'title' => 'Fee Setup | ' . institution()->name,
            ]);
    }
}