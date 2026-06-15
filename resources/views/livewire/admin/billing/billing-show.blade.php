<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Billing & Invoices</h5>
            <p id="cardHeaderSubtitle">View your monthly usage bills and payment status.</p>
        </div>

        {{-- Current Month Usage Estimate --}}
        <div class="card-body pb-0">
            <div class="usage-estimate-box">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <div style="font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
                            Current Month Estimate ({{ now()->format('F Y') }})
                        </div>
                        <div style="font-size:1.4rem;font-weight:700;color:var(--dark);margin-top:4px;">
                            ৳ {{ number_format($estimatedBill, 2) }}
                        </div>
                        <small class="text-muted">
                            {{ number_format($activeStudentCount) }} active students × ৳{{ number_format($rate, 2) }}/student
                            — Final bill generated on 1st of next month
                        </small>
                    </div>
                    <div>
                        <span class="material-icons-round" style="font-size:2.5rem;color:var(--primary);opacity:.2;">groups</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <h6 class="mb-0">Invoice History</h6>
                </div>

                <!-- Right Side -->
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterStatus">
                        <option value="">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterYear">
                        <option value="">All Years</option>
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                @if($invoices->total() > 12)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="12">12 / page</option>
                            <option value="24">24 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Invoice No</th>
                            <th>Period</th>
                            <th>Total Amount</th>
                            <th>Discount</th>
                            <th>Payable</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $i => $invoice)
                        <tr>
                            <td class="text-muted">{{ $invoices->firstItem() + $i }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-placeholder invoice-avatar-{{ $invoice->status }}">
                                        <span class="material-icons-round" style="font-size:1rem;">receipt_long</span>
                                    </div>
                                    <div class="fw-500 text-dark">{{ $invoice->invoice_no }}</div>
                                </div>
                            </td>

                            <td class="text-muted" style="font-size:.85rem;">
                                {{ \Carbon\Carbon::create($invoice->year, $invoice->month, 1)->format('F Y') }}
                            </td>

                            <td>৳ {{ number_format($invoice->total_amount, 2) }}</td>

                            <td class="text-muted">
                                @if($invoice->discount > 0)
                                    <span class="text-success">- ৳ {{ number_format($invoice->discount, 2) }}</span>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="fw-600">৳ {{ number_format($invoice->payable_amount, 2) }}</td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
                            </td>

                            <td>
                                @php
                                    $statusMap = [
                                        'paid'    => ['label' => 'Paid',    'color' => 'badge-active'],
                                        'pending' => ['label' => 'Pending', 'color' => 'badge-pending'],
                                        'overdue' => ['label' => 'Overdue', 'color' => 'badge-overdue'],
                                    ];
                                    $sc = $statusMap[$invoice->status] ?? $statusMap['pending'];
                                @endphp
                                <span class="badge rounded-pill {{ $sc['color'] }}" style="font-size:.72rem;">
                                    {{ $sc['label'] }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View Details" wire:click="openDetail({{ $invoice->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    @if($invoice->status !== 'paid')
                                        {{-- ✅ SSLCommerz: full page redirect দরকার, তাই wire:navigate নেই --}}
                                        <a href="{{ route('billing.pay', $invoice->id) }}"
                                           class="act-btn status btn-success pay-now-btn"
                                           title="Pay Now"
                                           onclick="handlePayNow(this)">
                                            <span class="material-icons-round pay-icon">payments</span>
                                            <span class="pay-spinner d-none">
                                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                            </span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt display-5 d-block mb-2 opacity-25"></i>
                                No invoices found yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $invoices->firstItem() ?? 0 }}–{{ $invoices->lastItem() ?? 0 }} of {{ $invoices->total() }}</small>
            {{ $invoices->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== INVOICE DETAIL MODAL ===== --}}
    @if($showDetailModal && $viewInvoice)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="material-icons-round me-2 text-danger" style="vertical-align:middle;">receipt_long</span>
                            Invoice {{ $viewInvoice->invoice_no }}
                        </h5>
                        <button class="btn-close" wire:click="$set('showDetailModal',false)"></button>
                    </div>
                    <div class="modal-body">

                        @php
                            $statusMap = [
                                'paid'    => ['label' => 'Paid',    'color' => '#16a34a'],
                                'pending' => ['label' => 'Pending', 'color' => '#d97706'],
                                'overdue' => ['label' => 'Overdue', 'color' => '#dc2626'],
                            ];
                            $sc = $statusMap[$viewInvoice->status] ?? $statusMap['pending'];
                        @endphp
                        <div style="border-left:4px solid {{ $sc['color'] }};padding:12px 16px;background:#f8f9fa;border-radius:0 8px 8px 0;margin-bottom:16px;">
                            <div style="font-size:.7rem;font-weight:600;color:{{ $sc['color'] }};text-transform:uppercase;letter-spacing:.05em;">
                                {{ $sc['label'] }}
                            </div>
                            <div style="font-weight:700;font-size:.95rem;margin-top:4px;">
                                {{ \Carbon\Carbon::create($viewInvoice->year, $viewInvoice->month, 1)->format('F Y') }} Bill
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Usage Type</th>
                                        <th>Quantity</th>
                                        <th>Rate</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewInvoice->items as $item)
                                    <tr>
                                        <td>{{ ucfirst($item->type) }}</td>
                                        <td>{{ number_format($item->quantity) }}</td>
                                        <td>৳ {{ number_format($item->rate, 2) }}</td>
                                        <td class="text-end">৳ {{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-600">Total</td>
                                        <td class="text-end fw-600">৳ {{ number_format($viewInvoice->total_amount, 2) }}</td>
                                    </tr>
                                    @if($viewInvoice->discount > 0)
                                    <tr>
                                        <td colspan="3" class="text-end text-success">Discount</td>
                                        <td class="text-end text-success">- ৳ {{ number_format($viewInvoice->discount, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td colspan="3" class="text-end fw-700">Payable</td>
                                        <td class="text-end fw-700">৳ {{ number_format($viewInvoice->payable_amount, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <table class="table table-sm mt-2">
                            <tr>
                                <th class="text-muted" style="width:40%">Due Date</th>
                                <td>{{ \Carbon\Carbon::parse($viewInvoice->due_date)->format('d M Y') }}</td>
                            </tr>
                            @if($viewInvoice->paid_at)
                            <tr>
                                <th class="text-muted">Paid On</th>
                                <td>{{ \Carbon\Carbon::parse($viewInvoice->paid_at)->format('d M Y') }}</td>
                            </tr>
                            @endif
                        </table>

                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" wire:click="$set('showDetailModal',false)">Close</button>
                        @if($viewInvoice->status !== 'paid')
                            {{-- ✅ SSLCommerz: full page redirect দরকার, তাই wire:navigate নেই --}}
                            <a href="{{ route('billing.pay', $viewInvoice->id) }}"
                               class="btn btn-primary pay-now-btn"
                               onclick="handlePayNow(this)">
                                <span class="material-icons-round me-1 pay-icon" style="font-size:.9rem;vertical-align:middle;">payments</span>
                                <span class="pay-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                </span>
                                <span class="pay-label">Pay Now</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>


@push('styles')
    <style>
        /* ── BADGES ── */
        .badge-pending  { background: rgba(217,119,6,.12); color: #d97706; }
        .badge-overdue  { background: rgba(220,38,38,.12); color: #dc2626; }

        /* ── AVATAR ── */
        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem;
        }
        .invoice-avatar-paid    { background: rgba(34,197,94,.12);  color: #16a34a; }
        .invoice-avatar-pending { background: rgba(217,119,6,.12);  color: #d97706; }
        .invoice-avatar-overdue { background: rgba(220,38,38,.12);  color: #dc2626; }

        /* ── USAGE ESTIMATE BOX ── */
        .usage-estimate-box {
            background: linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%);
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 16px;
        }

        /* ── PAY NOW LOADING ── */
        .pay-now-btn.loading {
            pointer-events: none;
            opacity: .75;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function handlePayNow(el) {
            // Double-click থেকে রক্ষা করো
            if (el.classList.contains('loading')) return false;

            el.classList.add('loading');

            // Icon hide, spinner show
            const icon    = el.querySelector('.pay-icon');
            const spinner = el.querySelector('.pay-spinner');
            if (icon)    icon.classList.add('d-none');
            if (spinner) spinner.classList.remove('d-none');

            // Normal href redirect চলতে দাও (SSLCommerz এর জন্য)
            return true;
        }
    </script>
@endpush