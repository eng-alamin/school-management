<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllEnrollments">
                Student Enrollments
            </h5>
            <p id="cardHeaderSubtitle">Manage enrollments, filter by class and section.</p>
        </div>

        {{-- ===== TOOLBAR (search + live filter + actions) ===== --}}
        <div class="card-header border-0">
            <div class="card-toolbar">

                {{-- Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Search name, roll no..."
                            class="tb-search"/>
                    </div>
                </div>

                {{-- Class filter --}}
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

                {{-- Section filter --}}
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

                {{-- Per page --}}
                @if($enrollments->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                {{-- <a href="{{ route('admin.enrollment.add') }}" class="btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> Add Enrollment
                </a> --}}

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="enrollmentTable">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-student">Student</th>
                            <th id="th-class">Class</th>
                            <th id="th-section">Section</th>
                            <th id="th-group">Group</th>
                            <th id="th-roll-no">Roll No</th>
                            <th id="th-carry-forward">Carry Forward Due</th>
                            <th id="th-status">Status</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enrollments as $i => $enrollment)
                        <tr>
                            <td class="text-muted">{{ $enrollments->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $enrollment->student?->photo ? asset('storage/' . $enrollment->student->photo) : asset('assets/img/boy.jpg') }}"
                                        style="width:36px;height:36px;border-radius:8px;object-fit:cover;" alt="">
                                    <span>
                                        <span class="fw-500">{{ $enrollment->student?->name ?? '—' }}</span> <br>
                                        <span class="fs-8 fw-bold">{{ $enrollment->student?->student_id ?? '—' }}</span>
                                    </span>
                                </div>
                            </td>
                            <td>{{ $enrollment->class?->name ?? '—' }}</td>
                            <td>{{ $enrollment->section?->name ?? '—' }}</td>
                            <td>{{ $enrollment->group?->name ?? '—' }}</td>
                            <td>{{ $enrollment->roll_no ?? '—' }}</td>
                            <td>
                                @if($enrollment->carry_forward_due)
                                    <span class="badge bg-danger">Yes</span>
                                @else
                                    <span class="badge bg-light text-dark">No</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'running'   => 'bg-success',
                                        'promoted'  => 'bg-primary',
                                        'left'      => 'bg-warning text-dark',
                                        'alumni'    => 'bg-secondary',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$enrollment->status] ?? 'bg-secondary' }} text-capitalize">
                                    {{ str_replace('_', ' ', $enrollment->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if($enrollment->student)
                                        <a href="{{ route('admin.student.overview', ['id' => $enrollment->student->id]) }}" target="_blank"
                                            class="act-btn view" title="View Student">
                                            <span class="material-icons-round">visibility</span>
                                        </a>
                                    @endif
                                    {{-- <a href="{{ route('admin.enrollment.edit', ['id' => $enrollment->id]) }}"
                                        class="act-btn edit" title="Edit">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </a> --}}
                                    <button class="act-btn" title="Change Status"
                                        wire:click="openStatusModal({{ $enrollment->id }})">
                                        <span class="material-icons-round">toggle_on</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete"
                                        wire:click="confirmDeleteRecord({{ $enrollment->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.2">groups</span>
                                No enrollments found.
                                {{-- <a href="{{ route('admin.enrollment.add') }}">Add one now</a>. --}}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $enrollments->firstItem() ?? 0 }}–{{ $enrollments->lastItem() ?? 0 }} of {{ $enrollments->total() }}</small>
            {{ $enrollments->links('vendor.pagination.custom') }}
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
                        <h6 class="fw-700">Delete Enrollment?</h6>
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
                        <h6 class="fw-700 mb-1" id="enr-status-title">Change Enrollment Status</h6>
                        <p class="text-muted small mb-3" id="enr-status-msg">Select a new status for this enrollment.</p>

                        <div class="text-start">
                            <div class="input-group input-group-outline" wire:ignore>
                                <select wire:model="newStatus" class="form-select no-custom-select @error('newStatus') is-invalid @enderror">
                                    @foreach($statusOptions as $option)
                                        <option value="{{ $option }}">{{ ucwords(str_replace('_', ' ', $option)) }}</option>
                                    @endforeach
                                </select>
                            </div>
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