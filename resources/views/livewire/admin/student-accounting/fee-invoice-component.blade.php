<div>
    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-pink-gradient">
            <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">receipt_long</span>Fee Invoices</h5>
            <p>View and manage student fee invoices by class and section</p>
        </div>

        {{-- Select Ground --}}
        <div class="form-section" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">tune</span> Select Ground
            </div>
            <div class="row g-4">

                {{-- Class --}}
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Class</label>
                        <select wire:model.live="filterClass" class="form-select">
                            <option value="">Select Class</option>
                            @foreach ($classes as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('filterClass') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Section --}}
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Section</label>
                        <select wire:model.live="filterSection" class="form-select"
                            {{ empty($sections) ? 'disabled' : '' }}>
                            <option value="">{{ !$filterClass ? 'Select Class First' : 'Select Section' }}</option>
                            @if (!empty($sections))
                                <option value="all">All Section</option>
                                @foreach ($sections as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Filter Button --}}
                <div class="col-md-12 text-center">
                    <button wire:click="filter"
                            wire:loading.attr="disabled"
                            wire:target="filter"
                            class="btn-pink w-100 d-flex justify-content-center align-items-center"
                            type="button">
                        <span wire:loading.remove wire:target="filter">
                            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">filter_alt</span> Filter
                        </span>
                        <span wire:loading wire:target="filter">
                            <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Filtering...
                        </span>
                    </button>
                </div>

            </div>
        </div>

        {{-- Student Invoice List --}}
        @if ($hasFiltered)
        <div class="form-section">
            <div class="section-heading">
                <span class="material-icons-round">receipt_long</span> Invoice List
                <span class="badge-count">{{ $students->count() }} Students</span>
            </div>

            @if ($students->count() > 0)

            {{-- Toolbar --}}
            <div class="alloc-toolbar">
                <div class="d-flex align-items-center gap-3">
                    <span class="alloc-counter">
                        <span class="material-icons-round" style="font-size:15px;vertical-align:middle">people</span>
                        {{ count($selectedStudents) }} / {{ $students->count() }} selected
                    </span>
                    <button class="btn-outline btn-sm-custom" type="button" wire:click="resetForm">
                        <span class="material-icons-round" style="font-size:14px">refresh</span> Reset
                    </button>
                </div>
            </div>

            <div class="table-responsive mt-2">
                <table class="table-loader">
                    <thead>
                        <tr>
                            <th style="width:44px"><input type="checkbox" class="alloc-checkbox" wire:model.live="selectAll"></th>
                            <th>SL</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Register No</th>
                            <th>Roll No</th>
                            <th>Mobile</th>
                            <th>Fee Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $i => $student)
                        @php
                            $invoice = $student->feeInvoices->first();
                        @endphp
                        <tr wire:key="student-{{ $student->id }}"
                            class="{{ in_array($student->id, $selectedStudents) ? 'row-selected' : '' }}">
                            <td>
                                <input type="checkbox"
                                       class="alloc-checkbox"
                                       wire:model.live="selectedStudents"
                                       value="{{ $student->id }}">
                            </td>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->class->name ?? '—' }}</td>
                            <td>{{ $student->section->name ?? '—' }}</td>
                            <td>{{ $student->student_id ?? '—' }}</td>
                            <td>{{ $student->roll_no ?? '—' }}</td>
                            <td>{{ $student->mobile ?? '—' }}</td>
                            <td>
                                @forelse ($student->feeInvoices as $inv)
                                    @foreach ($inv->items as $item)
                                        <span class="fee-tag">{{ $item->fee_type_name }}</span>
                                    @endforeach
                                @empty
                                    <span class="text-muted">—</span>
                                @endforelse
                            </td>
                            <td>
                                @if ($invoice)
                                    <span class="amount-text">৳{{ number_format($invoice->total_amount, 0) }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($invoice?->payment_status === 'paid')
                                    <span class="status-badge paid">Paid</span>
                                @elseif ($invoice?->payment_status === 'partial')
                                    <span class="status-badge partial">Partial</span>
                                @else
                                    <span class="status-badge unpaid">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.student.invoice', ['id' => $student->id]) }}"
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
                        @endforeach
                    </tbody>
                </table>
            </div>

            @else
            {{-- Empty State --}}
            <div class="empty-state">
                <span class="material-icons-round empty-icon">inbox</span>
                <p>No students with fee allocations found.</p>
            </div>
            @endif
        </div>
        @endif

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
                        <p class="text-muted small mb-0">This will remove all fee allocations and invoices for this student. This action cannot be undone.</p>
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
    .row-selected {
        background: rgba(224, 82, 82, .10) !important;
    }

    /* ── Checkbox ── */
    .alloc-checkbox {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #e05252;
    }

    /* ── Toolbar ── */
    .alloc-toolbar {
        display: flex;
        align-items: center;
        justify-content: end;
        padding: 10px 12px;
        background: rgba(255,255,255,.03);
        border-radius: 6px;
        border: 1px solid rgba(255,255,255,.06);
        margin-bottom: 4px;
    }
    .alloc-select-all {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        margin: 0;
    }
    .alloc-counter {
        font-size: 12px;
        color: #aaa;
    }
    .btn-sm-custom {
        font-size: 12px;
        padding: 4px 12px;
        height: auto;
    }

    /* ── Section Heading ── */
    .section-heading {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #bbb;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 16px;
    }
    .badge-count {
        margin-left: auto;
        background: rgba(224, 82, 82, .15);
        color: #e05252;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 20px;
        text-transform: none;
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

    /* ── Empty State ── */
    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #666;
    }
    .empty-icon {
        font-size: 48px;
        opacity: 0.2;
        display: block;
        margin-bottom: 10px;
    }
    .empty-state p {
        font-size: 13px;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el }) => {
            setTimeout(() => {
                el.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    if (!select.nextElementSibling || !select.nextElementSibling.classList.contains('custom-select-wrapper')) {
                        buildCustomSelect(select);
                    }
                });
                el.querySelectorAll('.input-group-outline input').forEach(function(input) {
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
            }, 0);
        });
    });
</script>
@endpush