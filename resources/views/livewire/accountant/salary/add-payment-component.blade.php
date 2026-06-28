<div class="mat-card" style="padding-top:28px">

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
    <div class="mat-card-header header-pink-gradient">
        <h5>
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">payments</span>
            Add Salary Payment
        </h5>
        <p>Review salary details and process payment for the selected employee</p>
    </div>

    {{-- ── Back Button ── --}}
    <div style="padding: 20px 0 0 0;">
        <a href="{{ route('admin.salary.payment') }}"
           class="btn-outline d-inline-flex align-items-center gap-1" style="text-decoration:none">
            <span class="material-icons-round" style="font-size:16px">arrow_back</span>
            Back to Payments
        </a>
    </div>

    {{-- ── Already Paid Warning ── --}}
    @if($alreadyPaid)
    <div class="alert alert-warning d-flex align-items-center gap-2 mt-3" style="border-radius:8px;">
        <span class="material-icons-round">warning_amber</span>
        {{--
            FIX 6: $month is guaranteed Y-m from mount() validation,
            so createFromFormat is safe here. No change needed in blade,
            the fix lives in mount() — kept as-is.
        --}}
        <span>Salary for <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</strong> has already been paid for this employee.</span>
    </div>
    @endif

    {{-- ══════════════════════════════════════
         TWO COLUMN LAYOUT
    ══════════════════════════════════════ --}}
    <div class="row g-4 mt-1">

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
                                    {{--
                                        FIX 5: joining_date is now a plain 'Y-m-d' string
                                        (serialized in loadEmployee()). Carbon::parse() on a
                                        Carbon object throws in Laravel 11 — safe now.
                                    --}}
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
                            {{--
                                FIX 3: $salaryGrade is now a string property.
                                Previously declared as float, so 'Grade A' became 0.
                                Now displays the actual grade label correctly.
                            --}}
                            {{ $salaryGrade ?: '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label">Basic Salary:</td>
                        <td class="info-value">${{ number_format($basicSalary, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Overtime Rate (Per Hour):</td>
                        <td class="info-value">${{ number_format($overtimeRate, 2) }}</td>
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
                                        <td>${{ number_format($row['amount'], 2) }}</td>
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
                                        <td>${{ number_format($row['amount'], 2) }}</td>
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
                               value="{{ number_format($totalAllowance, 2) }}">
                    </div>

                    {{-- Total Deductions (read-only) --}}
                    <div class="col-12">
                        <label class="ap-label">Total Deductions</label>
                        <input type="text" class="ap-input" readonly
                               value="{{ number_format($totalDeduction, 2) }}">
                    </div>

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
                               value="{{ number_format($overtimeAmount, 2) }}">
                    </div>

                    {{-- Net Salary (auto-calculated) --}}
                    <div class="col-12">
                        <label class="ap-label">Net Salary</label>
                        <input type="text" class="ap-input ap-input-highlight" readonly
                               value="{{ number_format($netSalary, 2) }}">
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
                            <option value="bank">Bank Transfer</option>
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
                                    class="btn-pink d-flex align-items-center gap-1">
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

    </div>{{-- /row --}}

</div>{{-- /mat-card --}}


@push('styles')
<style>
    /* ── Card ── */
    .ap-card {
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 10px;
        padding: 24px;
        height: 100%;
    }
    .ap-card-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        font-weight: 700;
        padding-bottom: 14px;
        margin-bottom: 18px;
        border-bottom: 2px solid var(--primary, #e74c3c);
        color: inherit;
    }
    .ap-card-title .material-icons-round {
        color: var(--primary, #e74c3c);
        font-size: 20px;
    }

    /* ── Employee info ── */
    .emp-info-row {
        display: flex;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 18px;
    }
    .emp-photo {
        width: 100px;
        height: 110px;
        border: 2px solid var(--border, #e5e7eb);
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: var(--bg-muted, #f3f4f6);
    }
    .emp-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .emp-meta { flex: 1; }

    /* ── Info table ── */
    .info-table {
        width: 100%;
        border-collapse: collapse;
    }
    .info-table tr td {
        padding: 6px 0;
        font-size: 13px;
        border-bottom: 1px solid var(--border, #e5e7eb);
    }
    .info-table tr:last-child td { border-bottom: none; }
    .info-label {
        color: var(--text-muted, #9ca3af);
        font-weight: 600;
        width: 45%;
        white-space: nowrap;
    }
    .info-value { font-weight: 500; }

    .ap-divider {
        height: 1px;
        background: var(--border, #e5e7eb);
        margin: 16px 0;
    }

    /* ── Breakdown boxes ── */
    .breakdown-box {
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 8px;
        overflow: hidden;
    }
    .breakdown-title {
        padding: 10px 14px;
        font-weight: 700;
        font-size: 13px;
        border-bottom: 2px solid var(--primary, #e74c3c);
        background: rgba(231,76,60,.06);
    }
    .breakdown-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .breakdown-table thead th {
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 600;
        text-align: left;
        border-bottom: 1px solid var(--border, #e5e7eb);
        color: var(--text-muted, #9ca3af);
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .breakdown-table tbody td {
        padding: 8px 12px;
        border-bottom: 1px solid var(--border, #e5e7eb);
        vertical-align: middle;
    }
    .breakdown-table tbody tr:last-child td { border-bottom: none; }
    .breakdown-table tbody td:last-child { text-align: right; font-weight: 500; }

    /* ── Payment inputs ── */
    .ap-label {
        display: block;
        font-size: .75rem;
        color: var(--text-muted, #9ca3af);
        margin-bottom: 4px;
        font-weight: 500;
    }
    .ap-input {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid var(--border, #3d3d3d);
        border-radius: 6px;
        font-size: .85rem;
        background: transparent;
        color: inherit;
        outline: none;
        transition: border-color .2s;
    }
    .ap-input:focus  { border-color: var(--primary, #e74c3c); }
    .ap-input[readonly] { opacity: .7; cursor: default; }
    .ap-input-highlight {
        font-weight: 700;
        font-size: 1rem;
        border-color: var(--primary, #e74c3c);
        color: var(--primary, #e74c3c);
    }

    /* ── Status badges ── */
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid;
        white-space: nowrap;
    }
    .status-paid { color: #16a34a; border-color: #16a34a; background: rgba(22,163,74,.08); }

    /* ── Spin ── */
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush