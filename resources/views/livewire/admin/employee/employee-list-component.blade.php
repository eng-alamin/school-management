{{-- livewire/theme/employee-list-component.blade.php --}}

<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="emp-list-header-title">All Employees</h5>
            <p id="emp-list-header-subtitle">Manage employees, view details, and organize easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                @if($employees->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <a href="{{ route('admin.employee.add') }}"
                   class="btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> New Employee
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="emp-th-sl">SL</th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">
                                <span id="emp-th-name">Name</span> @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="emp-th-role">Role</th>
                            <th id="emp-th-designation">Designation</th>
                            <th id="emp-th-department">Department</th>
                            <th wire:click="sortBy('email')" style="cursor:pointer">
                                <span id="emp-th-email">Email</span> @if($sortField === 'email') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('phone')" style="cursor:pointer">
                                <span id="emp-th-phone">Phone</span> @if($sortField === 'phone') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="emp-th-actions">Actions</th>
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
                                    <span>{{ $employee->name }}</span>
                                </div>
                            </td>
                            <td> <span class="badge bg-secondary">{{ $employee->user?->role ?? '—' }} </span></td>
                            <td>{{ $employee->designation?->name ?? '—' }}</td>
                            <td>{{ $employee->department?->name ?? '—' }}</td>
                            <td>{{ $employee->email ?? '—' }}</td>
                            <td>{{ $employee->phone ?? '—' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.employee.view', ['id' => $employee->id]) }}" target="_blank"
                                        class="act-btn view" title="View">
                                        <span class="material-icons-round">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.employee.edit', ['id' => $employee->id]) }}"
                                       class="act-btn edit" title="Edit">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </a>
                                    <button class="act-btn delete" title="Delete"
                                            wire:click="confirmDeleteRecord({{ $employee->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No employees found.
                                <a href="{{ route('admin.employee.add') }}">Add one now</a>.
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

</div>