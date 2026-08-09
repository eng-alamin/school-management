<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllassigns">Attendance Duty Assign</h5>
            <p id="cardHeaderSubtitle">Assign a teacher to take daily student attendance for each class/section.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                @if($assigns->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <button class="btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> <span>New Duty Assign</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl" wire:click="sortBy('id')" style="cursor:pointer">
                                SL
                                @if($sortField === 'id')
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">
                                        {{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th id="th-class" wire:click="sortBy('class_name')" style="cursor:pointer">
                                Class
                                @if($sortField === 'class_name')
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">
                                        {{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th id="th-section" wire:click="sortBy('section_name')" style="cursor:pointer">
                                Section
                                @if($sortField === 'section_name')
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">
                                        {{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th id="th-teacher" wire:click="sortBy('teacher_name')" style="cursor:pointer">
                                Duty Teacher
                                @if($sortField === 'teacher_name')
                                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle">
                                        {{ $sortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward' }}
                                    </span>
                                @endif
                            </th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assigns as $i => $assign)
                        <tr>
                            <td class="text-muted">{{ $assigns->firstItem() + $i }}</td>
                            <td>{{ $assign->classAssign?->academicClass?->name ?? '—' }}</td>
                            <td>{{ $assign->classAssign?->academicSection?->name ?? 'N/A' }}</td>
                            <td>{{ $assign->teacher?->name ?? '—' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $assign->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $assign->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No attendance duty assigned yet. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $assigns->firstItem() ?? 0 }}–{{ $assigns->lastItem() ?? 0 }} of {{ $assigns->total() }}</small>
            {{ $assigns->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">{{ $editId ? 'Edit' : 'Create' }} Duty Assign</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-12">
                                <label class="form-label">Class / Section <span class="text-danger">*</span></label>
                                <select class="form-select @error('academic_class_assign_id') is-invalid @enderror" wire:model="academic_class_assign_id">
                                    <option value="">Select Class / Section</option>
                                    @foreach($this->availableClassAssigns as $ca)
                                        <option value="{{ $ca->id }}">
                                            {{ $ca->academicClass?->name }}{{ $ca->academicSection ? ' - ' . $ca->academicSection->name : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('academic_class_assign_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Duty Teacher <span class="text-danger">*</span></label>
                                <select class="form-select @error('teacher_id') is-invalid @enderror" wire:model="teacher_id">
                                    <option value="">Select Teacher</option>
                                    @foreach($teachers as $teacherId => $teacherName)
                                        <option value="{{ $teacherId }}">{{ $teacherName }}</option>
                                    @endforeach
                                </select>
                                @error('teacher_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer border-0 mt-3">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn bg-dark text-white" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editId ? 'Update' : 'Create' }}
                        </button>
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
                        <h6 class="fw-700">Remove Duty Assign?</h6>
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