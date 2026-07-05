<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient no-print">
            <h5 id="cardHeaderTitleInvoiceHistory">Invoice History</h5>
        </div>

        <div class="container-xl mt-4">

            @include('livewire.accountant.student.student-navbar')

            {{-- Collect Button --}}
            <div class="mb-3 no-print">
                @if(count($selectedIds) > 0)
                    <button class="btn-pink d-inline-flex align-items-center gap-1"
                            wire:click="collectSelected" type="button">
                        <span class="material-icons-round" style="font-size:16px">payments</span>
                        Selected Fees Collect ({{ count($selectedIds) }})
                    </button>
                @else
                    <button class="btn-outline d-inline-flex align-items-center gap-1"
                            type="button" disabled>
                        <span class="material-icons-round" style="font-size:16px">payments</span>
                        Selected Fees Collect
                    </button>
                @endif
            </div>

            {{-- Invoice Table --}}
            <div class="table-responsive">
                <table class="table-loader">
                    <thead>
                        <tr>
                            <th class="no-print" style="width:42px">
                                <input type="checkbox" class="alloc-checkbox"
                                    wire:model.live="selectAll">
                            </th>
                            <th id="th-sl">SL</th>
                            <th id="th-fees-type">Fees Type</th>
                            <th id="th-due-date">Due Date</th>
                            <th id="th-status">Status</th>
                            <th id="th-amount">Amount</th>
                            <th id="th-discount">Discount</th>
                            <th id="th-fine">Fine</th>
                            <th id="th-paid">Paid</th>
                            <th id="th-balance">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sl            = 1;
                            $grandTotal    = 0;
                            $grandDiscount = 0;
                            $grandFine     = 0;
                            $grandPaid     = 0;
                            $grandDue      = 0;
                        @endphp

                        @forelse($invoices as $invoice)

                        {{-- Invoice Group Header --}}
                        <tr class="group-header-row {{ in_array($invoice->id, $selectedIds) ? 'row-selected' : '' }}">
                            <td class="no-print">
                                <input type="checkbox" class="alloc-checkbox"
                                    wire:model.live="selectedIds"
                                    value="{{ $invoice->id }}">
                            </td>
                            <td colspan="9">
                                <span class="material-icons-round"
                                    style="font-size:14px;vertical-align:middle;color:#e05252">
                                    arrow_drop_down
                                </span>
                                Invoice #{{ $invoice->invoice_no }}
                                <span class="text-muted" style="font-size:11px">
                                    ({{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d.M.Y') }})
                                </span>
                            </td>
                        </tr>

                        @forelse($invoice->items as $item)
                        @php
                            $amount   = (float) $item->base_amount;
                            $discount = (float) $item->discount_amount;
                            $fine     = (float) $item->fine_amount;

                            $grandTotal    += $amount;
                            $grandDiscount += $discount;
                            $grandFine     += $fine;
                        @endphp
                        <tr wire:key="invoice-item-{{ $item->id }}">
                            <td class="no-print"></td>
                            <td class="text-muted">{{ $sl++ }}</td>
                            <td>{{ $item->feeGroupItem?->feeType?->name ?? '—' }}</td>
                            <td>
                                {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d.M.Y') : '—' }}
                            </td>
                            <td>
                                @if($invoice->payment_status === 'paid')
                                    <span class="inv-badge paid">Paid</span>
                                @elseif($invoice->payment_status === 'partial')
                                    <span class="inv-badge partial">Partial</span>
                                @else
                                    <span class="inv-badge unpaid">Unpaid</span>
                                @endif
                            </td>
                            <td>{{ number_format($amount, 0) }}</td>
                            <td>{{ number_format($discount, 0) }}</td>
                            <td>{{ number_format($fine, 0) }}</td>
                            <td class="text-muted">—</td>
                            <td class="text-muted">—</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-3 text-muted">
                                No fee items found.
                            </td>
                        </tr>
                        @endforelse

                        {{-- Invoice Summary Row --}}
                        @php
                            $grandPaid += (float) $invoice->paid_amount;
                            $grandDue  += (float) $invoice->due_amount;
                        @endphp
                        <tr class="invoice-subtotal-row">
                            <td class="no-print"></td>
                            <td colspan="7" class="text-end text-muted" style="font-size:12px">
                                Invoice Total: {{ number_format($invoice->total_amount, 0) }}
                            </td>
                            <td>{{ number_format($invoice->paid_amount, 0) }}</td>
                            <td>{{ number_format($invoice->due_amount, 0) }}</td>
                        </tr>

                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No invoices found for this student.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Grand Total --}}
            @if($invoices->isNotEmpty())
            <div class="invoice-summary">
                <div class="inv-summary-row">
                    <span>Grand Total :</span>
                    <span>{{ number_format($grandTotal, 0) }}</span>
                </div>
                <div class="inv-summary-row">
                    <span>Discount :</span>
                    <span>{{ number_format($grandDiscount, 0) }}</span>
                </div>
                <div class="inv-summary-row">
                    <span>Fine :</span>
                    <span>{{ number_format($grandFine, 0) }}</span>
                </div>
                <div class="inv-summary-row">
                    <span>Paid :</span>
                    <span>{{ number_format($grandPaid, 0) }}</span>
                </div>
                <div class="inv-summary-row fw-bold">
                    <span>Balance :</span>
                    <span>{{ number_format($grandDue, 0) }}</span>
                </div>
            </div>
            @endif

            {{-- Footer --}}
            <div class="footer-actions mt-3 no-print">
                <button type="button" class="btn btn-dark" onclick="window.print()">
                    <span class="material-icons-round">print</span> Print
                </button>
            </div>

        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- PAYMENT COLLECT MODAL --}}
    {{-- ============================================================ --}}
    @if($showPaymentModal)
    <div class="pay-modal-backdrop" wire:click.self="closePaymentModal">
        <div class="pay-modal-box">

            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payment Collect</h5>
                <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
            </div>

            <div class="pay-modal-body">

                <div class="table-responsive mb-3">
                    <table class="table-loader">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Due Amount</th>
                                <th style="width:160px">Pay Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentRows as $invoiceId => $row)
                            <tr wire:key="pay-row-{{ $invoiceId }}">
                                <td>#{{ $row['invoice_no'] }}</td>
                                <td>{{ number_format($row['due'], 2) }}</td>
                                <td>
                                    <input type="number" step="0.01" min="0" max="{{ $row['due'] }}"
                                        class="form-control form-control-sm"
                                        wire:model.live="paymentRows.{{ $invoiceId }}.pay_amount">
                                    @error("paymentRows.$invoiceId.pay_amount")
                                        <div class="text-danger" style="font-size:11px">{{ $message }}</div>
                                    @enderror
                                </td>
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
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="bank">Bank</option>
                            <option value="cheque">Cheque</option>
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
                        <select class="form-select" wire:model="officeAccountId">
                            <option value="">-- Select --</option>
                            @foreach($officeAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('officeAccountId') <div class="text-danger" style="font-size:11px">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea class="form-control" rows="2" wire:model="remarks"></textarea>
                    </div>
                </div>

                <div class="pay-total-row mt-3">
                    <span>Total Pay Amount :</span>
                    <span class="fw-bold">{{ number_format($this->totalPayAmount, 2) }}</span>
                </div>

            </div>

            <div class="pay-modal-footer">
                <button type="button" class="btn-outline" wire:click="closePaymentModal">Cancel</button>
                <button type="button" class="btn-pink" wire:click="savePayment" wire:loading.attr="disabled">
                    <span wire:loading wire:target="savePayment" class="spinner-border spinner-border-sm me-1"></span>
                    Confirm Payment
                </button>
            </div>

        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
    .table-loader {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-loader thead th {
        padding: 10px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        white-space: nowrap;
        border-bottom: 1px solid rgba(0,0,0,.08);
    }
    .table-loader tbody td {
        padding: 9px 10px;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-loader tbody tr {
        border-bottom: 1px solid rgba(0,0,0,.05);
        transition: background .15s;
    }
    .table-loader tbody tr:hover {
        background: rgba(0,0,0,.02);
    }
    .group-header-row td {
        padding: 10px 10px 6px;
        font-size: 12px;
        font-weight: 600;
        color: #888;
        background: transparent;
        border-bottom: none !important;
    }
    .invoice-subtotal-row td {
        background: rgba(0,0,0,.015);
        font-weight: 600;
        border-top: 1px dashed rgba(0,0,0,.08);
    }
    .row-selected {
        background: rgba(224, 82, 82, .08) !important;
    }
    .alloc-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #e05252;
    }
    .inv-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid transparent;
    }
    .inv-badge.paid    { background: transparent; border-color: #22c55e; color: #22c55e; }
    .inv-badge.partial { background: transparent; border-color: #f59e0b; color: #f59e0b; }
    .inv-badge.unpaid  { background: transparent; border-color: #ef4444; color: #ef4444; }

    .invoice-summary {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
        padding: 16px 10px 8px;
        font-size: 13px;
        margin-top: 8px;
    }
    .inv-summary-row {
        display: flex;
        gap: 24px;
        min-width: 220px;
        justify-content: space-between;
    }
    .footer-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding: 16px 0 8px;
    }

    /* ---- Payment Modal ---- */
    .pay-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.5);
        z-index: 1055;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .pay-modal-box {
        background: #fff;
        width: 100%;
        max-width: 640px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0,0,0,.25);
    }
    .btn-close-modal {
        background: none;
        border: none;
        color: #fff;
        cursor: pointer;
        line-height: 0;
    }
    .pay-modal-body {
        padding: 18px;
    }
    .pay-total-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        padding: 10px;
        background: rgba(224,82,82,.06);
        border-radius: 6px;
    }
    .pay-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 18px;
        border-top: 1px solid rgba(0,0,0,.06);
    }
</style>

<style>
    @media print {
        .no-print, .sidenav, .navbar,
        .student-navbar,
        nav, header, aside, footer { display: none !important; }

        .card {background: none !important; border: none !important; box-shadow: none !important; padding: 0 !important; }

        .container-xl { max-width: 100% !important; padding: 0 !important; }

        .section-card { box-shadow: none !important; break-inside: avoid; }
        .section-card { page-break-inside: avoid; }

        body { background: white !important; }

        .profile-card > .d-flex {
            display: flex !important;
        }

        .profile-card .flex-grow-1 {
            width: 50% !important;
        }
    }
</style>
@endpush