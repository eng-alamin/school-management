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
            Payroll
        </h5>
        <p>Process and manage employee salary payments by role and month</p>
    </div>

    {{-- ══════════════════════════════════════
         SELECT GROUND
    ══════════════════════════════════════ --}}
    <div class="form-section" style="padding-top:40px;padding-bottom:20px">
        <div class="section-heading">
            <span class="material-icons-round">tune</span> Select Ground
        </div>

        <div class="row g-4">

            {{-- Role --}}
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Role <span class="req">*</span></label>
                    <select wire:model.live="role" class="form-select">
                        <option value="">Select Role</option>
                        @foreach($this->roles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Month --}}
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Month <span class="req">*</span></label>
                    <input type="month"
                           wire:model.live="month"
                           class="form-control"
                           style="padding:10px 14px;">
                </div>
                @error('month') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Filter Button --}}
            <div class="col-md-12 d-flex justify-content-end">
                <button wire:click="filter"
                        wire:loading.attr="disabled"
                        wire:target="filter"
                        class="btn-pink d-flex align-items-center gap-1"
                        type="button">
                    <span wire:loading.remove wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">filter_alt</span>
                        Filter
                    </span>
                    <span wire:loading wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                        Filtering...
                    </span>
                </button>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════
         STAFF LIST
    ══════════════════════════════════════ --}}
    @if($hasFiltered)
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">people</span> Staff List
        </div>

        {{-- Table Controls --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-3 mb-2">
            {{-- Per-page --}}
            <div class="d-flex align-items-center gap-2">
                <select wire:model.live="perPage" class="schedule-input" style="width:90px">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span style="font-size:12px;color:#9ca3af">rows per page</span>
            </div>

            {{-- Search --}}
            <div style="position:relative">
                <span class="material-icons-round"
                      style="position:absolute;left:8px;top:50%;transform:translateY(-50%);font-size:16px;color:#9ca3af">
                    search
                </span>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search..."
                       class="schedule-input"
                       style="padding-left:32px;width:220px;">
            </div>
        </div>

        @if($employees && $employees->count() > 0)
        <div class="table-responsive">
            <table class="table-loader">
                <thead>
                    <tr>
                        <th>Staff Id</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Mobile No</th>
                        <th>Salary Grade (GPA)</th>
                        <th>Basic Salary</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $employee)
                    <tr wire:key="emp-{{ $employee->id }}">
                        <td>{{ $employee->staff_id ?? '—' }}</td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->designation?->name ?? '—' }}</td>
                        <td>{{ $employee->department?->name ?? '—' }}</td>
                        <td>{{ $employee->mobile ?? '—' }}</td>

                        {{-- Salary Grade (from salary_assigns via subquery) --}}
                        <td>{{ $employee->sa_grade ?? '—' }}</td>

                        {{-- Basic Salary: paid amount if paid, else assign amount --}}
                        <td>
                            @if($employee->salary_basic)
                                ${{ number_format($employee->salary_basic, 2) }}
                            @elseif($employee->sa_basic)
                                ${{ number_format($employee->sa_basic, 2) }}
                            @else
                                —
                            @endif
                        </td>

                        {{-- Status Badge --}}
                        <td>
                            @if(($employee->salary_status ?? '') === 'paid')
                                <span class="status-badge status-paid">Salary Paid</span>
                            @elseif(($employee->salary_status ?? '') === 'partial')
                                <span class="status-badge status-partial">Partial</span>
                            @else
                                <span class="status-badge status-unpaid">Salary Unpaid</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td>
                            @if(($employee->salary_status ?? '') === 'paid')
                                <a class="action-btn btn-payslip" href="{{route('admin.salary.invoice-payment', ['id' => $employee->id, 'month' => $this->month]) }}" target="_blank">
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">visibility</span>
                                    Payslip
                        </a>
                            @else
                                <a class="action-btn btn-paynow" href="{{route('admin.salary.add-payment', ['id' => $employee->id, 'month' => $this->month]) }}" target="_blank">
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">credit_card</span>
                                    Pay Now
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3">
            <span style="font-size:12px;color:#9ca3af">
                Showing {{ $employees->firstItem() }}–{{ $employees->lastItem() }} of {{ $employees->total() }} employees
            </span>
            <div class="payroll-pagination">
                {{-- Previous --}}
                <button @if($employees->onFirstPage()) disabled @endif
                        wire:click="previousPage"
                        class="page-btn {{ $employees->onFirstPage() ? 'disabled' : '' }}">
                    <span class="material-icons-round" style="font-size:16px">chevron_left</span>
                </button>

                {{-- Page numbers --}}
                @for($p = max(1, $employees->currentPage() - 2); $p <= min($employees->lastPage(), $employees->currentPage() + 2); $p++)
                    <button wire:click="gotoPage({{ $p }})"
                            class="page-btn {{ $p === $employees->currentPage() ? 'active' : '' }}">
                        {{ $p }}
                    </button>
                @endfor

                {{-- Next --}}
                <button @if(!$employees->hasMorePages()) disabled @endif
                        wire:click="nextPage"
                        class="page-btn {{ !$employees->hasMorePages() ? 'disabled' : '' }}">
                    <span class="material-icons-round" style="font-size:16px">chevron_right</span>
                </button>
            </div>
        </div>

        @else
        <div class="text-center py-5 text-muted">
            <span class="material-icons-round d-block mb-2" style="font-size:3rem;opacity:.2">inbox</span>
            No employees found for selected role/month.
        </div>
        @endif

        {{-- Footer Reset --}}
        <div class="form-footer">
            <button class="btn-outline" type="button" wire:click="resetForm">
                <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
            </button>
        </div>
    </div>
    @endif

</div>{{-- /mat-card --}}


{{-- ══════════════════════════════════════════════════
     PAY NOW MODAL
══════════════════════════════════════════════════ --}}
@if($showPayModal)
<div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.65);">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">

            <div class="modal-header" style="background:var(--primary,#e74c3c);color:#fff;border:none;">
                <h5 class="modal-title">
                    <span class="material-icons-round me-2" style="vertical-align:middle">credit_card</span>
                    Process Salary Payment
                </h5>
                <button class="btn-close btn-close-white" wire:click="$set('showPayModal', false)"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row g-3">

                    {{-- Payment Date --}}
                    <div class="col-md-6">
                        <label class="pay-label">Payment Date <span class="req">*</span></label>
                        <input type="date" wire:model.defer="paymentDate" class="pay-input">
                        @error('paymentDate') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    {{-- Payment Method --}}
                    <div class="col-md-6">
                        <label class="pay-label">Payment Method <span class="req">*</span></label>
                        <select wire:model.live="paymentMethod" class="pay-input">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                        @error('paymentMethod') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    {{-- Account (for bank/cheque/mobile) --}}
                    @if(in_array($paymentMethod, ['bank','cheque','mobile_banking']))
                    <div class="col-md-6">
                        <label class="pay-label">Account</label>
                        <select wire:model.defer="accountId" class="pay-input">
                            <option value="">Select Account</option>
                            @foreach($officeAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Transaction ID --}}
                    <div class="col-md-6">
                        <label class="pay-label">Transaction ID</label>
                        <input type="text" wire:model.defer="transactionId"
                               placeholder="Ref / Trx ID"
                               class="pay-input">
                    </div>
                    @endif

                    {{-- Note --}}
                    <div class="col-12">
                        <label class="pay-label">Note</label>
                        <textarea wire:model.defer="note"
                                  rows="2"
                                  placeholder="Optional note..."
                                  class="pay-input" style="resize:vertical"></textarea>
                    </div>

                </div>
            </div>

            <div class="modal-footer" style="border:none;padding:12px 24px 20px;">
                <button class="btn btn-outline-secondary"
                        wire:click="$set('showPayModal', false)">
                    Cancel
                </button>
                <button class="btn-pink d-flex align-items-center gap-1"
                        wire:click="processPayment"
                        wire:loading.attr="disabled"
                        wire:target="processPayment">
                    <span wire:loading.remove wire:target="processPayment">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle">check_circle</span>
                        Confirm Payment
                    </span>
                    <span wire:loading wire:target="processPayment">
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                        Processing...
                    </span>
                </button>
            </div>

        </div>
    </div>
</div>
@endif


{{-- ══════════════════════════════════════════════════
     PAYSLIP MODAL
══════════════════════════════════════════════════ --}}
@if($showPayslip && $payslipData)
@php
    $ps = $payslipData;
@endphp
<div class="modal fade show d-block no-print" tabindex="-1" style="background:rgba(0,0,0,.65);">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">

            <div class="modal-header" style="background:var(--primary,#e74c3c);color:#fff;border:none;">
                <h5 class="modal-title">
                    <span class="material-icons-round me-2" style="vertical-align:middle">receipt_long</span>
                    Salary Payslip
                </h5>
                <button class="btn-close btn-close-white" wire:click="$set('showPayslip', false)"></button>
            </div>

            <div class="modal-body p-4" id="payslip-print-area">
                {{-- Header --}}
                <div class="text-center mb-4">
                    <h5 class="fw-700 mb-0" style="font-size:1.1rem">SALARY PAYSLIP</h5>
                    <div style="font-size:.8rem;color:#9ca3af">
                        Month: <strong>{{ \Carbon\Carbon::parse($ps['month'])->format('F Y') }}</strong>
                    </div>
                </div>

                {{-- Employee Info --}}
                <div class="row g-2 mb-4" style="font-size:.85rem">
                    <div class="col-6">
                        <span style="color:#9ca3af">Employee:</span>
                        <strong>{{ $ps['employee']['name'] ?? '—' }}</strong>
                    </div>
                    <div class="col-6">
                        <span style="color:#9ca3af">Staff ID:</span>
                        <strong>{{ $ps['employee']['staff_id'] ?? '—' }}</strong>
                    </div>
                    <div class="col-6">
                        <span style="color:#9ca3af">Designation:</span>
                        <strong>{{ $ps['employee']['designation']['name'] ?? '—' }}</strong>
                    </div>
                    <div class="col-6">
                        <span style="color:#9ca3af">Department:</span>
                        <strong>{{ $ps['employee']['department']['name'] ?? '—' }}</strong>
                    </div>
                    <div class="col-6">
                        <span style="color:#9ca3af">Payment Date:</span>
                        <strong>{{ $ps['payment_date'] ? \Carbon\Carbon::parse($ps['payment_date'])->format('d M Y') : '—' }}</strong>
                    </div>
                    <div class="col-6">
                        <span style="color:#9ca3af">Method:</span>
                        <strong>{{ ucfirst(str_replace('_', ' ', $ps['payment_method'])) }}</strong>
                    </div>
                </div>

                {{-- Salary Breakdown --}}
                <table style="width:100%;border-collapse:collapse;font-size:.85rem">
                    <thead>
                        <tr style="border-bottom:2px solid #e5e7eb">
                            <th style="padding:8px 10px;text-align:left">Description</th>
                            <th style="padding:8px 10px;text-align:right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom:1px solid #e5e7eb">
                            <td style="padding:8px 10px">Basic Salary</td>
                            <td style="padding:8px 10px;text-align:right">${{ number_format($ps['basic_salary'] ?? 0, 2) }}</td>
                        </tr>
                        <tr style="border-bottom:1px solid #e5e7eb">
                            <td style="padding:8px 10px;color:#16a34a">Total Allowance</td>
                            <td style="padding:8px 10px;text-align:right;color:#16a34a">+ ${{ number_format($ps['total_allowance'] ?? 0, 2) }}</td>
                        </tr>
                        @if(($ps['overtime_amount'] ?? 0) > 0)
                        <tr style="border-bottom:1px solid #e5e7eb">
                            <td style="padding:8px 10px;color:#16a34a">
                                Overtime ({{ $ps['overtime_hour'] }}h × ${{ $ps['overtime_rate'] }})
                            </td>
                            <td style="padding:8px 10px;text-align:right;color:#16a34a">+ ${{ number_format($ps['overtime_amount'], 2) }}</td>
                        </tr>
                        @endif
                        <tr style="border-bottom:1px solid #e5e7eb">
                            <td style="padding:8px 10px;color:#dc2626">Total Deduction</td>
                            <td style="padding:8px 10px;text-align:right;color:#dc2626">- ${{ number_format($ps['total_deduction'] ?? 0, 2) }}</td>
                        </tr>
                        <tr style="background:rgba(0,0,0,.03)">
                            <td style="padding:10px;font-weight:700">Gross Salary</td>
                            <td style="padding:10px;text-align:right;font-weight:700">${{ number_format($ps['gross_salary'] ?? 0, 2) }}</td>
                        </tr>
                        <tr style="background:var(--primary,#e74c3c);color:#fff">
                            <td style="padding:10px;font-weight:700">Net Payable</td>
                            <td style="padding:10px;text-align:right;font-weight:700">${{ number_format($ps['net_salary'] ?? 0, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                @if($ps['note'])
                <div style="margin-top:16px;font-size:.8rem;color:#9ca3af">
                    <strong>Note:</strong> {{ $ps['note'] }}
                </div>
                @endif
            </div>

            <div class="modal-footer" style="border:none;padding:12px 24px 20px;">
                <button class="btn btn-outline-secondary"
                        wire:click="$set('showPayslip', false)">
                    Close
                </button>
                <button class="btn-pink d-flex align-items-center gap-1"
                        onclick="printPayslip()">
                    <span class="material-icons-round" style="font-size:16px;vertical-align:middle">print</span>
                    Print Payslip
                </button>
            </div>

        </div>
    </div>
</div>
@endif


@push('styles')
<style>
    /* ── Table ── */
    .table-loader {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-loader thead th {
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        white-space: nowrap;
        border-bottom: 2px solid var(--border, #e5e7eb);
        color: var(--text-muted, #9ca3af);
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .table-loader tbody td {
        padding: 10px 12px;
        vertical-align: middle;
        font-size: 13px;
        border-bottom: 1px solid var(--border, #e5e7eb);
    }
    .table-loader tbody tr:hover {
        background: rgba(255,255,255,.03);
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
    .status-paid    { color: #16a34a; border-color: #16a34a; background: rgba(22,163,74,.08); }
    .status-unpaid  { color: #2563eb; border-color: #2563eb; background: rgba(37,99,235,.08); }
    .status-partial { color: #d97706; border-color: #d97706; background: rgba(217,119,6,.08);  }

    /* ── Action buttons ── */
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: opacity .15s;
        white-space: nowrap;
    }
    .action-btn:hover { opacity: .85; }
    .btn-payslip { background: #1f2937; color: #fff; }
    .btn-paynow  { background: var(--primary, #e74c3c); color: #fff; }

    /* ── Pagination ── */
    .payroll-pagination {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .page-btn {
        min-width: 32px;
        height: 32px;
        border-radius: 6px;
        border: 1px solid var(--border, #e5e7eb);
        background: transparent;
        color: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .15s;
        padding: 0 6px;
    }
    .page-btn:hover:not(.disabled):not(.active) {
        background: rgba(231,76,60,.1);
        border-color: var(--primary, #e74c3c);
    }
    .page-btn.active {
        background: var(--primary, #e74c3c);
        color: #fff;
        border-color: var(--primary, #e74c3c);
    }
    .page-btn.disabled {
        opacity: .35;
        cursor: default;
    }

    /* ── Shared input ── */
    .schedule-input {
        border: 1px solid var(--border, #3d3d3d);
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        outline: none;
        width: 100%;
        transition: border-color .2s;
        background: transparent;
        color: inherit;
    }
    .schedule-input:focus { border-color: var(--primary, #e05252); }

    /* ── Pay modal inputs ── */
    .pay-label {
        display: block;
        font-size: .75rem;
        color: #9ca3af;
        margin-bottom: 4px;
    }
    .pay-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--border, #3d3d3d);
        border-radius: 6px;
        font-size: .85rem;
        background: transparent;
        color: inherit;
        outline: none;
        transition: border-color .2s;
    }
    .pay-input:focus { border-color: var(--primary, #e74c3c); }

    /* ── Footer ── */
    .form-footer {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding-top: 20px;
        margin-top: 16px;
        gap: 12px;
    }

    /* ── Spin animation ── */
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Print payslip ── */
    @media print {
        body > *:not(#payslip-print-area) { display: none !important; }
        #payslip-print-area { display: block !important; }
    }
</style>
@endpush

@push('scripts')
<script>
    function printPayslip() {
        const content = document.getElementById('payslip-print-area').innerHTML;
        const win = window.open('', '_blank', 'width=800,height=600');
        win.document.write(`
            <html><head><title>Payslip</title>
            <style>
                body { font-family: sans-serif; padding: 32px; color: #1f2937; }
                table { width:100%; border-collapse:collapse; }
                th, td { padding:8px 10px; }
                thead th { border-bottom: 2px solid #e5e7eb; text-align:left; }
                tbody tr { border-bottom: 1px solid #e5e7eb; }
            </style></head>
            <body>${content}</body></html>
        `);
        win.document.close();
        win.print();
    }
</script>
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

                // ── 4. Datepicker ──
                document.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {
                    if (input.dataset.dpInit === '1') return;
                    input.dataset.dpInit = '1';
                    input.addEventListener('change', function() {
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    if (typeof buildDatepicker === 'function') {
                        buildDatepicker(input);
                    }
                });
            }

        });
    </script>
@endpush