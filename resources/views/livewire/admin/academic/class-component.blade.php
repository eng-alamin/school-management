<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllClasses">All Classes</h5>
            <p id="cardHeaderSubtitle">Manage classes, create, update, and organize easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                @if($classes->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <a href="{{ route($routePrefix . 'academic.classes') }}" class="btn btn-primary btn-sm">
                    <span>
                        <span class="material-icons-round">flight_class</span>
                        <span>Class</span>
                    </span>
                </a>
                <a href="{{ route($routePrefix . 'academic.sections') }}" class="btn btn-primary btn-sm">
                    <span>
                        <span class="material-icons-round">border_inner</span>
                        <span>Section</span>
                    </span>
                </a>
                <a href="{{ route($routePrefix . 'academic.subjects') }}" class="btn btn-primary btn-sm">
                    <span>
                        <span class="material-icons-round">subject</span>
                        <span>Subjects</span>
                    </span>
                </a>
                <a href="{{ route($routePrefix . 'academic.groups') }}" class="btn btn-primary btn-sm">
                    <span>
                        <span class="material-icons-round">group</span>
                        <span>Groups</span>
                    </span>
                </a>

                <button class="btn btn-primary btn-sm" wire:click="openCreate">
                    <span>
                        <span class="material-icons-round">add</span> 
                        <span>New Class</span>
                    </span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-name" wire:click="sortBy('name')" style="cursor:pointer">
                                Name @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-numeric" wire:click="sortBy('numeric')" style="cursor:pointer">
                                Numeric @if($sortField === 'numeric') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-has-section" wire:click="sortBy('has_section')" style="cursor:pointer">
                                Has Section @if($sortField === 'has_section') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-section">Section</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classes as $i => $class)
                        <tr>
                            <td class="text-muted">{{ $classes->firstItem() + $i }}</td>
                            <td>{{ $class->name }}</td>
                            <td>{{ $class->numeric ?? '—' }}</td>
                            <td>
                                @if($class->has_section)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                @if($class->has_section)
                                    @forelse($class->sections as $section)
                                        <span class="badge bg-primary">
                                            {{ $section->name }}
                                        </span>
                                    @empty
                                        —
                                    @endforelse
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $class->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $class->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No classes found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $classes->firstItem() ?? 0 }}–{{ $classes->lastItem() ?? 0 }} of {{ $classes->total() }}</small>
            {{ $classes->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">{{ $editId ? 'Edit' : 'Create' }} Class</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name" placeholder="e.g. Class One">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Numeric</label>
                                <input type="number" class="form-control @error('numeric') is-invalid @enderror" wire:model.defer="numeric" placeholder="e.g. 1">
                                @error('numeric') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="hasSectionSwitch" wire:model.live="hasSection">
                                    <label class="form-check-label form-label mb-0" for="hasSectionSwitch">
                                        This class has sections
                                    </label>
                                </div>
                                @if($editId && !$hasSection)
                                    <small class="text-warning d-block mt-1">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        Turning this off will remove all section assignments for this class.
                                    </small>
                                @endif
                            </div>
                            @if($hasSection)
                            <div class="col-md-12">
                                <div wire:ignore>
                                    <label class="form-label">Section</label>
                                    <select class="form-select w-100 selectpicker @error('sectionIds') is-invalid @enderror" wire:model.defer="sectionIds" multiple title="Select Section...">
                                        @foreach($sections as $section)
                                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('sectionIds') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
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

                        @if($deleteBlockedMessage)
                            <h6 class="fw-700">Cannot Delete</h6>
                            <p class="text-danger small">{{ $deleteBlockedMessage }}</p>
                        @else
                            <h6 class="fw-700">Delete Class?</h6>
                            <p class="text-muted small">This action cannot be undone.</p>
                        @endif
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('confirmDelete', false)">Cancel</button>
                        @if(! $deleteBlockedMessage)
                            <button class="btn btn-danger btn-sm" wire:click="deleteRecord">
                                <span wire:loading wire:target="deleteRecord" class="spinner-border spinner-border-sm me-1"></span>
                                Delete
                            </button>
                        @endif
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

    .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
    .form-control, .form-select {
        border-radius: 8px; border: 1px solid var(--border);
        font-size: .875rem; padding: .45rem .75rem;
        transition: border-color .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
    }

    .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
</style>
@endpush

@push('styles')
        {{-- selectpicker --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
@endpush
@push('scripts')
    {{-- selectpicker --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    {{-- <script>
        document.addEventListener('livewire:init', function () {

            function refreshPicker() {
                $('.selectpicker').selectpicker('refresh');
            }

            function initPicker() {
                $('.selectpicker').selectpicker();
            }

            // initial load
            setTimeout(() => {
                initPicker();
            }, 300);

            // Livewire 3 fix: use 'commit' hook instead of deprecated 'message.processed'
            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    setTimeout(() => {
                        refreshPicker();
                    }, 50);
                });
            });

            // NOTE: No manual @this.set('sectionIds', ...) here on purpose.
            // wire:model.defer already listens to the native <select>'s change
            // event, and bootstrap-select fires that same native event when a
            // user picks an option. Adding a manual @this.set() on top of that
            // caused duplicate/racing network requests, so it has been removed.

            // 🔥 THIS IS THE MOST IMPORTANT FIX
            Livewire.on('showModalChanged', () => {
                setTimeout(() => {
                    initPicker();
                }, 300);
            });

        });
    </script> --}}
    <script>
        // $('.selectpicker').selectpicker();
        document.addEventListener('livewire:init', function () {

            function refreshPicker() {
                $('.selectpicker').selectpicker('refresh');
            }

            function initPicker() {
                $('.selectpicker').selectpicker();
            }

            // initial load
            setTimeout(() => {
                initPicker();
            }, 50);

            Livewire.hook('commit', ({ succeed }) => {
                succeed(() => {
                    setTimeout(() => {
                        initPicker();
                    }, 50);
                });
            });

            // 🔥 THIS IS THE MOST IMPORTANT FIX
            Livewire.on('showModalChanged', () => {
                setTimeout(() => {
                    initPicker();
                }, 50);
            });

        });
    </script>
@endpush