<div>
    <div class="card">
        {{-- ── Toast Notification ── --}}
        <div x-data="{ show: false, type: 'success', message: '' }"
            x-on:notify.window="show = true; type = $event.detail.type; message = $event.detail.message; setTimeout(() => show = false, 3500)"
            x-show="show"
            x-transition
            :class="type === 'success' ? 'alert-success' : 'alert-danger'"
            class="alert alert-dismissible position-fixed top-0 end-0 m-3 shadow"
            style="z-index:9999;min-width:260px;display:none">
            <span x-text="message"></span>
        </div>

        {{-- ── Floating Header ── --}}
        <div class="mat-card-header header-primary-gradient">
            <h5> Add Salary Payment</h5>
            <p>Review salary details and process payment for the selected employee</p>
        </div>

        {{-- ── Already Paid Warning ── --}}
        @if($alreadyPaid)
        <div class="alert alert-warning d-flex align-items-center gap-2 mt-3 mx-3" style="border-radius:8px;">
            <span class="material-icons-round">warning_amber</span>
            <span>Salary for <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</strong> has already been paid for this employee.</span>
        </div>
        @endif

        {{-- ══════════════════════════════════════
            TWO COLUMN LAYOUT
        ══════════════════════════════════════ --}}
        <div class="row g-4 mt-1 p-3">

            {{-- ╔══════════════════════╗
                ║   LEFT: Salary Details
                ╚══════════════════════╝ --}}
            <div class="col-lg-7">
                <div class="ap-card">
                    <div class="ap-card-title">
                        <span class="material-icons-round">person</span>
                        Salary Details
                    </div>

                    {{-- Employee Info Row --}}
                    <div class="emp-info-row">
                        <div class="emp-photo">
                            @if(!empty($employee['photo']))
                                <img src="{{ asset('storage/' . $employee['photo']) }}" alt="Photo">
                            @else
                                <span class="material-icons-round" style="font-size:48px;color:#9ca3af">account_circle</span>
                            @endif
                        </div>
                        <div class="emp-meta">
                            <table class="info-table">
                                <tr>
                                    <td class="info-label">Name:</td>
                                    <td class="info-value">{{ $employee['name'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Staff ID:</td>
                                    <td class="info-value">{{ $employee['staff_id'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Joining Date:</td>
                                    <td class="info-value">
                                        {{ $employee['joining_date']
                                            ? \Carbon\Carbon::parse($employee['joining_date'])->format('d.M.Y')
                                            : '—' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="info-label">Designation:</td>
                                    <td class="info-value">{{ $employee['designation']['name'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="info-label">Department:</td>
                                    <td class="info-value">{{ $employee['department']['name'] ?? '—' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="ap-divider"></div>

                    {{-- Salary Summary --}}
                    <table class="info-table" style="margin-bottom:20px">
                        <tr>
                            <td class="info-label" style="width:50%">Salary Grade:</td>
                            <td class="info-value">
                                {{ $salaryGrade ?: '—' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">Basic Salary:</td>
                            <td class="info-value">৳{{ number_format($basicSalary, 0) }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Overtime Rate (Per Hour):</td>
                            <td class="info-value">৳{{ number_format($overtimeRate, 0) }}</td>
                        </tr>
                    </table>

                    {{-- Allowances & Deductions Side by Side --}}
                    <div class="row g-3">

                        {{-- Allowances --}}
                        <div class="col-md-6">
                            <div class="breakdown-box">
                                <div class="breakdown-title">Allowances</div>
                                <table class="breakdown-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allowances as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td>৳{{ number_format($row['amount'], 0) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted" style="font-size:12px;padding:12px">No allowances</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Deductions --}}
                        <div class="col-md-6">
                            <div class="breakdown-box">
                                <div class="breakdown-title">Deductions</div>
                                <table class="breakdown-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($deductions as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td>৳{{ number_format($row['amount'], 0) }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted" style="font-size:12px;padding:12px">No deductions</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ╔══════════════════════╗
                ║  RIGHT: Payment Details
                ╚══════════════════════╝ --}}
            <div class="col-lg-5">
                <div class="ap-card">
                    <div class="ap-card-title">
                        <span class="material-icons-round">credit_card</span>
                        Payment Details
                    </div>

                    <div class="row g-3">

                        {{-- Total Allowance (read-only) --}}
                        <div class="col-12">
                            <label class="ap-label">Total Allowance</label>
                            <input type="text" class="ap-input" readonly
                                value="৳{{ number_format($totalAllowance, 0) }}">
                        </div>

                        {{-- Total Deductions (read-only) --}}
                        <div class="col-12">
                            <label class="ap-label">Total Deductions</label>
                            <input type="text" class="ap-input" readonly
                                value="৳{{ number_format($totalDeduction, 0) }}">
                        </div>

                        {{-- Advance Deduction (read-only, auto-calculated) --}}
                        @if($advanceDeduction > 0)
                        <div class="col-12">
                            <label class="ap-label">Salary Advance Deduction</label>
                            <input type="text" class="ap-input" readonly
                                value="৳{{ number_format($advanceDeduction, 0) }}"
                                style="color:#d97706;font-weight:600">
                            @if(!$alreadyPaid && $advanceRemaining > 0)
                                <small class="text-muted d-block mt-1" style="font-size:.72rem">
                                    Remaining advance balance after this payment: ৳{{ number_format(max(0, $advanceRemaining - $advanceDeduction), 0) }}
                                </small>
                            @endif
                        </div>
                        @endif

                        {{-- Overtime Total Hour (editable) --}}
                        <div class="col-12">
                            <label class="ap-label">Overtime Total Hour</label>
                            <input type="number"
                                wire:model.live="overtimeHour"
                                class="ap-input"
                                min="0" step="0.5"
                                placeholder="0"
                                @if($alreadyPaid) readonly @endif>
                        </div>

                        {{-- Overtime Amount (auto-calculated) --}}
                        <div class="col-12">
                            <label class="ap-label">Overtime Amount</label>
                            <input type="text" class="ap-input" readonly
                                value="৳{{ number_format($overtimeAmount, 0) }}">
                        </div>

                        {{-- Net Salary (auto-calculated) --}}
                        <div class="col-12">
                            <label class="ap-label">Net Salary</label>
                            <input type="text" class="ap-input ap-input-highlight" readonly
                                value="৳{{ number_format($netSalary, 0) }}">
                        </div>

                        {{-- Payment Date --}}
                        <div class="col-12">
                            <label class="ap-label">Payment Date <span class="req">*</span></label>
                            <input type="date"
                                wire:model.defer="paymentDate"
                                class="ap-input"
                                @if($alreadyPaid) readonly @endif>
                            @error('paymentDate') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- Pay Via --}}
                        <div class="col-12">
                            <label class="ap-label">Pay Via <span class="req">*</span></label>
                            <select wire:model.live="payVia"
                                    class="ap-input"
                                    @if($alreadyPaid) disabled @endif>
                                <option value="">Select</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank</option>
                                <option value="cheque">Cheque</option>
                                <option value="mobile_banking">Mobile Banking</option>
                            </select>
                            @error('payVia') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- Account (shown for non-cash) --}}
                        @if(in_array($payVia, ['bank', 'cheque', 'mobile_banking']))
                        <div class="col-12">
                            <label class="ap-label">Account <span class="req">*</span></label>
                            <select wire:model.defer="accountId"
                                    class="ap-input"
                                    @if($alreadyPaid) disabled @endif>
                                <option value="">Select Account</option>
                                @foreach($officeAccounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                            @error('accountId') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        {{-- Remarks --}}
                        <div class="col-12">
                            <label class="ap-label">Remarks</label>
                            <textarea wire:model.defer="remarks"
                                    rows="3"
                                    class="ap-input"
                                    style="resize:vertical"
                                    placeholder="Optional remarks..."
                                    @if($alreadyPaid) readonly @endif></textarea>
                        </div>

                        {{-- Submit --}}
                        <div class="col-12 d-flex justify-content-end mt-1">
                            @if($alreadyPaid)
                                <span class="status-badge status-paid" style="padding:8px 20px;font-size:13px;">
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">check_circle</span>
                                    Already Paid
                                </span>
                            @else
                                <button type="button"
                                        wire:click="processPayment"
                                        wire:loading.attr="disabled"
                                        wire:target="processPayment"
                                        class="btn btn-primary d-flex align-items-center gap-1">
                                    <span wire:loading.remove wire:target="processPayment">
                                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle">check_circle</span>
                                        Paid
                                    </span>
                                    <span wire:loading wire:target="processPayment">
                                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                                        Processing...
                                    </span>
                                </button>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>