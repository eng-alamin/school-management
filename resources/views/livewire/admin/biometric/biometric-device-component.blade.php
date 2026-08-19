{{-- resources/views/livewire/admin/biometric-device-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Biometric Devices</h5>
            <p id="cardHeaderSubtitle">Register and manage attendance / access control devices.</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search name or serial" class="tb-search"/>
                    </div>
                </div>

                <!-- Right Side -->
                <div class="col-md-2">
                    <select class="form-select form-select-sm" wire:model.live="filterStatus">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                @if($devices->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
                <button class="btn btn-primary" wire:click="openCreateModal">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Add Device</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-name" role="button" wire:click="sortBy('device_name')">
                                Name
                                @if($sortField === 'device_name')
                                    <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                @endif
                            </th>
                            <th id="th-serial" role="button" wire:click="sortBy('device_serial')">Serial</th>
                            <th id="th-type" role="button" wire:click="sortBy('device_type')">Type</th>
                            <th id="th-users">Mapped Users</th>
                            <th id="th-lastseen" role="button" wire:click="sortBy('last_seen_at')">Last Seen</th>
                            <th id="th-status" role="button" wire:click="sortBy('is_active')">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $i => $device)
                        <tr wire:key="device-{{ $device->id }}">
                            <td class="text-muted">{{ $devices->firstItem() + $i }}</td>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-placeholder device-avatar-{{ $device->is_active ? 'active' : 'inactive' }}">
                                        <span class="material-icons-round" style="font-size:1rem;">fingerprint</span>
                                    </div>
                                    <div class="fw-500 text-dark">{{ $device->device_name }}</div>
                                </div>
                            </td>

                            <td>
                                <span class="device-serial-badge">{{ $device->device_serial }}</span>
                            </td>

                            <td>
                                @php
                                    $typeMap = [
                                        'attendance'     => ['label' => 'Attendance', 'color' => 'bg-primary-subtle text-primary'],
                                        'access_control' => ['label' => 'Access Control', 'color' => 'bg-info-subtle text-info'],
                                    ];
                                    $tc = $typeMap[$device->device_type] ?? $typeMap['attendance'];
                                @endphp
                                <span class="badge {{ $tc['color'] }}">{{ $tc['label'] }}</span>
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $device->user_mappings_count }}
                            </td>

                            <td class="text-muted" style="font-size:.78rem;">
                                {{ $device->last_seen_at?->diffForHumans() ?? '—' }}
                            </td>

                            <td>
                                <span class="badge rounded-pill {{ $device->is_active ? 'badge-active' : 'badge-inactive' }}" style="font-size:.72rem;">
                                    {{ $device->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openViewModal({{ $device->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <button class="act-btn edit" title="Edit" wire:click="openEditModal({{ $device->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDelete({{ $device->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">devices_other</span>
                                No devices found. <a href="#" wire:click.prevent="openCreateModal">Add one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $devices->firstItem() ?? 0 }}–{{ $devices->lastItem() ?? 0 }} of {{ $devices->total() }}</small>
            {{ $devices->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showFormModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="material-icons-round me-2 text-danger" style="vertical-align:middle;">fingerprint</span>
                            {{ $editingId ? 'Edit' : 'Add' }} Device
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showFormModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">Device Serial (SN) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('device_serial') is-invalid @enderror" wire:model.defer="device_serial" placeholder="e.g. ZK-8712A">
                                    @error('device_serial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Device Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('device_name') is-invalid @enderror" wire:model.defer="device_name" placeholder="e.g. Main Gate">
                                    @error('device_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Device Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('device_type') is-invalid @enderror" wire:model.defer="device_type">
                                        <option value="attendance">Attendance</option>
                                        <option value="access_control">Access Control</option>
                                    </select>
                                    @error('device_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">IP Address <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                    <input type="text" class="form-control @error('ip_address') is-invalid @enderror" wire:model.defer="ip_address" placeholder="e.g. 192.168.1.20">
                                    @error('ip_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Location <span class="text-muted" style="font-weight:400;">(optional)</span></label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" wire:model.defer="location" placeholder="e.g. Ground Floor, Main Building">
                                    @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model.defer="is_active" id="is_active" role="switch">
                                        <label class="form-check-label" for="is_active" style="font-size:.8rem;font-weight:600;color:var(--text-muted);">
                                            Active
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showFormModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editingId ? 'Update' : 'Save' }} Device
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== VIEW MODAL ===== --}}
    @if($showViewModal && $viewingDevice)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Device Details</h5>
                        <button class="btn-close" wire:click="$set('showViewModal',false)"></button>
                    </div>
                    <div class="modal-body">

                        <div style="border-left:4px solid {{ $viewingDevice->is_active ? '#16a34a' : '#6b7280' }};padding:12px 16px;background:#f8f9fa;border-radius:0 8px 8px 0;margin-bottom:16px;">
                            <div style="font-size:.7rem;font-weight:600;color:{{ $viewingDevice->is_active ? '#16a34a' : '#6b7280' }};text-transform:uppercase;letter-spacing:.05em;">
                                {{ $viewingDevice->is_active ? 'Active' : 'Inactive' }}
                            </div>
                            <div style="font-weight:700;font-size:.95rem;margin-top:4px;">{{ $viewingDevice->device_name }}</div>
                        </div>

                        <table class="table table-sm">
                            <tr>
                                <th class="text-muted" style="width:40%">Serial</th>
                                <td><span class="device-serial-badge">{{ $viewingDevice->device_serial }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Type</th>
                                <td>{{ ucfirst(str_replace('_', ' ', $viewingDevice->device_type)) }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">IP Address</th>
                                <td>{{ $viewingDevice->ip_address ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Location</th>
                                <td>{{ $viewingDevice->location ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Mapped Users</th>
                                <td>{{ $viewingDevice->user_mappings_count }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Last Seen</th>
                                <td>{{ $viewingDevice->last_seen_at?->format('d M Y, h:i A') ?? 'Never' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Status</th>
                                <td>
                                    <span class="badge rounded-pill {{ $viewingDevice->is_active ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $viewingDevice->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" wire:click="$set('showViewModal',false)">Close</button>
                        <button class="btn btn-primary" wire:click="openEditModal({{ $viewingDevice->id }}); $set('showViewModal',false)">
                            Edit Device
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== DELETE CONFIRM ===== --}}
    @if($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <span class="material-icons-round text-danger" style="font-size:1.5rem;">warning</span>
                        </div>
                        <h6 class="fw-700">Delete Device?</h6>
                        <p class="text-muted small">এই device-টি ডিলিট করলে আর attendance push গ্রহণ করা হবে না। এই কাজটি undo করা যাবে না।</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('showDeleteModal',false)">Cancel</button>
                        <button class="btn btn-danger btn-sm" wire:click="delete">
                            <span wire:loading wire:target="delete" class="spinner-border spinner-border-sm me-1"></span>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>