<div>

    <div class="card">

        {{-- Floating header --}}
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllsections">My Leave Applications</h5>
            <p id="cardHeaderSubtitle">Apply for leave and track the status of your submitted applications.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">

                {{-- Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by category"
                               style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                {{-- Per page --}}
                @if($applications->total() > 10)
                    <div>
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <button class="btn-sm btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span>
                    <span>Apply Leave</span>
                </button>

            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible mx-3 mt-2 py-2 px-3" style="font-size:.82rem;border-radius:8px;">
                <span class="material-icons-round" style="font-size:15px;vertical-align:middle">check_circle</span>
                {{ session('success') }}
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible mx-3 mt-2 py-2 px-3" style="font-size:.82rem;border-radius:8px;">
                <span class="material-icons-round" style="font-size:15px;vertical-align:middle">error</span>
                {{ session('error') }}
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('leave_category_id')" style="cursor:pointer">
                                Leave Category @if($sortField === 'leave_category_id') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('start_date')" style="cursor:pointer">
                                Start Date @if($sortField === 'start_date') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('end_date')" style="cursor:pointer">
                                End Date @if($sortField === 'end_date') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('total_days')" style="cursor:pointer">
                                Days @if($sortField === 'total_days') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('created_at')" style="cursor:pointer">
                                Apply Date @if($sortField === 'created_at') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Reviewed By</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $i => $app)
                        @php
                            $canModify = in_array($app->status, ['pending', 'cancelled']);
                            $statusMap = [
                                'approved'  => ['label' => 'Approved',   'color' => '#28a745'],
                                'pending'   => ['label' => 'Pending',    'color' => '#fd7e14'],
                                'rejected'  => ['label' => 'Rejected',   'color' => '#dc3545'],
                                'cancelled' => ['label' => 'Cancelled',  'color' => '#6c757d'],
                            ];
                            $s = $statusMap[$app->status] ?? ['label' => ucfirst($app->status), 'color' => '#6c757d'];
                        @endphp
                        <tr>
                            <td class="text-muted">{{ $applications->firstItem() + $i }}</td>
                            <td>
                                @if($app->document_path)
                                    <span class="material-icons-round" style="font-size:13px;vertical-align:middle;color:var(--muted)">attach_file</span>
                                @endif
                                {{ optional($app->leaveCategory)->name ?? '—' }}
                            </td>
                            <td>{{ $app->start_date->format('d.M.Y') }}</td>
                            <td>{{ $app->end_date->format('d.M.Y') }}</td>
                            <td>{{ $app->total_days }}</td>
                            <td>{{ $app->created_at?->format('d.M.Y') }}</td>
                            <td>
                                <small>{{ optional($app->approvedByUser)->name ?? '—' }}</small>
                            </td>
                            <td>
                                <span style="display:inline-block;padding:2px 10px;border-radius:4px;border:1px solid {{ $s['color'] }};color:{{ $s['color'] }};font-size:.75rem;font-weight:600;">
                                    {{ $s['label'] }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    {{-- Detail --}}
                                    <button class="act-btn edit" title="Details" wire:click="openDetail({{ $app->id }})">
                                        <span class="material-icons-round">reorder</span>
                                    </button>
                                    {{-- Edit — শুধু pending/cancelled --}}
                                    @if($canModify)
                                        <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $app->id }})">
                                            <span class="material-icons-round">drive_file_rename_outline</span>
                                        </button>
                                    @else
                                        <button class="act-btn edit" title="Cannot edit" disabled style="opacity:.35;cursor:not-allowed;">
                                            <span class="material-icons-round">drive_file_rename_outline</span>
                                        </button>
                                    @endif
                                    {{-- Delete — শুধু pending/cancelled --}}
                                    @if($canModify)
                                        <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $app->id }})">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    @else
                                        <button class="act-btn delete" title="Cannot delete" disabled style="opacity:.35;cursor:not-allowed;">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No leave applications yet. <a href="#" wire:click.prevent="openCreate">Apply now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $applications->firstItem() ?? 0 }}–{{ $applications->lastItem() ?? 0 }} of {{ $applications->total() }}</small>
            {{ $applications->links('vendor.pagination.custom') }}
        </div>

    </div>


    {{-- ===== ADD / EDIT LEAVE MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">
                            <span class="material-icons-round me-1" style="vertical-align:middle;font-size:1.1rem">add_circle_outline</span>
                            {{ $editId ? 'Edit Leave Application' : 'Apply for Leave' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">

                            {{-- Leave Category --}}
                            <div class="col-md-12">
                                <label class="form-label">Leave Category <span class="text-danger">*</span></label>
                                <select wire:model="leave_category_id" class="form-select @error('leave_category_id') is-invalid @enderror">
                                    <option value="">Select</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('leave_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Leave Date Range --}}
                            <div class="col-md-12">
                                <label class="form-label">Leave Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text" style="border-radius:8px 0 0 8px;background:#f8f9fa;border:1px solid rgba(0,0,0,.1);">
                                        <span class="material-icons-round" style="font-size:16px;color:var(--muted)">calendar_month</span>
                                    </span>
                                    <input type="date" wire:model.live="start_date"
                                           class="form-control @error('start_date') is-invalid @enderror"
                                           style="border-radius:0;border-left:0;">
                                    <span class="input-group-text" style="background:#f8f9fa;border-left:0;border-right:0;border-color:rgba(0,0,0,.1);">—</span>
                                    <input type="date" wire:model.live="end_date"
                                           class="form-control @error('end_date') is-invalid @enderror"
                                           style="border-radius:0 8px 8px 0;">
                                </div>
                                @error('start_date') <div class="text-danger" style="font-size:.8rem">{{ $message }}</div> @enderror
                                @error('end_date')   <div class="text-danger" style="font-size:.8rem">{{ $message }}</div> @enderror
                                @if($start_date && $end_date)
                                    <small class="text-muted mt-1 d-block">
                                        <span class="material-icons-round" style="font-size:13px;vertical-align:middle">info</span>
                                        Total: <strong>{{ $this->getTotalDays() }} day(s)</strong>
                                    </small>
                                @endif
                            </div>

                            {{-- Reason --}}
                            <div class="col-md-12">
                                <label class="form-label">Reason</label>
                                <textarea class="form-control" wire:model.defer="reason" rows="3" placeholder="Leave reason..."></textarea>
                            </div>

                            {{-- Attachment --}}
                            <div class="col-md-12">
                                <label class="form-label">Attachment</label>
                                <div style="border:2px dashed rgba(0,0,0,.12);border-radius:8px;padding:24px;text-align:center;background:#f8f9fa;cursor:pointer"
                                     onclick="document.getElementById('leaveAttachment').click()">
                                    <span class="material-icons-round d-block mb-1" style="font-size:2rem;color:var(--muted)">cloud_upload</span>
                                    <small class="text-muted">Drag and drop a file here or click</small>
                                    <input type="file" id="leaveAttachment" wire:model="attachment" style="display:none">
                                </div>
                                @if($attachment)
                                    <small class="text-success mt-1 d-block">
                                        <span class="material-icons-round" style="font-size:13px;vertical-align:middle">check_circle</span>
                                        {{ $attachment->getClientOriginalName() }}
                                    </small>
                                @elseif($document_path)
                                    <small class="text-muted mt-1 d-block">
                                        <span class="material-icons-round" style="font-size:13px;vertical-align:middle">attach_file</span>
                                        Existing attachment
                                        <a href="{{ asset('storage/'.$document_path) }}" target="_blank" class="ms-1">View</a>
                                    </small>
                                @endif
                                @error('attachment') <div class="text-danger" style="font-size:.8rem">{{ $message }}</div> @enderror
                            </div>

                            {{-- Comments --}}
                            <div class="col-md-12">
                                <label class="form-label">Additional Comments</label>
                                <textarea class="form-control" wire:model.defer="comments" rows="3" placeholder="Any additional comments..."></textarea>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn bg-dark text-white" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            <span class="material-icons-round me-1" style="font-size:.9rem;vertical-align:middle">send</span>
                            {{ $editId ? 'Update' : 'Submit Application' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif


    {{-- ===== DETAIL MODAL (View Only) ===== --}}
    @if($showDetail)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">
                            <span class="material-icons-round me-1" style="vertical-align:middle;font-size:1.1rem">reorder</span>
                            Application Details
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showDetail',false)"></button>
                    </div>
                    <div class="modal-body">
                        @php
                            $detailStatusMap = [
                                'approved'  => ['label' => 'Approved',  'color' => '#28a745', 'bg' => '#d4edda'],
                                'pending'   => ['label' => 'Pending',   'color' => '#fd7e14', 'bg' => '#fff3cd'],
                                'rejected'  => ['label' => 'Rejected',  'color' => '#dc3545', 'bg' => '#f8d7da'],
                                'cancelled' => ['label' => 'Cancelled', 'color' => '#6c757d', 'bg' => '#e9ecef'],
                            ];
                            $ds = $detailStatusMap[$detail['status'] ?? 'pending'] ?? ['label' => ucfirst($detail['status'] ?? ''), 'color' => '#6c757d', 'bg' => '#e9ecef'];
                        @endphp

                        {{-- Status banner --}}
                        <div style="background:{{ $ds['bg'] }};border-radius:8px;padding:10px 16px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                            <span class="material-icons-round" style="font-size:18px;color:{{ $ds['color'] }}">
                                @if(($detail['status'] ?? '') === 'approved') check_circle
                                @elseif(($detail['status'] ?? '') === 'rejected') cancel
                                @elseif(($detail['status'] ?? '') === 'cancelled') block
                                @else hourglass_empty
                                @endif
                            </span>
                            <span style="font-weight:600;color:{{ $ds['color'] }};font-size:.875rem;">{{ $ds['label'] }}</span>
                        </div>

                        <table class="table table-borderless mb-0" style="font-size:.875rem;">
                            <tbody>
                                <tr>
                                    <td class="fw-600 text-nowrap" style="width:150px">Leave Category :</td>
                                    <td>{{ $detail['leave_category'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-600">Apply Date :</td>
                                    <td>{{ $detail['apply_date'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-600">Start Date :</td>
                                    <td>{{ $detail['start_date'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-600">End Date :</td>
                                    <td>{{ $detail['end_date'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-600">Total Days :</td>
                                    <td>{{ $detail['total_days'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-600">Reason :</td>
                                    <td>{{ $detail['reason'] ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-600">Attachment :</td>
                                    <td>
                                        @if(!empty($detail['document_path']))
                                            <a href="{{ asset('storage/'.$detail['document_path']) }}" target="_blank"
                                               class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;border-radius:6px;">
                                                <span class="material-icons-round" style="font-size:13px;vertical-align:middle">download</span>
                                                Download
                                            </a>
                                        @else
                                            <span class="text-muted">No attachment</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-600">Reviewed By :</td>
                                    <td>{{ $detail['reviewed_by'] ?? '—' }}</td>
                                </tr>
                                @if(($detail['approval_note'] ?? '—') !== '—')
                                <tr>
                                    <td class="fw-600">Review Note :</td>
                                    <td>{{ $detail['approval_note'] }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light btn-sm" wire:click="$set('showDetail',false)">Close</button>
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
                        <h6 class="fw-700">Delete Application?</h6>
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
    :root {
        --primary: rgba(33, 37, 41);
        --primary-light: rgba(239,84,84,.12);
    }
    .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
    .modal-title { font-weight: 600; font-size: 1rem; }
    .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
    .form-control, .form-select {
        border-radius: 8px; border: 1px solid var(--border);
        font-size: .875rem; padding: .45rem .75rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
    }
    .fw-600 { font-weight: 600; }
    .fw-700 { font-weight: 700; }
    .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
</style>
@endpush