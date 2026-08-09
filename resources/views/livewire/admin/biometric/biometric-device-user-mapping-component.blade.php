{{-- resources/views/livewire/admin/biometric-device-user-mapping-component.blade.php --}}
<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Device User Mapping</h5>
            <p id="cardHeaderSubtitle">Link biometric device user IDs with students and employees.</p>
        </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side: device selector --}}
                <div class="card-toolbar-title" style="min-width:280px;">
                    <select class="form-select form-select-sm" wire:model.live="selectedDeviceId">
                        <option value="" data-en="-- Select Device --" data-bn="-- Device সিলেক্ট করো --">-- Select Device --</option>
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->device_name }} ({{ $device->device_serial }})</option>
                        @endforeach
                    </select>
                </div>

                @if ($selectedDeviceId)
                    <!-- search in table -->
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

                    <button class="btn-outline bg-dark text-white" wire:click="openCreateModal">
                        <span class="material-icons-round">add</span> <span id="newSectionBtn">Add Mapping</span>
                    </button>
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
                                        <button class="act-btn edit" title="Edit" wire:click="openEditModal({{ $mapping->id }})">
                                            <span class="material-icons-round">edit</span>
                                        </button>
                                        <button class="act-btn delete" title="Delete" wire:click="confirmDelete({{ $mapping->id }})">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.25;">link_off</span>
                                    <span data-en="No mappings found for this device. "
                                          data-bn="এই device-এর জন্য কোনো mapping নেই। ">এই device-এর জন্য কোনো mapping নেই। </span>
                                    <a href="#" wire:click.prevent="openCreateModal"
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

    {{-- ===== ADD / EDIT MAPPING MODAL ===== --}}
    @if ($showFormModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="material-icons-round me-2 text-danger" style="vertical-align:middle;">
                                {{ $isEditMode ? 'edit' : 'link' }}
                            </span>
                            {{ $isEditMode ? 'Edit Mapping' : 'Add Mapping' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showFormModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label">Device User ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('device_user_id') is-invalid @enderror" wire:model.defer="device_user_id"
                                           placeholder="e.g. 1001 (the ID given during device enrollment)"
                                           data-en-placeholder="e.g. 1001 (the ID given during device enrollment)"
                                           data-bn-placeholder="e.g. 1001 (device-এ enroll করার সময় যেটা দিয়েছো)">
                                    @error('device_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($isEditMode)
                                        <small class="text-muted d-block mt-1"
                                               data-en="Changing this will remove the old ID from the device and push the new one."
                                               data-bn="এটা বদলালে পুরোনো ID device থেকে সরিয়ে নতুনটা push করা হবে।">
                                            এটা বদলালে পুরোনো ID device থেকে সরিয়ে নতুনটা push করা হবে।
                                        </small>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" inputmode="numeric" class="form-control @error('card_number') is-invalid @enderror" wire:model.defer="card_number"
                                        placeholder="Numbers only, e.g. 0089345621"
                                        data-en-placeholder="Numbers only, e.g. 0089345621"
                                        data-bn-placeholder="শুধু সংখ্যা লিখুন, e.g. 0089345621">
                                    @error('card_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select class="form-select" wire:model.live="attendable_type" @disabled($isEditMode)>
                                        <option value="student">Student</option>
                                        <option value="employee">Employee</option>
                                    </select>
                                    @if($isEditMode)
                                        <small class="text-muted d-block mt-1"
                                               data-en="Type/Person can't be changed here. Delete and re-add if wrong."
                                               data-bn="এখানে Type/Person পরিবর্তন করা যাবে না। ভুল হলে ডিলিট করে নতুন করে যোগ করো।">
                                            এখানে Type/Person পরিবর্তন করা যাবে না। ভুল হলে ডিলিট করে নতুন করে যোগ করো।
                                        </small>
                                    @endif
                                </div>

                                <div class="col-12 position-relative">
                                    <label class="form-label">{{ $attendable_type === 'employee' ? 'Employee' : 'Student' }} Search <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('selectedPersonId') is-invalid @enderror" wire:model="personSearch"
                                           @disabled($isEditMode)
                                           placeholder="Search by name, {{ $attendable_type === 'employee' ? 'Employee ID' : 'Student ID / Roll' }}..."
                                           data-en-placeholder="Search by name, {{ $attendable_type === 'employee' ? 'Employee ID' : 'Student ID / Roll' }}..."
                                           data-bn-placeholder="নাম, {{ $attendable_type === 'employee' ? 'Employee ID' : 'Student ID / Roll' }} দিয়ে সার্চ করো...">
                                    @error('selectedPersonId') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                    @if (! $isEditMode && count($personResults) > 0)
                                        <div class="person-search-results">
                                            @foreach ($personResults as $person)
                                                <button type="button" wire:click="selectPerson({{ $person['id'] }})">
                                                    {{ $person['label'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($selectedPersonId)
                                        <div class="small text-success mt-1">
                                            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">check_circle</span>
                                            <span data-en="Selected" data-bn="সিলেক্ট করা হয়েছে">সিলেক্ট করা হয়েছে</span>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" wire:click="$set('showFormModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $isEditMode ? 'Update Mapping' : 'Save Mapping' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

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


@push('styles')
    <style>
        /* ── BADGES ── */
        .badge-active   { background: rgba(34,197,94,.12);  color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }

        /* ── AVATAR ── */
        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 8px;
            background: var(--primary-light); color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem;
        }
        .mapping-avatar-student  { background: rgba(34,197,94,.12);  color: #16a34a; }
        .mapping-avatar-employee { background: rgba(245,158,11,.12); color: #d97706; }

        .device-serial-badge {
            font-family: monospace;
            background: #f1f3f9;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.78rem;
        }

        /* ── ACTION BUTTONS ── */
        .act-btn {
            border: none;
            background: none;
            width: 30px; height: 30px;
            border-radius: 6px;
            display: inline-flex; align-items: center; justify-content: center;
            color: var(--muted);
            transition: background .15s, color .15s;
        }
        .act-btn .material-icons-round { font-size: 1.05rem; }
        .act-btn.edit { color: #2563eb; }
        .act-btn.edit:hover { background: rgba(37,99,235,.08); }
        .act-btn.delete { color: #dc2626; }
        .act-btn.delete:hover { background: rgba(220,38,38,.08); }

        /* ── PERSON SEARCH DROPDOWN ── */
        .person-search-results {
            position: absolute;
            z-index: 1060;
            width: 100%;
            max-height: 220px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
            margin-top: 2px;
        }
        .person-search-results button {
            width: 100%;
            text-align: left;
            border: none;
            background: none;
            padding: 8px 12px;
            font-size: .82rem;
        }
        .person-search-results button:hover {
            background: #f8f9fc;
        }

        /* ── FORM ── */
        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select {
            border-radius: 8px; border: 1px solid var(--border);
            font-size: .875rem; padding: .45rem .75rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }
        .form-control:disabled, .form-select:disabled {
            background: #f3f4f6; opacity: .8;
        }

        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover, .btn-primary:focus { background: #d63e3e; border-color: #d63e3e; }
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
    </style>
@endpush