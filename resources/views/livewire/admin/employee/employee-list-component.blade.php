{{-- livewire/admin/employee/employee-list-component.blade.php --}}

<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="emp-list-header-title">All Employees</h5>
            <p id="emp-list-header-subtitle">Manage employees, view details, and organize easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                @if($employees->total() > 10)
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

                <a href="{{ route($routePrefix . 'employee.add') }}" class="btn btn-primary">
                    <span>
                        <span class="material-icons-round">add_circle</span> 
                        <span>New Employee</span>
                    </span>
                </a>

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">
                                <span id="th-name">Name</span> @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-role">Role</th>
                            <th id="th-designation">Designation</th>
                            <th id="th-department">Department</th>
                            <th wire:click="sortBy('email')" style="cursor:pointer">
                                <span id="th-email">Email</span> @if($sortField === 'email') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('mobile')" style="cursor:pointer">
                                <span id="th-phone">Phone</span> @if($sortField === 'mobile') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $i => $employee)
                        <tr>
                            <td class="text-muted">{{ $employees->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $employee->photo ? asset('storage/' . $employee->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->name) . '&size=64&background=random' }}"
                                        alt="{{ $employee->name }}"
                                        style="width:32px;height:32px;border-radius:50%;object-fit:cover;"/>
                                    <span>
                                        <span>{{ $employee->name }}</span> <br>
                                        <span class="fs-8 fw-bold">{{ $employee->employee_id }}</span>
                                    </span>
                                </div>
                            </td>
                            <td> <span class="badge bg-secondary">{{ $employee->user?->role ?? '—' }} </span></td>
                            <td>{{ $employee->designation?->name ?? '—' }}</td>
                            <td>{{ $employee->department?->name ?? '—' }}</td>
                            <td>{{ $employee->email ?? '—' }}</td>
                            <td>{{ $employee->mobile ?? '—' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active'     => 'bg-success',
                                        'inactive'   => 'bg-secondary',
                                        'resigned'   => 'bg-warning text-dark',
                                        'terminated' => 'bg-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$employee->status] ?? 'bg-secondary' }} text-capitalize">
                                    {{ $employee->status }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route($routePrefix . 'employee.view', ['id' => $employee->id]) }}" target="_blank"
                                        class="act-btn view" title="View">
                                        <span class="material-icons-round">visibility</span>
                                    </a>
                                    <a href="{{ route($routePrefix . 'employee.edit', ['id' => $employee->id]) }}"
                                       class="act-btn edit" title="Edit">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </a>
                                    <button class="act-btn" title="Change Status"
                                            wire:click="openStatusModal({{ $employee->id }})">
                                        <span class="material-icons-round">toggle_on</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete"
                                            wire:click="confirmDeleteRecord({{ $employee->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No employees found.
                                <a href="{{ route($routePrefix . 'employee.add') }}">Add one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $employees->firstItem() ?? 0 }}–{{ $employees->lastItem() ?? 0 }} of {{ $employees->total() }}</small>
            {{ $employees->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== DELETE CONFIRM ===== --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                        </div>
                        <h6 class="fw-700" id="emp-delete-title">Delete Employee?</h6>
                        <p class="text-muted small" id="emp-delete-msg">This action cannot be undone.</p>
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

    {{-- ===== STATUS UPDATE MODAL ===== --}}
    @if($showStatusModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-4 px-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <span class="material-icons-round text-danger" style="font-size:1.5rem;">toggle_on</span>
                        </div>
                        <h6 class="fw-700 mb-1" id="emp-status-title">Change Employee Status</h6>
                        <p class="text-muted small mb-3" id="emp-status-msg">Select a new status for this employee.</p>

                        <div class="text-start">
                            <select wire:model="newStatus" class="form-select no-custom-select @error('newStatus') is-invalid @enderror">
                                @foreach($statusOptions as $option)
                                    <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('newStatus') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        @error('statusId') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="closeStatusModal">Cancel</button>
                        <button class="btn btn-danger btn-sm" wire:click="updateStatus" wire:loading.attr="disabled" wire:target="updateStatus">
                            <span wire:loading wire:target="updateStatus" class="spinner-border spinner-border-sm me-1"></span>
                            Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>