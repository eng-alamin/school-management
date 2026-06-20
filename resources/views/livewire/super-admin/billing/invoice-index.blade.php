{{-- resources/views/livewire/superadmin/billing/invoice-index.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">Manage Invoices</h5>
            <p id="cardHeaderSubtitle">Track school billing, payments, and apply discounts.</p>
        </div>

        {{-- Quick Stats --}}
        <div class="card-body pb-0">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="stat-box stat-paid">
                        <span class="material-icons-round">check_circle</span>
                        <div>
                            <div class="stat-label">Total Collected</div>
                            <div class="stat-value">৳ {{ number_format($totalPaid, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box stat-pending">
                        <span class="material-icons-round">hourglass_top</span>
                        <div>
                            <div class="stat-label">Pending Amount</div>
                            <div class="stat-value">৳ {{ number_format($totalPending, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-box stat-overdue">
                        <span class="material-icons-round">error</span>
                        <div>
                            <div class="stat-label">Overdue Invoices</div>
                            <div class="stat-value">{{ $totalOverdue }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search invoice or school" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:240px"/>
                    </div>
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
                    <select class="form-select form-select-sm" wire:model.live="filterMonth">
                        <option value="">All Months</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                        @endfor
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
                @if($invoices->total() > 15)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="15">15 / page</option>
                            <option value="25">25 / page</option>
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
                            <th>School</th>
                            <th>Invoice No</th>
                            <th>Period</th>
                            <th>Total</th>
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
                                        <span class="material-icons-round" style="font-size:1rem;">school</span>
                                    </div>
                                    <div class="fw-500 text-dark">{{ $invoice->school->name ?? '—' }}</div>
                                </div>
                            </td>

                            <td class="text-muted" style="font-size:.85rem;">{{ $invoice->invoice_no }}</td>

                            <td class="text-muted" style="font-size:.85rem;">
                                {{ \Carbon\Carbon::create($invoice->year, $invoice->month, 1)->format('M Y') }}
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
                                        'free'    => ['label' => 'Free', 'color' => 'badge-free'],
                                    ];
                                    $sc = $statusMap[$invoice->status] ?? $statusMap['pending'];
                                @endphp
                                <span class="badge rounded-pill {{ $sc['color'] }}" style="font-size:.72rem;">
                                    {{ $sc['label'] }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openDetail({{ $invoice->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <button class="act-btn edit" title="Apply Discount" wire:click="openDiscount({{ $invoice->id }})">
                                        <span class="material-icons-round">percent</span>
                                    </button>
                                    @if($invoice->status !== 'paid')
                                        <button class="act-btn status btn-success" title="Mark as Paid" wire:click="confirmMarkPaid({{ $invoice->id }})">
                                            <span class="material-icons-round">check_circle</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt display-5 d-block mb-2 opacity-25"></i>
                                No invoices found.
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
                            Invoice {{ $viewInvoice->invoice_no }} — {{ $viewInvoice->school->name ?? '' }}
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
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MARK AS PAID CONFIRM ===== --}}
    @if($showPaidModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="bi bi-check-circle text-success" style="font-size:1.5rem;"></i>
                        </div>
                        <h6 class="fw-700">Mark Invoice as Paid?</h6>
                        <p class="text-muted small">This will update the invoice status to Paid and record today's date as payment date.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('showPaidModal',false)">Cancel</button>
                        <button class="btn btn-success btn-sm" wire:click="markPaid">
                            <span wire:loading wire:target="markPaid" class="spinner-border spinner-border-sm me-1"></span>
                            Confirm Paid
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== DISCOUNT MODAL ===== --}}
    @if($showDiscountModal && $discountInvoice)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="material-icons-round me-2 text-danger" style="vertical-align:middle;">percent</span>
                            Apply Discount — {{ $discountInvoice->invoice_no }}
                        </h5>
                        <button class="btn-close" wire:click="$set('showDiscountModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="applyDiscount">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Total Amount</label>
                                    <input type="text" class="form-control" value="৳ {{ number_format($discountInvoice->total_amount, 2) }}" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Discount Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('discount') is-invalid @enderror" wire:model.defer="discount" placeholder="e.g. 100">
                                    @error('discount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <div class="alert alert-light border" style="font-size:.85rem;">
                                        Payable Amount after discount will be:
                                        <strong>৳ {{ number_format(max(0, $discountInvoice->total_amount - (float)($discount ?: 0)), 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showDiscountModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="applyDiscount" wire:loading.attr="disabled">
                            <span wire:loading wire:target="applyDiscount" class="spinner-border spinner-border-sm me-1"></span>
                            Apply Discount
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>


@push('styles')
    <style>
        /* ── BADGES ── */
        .badge-active   { background: rgba(34,197,94,.12);  color: #16a34a; }
        .badge-pending  { background: rgba(217,119,6,.12);  color: #d97706; }
        .badge-overdue  { background: rgba(220,38,38,.12);  color: #dc2626; }
        .badge-free     { background: rgba(220,38,38,.12);  color: #0280ce; }

        /* ── AVATAR ── */
        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem;
        }
        .invoice-avatar-paid    { background: rgba(34,197,94,.12);  color: #16a34a; }
        .invoice-avatar-pending { background: rgba(217,119,6,.12);  color: #d97706; }
        .invoice-avatar-overdue { background: rgba(220,38,38,.12);  color: #dc2626; }
        .invoice-avatar-free    { background: rgba(220,38,38,.12);  color: #0280ce; }

        /* ── STAT BOXES ── */
        .stat-box {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 16px; border-radius: 12px; margin-bottom: 16px;
        }
        .stat-box .material-icons-round { font-size: 2rem; }
        .stat-label { font-size: .72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; }
        .stat-value { font-size: 1.2rem; font-weight: 700; color: var(--dark); }

        .stat-paid    { background: #f0fdf4; }
        .stat-paid .material-icons-round    { color: #16a34a; }

        .stat-pending { background: #fffbeb; }
        .stat-pending .material-icons-round { color: #d97706; }

        .stat-overdue { background: #fef2f2; }
        .stat-overdue .material-icons-round { color: #dc2626; }

        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover, .btn-primary:focus { background: #d63e3e; border-color: #d63e3e; }
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
    </style>
@endpush