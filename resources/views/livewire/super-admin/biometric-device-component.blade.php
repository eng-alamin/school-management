<div>
    @push('styles')
        <style>
            .device-serial-badge {
                font-family: monospace;
                background: #f1f3f9;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 0.85rem;
            }
        </style>
    @endpush

    <div class="mat-card">
        <div class="mat-card-header header-primary-gradient d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><span class="material-icons-round align-middle">fingerprint</span> Biometric Devices (All Institutions)</h5>
            <button class="btn btn-light btn-sm" wire:click="openCreateModal">
                <span class="material-icons-round align-middle" style="font-size:18px;">add</span> Add Device
            </button>
        </div>

        <div class="mat-card-body p-3">
            {{-- Toolbar --}}
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search name or serial..."
                           wire:model.live.debounce.400ms="search">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterInstitution">
                        <option value="all">All Institutions</option>
                        @foreach ($institutions as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="perPage">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                    </select>
                </div>
            </div>

            {{-- Desktop table --}}
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th role="button" wire:click="sortBy('device_name')">Name</th>
                            <th role="button" wire:click="sortBy('device_serial')">Serial</th>
                            <th>Institution</th>
                            <th role="button" wire:click="sortBy('device_type')">Type</th>
                            <th>Mapped Users</th>
                            <th role="button" wire:click="sortBy('last_seen_at')">Last Seen</th>
                            <th role="button" wire:click="sortBy('is_active')">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($devices as $device)
                            <tr wire:key="device-{{ $device->id }}">
                                <td>{{ $device->device_name }}</td>
                                <td><span class="device-serial-badge">{{ $device->device_serial }}</span></td>
                                <td>{{ $device->institution?->name }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $device->device_type)) }}</td>
                                <td>{{ $device->user_mappings_count }}</td>
                                <td>{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</td>
                                <td>
                                    <span class="badge {{ $device->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $device->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-link" wire:click="openViewModal({{ $device->id }})">
                                        <span class="material-icons-round" style="font-size:18px;">visibility</span>
                                    </button>
                                    <button class="btn btn-sm btn-link" wire:click="openEditModal({{ $device->id }})">
                                        <span class="material-icons-round" style="font-size:18px;">edit</span>
                                    </button>
                                    <button class="btn btn-sm btn-link text-danger" wire:click="confirmDelete({{ $device->id }})">
                                        <span class="material-icons-round" style="font-size:18px;">delete</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4">No devices found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile card layout --}}
            <div class="d-md-none">
                @forelse ($devices as $device)
                    <div class="card mb-2" wire:key="device-m-{{ $device->id }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $device->device_name }}</strong>
                                <span class="badge {{ $device->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $device->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="device-serial-badge mt-1">{{ $device->device_serial }}</div>
                            <div class="small text-muted mt-1">{{ $device->institution?->name }} · {{ $device->user_mappings_count }} users</div>
                            <div class="d-flex column-reverse gap-2 mt-2">
                                <button class="btn btn-sm btn-outline-primary w-100" wire:click="openViewModal({{ $device->id }})">View</button>
                                <button class="btn btn-sm btn-outline-secondary w-100" wire:click="openEditModal({{ $device->id }})">Edit</button>
                                <button class="btn btn-sm btn-outline-danger w-100" wire:click="confirmDelete({{ $device->id }})">Delete</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">No devices found.</div>
                @endforelse
            </div>

            <div class="mt-3">{{ $devices->links() }}</div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showFormModal)
        <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit="save">
                        <div class="modal-header header-primary-gradient">
                            <h5 class="modal-title">{{ $editingId ? 'Edit Device' : 'Add Device' }}</h5>
                            <button type="button" class="btn-close" wire:click="$set('showFormModal', false)"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Institution</label>
                                <select class="form-select" wire:model="institution_id">
                                    <option value="">-- Select --</option>
                                    @foreach ($institutions as $inst)
                                        <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                    @endforeach
                                </select>
                                @error('institution_id') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Device Serial (SN)</label>
                                <input type="text" class="form-control" wire:model="device_serial">
                                @error('device_serial') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Device Name</label>
                                <input type="text" class="form-control" wire:model="device_name" placeholder="e.g. Main Gate - Campus A">
                                @error('device_name') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Device Type</label>
                                <select class="form-select" wire:model="device_type">
                                    <option value="attendance">Attendance</option>
                                    <option value="access_control">Access Control</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">IP Address (optional)</label>
                                <input type="text" class="form-control" wire:model="ip_address">
                                @error('ip_address') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Location (optional)</label>
                                <input type="text" class="form-control" wire:model="location">
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="is_active">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showFormModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- View Modal --}}
    @if ($showViewModal && $viewingDevice)
        <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header header-primary-gradient">
                        <h5 class="modal-title">{{ $viewingDevice->device_name }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showViewModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Serial:</strong> <span class="device-serial-badge">{{ $viewingDevice->device_serial }}</span></p>
                        <p><strong>Institution:</strong> {{ $viewingDevice->institution?->name }}</p>
                        <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $viewingDevice->device_type)) }}</p>
                        <p><strong>IP:</strong> {{ $viewingDevice->ip_address ?? '—' }}</p>
                        <p><strong>Location:</strong> {{ $viewingDevice->location ?? '—' }}</p>
                        <p><strong>Mapped Users:</strong> {{ $viewingDevice->user_mappings_count }}</p>
                        <p><strong>Last Seen:</strong> {{ $viewingDevice->last_seen_at?->format('d M Y, h:i A') ?? 'Never' }}</p>
                        <p><strong>Status:</strong>
                            <span class="badge {{ $viewingDevice->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $viewingDevice->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showViewModal', false)">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirm Modal --}}
    @if ($showDeleteModal)
        <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header header-primary-gradient">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <p>এই device-টি ডিলিট করলে আর attendance push গ্রহণ করা হবে না। আপনি কি নিশ্চিত?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="delete">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>