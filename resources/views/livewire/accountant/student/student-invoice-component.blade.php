<div class="mat-card" style="padding-top:28px">

    {{-- Floating Header --}}
    <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleStudentOverview">Invoice History</h5>
    </div>

    <div class="container-xl mt-4">

        @include('livewire.accountant.student.student-navbar')

        {{-- Collect Button --}}
        <div class="mb-3">
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
                        <th style="width:42px">
                            <input type="checkbox" class="alloc-checkbox"
                                   wire:model.live="selectAll">
                        </th>
                        <th>SL#</th>
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
                    @php
                        $sl            = 1;
                        $grandTotal    = 0;
                        $grandDiscount = 0;
                        $grandFine     = 0;
                        $grandPaid     = 0;
                    @endphp

                    @forelse($feeAllocations as $allocation)

                    {{-- Group Header --}}
                    <tr class="group-header-row">
                        <td colspan="10">
                            <span class="material-icons-round"
                                  style="font-size:14px;vertical-align:middle;color:#e05252">
                                arrow_drop_down
                            </span>
                            {{ $allocation->feeGroup->name }}
                        </td>
                    </tr>

                    @forelse($allocation->feeGroup->items as $item)
                    @php
                        $invItem  = $invoiceItemsMap[$item->id] ?? null;
                        $amount   = (float) ($item->amount ?? 0);
                        $discount = (float) ($invItem?->discount_amount ?? 0);
                        $fine     = (float) ($invItem?->fine_amount ?? 0);
                        $paid     = (float) ($invItem?->total_paid ?? 0);
                        $balance  = max(0, $amount - $discount + $fine - $paid);
                        $status   = $invItem?->payment_status ?? 'unpaid';
                        $dueDate  = $invItem?->invoice?->due_date;

                        $grandTotal    += $amount;
                        $grandDiscount += $discount;
                        $grandFine     += $fine;
                        $grandPaid     += $paid;
                    @endphp
                    <tr wire:key="item-{{ $item->id }}"
                        class="{{ in_array($item->id, $selectedIds) ? 'row-selected' : '' }}">

                        <td>
                            <input type="checkbox" class="alloc-checkbox"
                                   wire:model.live="selectedIds"
                                   value="{{ $item->id }}">
                        </td>
                        <td class="text-muted">{{ $sl++ }}</td>
                        <td>{{ $item->feeType?->name ?? '—' }}</td>
                        <td>
                            {{ $dueDate ? \Carbon\Carbon::parse($dueDate)->format('d.M.Y') : '—' }}
                        </td>
                        <td>
                            @if($status === 'paid')
                                <span class="inv-badge paid">Total Paid</span>
                            @elseif($status === 'partial')
                                <span class="inv-badge partial">Partial</span>
                            @else
                                <span class="inv-badge unpaid">Unpaid</span>
                            @endif
                        </td>
                        <td>{{ number_format($amount, 2) }}</td>
                        <td>{{ number_format($discount, 2) }}</td>
                        <td>{{ number_format($fine, 2) }}</td>
                        <td>{{ number_format($paid, 2) }}</td>
                        <td>{{ number_format($balance, 2) }}</td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-3 text-muted">
                            No fee items found.
                        </td>
                    </tr>
                    @endforelse

                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                            No fee allocations found for this student.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Grand Total --}}
        @if($feeAllocations->isNotEmpty())
        <div class="invoice-summary">
            <div class="inv-summary-row">
                <span>Grand Total :</span>
                <span>{{ number_format($grandTotal, 2) }}</span>
            </div>
            <div class="inv-summary-row">
                <span>Discount :</span>
                <span>{{ number_format($grandDiscount, 2) }}</span>
            </div>
            <div class="inv-summary-row">
                <span>Fine :</span>
                <span>{{ number_format($grandFine, 2) }}</span>
            </div>
            <div class="inv-summary-row">
                <span>Paid :</span>
                <span>{{ number_format($grandPaid, 2) }}</span>
            </div>
            <div class="inv-summary-row fw-bold">
                <span>Balance :</span>
                <span>{{ number_format($grandTotal - $grandDiscount + $grandFine - $grandPaid, 2) }}</span>
            </div>
        </div>
        @endif

        {{-- Footer --}}
        <div class="footer-actions mt-3">
            <a href="#" class="btn btn-sm btn-dark">
                <span class="material-icons-round">print</span> Print
            </a>
        </div>

    </div>
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
</style>
@endpush