<div>
    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-pink-gradient">
            <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">receipt_long</span>Fee Invoices</h5>
            <p>View and manage student fee invoices by class and section</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">

                {{-- Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Search by name, roll or register no"
                            style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:240px"/>
                    </div>
                </div>

                {{-- Class filter --}}
                <div>
                    <select wire:model.live="filterClass" class="form-select form-select-sm" style="min-width:140px">
                        <option value="">All Classes</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section filter --}}
                <div>
                    <select wire:model.live="filterSection" class="form-select form-select-sm" style="min-width:140px"
                        {{ empty($availableSections) ? 'disabled' : '' }}>
                        <option value="">All Sections</option>
                        @if(!empty($availableSections))
                            @foreach ($availableSections as $s)
                                <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Per page --}}
                @if($students->total() > 10)
                    <div>
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table-loader">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-name" wire:click="sortBy('name')" style="cursor:pointer">
                                Name @if($sortField === 'name') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-class">Class</th>
                            <th id="th-section">Section</th>
                            <th id="th-register-no">Register No</th>
                            <th id="th-roll-no" wire:click="sortBy('roll_no')" style="cursor:pointer">
                                Roll No @if($sortField === 'roll_no') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-fee-items">Fee Items</th>
                            <th id="th-total">Total</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $i => $student)
                        @php
                            $invoices     = $student->feeInvoices;
                            $totalAmount  = $invoices->sum('total_amount');
                            $allPaid      = $invoices->isNotEmpty() && $invoices->every(fn($inv) => $inv->payment_status === 'paid');
                            $nonePaid     = $invoices->isEmpty() || $invoices->every(fn($inv) => $inv->payment_status === 'unpaid');
                            $feeItemNames = $invoices->flatMap->items->map(fn($item) => $item->feeGroupItem?->feeType?->name ?? '—')->unique();
                        @endphp
                        <tr wire:key="student-{{ $student->id }}">
                            <td class="text-muted">{{ $students->firstItem() + $i }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->class->name ?? '—' }}</td>
                            <td>{{ $student->section->name ?? '—' }}</td>
                            <td>{{ $student->student_id ?? '—' }}</td>
                            <td>{{ $student->roll_no ?? '—' }}</td>
                            <td>
                                @forelse ($feeItemNames as $name)
                                    <span class="fee-tag">{{ $name }}</span>
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>
                                @if ($invoices->isNotEmpty())
                                    <span class="amount-text">৳{{ number_format($totalAmount, 0) }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($invoices->isEmpty())
                                    <span class="text-muted">—</span>
                                @elseif ($allPaid)
                                    <span class="status-badge paid">Paid</span>
                                @elseif ($nonePaid)
                                    <span class="status-badge unpaid">Unpaid</span>
                                @else
                                    <span class="status-badge partial">Partial</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('accountant.student.invoice', ['id' => $student->id]) }}"
                                       target="_blank"
                                       class="act-btn view"
                                       title="View Invoice">
                                        <span class="material-icons-round">open_in_new</span>
                                    </a>
                                    <button class="act-btn delete"
                                            title="Delete All Invoices"
                                            wire:click="confirmDeleteRecord({{ $student->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.2">inbox</span>
                                No students with fee invoices found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} of {{ $students->total() }}</small>
            {{ $students->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- Delete Confirm Modal --}}
    @if ($confirmDelete)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <span class="material-icons-round text-danger" style="font-size:28px">warning</span>
                    </div>
                    <h6 class="fw-bold">Delete All Invoices?</h6>
                    <p class="text-muted small mb-0">This will remove all fee invoices (and their items) for this student. This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center border-0 pt-0 pb-3">
                    <button class="btn btn-light btn-sm px-4"
                            wire:click="$set('confirmDelete', false)">Cancel</button>
                    <button class="btn btn-danger btn-sm px-4"
                            wire:click="deleteRecord"
                            wire:loading.attr="disabled"
                            wire:target="deleteRecord">
                        <span wire:loading wire:target="deleteRecord"
                              class="spinner-border spinner-border-sm me-1"></span>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
    /* ── Table ── */
    .table-loader {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-loader thead th {
        padding: 10px 10px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        color: #aaa;
        white-space: nowrap;
    }
    .table-loader tbody td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-loader tbody tr {
        transition: background .15s;
    }
    .table-loader tbody tr:hover {
        background: rgba(255,255,255,.03);
    }

    /* ── Fee Tag ── */
    .fee-tag {
        display: inline-block;
        background: rgba(255,255,255,.06);
        border-radius: 4px;
        padding: 1px 7px;
        font-size: 11px;
        margin: 1px 2px 1px 0;
        white-space: nowrap;
    }

    /* ── Amount ── */
    .amount-text {
        font-weight: 600;
        font-size: 13px;
        color: #e05252;
    }

    /* ── Status Badge ── */
    .status-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .status-badge.paid {
        background: rgba(34,197,94,.15);
        color: #22c55e;
    }
    .status-badge.partial {
        background: rgba(234,179,8,.15);
        color: #eab308;
    }
    .status-badge.unpaid {
        background: rgba(239,68,68,.15);
        color: #ef4444;
    }
</style>
@endpush