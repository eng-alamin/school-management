<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllStudents">
                All Students
            </h5>
            <p id="cardHeaderSubtitle">Manage students, filter by class and section.</p>
        </div>

        {{-- ===== TOOLBAR (search + live filter + actions) ===== --}}
        <div class="card-header border-0">
            <div class="card-toolbar">

                {{-- Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Search name, reg no..."
                            class="tb-search"/>
                    </div>
                </div>

                @if($students->total() > 10)
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

                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select wire:model.live="filterClass" class="form-select form-select-sm">
                            <option value="">All Classes</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="input-group input-group-outline">
                        <select wire:model.live="filterSection" class="form-select form-select-sm"
                            {{ empty($availableSections) ? 'disabled' : '' }}>
                            <option value="">All Sections</option>
                            @if(!empty($availableSections))
                                @foreach ($availableSections as $s)
                                    <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>

                <a href="{{ route($routePrefix . 'student.add') }}" class="btn btn-primary">
                    <span>
                        <span class="material-icons-round">add</span>
                        <span>Add Student</span>
                    </span>
                </a>

            </div>
        </div>

        <div class="card-body pt-0" id="printArea">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="studentTable">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-name">Name</th>
                            <th id="th-class">Class</th>
                            <th id="th-section">Section</th>
                            <th id="th-gender">Gender</th>
                            <th id="th-register-no">Register No</th>
                            <th id="th-roll-no">Roll No</th>
                            <th id="th-guardian">Guardian</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions" class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $i => $student)
                        <tr>
                            <td class="text-muted">{{ $students->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $student->photo ? asset('storage/' . $student->photo) : asset('assets/img/boy.jpg') }}"
                                        style="width:36px;height:36px;border-radius:8px;object-fit:cover;" alt="">
                                    <span>
                                        <span class="fw-500">{{ $student->name }}</span> <br> 
                                        <span class="fs-8 fw-bold">{{ $student->student_id }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>{{ $student->class?->name ?? '—' }}</td>
                            <td>{{ $student->section?->name ?? '—' }}</td>
                            <td>{{ $student->gender ?? '—' }}</td>
                            <td>{{ $student->registration_no }}</td>
                            <td>{{ $student->roll_no ?? '—' }}</td>
                            <td>{{ $student->guardians->first()?->name ?? '—' }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'active'      => 'bg-success',
                                        'inactive'    => 'bg-secondary',
                                        'graduated'   => 'bg-primary',
                                        'transferred' => 'bg-warning text-dark',
                                        'dropped_out' => 'bg-danger',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$student->status] ?? 'bg-secondary' }} text-capitalize">
                                    {{ str_replace('_', ' ', $student->status) }}
                                </span>
                            </td>
                            <td class="no-print">
                                <div class="d-flex gap-1">
                                    <a href="{{ route($routePrefix . 'student.overview', ['id' => $student->id]) }}" target="_blank"
                                        class="act-btn view" title="View">
                                        <span class="material-icons-round">visibility</span>
                                    </a>
                                    <a href="{{ route($routePrefix . 'student.edit', ['id' => $student->id]) }}"
                                        class="act-btn edit" title="Edit">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </a>
                                    <button class="act-btn" title="Change Status"
                                        wire:click="openStatusModal({{ $student->id }})">
                                        <span class="material-icons-round">toggle_on</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete"
                                        wire:click="confirmDeleteRecord({{ $student->user?->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.2">groups</span>
                                <span>No students found.</span> <br><br>
                                <a href="{{ route($routePrefix . 'student.add') }}" class="btn btn-primary btn-sm px-4">Add one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} of {{ $students->total() }}</small>
            {{ $students->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== DELETE CONFIRM ===== --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <span class="material-icons-round text-danger" style="font-size:1.5rem;">warning</span>
                        </div>
                        <h6 class="fw-700">Delete Student?</h6>
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

    {{-- ===== STATUS UPDATE MODAL ===== --}}
    @if($showStatusModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center py-4 px-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <span class="material-icons-round text-danger" style="font-size:1.5rem;">toggle_on</span>
                        </div>
                        <h6 class="fw-700 mb-1" id="stu-status-title">Change Student Status</h6>
                        <p class="text-muted small mb-3" id="stu-status-msg">Select a new status for this student.</p>

                        <div class="text-start">
                            <select wire:model="newStatus" class="form-select no-custom-select @error('newStatus') is-invalid @enderror">
                                @foreach($statusOptions as $option)
                                    <option value="{{ $option }}">{{ ucwords(str_replace('_', ' ', $option)) }}</option>
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