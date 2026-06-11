<div class="mat-card" style="padding-top:28px">

    <!-- Floating Header -->
    <div class="mat-card-header header-pink-gradient">
        <h5>
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">
                edit
            </span>
            Edit Salary Template
        </h5>
        <p>Update salary template record</p>
    </div>

    <!-- ══ BASIC INFO ══ -->
    <div class="form-section">
        <div class="row g-4">

            <!-- Salary Grade -->
            <div class="col-md-12">
                <div class="input-group input-group-outline">
                    <label class="form-label">Salary Grade <span class="req">*</span></label>
                    <input type="text"
                           wire:model="salary_grade"
                           class="form-control"
                           placeholder=" "
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('salary_grade') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Basic Salary -->
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Basic Salary <span class="req">*</span></label>
                    <input type="number"
                           wire:model.live="basic_salary"
                           class="form-control"
                           placeholder=" "
                           min="0"
                           step="0.01"
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('basic_salary') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <!-- Overtime Rate -->
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Overtime Rate (Per Hour)</label>
                    <input type="number"
                           wire:model.live="overtime_rate"
                           class="form-control"
                           placeholder=" "
                           min="0"
                           step="0.01"
                           onfocus="focused(this)"
                           onfocusout="defocused(this)">
                </div>
                @error('overtime_rate') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

        </div>
    </div>

    <!-- ══ ALLOWANCES & DEDUCTIONS ══ -->
    <div class="form-section">
        <div class="row g-4">

            <!-- Allowances -->
            <div class="col-md-6">
                <div class="mat-card" style="padding:20px">
                    <h6 style="font-weight:600;margin-bottom:16px;color:var(--text)">Allowances</h6>

                    @foreach ($allowances as $index => $allowance)
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Name Of Allowance</label>
                                    <input type="text"
                                           wire:model.live="allowances.{{ $index }}.name"
                                           class="form-control"
                                           placeholder=" "
                                           onfocus="focused(this)"
                                           onfocusout="defocused(this)">
                                </div>
                            </div>
                            <div class="col-auto" style="width:140px">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Amount</label>
                                    <input type="number"
                                           wire:model.live="allowances.{{ $index }}.amount"
                                           class="form-control"
                                           placeholder=" "
                                           min="0"
                                           step="0.01"
                                           onfocus="focused(this)"
                                           onfocusout="defocused(this)">
                                </div>
                            </div>
                            @if (count($allowances) > 1)
                                <div class="col-auto">
                                    <button type="button"
                                            wire:click="removeAllowanceRow({{ $index }})"
                                            class="btn btn-sm btn-outline-danger px-2 py-1">
                                        <span class="material-icons-round" style="font-size:16px">close</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <button type="button"
                            wire:click="addAllowanceRow"
                            class="btn-pink mt-2"
                            style="font-size:.78rem;padding:6px 14px">
                        <span class="material-icons-round" style="font-size:16px">add_circle</span>
                        Add Rows
                    </button>
                </div>
            </div>

            <!-- Deductions + Salary Details -->
            <div class="col-md-6 d-flex flex-column gap-4">

                <!-- Deductions -->
                <div class="mat-card" style="padding:20px">
                    <h6 style="font-weight:600;margin-bottom:16px;color:var(--text)">Deductions</h6>

                    @foreach ($deductions as $index => $deduction)
                        <div class="row g-2 mb-2 align-items-center">
                            <div class="col">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Name Of Deductions</label>
                                    <input type="text"
                                           wire:model.live="deductions.{{ $index }}.name"
                                           class="form-control"
                                           placeholder=" "
                                           onfocus="focused(this)"
                                           onfocusout="defocused(this)">
                                </div>
                            </div>
                            <div class="col-auto" style="width:140px">
                                <div class="input-group input-group-outline">
                                    <label class="form-label">Amount</label>
                                    <input type="number"
                                           wire:model.live="deductions.{{ $index }}.amount"
                                           class="form-control"
                                           placeholder=" "
                                           min="0"
                                           step="0.01"
                                           onfocus="focused(this)"
                                           onfocusout="defocused(this)">
                                </div>
                            </div>
                            @if (count($deductions) > 1)
                                <div class="col-auto">
                                    <button type="button"
                                            wire:click="removeDeductionRow({{ $index }})"
                                            class="btn btn-sm btn-outline-danger px-2 py-1">
                                        <span class="material-icons-round" style="font-size:16px">close</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <button type="button"
                            wire:click="addDeductionRow"
                            class="btn-pink mt-2"
                            style="font-size:.78rem;padding:6px 14px">
                        <span class="material-icons-round" style="font-size:16px">add_circle</span>
                        Add Rows
                    </button>
                </div>

                <!-- Salary Details -->
                <div class="mat-card" style="padding:20px">
                    <h6 style="font-weight:600;margin-bottom:16px;color:var(--text)">Salary Details</h6>

                    <table class="w-100" style="border-collapse:collapse">
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:10px 4px;font-size:.85rem;color:var(--muted)">Basic Salary</td>
                            <td style="padding:10px 4px;text-align:right;font-size:.85rem">
                                <span style="color:var(--muted);margin-right:6px">$</span>
                                <span>{{ number_format((float) $basic_salary, 2) }}</span>
                            </td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:10px 4px;font-size:.85rem;color:var(--muted)">Total Allowance</td>
                            <td style="padding:10px 4px;text-align:right;font-size:.85rem">
                                <span style="color:var(--muted);margin-right:6px">$</span>
                                <span>{{ number_format($this->totalAllowance, 2) }}</span>
                            </td>
                        </tr>
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:10px 4px;font-size:.85rem;color:var(--muted)">Total Deductions</td>
                            <td style="padding:10px 4px;text-align:right;font-size:.85rem">
                                <span style="color:var(--muted);margin-right:6px">$</span>
                                <span>{{ number_format($this->totalDeduction, 2) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:12px 4px;font-size:.9rem;font-weight:700;color:var(--text)">Net Salary</td>
                            <td style="padding:12px 4px;text-align:right;font-size:.9rem;font-weight:700;color:var(--text)">
                                <span style="color:var(--muted);margin-right:6px">$</span>
                                <span>{{ number_format($this->netSalary, 2) }}</span>
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- FORM FOOTER -->
    <div class="form-footer">

        <a href="{{ route('accountant.salary.list-template', ['tenant' => tenant('id')]) }}"
           class="btn-outline">
            <span class="material-icons-round" style="font-size:16px">arrow_back</span>
            Back
        </a>

        <button class="btn-pink"
                type="button"
                wire:click="update"
                wire:loading.attr="disabled"
                wire:target="update">

            <span wire:loading.remove wire:target="update">
                <span class="material-icons-round">save</span>
                Update
            </span>

            <span wire:loading wire:target="update">
                <span class="material-icons-round"
                      style="font-size:16px;animation:spin .7s linear infinite">
                    sync
                </span>
                Updating...
            </span>

        </button>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {

        setTimeout(() => initAllFields(), 100);

        Livewire.hook('morph.updated', ({ el }) => {
            setTimeout(() => initAllFields(), 0);
        });

        function initAllFields() {
            document.querySelectorAll('.input-group-outline input').forEach(function (input) {
                var group = input.closest('.input-group');
                if (!group) return;
                if (input.value && input.value.trim() !== '') {
                    group.classList.add('is-filled');
                } else {
                    group.classList.remove('is-filled');
                }
                if (input._materialInit) return;
                input._materialInit = true;
                input.addEventListener('focus',  function () { group.classList.add('is-focused'); });
                input.addEventListener('blur',   function () {
                    group.classList.remove('is-focused');
                    group.classList.toggle('is-filled', !!input.value.trim());
                });
                input.addEventListener('input',  function () {
                    group.classList.toggle('is-filled', !!input.value.trim());
                });
            });
        }

    });
</script>
@endpush