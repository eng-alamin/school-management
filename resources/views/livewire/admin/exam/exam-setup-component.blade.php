<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5>Exam Setup</h5>
            <p>Manage exam setups, create, update, and organize academic setups easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                @if($setups->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <a href="{{ route('admin.exam.terms') }}" target="_blank" class="btn-sm btn-outline">
                    <span class="material-icons-round fs-6">history_edu</span> Terms
                </a>
                <a href="{{ route('admin.exam.types') }}" target="_blank" class="btn-sm btn-outline">
                    <span class="material-icons-round fs-6">check_circle</span> Types
                </a>
                <button class="btn-sm btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> Add Exam
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">
                                Name @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Class</th>
                            <th>Term</th>
                            <th>Type</th>
                            <th>Total Subjects</th>
                            <th>Publish</th>
                            <th>Result</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($setups as $i => $setup)
                        <tr wire:key="setup-{{ $setup->id }}">
                            <td class="text-muted">{{ $setups->firstItem() + $i }}</td>
                            <td><strong>{{ $setup->name }}</strong></td>
                            <td>
                                @if($setup->classAssign)
                                    <span class="badge bg-info-subtle text-dark">
                                        {{ $setup->classAssign->class->name ?? '—' }}
                                        @if($setup->classAssign->section)
                                            - {{ $setup->classAssign->section->name }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-danger">— Not Set —</span>
                                @endif
                            </td>
                            <td>{{ $setup->term->name ?? 'N/A' }}</td>
                            <td>{{ $setup->type->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $setup->details->count() }} subjects</span>
                            </td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox"
                                        wire:click="togglePublished({{ $setup->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="togglePublished({{ $setup->id }})"
                                        @checked($setup->is_published)>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox"
                                        wire:click="toggleResultPublished({{ $setup->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleResultPublished({{ $setup->id }})"
                                        @checked($setup->is_result_published)>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $setup->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $setup->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $setup->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No setups found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $setups->firstItem() ?? 0 }}–{{ $setups->lastItem() ?? 0 }} of {{ $setups->total() }}</small>
            {{ $setups->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE / EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">{{ $editId ? 'Edit' : 'Create (Bulk)' }} Exam Setup</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">

                            {{-- Name --}}
                            <div class="col-md-4">
                                <label class="form-label">Exam Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       wire:model.defer="name" placeholder="e.g. Half Yearly Exam 2025">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if(!$editId)
                                    <small class="text-muted">This name will be used the same way for each selected class.</small>
                                @endif
                            </div>

                            {{-- EDIT MODE: single class dropdown --}}
                            @if($editId)
                                <div class="col-md-4">
                                    <label class="form-label">Class <span class="text-danger">*</span></label>
                                    <select class="form-select @error('academic_class_assign_id') is-invalid @enderror"
                                            wire:model.live="academic_class_assign_id">
                                        <option value="">Select Class</option>
                                        @foreach($classAssigns as $ca)
                                            <option value="{{ $ca['id'] }}">{{ $ca['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('academic_class_assign_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            {{-- Term --}}
                            <div class="col-md-4">
                                <label class="form-label">Term</label>
                                <select class="form-select @error('exam_term_id') is-invalid @enderror"
                                        wire:model.defer="exam_term_id">
                                    <option value="">Select Term</option>
                                    @foreach($terms as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('exam_term_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Type --}}
                            <div class="col-md-4">
                                <label class="form-label">Type</label>
                                <select class="form-select @error('exam_type_id') is-invalid @enderror"
                                        wire:model.defer="exam_type_id">
                                    <option value="">Select Type</option>
                                    @foreach($types as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('exam_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Remarks --}}
                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <input type="text" class="form-control" wire:model.defer="remarks" placeholder="Optional">
                            </div>

                        </div>

                        @if($editId)
                            {{-- ═══════════ EDIT MODE: Subject Marks Table ═══════════ --}}
                            <div wire:loading wire:target="academic_class_assign_id" class="text-center py-3">
                                <span class="spinner-border spinner-border-sm"></span> Loading subjects...
                            </div>

                            <div wire:loading.remove wire:target="academic_class_assign_id">
                                @if(!empty($subjects))
                                    <hr class="my-2">
                                    <p class="text-muted mb-3" style="font-size:.82rem">
                                        <span class="material-icons-round" style="font-size:15px;vertical-align:middle">info</span>
                                        নির্বাচিত class এর subjects এর mark distribution সেট করুন।
                                    </p>

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0" style="font-size:.82rem">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="min-width:140px">Subject</th>
                                                    <th style="min-width:90px">Full Mark</th>
                                                    <th style="min-width:90px">Pass Mark</th>
                                                    <th style="min-width:90px">Written</th>
                                                    <th style="min-width:90px">MCQ</th>
                                                    <th style="min-width:90px">Practical</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($subjects as $detailId => $subject)
                                                <tr wire:key="subject-{{ $detailId }}">
                                                    <td class="fw-500">{{ $subject['subject_name'] }}</td>
                                                    <td>
                                                        <input type="number" min="0"
                                                               class="form-control form-control-sm"
                                                               wire:model.defer="subjects.{{ $detailId }}.full_mark">
                                                    </td>
                                                    <td>
                                                        <input type="number" min="0"
                                                               class="form-control form-control-sm"
                                                               wire:model.defer="subjects.{{ $detailId }}.pass_mark">
                                                    </td>
                                                    <td>
                                                        <input type="number" min="0"
                                                               class="form-control form-control-sm"
                                                               wire:model.defer="subjects.{{ $detailId }}.written_mark">
                                                    </td>
                                                    <td>
                                                        <input type="number" min="0"
                                                               class="form-control form-control-sm"
                                                               wire:model.defer="subjects.{{ $detailId }}.mcq_mark">
                                                    </td>
                                                    <td>
                                                        <input type="number" min="0"
                                                               class="form-control form-control-sm"
                                                               wire:model.defer="subjects.{{ $detailId }}.practical_mark">
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @elseif($academic_class_assign_id)
                                    <div class="alert alert-warning py-2 mb-0" style="font-size:.82rem">
                                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle">warning</span>
                                        এই class এ কোনো subject assign করা নেই।
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- ═══════════ CREATE MODE: Bulk Checkbox + Default Marks ═══════════ --}}
                            <hr class="my-2">

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label mb-0">Class Select <span class="text-danger">*</span></label>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-dark" wire:click="selectAllClasses">Select All</button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="deselectAllClasses">Clear</button>
                                </div>
                            </div>

                            @error('selectedClassAssignIds') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                            <div class="class-checkbox-grid mb-3">
                                @forelse($classAssigns as $ca)
                                    <label class="class-checkbox-item {{ $ca['subject_count'] === 0 ? 'disabled' : '' }}">
                                        <input type="checkbox"
                                               wire:model="selectedClassAssignIds"
                                               value="{{ $ca['id'] }}"
                                               @disabled($ca['subject_count'] === 0)>
                                        <span class="cb-label">{{ $ca['label'] }}</span>
                                        <span class="badge bg-secondary">{{ $ca['subject_count'] }} subj</span>
                                    </label>
                                @empty
                                    <p class="text-muted">কোনো Class Assign পাওয়া যায়নি।</p>
                                @endforelse
                            </div>

                            <p class="text-muted mb-2" style="font-size:.82rem">
                                <span class="material-icons-round" style="font-size:15px;vertical-align:middle">info</span>
                                The following Default Marks will be applied to all Subjects of all selected Classes. You can edit the marks of individual classes later if necessary.
                            </p>

                            <div class="row g-2">
                                <div class="col-md-2">
                                    <label class="form-label">Full Mark</label>
                                    <input type="number" min="0" class="form-control form-control-sm @error('default_full_mark') is-invalid @enderror" wire:model.defer="default_full_mark">
                                    @error('default_full_mark') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Pass Mark</label>
                                    <input type="number" min="0" class="form-control form-control-sm @error('default_pass_mark') is-invalid @enderror" wire:model.defer="default_pass_mark">
                                    @error('default_pass_mark') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Written</label>
                                    <input type="number" min="0" class="form-control form-control-sm" wire:model.defer="default_written_mark">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">MCQ</label>
                                    <input type="number" min="0" class="form-control form-control-sm" wire:model.defer="default_mcq_mark">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Practical</label>
                                    <input type="number" min="0" class="form-control form-control-sm" wire:model.defer="default_practical_mark">
                                </div>
                            </div>
                        @endif

                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn bg-dark text-white" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editId ? 'Update' : 'Create All' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== VIEW MODAL ===== --}}
    @if($showViewModal && $viewRecord)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">{{ $viewRecord->name }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showViewModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <small class="text-muted d-block">Class</small>
                                <strong>
                                    {{ $viewRecord->classAssign->class->name ?? 'N/A' }}
                                    @if($viewRecord->classAssign->section ?? null)
                                        - {{ $viewRecord->classAssign->section->name }}
                                    @endif
                                </strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Term</small>
                                <strong>{{ $viewRecord->term->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Type</small>
                                <strong>{{ $viewRecord->type->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Remarks</small>
                                <strong>{{ $viewRecord->remarks ?? '—' }}</strong>
                            </div>
                        </div>

                        @if($viewRecord->details->count())
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0" style="font-size:.82rem">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Subject</th>
                                            <th>Full</th>
                                            <th>Pass</th>
                                            <th>Written</th>
                                            <th>MCQ</th>
                                            <th>Practical</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($viewRecord->details as $i => $detail)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $detail->classAssignDetail->subject->name ?? '—' }}</td>
                                            <td>{{ $detail->full_mark }}</td>
                                            <td>{{ $detail->pass_mark }}</td>
                                            <td>{{ $detail->written_mark }}</td>
                                            <td>{{ $detail->mcq_mark }}</td>
                                            <td>{{ $detail->practical_mark }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center py-3">No subject details found.</p>
                        @endif
                    </div>
                    <div class="modal-footer border-0">
                        <button class="btn btn-light" wire:click="$set('showViewModal', false)">Close</button>
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
                        <h6 class="fw-bold">Delete Setup?</h6>
                        <p class="text-muted small">এই exam setup এর সব subject details ও মুছে যাবে।</p>
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

@push('styles')
<style>
    :root {
        --primary: rgba(33, 37, 41);
        --primary-light: rgba(239,84,84,.12);
    }
    .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
    .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); border-bottom: 2px solid var(--border); }
    .table td { vertical-align: middle; font-size: .875rem; }
    .table > :not(caption) > * > * { padding: .6rem .8rem; }
    .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
    .form-control, .form-select { border-radius: 8px; border: 1px solid var(--border); font-size: .875rem; padding: .45rem .75rem; }
    .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
    .fw-500 { font-weight: 500; }

    /* Bulk checkbox grid */
    .class-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 8px;
        max-height: 260px;
        overflow-y: auto;
        padding: 10px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #f8f9fa;
    }
    .class-checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 7px 10px;
        font-size: .8rem;
        cursor: pointer;
        transition: .15s;
    }
    .class-checkbox-item:hover { border-color: #ec407a; background: #fff5f8; }
    .class-checkbox-item.disabled { opacity: .5; cursor: not-allowed; }
    .class-checkbox-item input[type="checkbox"] { margin: 0; cursor: pointer; }
    .class-checkbox-item .cb-label { flex: 1; }

    /* Toggle */
    .toggle-switch { position: relative; display: inline-block; width: 42px; height: 22px; vertical-align: middle; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #d1d5db; transition: .2s; border-radius: 999px; }
    .toggle-slider::before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: #fff; transition: .2s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,.3); }
    .toggle-switch input:checked + .toggle-slider { background: linear-gradient(195deg, #ec407a, #d81b60); }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }
    .toggle-switch input:disabled + .toggle-slider { opacity: .6; cursor: not-allowed; }
    .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
</style>
@endpush