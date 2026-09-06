<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Student Fine</h5>
            <p id="cardHeaderSubtitle">Add Student Fine for any reason including Absent, Indiscipline.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search by student name/ID" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:240px"/>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select class="form-select form-select-sm" wire:model.live="filterStatus">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="invoiced">Invoiced</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select class="form-select form-select-sm" wire:model.live="filterReason">
                            <option value="all">All Reasons</option>
                            @foreach($reasonOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($studentFines->total() > 10)
                    <div class="col-md-2">
                        <div class="input-group input-group-outline">
                            <select class="form-select form-select-sm" wire:model.live="perPage">
                                <option value="10">10 / page</option>
                                <option value="25">25 / page</option>
                                <option value="50">50 / page</option>
                            </select>
                        </div>
                    </div>
                @endif

                <button class="btn btn-primary" wire:click="openCreate">
                    <span>
                        <span class="material-icons-round">add</span> 
                        <span>Add Fine</span>
                    </span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-student">Student</th>
                            <th id="th-reason">Reason</th>
                            <th id="th-amount">Amount</th>
                            <th id="th-date">Fine Date</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentFines as $i => $fine)
                        <tr wire:key="student-fine-{{ $fine->id }}">
                            <td class="text-muted">{{ $studentFines->firstItem() + $i }}</td>
                            <td>
                                {{ $fine->student->name ?? '—' }}
                                <br><small class="text-muted">{{ $fine->student->student_id ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $reasonOptions[$fine->reason] ?? ucfirst($fine->reason) }}</span>
                            </td>
                            <td>৳{{ number_format($fine->amount, 0) }}</td>
                            <td>{{ $fine->fine_date->format('d M, Y') }}</td>
                            <td>
                                @if($fine->status === 'pending')
                                    <span class="badge badge-inactive">Pending</span>
                                @else
                                    <span class="badge badge-active">Invoiced</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $fine->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    @if($fine->status === 'pending')
                                        <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $fine->id }})">
                                            <span class="material-icons-round">drive_file_rename_outline</span>
                                        </button>
                                        <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $fine->id }})">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No student fines found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $studentFines->firstItem() ?? 0 }}–{{ $studentFines->lastItem() ?? 0 }} of {{ $studentFines->total() }}</small>
            {{ $studentFines->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">{{ $editId ? 'Edit' : 'Add' }} Student Fine</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                <div class="col-md-12">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Student <span class="text-danger">*</span></label>
                                        <select class="form-select selectpicker @error('student_id') is-invalid @enderror" wire:model.live="student_id">
                                            <option value="">— Select Student —</option>
                                            @foreach($students as $s)
                                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->student_id }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('student_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                                        <select class="form-select @error('reason') is-invalid @enderror" wire:model.defer="reason">
                                            @foreach($reasonOptions as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Amount (৳) <span class="text-danger">*</span></label>
                                        <input type="number" step="1" min="0" class="form-control @error('amount') is-invalid @enderror" wire:model.defer="amount">
                                    </div>
                                    @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Fine Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('fine_date') is-invalid @enderror" wire:model.defer="fine_date">
                                    </div>
                                    @error('fine_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Remarks</label>
                                        <textarea class="form-control @error('remarks') is-invalid @enderror" wire:model.defer="remarks" rows="2"></textarea>
                                    </div>
                                    @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== VIEW MODAL ===== --}}
    @if($showViewModal && $viewFine)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h5 class="modal-title mb-1">{{ $viewFine->student->name ?? '—' }}</h5>
                            <span class="text-muted small">{{ $viewFine->student->student_id ?? '—' }}</span>
                        </div>
                        <button type="button" class="btn-close" wire:click="$set('showViewModal',false)"></button>
                    </div>
                    <div class="modal-body pt-2">
                        <div class="fine-amount-highlight">
                            <span class="fine-amount-label">Fine Amount</span>
                            <span class="fine-amount-value">৳{{ number_format($viewFine->amount, 2) }}</span>
                        </div>

                        <div class="fine-detail-grid">
                            <div class="fine-detail-item">
                                <span class="fine-detail-label"><span class="material-icons-round">sell</span> Reason</span>
                                <span class="badge bg-secondary">{{ $reasonOptions[$viewFine->reason] ?? ucfirst($viewFine->reason) }}</span>
                            </div>
                            <div class="fine-detail-item">
                                <span class="fine-detail-label"><span class="material-icons-round">event</span> Fine Date</span>
                                <span class="small">{{ $viewFine->fine_date->format('d M, Y') }}</span>
                            </div>
                            <div class="fine-detail-item">
                                <span class="fine-detail-label"><span class="material-icons-round">toggle_on</span> Status</span>
                                @if($viewFine->status === 'pending')
                                    <span class="badge badge-inactive">Pending</span>
                                @else
                                    <span class="badge badge-active">Invoiced</span>
                                @endif
                            </div>
                            <div class="fine-detail-item">
                                <span class="fine-detail-label"><span class="material-icons-round">person</span> Added By</span>
                                <span class="small">{{ $viewFine->creator->name ?? '—' }}</span>
                            </div>
                        </div>

                        @if($viewFine->remarks)
                            <div class="mt-3">
                                <span class="fine-detail-label d-block mb-1"><span class="material-icons-round">notes</span> Remarks</span>
                                <p class="small mb-0">{{ $viewFine->remarks }}</p>
                            </div>
                        @endif

                        @if($viewFine->feeInvoice)
                            <div class="mt-3">
                                <span class="fine-detail-label d-block mb-1"><span class="material-icons-round">receipt_long</span> Invoiced In</span>
                                <p class="small mb-0">{{ $viewFine->feeInvoice->invoice_no ?? '—' }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showViewModal',false)">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== DELETE CONFIRM ===== --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                        </div>
                        <h6 class="fw-700">Delete Student Fine?</h6>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('confirmDelete',false)">Cancel</button>
                        <button class="btn btn-danger btn-sm" wire:click="deleteRecord">
                            <span wire:loading wire:target="deleteRecord" class="spinner-border spinner-border-sm me-1"></span>
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
        .fine-amount-highlight {
            background: linear-gradient(135deg, #fafafa, #f2f2f2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px 18px;
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px;
        }
        .fine-amount-label { font-size: .78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; }
        .fine-amount-value { font-size: 1.4rem; font-weight: 700; color: var(--dark); }
        .fine-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .fine-detail-item {
            display: flex; flex-direction: column; gap: 6px;
            background: #fafafa; border: 1px solid var(--border);
            border-radius: 10px; padding: 10px 12px;
        }
        .fine-detail-label {
            font-size: .72rem; font-weight: 600; color: var(--text-muted);
            display: flex; align-items: center; gap: 4px;
            text-transform: uppercase; letter-spacing: .03em;
        }
        .fine-detail-label .material-icons-round { font-size: 14px; }
    </style>
@endpush

@push('scripts')
    <script>
        function initFineSelectpicker() {
            if (window.jQuery && jQuery.fn.selectpicker) {
                jQuery('.selectpicker').selectpicker('destroy');
                jQuery('.selectpicker').selectpicker();
            }
        }
        document.addEventListener('livewire:init', () => {
            initFineSelectpicker();
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => setTimeout(initFineSelectpicker, 50));
            });
        });
    </script>
@endpush