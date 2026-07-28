<?php

namespace App\Livewire\Admin\StudentAccounting;

use App\Models\AcademicClassAssign;
use App\Models\FeeSetup;
use App\Models\FeeType;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FeeSetupComponent extends Component
{
    // grid data: [class_id][fee_type_id] = ['amount' => x, 'status' => bool, 'frequency' => .., 'billing_month' => ..]
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
    }

    public function loadData()
    {
        $this->classes = AcademicClassAssign::with('class')
            ->get()
            ->pluck('class')
            ->filter()
            ->unique('id')
            ->sortBy('numeric')
            ->values();

        $this->feeTypes = FeeType::query()
            ->where('status', true)
            ->orderBy('id')
            ->get();

        $existing = FeeSetup::query()
            ->get()
            ->keyBy(fn ($row) => $row->class_id . '_' . $row->fee_type_id);

        $grid = [];
        foreach ($this->classes as $class) {
            foreach ($this->feeTypes as $feeType) {
                $key = $class->id . '_' . $feeType->id;
                $row = $existing->get($key);

                $grid[$class->id][$feeType->id] = [
                    'amount'        => $row?->amount !== null ? number_format($row->amount, 0, '.', '') : '',
                    'status'        => $row?->status ?? true,
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
                $amountField  = "grid.{$class->id}.{$feeType->id}.amount";
                $freqField    = "grid.{$class->id}.{$feeType->id}.frequency";
                $monthField   = "grid.{$class->id}.{$feeType->id}.billing_month";

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
            '*.amount.numeric'        => 'শুধু সংখ্যা দিতে হবে।',
            '*.amount.min'            => 'Amount ঋণাত্মক (negative) হতে পারবে না।',
            '*.frequency.required'    => 'Frequency সিলেক্ট করতে হবে।',
            '*.billing_month.required_if' => 'Yearly হলে Billing Month সিলেক্ট করতে হবে।',
            '*.billing_month.between' => 'Billing Month ১ থেকে ১২ এর মধ্যে হতে হবে।',
        ];
    }

    public function failedValidation(\Illuminate\Validation\Validator $validator)
    {
        $this->dispatch('toast', type: 'error', message: 'ফর্মে কিছু ভুল আছে, দয়া করে চেক করুন।');

        throw new \Illuminate\Validation\ValidationException($validator);
    }

    // Frequency পরিবর্তন হলে Yearly না হলে Billing Month রিসেট করে দেওয়া
    public function updatedGrid($value, $key)
    {
        // $key এর ফরম্যাট হবে "classId.feeTypeId.frequency" অথবা অন্য কিছু
        if (str_ends_with($key, '.frequency')) {
            [$classId, $feeTypeId] = explode('.', $key);

            if ($value !== 'yearly') {
                $this->grid[$classId][$feeTypeId]['billing_month'] = null;
            }
        }
    }

    public function save()
    {
        $this->validate();

        $institutionId = institution()->id;

        DB::beginTransaction();

        try {
            foreach ($this->grid as $classId => $feeTypeRow) {
                foreach ($feeTypeRow as $feeTypeId => $data) {

                    // amount blank rakhle oi combination save/skip korbo, delete korbo na
                    // karon already invoice generate hoye thakte pare
                    if ($data['amount'] === '' || $data['amount'] === null) {
                        continue;
                    }

                    $frequency = $data['frequency'] ?? 'monthly';

                    FeeSetup::updateOrCreate(
                        [
                            'institution_id' => $institutionId,
                            'class_id'       => $classId,
                            'fee_type_id'    => $feeTypeId,
                        ],
                        [
                            'amount'        => $data['amount'],
                            'status'        => $data['status'] ?? true,
                            'frequency'     => $frequency,
                            // yearly না হলে billing_month সবসময় null থাকবে
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

            $this->dispatch('toast', type: 'success', message: 'Fee Setup সফলভাবে সেভ হয়েছে।');
            $this->loadData();

        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            $this->dispatch('toast', type: 'error', message: 'সেভ করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        }
    }

    public function toggleStatus($classId, $feeTypeId)
    {
        $current = $this->grid[$classId][$feeTypeId]['status'] ?? true;
        $this->grid[$classId][$feeTypeId]['status'] = ! $current;
    }

    public function render()
    {
        return view('livewire.admin.student-accounting.fee-setup-component')
            ->layout('layouts.admin.app', [
                'title' => 'Fee Setup | ' . institution()->name,
            ]);
    }
}