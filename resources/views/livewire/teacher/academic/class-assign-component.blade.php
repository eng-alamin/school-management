<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllassigns">All Class Assign</h5>
            <p id="cardHeaderSubtitle">Manage class assignments, create, update, and organize easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
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
                    <span class="material-icons-round">add</span> <span>New Assign</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Subjects</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assigns as $i => $assign)
                        <tr>
                            <td class="text-muted">{{ $assigns->firstItem() + $i }}</td>
                            <td>{{ $assign->class?->name ?? '—' }}</td>
                            <td>{{ $assign->section?->name ?? '—' }}</td>
                            <td>
                                @if(!empty($assign->subjects))
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($assign->subjects as $subject)
                                            <span class="badge bg-secondary">{{ $subject }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">all</span>
                                @endif
                            </td>
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
                                No assignments found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
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
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">{{ $editId ? 'Edit' : 'Create' }} Assignment</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">

                            {{-- Class --}}
                            <div class="col-md-12">
                                <label class="form-label">Class <span class="text-danger">*</span></label>
                                <select class="form-select @error('class_id') is-invalid @enderror" wire:model.live="class_id">
                                    <option value="">Select Class</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Section --}}
                            <div class="col-md-12">
                                <label class="form-label">Section</label>
                                <select class="form-select @error('section_id') is-invalid @enderror" wire:model.live="section_id" @disabled(empty($sections))>
                                    <option value="">{{ empty($sections) ? 'Select class first' : 'Select Section' }}</option>
                                    @if(!empty($sections))
                                        <option value="all">All Section</option>
                                        @foreach ($sections as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('section_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Subjects --}}
                            <div class="col-md-12">
                                <label class="form-label">Subjects</label>

                                {{-- subjects JSON এখানে render হবে, wire:ignore এর বাইরে --}}
                                <span id="subjectData" style="display:none"
                                      data-subjects="{{ json_encode($subjects->values()) }}"></span>

                                <div wire:ignore>
                                    <select
                                        id="subjectArrayPicker"
                                        multiple
                                        title="Select Subject..."
                                        class="form-select w-100 selectpicker">
                                    </select>
                                </div>
                                @error('subject_array') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
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
                        <h6 class="fw-700">Delete Assignment?</h6>
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

    .form-check-input:checked { background-color: #212529; border-color: #212529; }
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
    <script>
        document.addEventListener('livewire:init', function () {

            // subject select এর কারণে commit হচ্ছে কিনা track করতে flag
            let isSubjectChanging = false;

            // পূর্বের subjects JSON track করো — পরিবর্তন হলেই rebuild করবো
            let previousSubjectsJson = '';

            function getSubjectsJson() {
                const span = document.getElementById('subjectData');
                if (!span) return '[]';
                return span.dataset.subjects || '[]';
            }

            function getSubjects() {
                try { return JSON.parse(getSubjectsJson()); } catch(e) { return []; }
            }

            function destroyPicker() {
                try { $('#subjectArrayPicker').selectpicker('destroy'); } catch(e) {}
            }

            function buildAndInitPicker(selected = []) {
                const subjects = getSubjects();
                const $select = $('#subjectArrayPicker');
                if (!$select.length) return;

                $select.empty();
                subjects.forEach(function(sub) {
                    $select.append(new Option(sub, sub));
                });

                destroyPicker();
                $select.selectpicker();

                if (selected.length > 0) {
                    setTimeout(function() {
                        $select.selectpicker('val', selected);
                    }, 50);
                }
            }

            // Livewire commit hook — শুধু subjects পরিবর্তন হলেই rebuild
            Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                succeed(({ snapshot, effect }) => {
                    // subject select এর কারণে commit হলে rebuild করবো না
                    if (isSubjectChanging) {
                        isSubjectChanging = false;
                        return;
                    }

                    setTimeout(function() {
                        if (!$('#subjectArrayPicker').length) return;

                        const newSubjectsJson = getSubjectsJson();

                        // subjects JSON পরিবর্তন হলেই rebuild করো
                        if (newSubjectsJson !== previousSubjectsJson) {
                            previousSubjectsJson = newSubjectsJson;
                            buildAndInitPicker([]);
                        }
                    }, 150);
                });
            });

            // selectpicker change → Livewire sync, flag set করো
            $(document).on('changed.bs.select', '#subjectArrayPicker', function () {
                isSubjectChanging = true;
                @this.set('subject_array', $(this).val() || []);
            });

            // modal open (create / edit)
            Livewire.on('showModalChanged', function(event) {
                previousSubjectsJson = ''; // reset করো যাতে প্রথম load এ rebuild হয়
                setTimeout(function() {
                    buildAndInitPicker(event.selected || []);
                    previousSubjectsJson = getSubjectsJson();
                }, 400);
            });

        });
    </script>
@endpush