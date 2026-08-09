<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5>Salary Templates</h5>
            <p>Manage salary templates, create, update, and organize salary grades easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Search"
                               class="tb-search" />
                    </div>
                </div>

                {{-- Right side --}}
                @if($templates->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <a href="{{ route('admin.salary.add-template') }}"
                   class="btn-sm btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> Add Template
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">
                                Template Name
                                @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('salary_grade')" style="cursor:pointer">
                                Salary Grade
                                @if($sortField === 'salary_grade') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('basic_salary')" style="cursor:pointer">
                                Basic Salary
                                @if($sortField === 'basic_salary') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Overtime Rate</th>
                            <th>Total Allowance</th>
                            <th>Total Deduction</th>
                            <th wire:click="sortBy('net_salary')" style="cursor:pointer">
                                Net Salary
                                @if($sortField === 'net_salary') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $i => $template)
                            <tr>
                                <td class="text-muted">{{ $templates->firstItem() + $i }}</td>
                                <td>{{ $template->name }}</td>
                                <td>{{ $template->salary_grade }}</td>
                                <td>৳{{ number_format($template->basic_salary, 0) }}</td>
                                <td>
                                    @if($template->overtime_rate)
                                        ৳{{ number_format($template->overtime_rate, 0) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>৳{{ number_format($template->total_allowance, 0) }}</td>
                                <td>৳{{ number_format($template->total_deduction, 0) }}</td>
                                <td>
                                    <span style="font-weight:600;color:#16a34a">
                                        ৳{{ number_format($template->net_salary, 0) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                         <button class="act-btn view" title="View" wire:click="openView({{ $template->id }})">
                                            <span class="material-icons-round">visibility</span>
                                        </button>
                                        <a href="{{ route('admin.salary.edit-template', ['id' => $template->id]) }}"
                                           class="act-btn edit" title="Edit">
                                            <span class="material-icons-round">drive_file_rename_outline</span>
                                        </a>
                                        <button class="act-btn delete" title="Delete"
                                                wire:click="confirmDeleteRecord({{ $template->id }})">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No salary templates found.
                                    <a href="{{ route('admin.salary.add-template') }}">Create one now</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $templates->firstItem() ?? 0 }}–{{ $templates->lastItem() ?? 0 }} of {{ $templates->total() }}</small>
            {{ $templates->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== VIEW MODAL (Redesigned) ===== --}}
    @if($showViewModal && $viewRecord)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.55);" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius:16px;overflow:hidden;">

                    {{-- Header --}}
                    <div class="modal-header border-0 text-white"
                         style="background:linear-gradient(135deg,#4338ca 0%,#7c3aed 100%);padding:20px 24px;">
                        <div>
                            <h5 class="mb-1 fw-700">{{ $viewRecord->name }}</h5>
                            <span class="badge bg-white text-dark" style="font-size:.72rem;font-weight:600;">
                                Grade: {{ $viewRecord->salary_grade }}
                            </span>
                        </div>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showViewModal', false)"></button>
                    </div>

                    <div class="modal-body p-4" style="background:#f8f9fc;">

                        {{-- Summary Cards --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="p-3" style="background:#fff;border-radius:12px;border:1px solid #eef0f5;">
                                    <small class="text-muted d-block mb-1">Basic Salary</small>
                                    <strong style="font-size:1.05rem;color:#1e293b;">৳{{ number_format($viewRecord->basic_salary, 0) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3" style="background:#fff;border-radius:12px;border:1px solid #eef0f5;">
                                    <small class="text-muted d-block mb-1">Overtime Rate</small>
                                    <strong style="font-size:1.05rem;color:#1e293b;">
                                        @if($viewRecord->overtime_rate)
                                            ৳{{ number_format($viewRecord->overtime_rate, 0) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 text-white" style="background:linear-gradient(135deg,#16a34a,#22c55e);border-radius:12px;">
                                    <small class="d-block mb-1" style="opacity:.85;">Net Salary</small>
                                    <strong style="font-size:1.15rem;">৳{{ number_format($viewRecord->net_salary, 0) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            {{-- Allowances --}}
                            <div class="col-md-6">
                                <div style="background:#fff;border-radius:12px;border:1px solid #eef0f5;overflow:hidden;">
                                    <div class="d-flex align-items-center justify-content-between px-3 py-2"
                                         style="background:#ecfdf5;border-bottom:1px solid #eef0f5;">
                                        <span class="fw-600" style="font-size:.85rem;color:#065f46;">
                                            <i class="bi bi-arrow-up-circle-fill me-1"></i> Allowances
                                        </span>
                                        <span class="fw-700" style="font-size:.85rem;color:#065f46;">
                                            ৳{{ number_format($viewRecord->total_allowance, 0) }}
                                        </span>
                                    </div>
                                    <div class="p-2">
                                        @forelse($viewRecord->allowances as $allowance)
                                            <div class="d-flex align-items-center justify-content-between px-2 py-2"
                                                 style="font-size:.82rem;{{ !$loop->last ? 'border-bottom:1px dashed #eef0f5;' : '' }}">
                                                <span class="text-muted">{{ $allowance->name }}</span>
                                                <span class="fw-600">৳{{ number_format($allowance->amount, 0) }}</span>
                                            </div>
                                        @empty
                                            <p class="text-muted text-center py-3 mb-0" style="font-size:.8rem;">No allowances added.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            {{-- Deductions --}}
                            <div class="col-md-6">
                                <div style="background:#fff;border-radius:12px;border:1px solid #eef0f5;overflow:hidden;">
                                    <div class="d-flex align-items-center justify-content-between px-3 py-2"
                                         style="background:#fef2f2;border-bottom:1px solid #eef0f5;">
                                        <span class="fw-600" style="font-size:.85rem;color:#991b1b;">
                                            <i class="bi bi-arrow-down-circle-fill me-1"></i> Deductions
                                        </span>
                                        <span class="fw-700" style="font-size:.85rem;color:#991b1b;">
                                            ৳{{ number_format($viewRecord->total_deduction, 0) }}
                                        </span>
                                    </div>
                                    <div class="p-2">
                                        @forelse($viewRecord->deductions as $deduction)
                                            <div class="d-flex align-items-center justify-content-between px-2 py-2"
                                                 style="font-size:.82rem;{{ !$loop->last ? 'border-bottom:1px dashed #eef0f5;' : '' }}">
                                                <span class="text-muted">{{ $deduction->name }}</span>
                                                <span class="fw-600">৳{{ number_format($deduction->amount, 0) }}</span>
                                            </div>
                                        @empty
                                            <p class="text-muted text-center py-3 mb-0" style="font-size:.8rem;">No deductions added.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer border-0" style="background:#f8f9fc;">
                        <button class="btn btn-light btn-sm" wire:click="$set('showViewModal', false)">Close</button>
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
                        <h6 class="fw-700">Delete Salary Template?</h6>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('confirmDelete', false)">Cancel</button>
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