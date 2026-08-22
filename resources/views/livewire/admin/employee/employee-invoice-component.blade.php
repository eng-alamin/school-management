<div>
    <div class="card border-0 bg-transparent">
        <div class="container-xl mt-4">

            @include('livewire.admin.employee.employee-navbar')

            {{-- Action Buttons --}}
            <div class="mb-3 no-print d-flex gap-2">
                @if(count($selectedIds) > 0)
                    <button class="btn-primary d-inline-flex align-items-center gap-1"
                            wire:click="collectSelected" type="button">
                        <span class="material-icons-round" style="font-size:16px">payments</span>
                        Selected Salary Collect ({{ count($selectedIds) }})
                    </button>
                    <button class="btn-outline d-inline-flex align-items-center gap-1"
                            type="button" onclick="printSelectedPayments()">
                        <span class="material-icons-round" style="font-size:16px">print</span>
                        Print Selected
                    </button>
                @else
                    <button class="btn-outline d-inline-flex align-items-center gap-1"
                            type="button" disabled>
                        <span class="material-icons-round" style="font-size:16px">payments</span>
                        Selected Salary Collect
                    </button>
                @endif
            </div>

            {{-- ============================================================ --}}
            {{-- YEAR ACCORDION --}}
            {{-- ============================================================ --}}
            @forelse($paymentsByYear as $groupIndex => $group)
                @php
                    $year = $group['year'];
                    $yearPayments = $group['payments'];

                    $yGrossTotal = 0; $yAllowance = 0; $yDeduction = 0; $yNetTotal = 0;
                @endphp

                <div class="card session-accordion-item">
                    <div class="session-accordion-header" onclick="toggleSessionPanel(this)">
                        <span class="material-icons-round chevron" style="font-size:18px">expand_more</span>
                        <span class="session-name">{{ $year }}</span>
                        <span class="session-count">({{ $yearPayments->count() }} Record)</span>
                    </div>

                    <div class="session-accordion-body" style="display:{{ $loop->first ? 'block' : 'none' }}">

                        <div class="table-responsive">
                            <table class="table-loader">
                                <thead>
                                    <tr>
                                        <th class="no-print" style="width:42px">
                                            <input type="checkbox" class="alloc-checkbox"
                                                onclick="toggleSessionSelectAll(this, {{ $groupIndex }})">
                                        </th>
                                        <th>SL</th>
                                        <th>Month</th>
                                        <th>Basic</th>
                                        <th>Allowance</th>
                                        <th>Deduction</th>
                                        <th>Overtime</th>
                                        <th>Gross</th>
                                        <th>Net Salary</th>
                                        <th>Status</th>
                                        <th>Payment Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sl = 1; @endphp
                                    @forelse($yearPayments as $payment)
                                        @php
                                            $yGrossTotal += (float) $payment->gross_salary;
                                            $yAllowance  += (float) $payment->total_allowance;
                                            $yDeduction  += (float) $payment->total_deduction;
                                            $yNetTotal   += (float) $payment->net_salary;
                                        @endphp
                                        <tr class="{{ in_array($payment->id, $selectedIds) ? 'row-selected' : '' }}"
                                            wire:key="salary-{{ $payment->id }}">
                                            <td class="no-print">
                                                <input type="checkbox" class="alloc-checkbox session-{{ $groupIndex }}-checkbox"
                                                    wire:model.live="selectedIds"
                                                    value="{{ $payment->id }}"
                                                    {{ $payment->status === 'paid' ? 'disabled' : '' }}>
                                            </td>
                                            <td class="text-muted">{{ $sl++ }}</td>
                                            <td>
                                                <a class="text-primary" href="{{ route('admin.salary.invoice-payment', ['id' => $payment->employee_id, 'month' => \Carbon\Carbon::parse($payment->month)->format('Y-m')]) }}" target="_blank">
                                                    {{ \Carbon\Carbon::parse($payment->month)->format('F, Y') }}
                                                </a>
                                            </td>
                                            <td>{{ number_format($payment->basic_salary, 0) }}</td>
                                            <td>{{ number_format($payment->total_allowance, 0) }}</td>
                                            <td>{{ number_format($payment->total_deduction, 0) }}</td>
                                            <td>{{ number_format($payment->overtime_amount, 0) }}</td>
                                            <td>{{ number_format($payment->gross_salary, 0) }}</td>
                                            <td class="fw-bold">{{ number_format($payment->net_salary, 0) }}</td>
                                            <td>
                                                @if($payment->status === 'paid')
                                                    <span class="inv-badge paid">Paid</span>
                                                @elseif($payment->status === 'partial')
                                                    <span class="inv-badge partial">Partial</span>
                                                @else
                                                    <span class="inv-badge unpaid">Unpaid</span>
                                                @endif
                                            </td>
                                            <td class="text-muted">
                                                {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d.M.Y') : '—' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center py-3 text-muted">No salary records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Year Subtotal --}}
                        <div class="invoice-summary">
                            <div class="inv-summary-row">
                                <span>Gross Total :</span>
                                <span>{{ number_format($yGrossTotal, 0) }}</span>
                            </div>
                            <div class="inv-summary-row">
                                <span>Allowance :</span>
                                <span>{{ number_format($yAllowance, 0) }}</span>
                            </div>
                            <div class="inv-summary-row">
                                <span>Deduction :</span>
                                <span>{{ number_format($yDeduction, 0) }}</span>
                            </div>
                            <div class="inv-summary-row fw-bold">
                                <span>Net Total :</span>
                                <span>{{ number_format($yNetTotal, 0) }}</span>
                            </div>
                        </div>

                    </div>
                </div>

            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                    No salary records found for this employee.
                </div>
            @endforelse

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- PRINTABLE — শুধু Selected Record গুলোর জন্য (Hidden, JS দিয়ে Print হবে) --}}
    {{-- ============================================================ --}}
    <div id="salaryPrintable" style="display:none">
        <h6>{{ $employee->name ?? '' }} — Salary Statement</h6>
        <p>Designation: {{ $employee->designation->name ?? '—' }} | Department: {{ $employee->department->name ?? '—' }}</p>

        @php
            $selectedPayments = $salaryPayments->whereIn('id', $selectedIds);
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Basic</th>
                    <th>Allowance</th>
                    <th>Deduction</th>
                    <th>Net Salary</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($selectedPayments as $payment)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($payment->month)->format('F, Y') }}</td>
                        <td>{{ number_format($payment->basic_salary, 0) }}</td>
                        <td>{{ number_format($payment->total_allowance, 0) }}</td>
                        <td>{{ number_format($payment->total_deduction, 0) }}</td>
                        <td>{{ number_format($payment->net_salary, 0) }}</td>
                        <td>{{ ucfirst($payment->status) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ============================================================ --}}
    {{-- PAYMENT COLLECT MODAL --}}
    {{-- ============================================================ --}}
    @if($showPaymentModal)
    <div class="pay-modal-backdrop" wire:click.self="closePaymentModal">
        <div class="pay-modal-box">

            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Salary Payment Collect</h5>
                <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
            </div>

            <div class="pay-modal-body">

                <div class="table-responsive mb-3">
                    <table class="table-loader">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Net Salary</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentRows as $paymentId => $row)
                            <tr wire:key="pay-row-{{ $paymentId }}">
                                <td>{{ $row['month_label'] }}</td>
                                <td>{{ number_format($row['net_salary'], 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Payment Method</label>
                        <select class="form-select" wire:model="paymentMethod">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank">Bank</option>
                            <option value="cheque">Cheque</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                        @error('paymentMethod') <div class="text-danger" style="font-size:11px">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Date</label>
                        <input type="date" class="form-control" wire:model="paymentDate">
                        @error('paymentDate') <div class="text-danger" style="font-size:11px">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Office Account (Optional)</label>
                        <select class="form-select" wire:model="accountId">
                            <option value="">-- Select --</option>
                            @foreach($officeAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('accountId') <div class="text-danger" style="font-size:11px">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Transaction ID (Optional)</label>
                        <input type="text" class="form-control" wire:model="transactionId">
                        @error('transactionId') <div class="text-danger" style="font-size:11px">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Note (Optional)</label>
                        <input type="text" class="form-control" wire:model="note">
                        @error('note') <div class="text-danger" style="font-size:11px">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pay-total-row mt-3">
                    <span>Total Pay Amount :</span>
                    <span class="fw-bold">{{ number_format($this->totalPayAmount, 2) }}</span>
                </div>

            </div>

            <div class="pay-modal-footer">
                <button type="button" class="btn-outline" wire:click="closePaymentModal">Cancel</button>
                <button type="button" class="btn-primary" wire:click="savePayment" wire:loading.attr="disabled">
                    <span wire:loading wire:target="savePayment" class="spinner-border spinner-border-sm me-1"></span>
                    Confirm Payment
                </button>
            </div>

        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
    function toggleSessionPanel(headerEl) {
        headerEl.classList.toggle('open');
        const body = headerEl.nextElementSibling;
        if (!body) return;
        body.style.display = (body.style.display === 'none' || !body.style.display) ? 'block' : 'none';
    }

    function toggleSessionSelectAll(checkboxEl, groupIndex) {
        const checked = checkboxEl.checked;
        document.querySelectorAll('.session-' + groupIndex + '-checkbox').forEach(function (cb) {
            if (cb.disabled) return;
            if (cb.checked !== checked) {
                cb.checked = checked;
                cb.dispatchEvent(new Event('change'));
            }
        });
    }

    function printSelectedPayments() {
        const printableEl = document.getElementById('salaryPrintable');

        if (!printableEl) {
            return;
        }

        const printContent = printableEl.innerHTML;
        const printWindow = window.open('', '_blank', 'width=900,height=650');

        if (!printWindow) {
            alert('Print window block hoye গেছে। Browser-er popup blocker check korun.');
            return;
        }

        printWindow.document.write(`
            <html>
                <head>
                    <title>Salary Statement</title>
                    <style>
                        * { box-sizing: border-box; }
                        body { font-family: Arial, Helvetica, sans-serif; padding: 28px; color: #222; }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; font-size: 13px; }
                        thead th { background: #f5f5f5; }
                        h6 { margin: 0 0 2px 0; font-size: 16px; }
                        p { margin: 0; color: #555; font-size: 13px; }
                        .text-muted { color: #777 !important; text-transform: uppercase; font-size: 11px; }
                    </style>
                </head>
                <body>
                    ${printContent}
                </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }
</script>
@endpush