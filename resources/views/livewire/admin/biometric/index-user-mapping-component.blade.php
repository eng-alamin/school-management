{{-- resources/views/livewire/adming-component.blade.php --}}
<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Device User Mapping</h5>
            <p id="cardHeaderSubtitle">Link biometric device user IDs with students and employees.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title" style="min-width:280px;">
                    <select class="form-select form-select-sm" wire:model.live="selectedDeviceId">
                        <option value="" data-en="-- Select Device --" data-bn="-- Device সিলেক্ট করো --">-- Select Device --</option>
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->device_name }} ({{ $device->device_serial }})</option>
                        @endforeach
                    </select>
                </div>

                @if ($selectedDeviceId)
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch"
                               placeholder="Search device user id"
                               data-en-placeholder="Search device user id"
                               data-bn-placeholder="Device User ID দিয়ে খুঁজো"
                               class="tb-search"/>
                    </div>

                    @if($mappings->total() > 10)
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" wire:model.live="perPage">
                                <option value="10">10 / page</option>
                                <option value="25">25 / page</option>
                                <option value="50">50 / page</option>
                            </select>
                        </div>
                    @endif

                    <a wire:navigate
                       href="{{ route($routePrefix . 'biometric.mapping.create', ['device_id' => $selectedDeviceId]) }}"
                       class="btn btn-primary">
                        <span class="material-icons-round">add</span> <span id="newSectionBtn">Add Mapping</span>
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body pt-0">
            @if (! $selectedDeviceId)
                <div class="text-center text-muted py-5">
                    <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">link</span>
                    <span data-en="Select a device from above, then you can view or add its mappings."
                          data-bn="উপরে থেকে একটা Device সিলেক্ট করো, তারপর তার mapping দেখা/যোগ করা যাবে।">
                        উপরে থেকে একটা Device সিলেক্ট করো, তারপর তার mapping দেখা/যোগ করা যাবে।
                    </span>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th id="th-sl">SL</th>
                                <th id="th-devuid" role="button" wire:click="sortBy('device_user_id')">
                                    Device User ID
                                    @if($sortField === 'device_user_id')
                                        <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </th>
                                <th id="th-cardno" role="button" wire:click="sortBy('card_number')">
                                    Card Number
                                    @if($sortField === 'card_number')
                                        <span class="material-icons-round" style="font-size:.9rem;vertical-align:middle;">{{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                                    @endif
                                </th>
                                <th id="th-type" role="button" wire:click="sortBy('attendable_type')">Type</th>
                                <th id="th-name">Name</th>
                                <th id="th-idno">ID No.</th>
                                <th id="th-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mappings as $i => $mapping)
                            <tr wire:key="mapping-{{ $mapping->id }}">
                                <td class="text-muted">{{ $mappings->firstItem() + $i }}</td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-placeholder mapping-avatar-{{ str_contains($mapping->attendable_type, 'Employee') ? 'employee' : 'student' }}">
                                            <span class="material-icons-round" style="font-size:1rem;">badge</span>
                                        </div>
                                        <span class="device-serial-badge">{{ $mapping->device_user_id }}</span>
                                    </div>
                                </td>

                                <td>
                                    @if($mapping->card_number)
                                        <span class="device-serial-badge">{{ $mapping->card_number }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if(str_contains($mapping->attendable_type, 'Employee'))
                                        <span class="badge bg-warning-subtle text-warning">Employee</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success">Student</span>
                                    @endif
                                </td>

                                <td class="fw-500 text-dark">{{ $mapping->attendable?->name ?? '— (deleted)' }}</td>

                                <td class="text-muted" style="font-size:.78rem;">
                                    {{ str_contains($mapping->attendable_type, 'Employee')
                                        ? $mapping->attendable?->employee_id
                                        : $mapping->attendable?->student_id }}
                                </td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a wire:navigate href="{{ route($routePrefix . 'biometric.mapping.edit', $mapping->id) }}" class="act-btn edit" title="Edit">
                                            <span class="material-icons-round">edit</span>
                                        </a>
                                        <button class="act-btn delete" title="Delete" wire:click="confirmDelete({{ $mapping->id }})">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">link_off</span>
                                    <span data-en="No mappings found for this device. "
                                          data-bn="এই device-এর জন্য কোনো mapping নেই। ">এই device-এর জন্য কোনো mapping নেই। </span>
                                    <a wire:navigate href="{{ route('biometric.mapping.create', ['device_id' => $selectedDeviceId]) }}"
                                       data-en="Add one now" data-bn="এখনই যোগ করো">এখনই যোগ করো</a><span data-en="." data-bn="।">।</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
                    <small class="text-muted">Showing {{ $mappings->firstItem() ?? 0 }}–{{ $mappings->lastItem() ?? 0 }} of {{ $mappings->total() }}</small>
                    {{ $mappings->links('vendor.pagination.custom') }}
                </div>
            @endif
        </div>

    </div>

    {{-- ===== DELETE CONFIRM ===== --}}
    @if ($showDeleteModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <span class="material-icons-round text-danger" style="font-size:1.5rem;">warning</span>
                        </div>
                        <h6 class="fw-700">Delete Mapping?</h6>
                        <p class="text-muted small"
                           data-en="Deleting this mapping means biometric attendance will no longer automatically match for this person. This action cannot be undone."
                           data-bn="এই mapping মুছে ফেললে সেই ব্যক্তির জন্য biometric attendance আর automatically match হবে না। এই কাজটি undo করা যাবে না।">
                            এই mapping মুছে ফেললে সেই ব্যক্তির জন্য biometric attendance আর automatically match হবে না। এই কাজটি undo করা যাবে না।
                        </p>
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