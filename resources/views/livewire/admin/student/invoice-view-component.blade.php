<div>
    <div class="card border-0 bg-transparent">
        <div class="container-xl mt-4">

            {{-- Action Buttons --}}
            <div class="mb-3 no-print d-flex justify-content-between align-items-center">
                <a href="{{ route($routePrefix.'student.invoice', $student->id) }}"
                   class="btn btn-secondary d-inline-flex align-items-center gap-1">
                    <span class="material-icons-round" style="font-size:16px">arrow_back</span>
                    Back to Invoice List
                </a>

                <button class="btn btn-primary d-inline-flex align-items-center gap-1"
                        type="button" onclick="window.print()">
                    <span class="material-icons-round" style="font-size:16px">print</span>
                    Print Invoice
                </button>
            </div>

            {{-- ============================================================ --}}
            {{-- PRINTABLE INVOICE AREA --}}
            {{-- ============================================================ --}}
            <div class="invoice-printable">

                <div class="inv-print-header">
                    <div class="inv-print-institution">
                        <h1>{{ institution()->name ?? 'Institution Name' }}</h1>
                        @if(!empty(institution()->address))
                            <p class="inv-print-address">{{ institution()->address }}</p>
                        @endif
                        <p class="inv-print-invoice-ref">Invoice #{{ $invoice->invoice_no }}</p>
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
                    <div>
                        <span class="lbl">Invoice Date</span>
                        <strong>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d.M.Y') }}</strong>
                    </div>
                    <div>
                        <span class="lbl">Due Date</span>
                        <strong>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d.M.Y') : '—' }}</strong>
                    </div>
                    <div>
                        <span class="lbl">Status</span>
                        <strong>
                            @if($invoice->payment_status === 'paid')
                                <span class="inv-badge paid">Paid</span>
                            @elseif($invoice->payment_status === 'partial')
                                <span class="inv-badge partial">Partial</span>
                            @else
                                <span class="inv-badge unpaid">Unpaid</span>
                            @endif
                        </strong>
                    </div>
                </div>

                <div class="inv-print-block">
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
                            @forelse($invoice->items as $item)
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
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No fee items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="inv-print-block-summary">
                        <span>Total: <strong>{{ number_format($invoice->total_amount, 0) }}</strong></span>
                        <span>Paid: <strong>{{ number_format($invoice->paid_amount, 0) }}</strong></span>
                        <span>Due: <strong>{{ number_format($invoice->due_amount, 0) }}</strong></span>
                    </div>
                </div>

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

        </div>
    </div>

    @push('styles')
    <style>
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
        .inv-print-doc-title { text-align: right; }
        .inv-print-doc-title span {
            display: block;
            font-size: 20px;
            font-weight: 700;
            color: #e05252;
            letter-spacing: 1px;
        }
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
        .inv-print-block { margin-bottom: 20px; }
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
        .inv-badge {
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .inv-badge.paid { background: #e6f9ed; color: #1e9e5a; }
        .inv-badge.partial { background: #fff6e0; color: #c98a00; }
        .inv-badge.unpaid { background: #fde9e9; color: #e05252; }

        /* ── Print Mode: শুধু .invoice-printable অংশটাই প্রিন্ট হবে ── */
        @media print {
            .no-print { display: none !important; }
            body * { visibility: hidden; }
            .invoice-printable, .invoice-printable * { visibility: visible; }
            .invoice-printable {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                padding: 20px;
            }
        }
    </style>
    @endpush
</div>