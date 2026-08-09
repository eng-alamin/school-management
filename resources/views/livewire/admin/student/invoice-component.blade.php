<div>
    <div class="card border-0 bg-transparent">
        <div class="container-xl mt-4">

            @include('livewire.admin.student.student-navbar')

            {{-- Action Buttons --}}
            <div class="mb-3 no-print d-flex gap-2">
                @if(count($selectedIds) > 0)
                    <button class="btn-primary d-inline-flex align-items-center gap-1"
                            wire:click="collectSelected" type="button">
                        <span class="material-icons-round" style="font-size:16px">payments</span>
                        Selected Fees Collect ({{ count($selectedIds) }})
                    </button>
                    <button class="btn-outline d-inline-flex align-items-center gap-1"
                            type="button" onclick="printSelectedInvoices()">
                        <span class="material-icons-round" style="font-size:16px">print</span>
                        Print Selected
                    </button>
                @else
                    <button class="btn-outline d-inline-flex align-items-center gap-1"
                            type="button" disabled>
                        <span class="material-icons-round" style="font-size:16px">payments</span>
                        Selected Fees Collect
                    </button>
                @endif
            </div>

            {{-- ============================================================ --}}
            {{-- SESSION YEAR ACCORDION --}}
            {{-- ============================================================ --}}
            @forelse($invoicesBySession as $groupIndex => $group)
                @php
                    $session = $group['session'];
                    $sessionInvoices = $group['invoices'];

                    $sGrandTotal = 0; $sGrandDiscount = 0; $sGrandFine = 0; $sGrandPaid = 0; $sGrandDue = 0;
                @endphp

                <div class="card session-accordion-item">
                    <div class="session-accordion-header" onclick="toggleSessionPanel(this)">
                        <span class="material-icons-round chevron" style="font-size:18px">expand_more</span>
                        <span class="session-name">{{ $session->name }}</span>
                        <span class="session-count">({{ $sessionInvoices->count() }} Invoice)</span>
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
                                        <th>Fees Type</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Discount</th>
                                        <th>Fine</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sl = 1; @endphp
                                    @forelse($sessionInvoices as $invoice)

                                    <tr class="group-header-row {{ in_array($invoice->id, $selectedIds) ? 'row-selected' : '' }}"
                                        wire:key="invoice-group-{{ $invoice->id }}">
                                        <td class="no-print">
                                            <input type="checkbox" class="alloc-checkbox session-{{ $groupIndex }}-checkbox"
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

                                            $sGrandTotal    += $amount;
                                            $sGrandDiscount += $discount;
                                            $sGrandFine     += $fine;

                                            $isMonthly   = $item->feeSetup?->frequency === 'monthly';
                                            $feeTypeName = $item->feeSetup?->feeType?->name ?? '—';
                                            $monthLabel  = $isMonthly
                                                ? \Carbon\Carbon::parse($invoice->invoice_date)->format('F')
                                                : null;
                                        @endphp
                                        <tr wire:key="invoice-item-{{ $item->id }}">
                                            <td class="no-print"></td>
                                            <td class="text-muted">{{ $sl++ }}</td>
                                            <td>
                                                {{ $feeTypeName }}
                                                @if($monthLabel)
                                                    <span class="text-muted" style="font-size:11px"> — {{ $monthLabel }}</span>
                                                @endif
                                            </td>
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
                                            <td colspan="10" class="text-center py-3 text-muted">No fee items found.</td>
                                        </tr>
                                    @endforelse

                                    @php
                                        $sGrandPaid += (float) $invoice->paid_amount;
                                        $sGrandDue  += (float) $invoice->due_amount;
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
                                        <td colspan="10" class="text-center py-5 text-muted">No invoices found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Session Subtotal --}}
                        <div class="invoice-summary">
                            <div class="inv-summary-row">
                                <span>Session Total :</span>
                                <span>{{ number_format($sGrandTotal, 0) }}</span>
                            </div>
                            <div class="inv-summary-row">
                                <span>Discount :</span>
                                <span>{{ number_format($sGrandDiscount, 0) }}</span>
                            </div>
                            <div class="inv-summary-row">
                                <span>Fine :</span>
                                <span>{{ number_format($sGrandFine, 0) }}</span>
                            </div>
                            <div class="inv-summary-row">
                                <span>Paid :</span>
                                <span>{{ number_format($sGrandPaid, 0) }}</span>
                            </div>
                            <div class="inv-summary-row fw-bold">
                                <span>Balance :</span>
                                <span>{{ number_format($sGrandDue, 0) }}</span>
                            </div>
                        </div>

                    </div>
                </div>

            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                    No invoices found for this student.
                </div>
            @endforelse

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- PRINTABLE — শুধু Selected Invoice গুলোর জন্য (Hidden, JS দিয়ে Print হবে) --}}
    {{-- ============================================================ --}}
    <div id="invoicePrintable" style="display:none">

        @php
            $selectedInvoices = $invoices->whereIn('id', $selectedIds);
            $printInvoiceNos  = $selectedInvoices->pluck('invoice_no')->implode(', ');
        @endphp

        <div class="inv-print-header">
            <div class="inv-print-institution">
                <h1>{{ institution()->name ?? 'Institution Name' }}</h1>
                @if(!empty(institution()->address))
                    <p class="inv-print-address">{{ institution()->address }}</p>
                @endif
                @if($printInvoiceNos)
                    <p class="inv-print-invoice-ref">Invoice #{{ $printInvoiceNos }}</p>
                @endif
            </div>
            <div class="inv-print-doc-title">
                <span>FEE INVOICE</span>
            </div>
        </div>

        <div class="inv-print-student-card">
            <div>
                <span class="lbl">Student Name</span>
                <strong>{{ $student->name ?? '—' }}</strong>
            </div>
            <div>
                <span class="lbl">Student ID</span>
                <strong>{{ $student->student_id ?? '—' }}</strong>
            </div>
            <div>
                <span class="lbl">Class</span>
                <strong>{{ $student->class->name ?? '—' }}</strong>
            </div>
            <div>
                <span class="lbl">Section</span>
                <strong>{{ $student->section->name ?? '—' }}</strong>
            </div>
        </div>

        @foreach($selectedInvoices as $invoice)
            <div class="inv-print-block">
                @if($selectedInvoices->count() > 1)
                    <div class="inv-print-block-header">
                        <span class="inv-print-invoice-no">Invoice #{{ $invoice->invoice_no }}</span>
                    </div>
                @endif

                <table class="inv-print-table">
                    <thead>
                        <tr>
                            <th>Fees Type</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                            @php
                                $isMonthly   = $item->feeSetup?->frequency === 'monthly';
                                $feeTypeName = $item->feeSetup?->feeType?->name ?? '—';
                                $monthLabel  = $isMonthly ? \Carbon\Carbon::parse($invoice->invoice_date)->format('F') : null;
                            @endphp
                            <tr>
                                <td>{{ $feeTypeName }}{{ $monthLabel ? ' — '.$monthLabel : '' }}</td>
                                <td class="text-right">{{ number_format($item->base_amount, 0) }}</td>
                                <td class="text-right">{{ number_format($item->discount_amount, 0) }}</td>
                                <td class="text-right">{{ number_format($item->fine_amount, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="inv-print-block-summary">
                    <span>Total: <strong>{{ number_format($invoice->total_amount, 0) }}</strong></span>
                    <span>Paid: <strong>{{ number_format($invoice->paid_amount, 0) }}</strong></span>
                    <span>Due: <strong>{{ number_format($invoice->due_amount, 0) }}</strong></span>
                </div>
            </div>
        @endforeach

        <div class="inv-print-signatures">
            <div class="inv-print-sign-box">
                <div class="inv-print-sign-line"></div>
                <span>Received By</span>
            </div>
            <div class="inv-print-sign-box">
                <div class="inv-print-sign-line"></div>
                <span>Authorized Signature</span>
            </div>
        </div>

        <p class="inv-print-footnote">This is a computer-generated invoice and does not require a physical stamp.</p>
    </div>

    {{-- ============================================================ --}}
    {{-- PAYMENT COLLECT MODAL — ✅ FIX: এখন Root div-এর ভেতরেই আছে --}}
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
                <button type="button" class="btn-primary" wire:click="savePayment" wire:loading.attr="disabled">
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

    /* ---- Session Accordion (Image-এর মতো) ---- */
    .session-accordion-item {
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 10px;
        margin-bottom: 12px;
        overflow: hidden;
    }
    .session-accordion-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 14px 16px;
        background: rgba(224,82,82,.05);
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        user-select: none;
    }
    .session-accordion-header .chevron {
        transition: transform .2s;
    }
    .session-accordion-header.open .chevron {
        transform: rotate(180deg);
    }
    .session-accordion-header .session-count {
        color: #999;
        font-weight: 400;
        font-size: 12px;
        margin-left: 4px;
    }
    .session-accordion-body {
        padding: 12px 16px 16px;
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

        .session-accordion-body { display: block !important; }

        #invoicePrintable { display: none !important; }
    }
</style>
@endpush

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
            if (cb.checked !== checked) {
                cb.checked = checked;
                cb.dispatchEvent(new Event('change'));
            }
        });
    }

    function printSelectedInvoices() {
        const printableEl = document.getElementById('invoicePrintable');

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
                    <title>Fee Invoice{{ $student->name ? ' - '.$student->name : '' }}</title>
                    <style>
                        * { box-sizing: border-box; }

                        body {
                            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
                            padding: 36px 40px;
                            color: #2b2b2b;
                            font-size: 13px;
                            line-height: 1.5;
                        }

                        /* ── Header: Institution name + doc title ── */
                        .inv-print-header {
                            display: flex;
                            justify-content: space-between;
                            align-items: flex-start;
                            border-bottom: 3px solid #e05252;
                            padding-bottom: 14px;
                            margin-bottom: 18px;
                        }
                        .inv-print-institution h1 {
                            margin: 0 0 4px 0;
                            font-size: 22px;
                            font-weight: 700;
                            color: #1a1a1a;
                            letter-spacing: .3px;
                        }
                        .inv-print-address {
                            margin: 0;
                            font-size: 12px;
                            color: #777;
                        }
                        .inv-print-invoice-ref {
                            margin: 6px 0 0 0;
                            font-size: 13px;
                            font-weight: 600;
                            color: #e05252;
                        }
                        .inv-print-doc-title {
                            text-align: right;
                        }
                        .inv-print-doc-title span {
                            display: block;
                            font-size: 20px;
                            font-weight: 700;
                            color: #e05252;
                            letter-spacing: 1px;
                        }

                        /* ── Student info card ── */
                        .inv-print-student-card {
                            display: flex;
                            flex-wrap: wrap;
                            gap: 18px;
                            background: #f9f9f9;
                            border: 1px solid #eee;
                            border-radius: 8px;
                            padding: 14px 18px;
                            margin-bottom: 22px;
                        }
                        .inv-print-student-card > div {
                            flex: 1 1 20%;
                            min-width: 110px;
                        }
                        .inv-print-student-card .lbl {
                            display: block;
                            font-size: 10px;
                            text-transform: uppercase;
                            letter-spacing: .5px;
                            color: #999;
                            margin-bottom: 3px;
                        }
                        .inv-print-student-card strong {
                            font-size: 13.5px;
                            color: #1a1a1a;
                        }

                        /* ── Per-invoice block ── */
                        .inv-print-block {
                            margin-bottom: 20px;
                            page-break-inside: avoid;
                        }
                        .inv-print-block-header {
                            display: flex;
                            align-items: center;
                            gap: 12px;
                            background: #212529;
                            color: #fff;
                            padding: 8px 14px;
                            border-radius: 6px 6px 0 0;
                            font-size: 12.5px;
                        }
                        .inv-print-invoice-no { font-weight: 700; }

                        .inv-print-table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        .inv-print-table th, .inv-print-table td {
                            padding: 9px 14px;
                            text-align: left;
                            font-size: 12.5px;
                            border-bottom: 1px solid #eee;
                        }
                        .inv-print-table thead th {
                            background: #f5f5f5;
                            font-weight: 600;
                            font-size: 11px;
                            text-transform: uppercase;
                            letter-spacing: .3px;
                            color: #666;
                            border-bottom: 2px solid #ddd;
                        }
                        .inv-print-table tbody tr:nth-child(even) { background: #fafafa; }
                        .text-right { text-align: right !important; }

                        .inv-print-block-summary {
                            display: flex;
                            justify-content: flex-end;
                            gap: 24px;
                            padding: 10px 14px;
                            border: 1px solid #eee;
                            border-top: none;
                            border-radius: 0 0 6px 6px;
                            font-size: 12.5px;
                            background: #fcfcfc;
                        }
                        .inv-print-block-summary strong { color: #1a1a1a; }

                        /* ── Signatures ── */
                        .inv-print-signatures {
                            display: flex;
                            justify-content: space-between;
                            margin-top: 60px;
                            padding: 0 20px;
                        }
                        .inv-print-sign-box {
                            text-align: center;
                            width: 200px;
                            font-size: 11.5px;
                            color: #555;
                        }
                        .inv-print-sign-line {
                            border-top: 1px solid #999;
                            margin-bottom: 6px;
                        }

                        .inv-print-footnote {
                            margin-top: 28px;
                            text-align: center;
                            font-size: 10.5px;
                            color: #aaa;
                            font-style: italic;
                        }

                        @media print {
                            body { padding: 0; }
                        }
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