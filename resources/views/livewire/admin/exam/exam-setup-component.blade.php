<div>

    <div class="card">

      <!-- floating header -->
      <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleAllsections">Exam Setup</h5>
        <p id="cardHeaderSubtitle">Manage exam setups, create, update, and organize academic setups easily.</p>
      </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                <!-- Right Side -->
                @if($setups->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
                <a href="{{route('admin.exam.terms')}}" target="_blank" class="btn-sm btn-outline">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Terms</span>
                </a>
                <a href="{{route('admin.exam.halls')}}" target="_blank" class="btn-sm btn-outline">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Halls</span>
                </a>
                <a href="{{route('admin.exam.marks')}}" target="_blank" class="btn-sm btn-outline">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Marks</span>
                </a>
                <a href="{{route('admin.exam.types')}}" target="_blank" class="btn-sm btn-outline">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Types</span>
                </a>
                <button class="btn-sm btn-outline bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Add Exam</span>
                </button>

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">Name @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif </th>
                            <th>Type</th>
                            <th>Term</th>
                            <th>Mark Distribution</th>
                            <th>Publish</th>
                            <th>Publish Result</th>
                            <th>Remarks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($setups as $i => $setup)
                        <tr wire:key="setup-{{ $setup->id }}">
                            <td class="text-muted">{{ $setups->firstItem() + $i }}</td>
                            <td> {{ $setup->name }} </td>
                            <td> {{ $setup->type->name ?? 'N/A' }} </td>
                            <td> {{ $setup->term->name ?? 'N/A' }} </td>
                            <td>{{ implode(', ', $setup->marks ?? []) }}</td>
                            <td>
                                <label class="toggle-switch" title="{{ $setup->is_published ? 'Published' : 'Not Published' }}">
                                    <input type="checkbox"
                                        wire:click="togglePublished({{ $setup->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="togglePublished({{ $setup->id }})"
                                        @checked($setup->is_published)>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td>
                                <label class="toggle-switch" title="{{ $setup->is_result_published ? 'Result Published' : 'Result Not Published' }}">
                                    <input type="checkbox"
                                        wire:click="toggleResultPublished({{ $setup->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleResultPublished({{ $setup->id }})"
                                        @checked($setup->is_result_published)>
                                    <span class="toggle-slider"></span>
                                </label>
                            </td>
                            <td> {{ $setup->remarks }} </td>
                            <td>
                                <div class="d-flex gap-1">
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

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">
                            {{ $editId ? 'Edit' : 'Create' }} Exam Setup
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name" placeholder="">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Term</label>
                                    <select class="form-select @error('exam_term_id') is-invalid @enderror" wire:model.defer="exam_term_id">
                                        <option value="">Select Term</option>
                                        @foreach($terms as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('exam_term_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Type </label>
                                    <select class="form-select @error('exam_type_id') is-invalid @enderror" wire:model.defer="exam_type_id">
                                        <option value="">Select Type</option>
                                        @foreach($types as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('exam_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Mark Distribution</label>
                                    <div wire:ignore>
                                        <select wire:model="mark_distribution" multiple title="Select Distribution..." class="form-control selectpicker @error('mark_distribution') is-invalid @enderror">
                                            @foreach($marks as $key => $label)
                                                <option value="{{ $label }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('mark_distribution') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Remarks </label>
                                    <input type="text" class="form-control @error('remarks') is-invalid @enderror" wire:model.defer="remarks" placeholder="">
                                    @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn bg-dark text-white" wire:click="save" wire:loading.attr="disabled">
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
                        <h6 class="fw-700">Delete Setup?</h6>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('confirmDelete',false)">Cancel</button>
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

        /* ── CARD ── */
        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
        .card-header .card-title { font-size: .95rem; font-weight: 600; margin: 0; }
 
        /* ── TABLE ── */
        .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); border-bottom: 2px solid var(--border); }
        .table td { vertical-align: middle; font-size: .875rem; }
        .table > :not(caption) > * > * { padding: .7rem 1rem; }

        /* ── TOGGLE SWITCH (Publish / Publish Result) ── */
        .toggle-switch { position: relative; display: inline-block; width: 42px; height: 22px; vertical-align: middle; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #d1d5db; transition: .2s; border-radius: 999px;
        }
        .toggle-slider::before {
            position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px;
            background-color: #fff; transition: .2s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,.3);
        }
        .toggle-switch input:checked + .toggle-slider { background: linear-gradient(195deg, #ec407a, #d81b60); }
        .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }
        .toggle-switch input:disabled + .toggle-slider { opacity: .6; cursor: not-allowed; }
 
        /* ── BADGES ── */
        .badge-active { background: rgba(34,197,94,.12); color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }
        .badge-expired, .badge-cancelled, .badge-suspended { background: rgba(239,68,68,.12); color: #dc2626; }
        .badge-used { background: rgba(59,130,246,.12); color: #2563eb; }
 
        /* ── AVATAR ── */
        .avatar { width: 38px; height: 38px; border-radius: 8px; object-fit: cover; }
        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 8px;
            background: var(--primary-light); color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem;
        }
 
        /* ── MODAL ── */
        .modal-header { border-bottom: 1px solid var(--border); }
        .modal-footer { border-top: 1px solid var(--border); }
        .modal-title { font-weight: 600; font-size: 1rem; }
 
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
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
 
        /* Color picker */
        .color-input-wrap { display: flex; align-items: center; gap: 8px; }
        .color-input-wrap input[type="color"] {
            width: 40px; height: 38px; padding: 2px; border-radius: 8px;
            cursor: pointer; border: 1px solid var(--border);
        }
 
        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover, .btn-primary:focus { background: #d63e3e; border-color: #d63e3e; }
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 7px; }
 
        /* Stat cards */
        .stat-card { border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: grid; place-items: center; font-size: 1.4rem; }
        .stat-label { font-size: .75rem; color: var(--text-muted); font-weight: 500; }
        .stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1; }
 
        /* ID Card Preview */
        .id-card-preview {
            width: 325px; min-height: 200px; border-radius: 14px; overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,.15); margin: 0 auto;
            position: relative; font-family: 'Inter', sans-serif;
        }
        .id-card-preview .card-header-band { padding: 16px; text-align: center; }
        .id-card-preview .card-body-area { padding: 14px 16px; display: flex; gap: 14px; }
        .id-card-preview .card-photo {
            width: 80px; height: 95px; border-radius: 8px;
            object-fit: cover; border: 3px solid rgba(255,255,255,.5);
        }
 
        /* Print */
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-content { margin: 0; padding: 0; }
            .print-area { display: block !important; }
        }
 
        .alert { border-radius: 10px; font-size: .875rem; }
 
        /* Subject rows */
        .subject-row { background: var(--bg); border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; }
 

        /* Pagination */
        .custom-pagination {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .custom-pagination li {
            list-style: none;
        }

        .custom-pagination button {
            min-width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            background: #f5f5f5;
            color: #444;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
        }

        /* Hover */
        .custom-pagination button:hover {
            background: #eee;
        }

        /* Active (Pink) */
        .custom-pagination button.active {
            background: linear-gradient(195deg, #ec407a, #d81b60);
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(216,27,96,.4);
        }

        /* Disabled */
        .custom-pagination button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
    </style>

    {{-- selectpicker --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
@endpush

@push('scripts')
    {{-- selectpicker --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <script>
        // $('.selectpicker').selectpicker();
        document.addEventListener('livewire:init', function () {

            function refreshPicker() {
                $('.selectpicker').selectpicker('refresh');
            }

            function initPicker() {
                $('.selectpicker').selectpicker();
                // refreshPicker();
            }

            // initial load
            setTimeout(() => {
                initPicker();
            }, 300);

            // Livewire update fix
            Livewire.hook('message.processed', () => {
                setTimeout(() => {
                    refreshPicker();
                }, 50);
            });

            // sync value
            $(document).on('changed.bs.select', '.selectpicker', function () {
                @this.set('mark_distribution', $(this).val());
            });

            // 🔥 THIS IS THE MOST IMPORTANT FIX
            Livewire.on('showModalChanged', () => {
                setTimeout(() => {
                    initPicker();
                }, 300);
            });

        });
    </script>
@endpush