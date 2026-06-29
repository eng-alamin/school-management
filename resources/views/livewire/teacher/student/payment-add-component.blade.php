<div class="mat-card" style="padding-top:28px">

    {{-- Floating Header --}}
    <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleStudentOverview">
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">
                payments
            </span>
            Add Payment
        </h5>
        <p>Create new payment record</p>
    </div>

    <div class="container-xl mt-4">

        @include('livewire.teacher.student.student-navbar')

        <div class="form-section">
    <div class="row g-4">

        {{-- Left Column - All Fields --}}
        <div class="col-md-6 offset-md-1">
            <div class="row g-4">

                {{-- Fees Type --}}
                <div class="col-md-12">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Fees Type <span class="req">*</span></label>
                        <select wire:model.live="fee_invoice_item_id" class="form-select">
                            <option value="">Select</option>
                            @foreach($invoices as $invoice)
                                @php
                                    $unpaid = $invoice->items->filter(fn($i) => !$i->is_paid);
                                @endphp
                                @if($unpaid->isNotEmpty())
                                    <optgroup label="{{ $invoice->invoice_no }}">
                                        @foreach($unpaid as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->fee_type_name }}
                                                (Due: {{ number_format($item->remaining, 2) }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    @error('fee_invoice_item_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Date --}}
                <div class="col-md-12">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Date <span class="req">*</span></label>
                        <input type="date" wire:model="payment_date" data-dp-value="{{ $payment_date }}"
                               class="form-control">
                    </div>
                    @error('payment_date') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Amount --}}
                <div class="col-md-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Amount <span class="req">*</span></label>
                        <input type="number" step="0.01" min="0"
                               wire:model.live="amount"
                               class="form-control"
                               placeholder=" "
                               onfocus="focused(this)"
                               onfocusout="defocused(this)">
                    </div>
                    @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Discount --}}
                <div class="col-md-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Discount</label>
                        <input type="number" step="0.01" min="0"
                               wire:model.live="discount"
                               class="form-control"
                               placeholder=" "
                               onfocus="focused(this)"
                               onfocusout="defocused(this)">
                    </div>
                    @error('discount') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Fine --}}
                <div class="col-md-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Fine</label>
                        <input type="number" step="0.01" min="0"
                               wire:model.live="fine"
                               class="form-control"
                               placeholder=" "
                               onfocus="focused(this)"
                               onfocusout="defocused(this)">
                    </div>
                    @error('fine') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Payment Method --}}
                <div class="col-md-12">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Payment Method <span class="req">*</span></label>
                        <select wire:model="payment_method" class="form-select">
                            <option value="">Select</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="cheque">Cheque</option>
                            <option value="online">Online</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    @error('payment_method') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Account --}}
                <div class="col-md-12">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Account <span class="req">*</span></label>
                        <select wire:model="office_account_id" class="form-select">
                            <option value="">Select</option>
                            @foreach($officeAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('office_account_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Remarks --}}
                <div class="col-md-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Remarks</label>
                        <textarea wire:model="remarks"
                                  class="form-control"
                                  style="min-height:100px"
                                  placeholder="Write Your Remarks"
                                  onfocus="focused(this)"
                                  onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('remarks') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Submit --}}
                <div class="col-md-12">
                    <button class="btn-pink w-100 d-flex justify-content-center align-items-center gap-1"
                            type="button"
                            wire:click="save"
                            wire:loading.attr="disabled"
                            wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <span class="material-icons-round" style="font-size:16px;vertical-align:middle">save</span> Save Payment
                        </span>
                        <span wire:loading wire:target="save">
                            <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Saving...
                        </span>
                    </button>
                </div>

            </div>
        </div>

        {{-- Right Column - Empty for now --}}
        <div class="col-md-4">
            {{-- Paid Amount (calculated) --}}
            @if($amount || $discount || $fine)
            <div class="col-md-12">
                <div class="pay-summary">
                    <div class="pay-summary-row">
                        <span>Amount</span>
                        <span>${{ number_format((float)$amount, 2) }}</span>
                    </div>
                    <div class="pay-summary-row">
                        <span>Discount</span>
                        <span>- ${{ number_format((float)$discount, 2) }}</span>
                    </div>
                    <div class="pay-summary-row">
                        <span>Fine</span>
                        <span>+ ${{ number_format((float)$fine, 2) }}</span>
                    </div>
                    <div class="pay-summary-row total">
                        <span>Paid Amount</span>
                        <span>${{ number_format((float)$amount - (float)$discount + (float)$fine, 2) }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

    </div>
</div>

@push('styles')
<style>
    .pay-summary {
        border: 1px solid rgba(0,0,0,.1);
        border-radius: 8px;
        padding: 12px 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        font-size: 13px;
    }
    .pay-summary-row {
        display: flex;
        justify-content: space-between;
        color: var(--text-muted);
    }
    .pay-summary-row.total {
        border-top: 1px solid rgba(0,0,0,.08);
        padding-top: 8px;
        margin-top: 4px;
        font-weight: 700;
        font-size: 14px;
        color: var(--dark);
    }
</style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {

            setTimeout(() => initAllFields(), 100);

            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => initAllFields(), 0);
            });

            function initAllFields() {

                // ── 1. Text/Textarea/Number is-filled ──
                document.querySelectorAll('.input-group-outline input, .input-group-outline textarea').forEach(function(input) {
                    var group = input.closest('.input-group');
                    if (!group) return;
                    if (input.value && input.value.trim() !== '') {
                        group.classList.add('is-filled');
                    } else {
                        group.classList.remove('is-filled');
                    }
                    if (input._materialInit) return;
                    input._materialInit = true;
                    input.addEventListener('focus', function() { group.classList.add('is-focused'); });
                    input.addEventListener('blur', function() {
                        group.classList.remove('is-focused');
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                    input.addEventListener('input', function() {
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                });

                // ── 2. Select is-filled ──
                document.querySelectorAll('.input-group-outline select').forEach(function(select) {
                    var group = select.closest('.input-group');
                    if (!group) return;
                    if (select.value && select.value !== '') {
                        group.classList.add('is-filled');
                    } else {
                        group.classList.remove('is-filled');
                    }
                    if (select._materialInit) return;
                    select._materialInit = true;
                    select.addEventListener('change', function() {
                        group.classList.toggle('is-filled', !!select.value);
                    });
                    select.addEventListener('focus', function() { group.classList.add('is-focused'); });
                    select.addEventListener('blur', function() { group.classList.remove('is-focused'); });
                });

                // ── 3. Custom Select rebuild ──
                document.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    var old = select.parentNode.querySelector('.custom-select-wrapper');
                    if (old) old.remove();
                    select.style.display = '';
                    if (typeof buildCustomSelect === 'function') {
                        buildCustomSelect(select);
                    }
                });
            }

        });
    </script>
@endpush