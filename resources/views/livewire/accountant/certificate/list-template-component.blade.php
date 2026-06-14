<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5>Certificate Templates</h5>
            <p>Manage certificate templates, create, update, and organize them easily.</p>
        </div>

        @if (session()->has('success'))
            <div class="alert alert-success mx-4 mt-3" role="alert">
                <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        <div class="card-header border-0">
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Search templates..."
                               style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                {{-- Per page --}}
                @if($templates->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                {{-- Add button --}}
                <a href="{{ route('accountant.certificate.add-template') }}"
                   class="btn-sm btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> Add Template
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('certificate_name')" style="cursor:pointer">
                                Name
                                @if($sortField === 'certificate_name')
                                    {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                                @endif
                            </th>
                            <th wire:click="sortBy('applicable_user')" style="cursor:pointer">
                                Applicable For
                                @if($sortField === 'applicable_user')
                                    {!! $sortDirection === 'asc' ? '↑' : '↓' !!}
                                @endif
                            </th>
                            <th>Page Layout</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $i => $template)
                            <tr>
                                <td class="text-muted">{{ $templates->firstItem() + $i }}</td>

                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        @if($template->logo_image)
                                            <img src="{{ asset($template->logo_image) }}"
                                                 style="width:32px;height:32px;object-fit:cover;border-radius:6px;border:1px solid #eee">
                                        @else
                                            <span class="material-icons-round"
                                                  style="font-size:28px;color:#ddd">workspace_premium</span>
                                        @endif
                                        <span>{{ $template->certificate_name }}</span>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge rounded-pill
                                        {{ $template->applicable_user === 'student' ? 'badge-used' : 'badge-warning' }}">
                                        {{ ucfirst($template->applicable_user) }}
                                    </span>
                                </td>

                                <td>
                                    <span style="font-size:.78rem;color:var(--muted)">
                                        {{ strtoupper(str_replace('_', ' ', $template->page_layout)) }}
                                    </span>
                                </td>

                                <td>
                                    @if($template->is_active)
                                        <span class="badge rounded-pill badge-active">Active</span>
                                    @else
                                        <span class="badge rounded-pill badge-inactive">Inactive</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('accountant.certificate.edit-template', ['id' => $template->id]) }}"
                                           class="act-btn edit" title="Edit">
                                            <span class="material-icons-round">drive_file_rename_outline</span>
                                        </a>
                                        <button class="act-btn delete" title="Delete"
                                                wire:click="confirmDeleteRecord({{ $template->id }})">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <span class="material-icons-round d-block mb-2"
                                          style="font-size:2.5rem;opacity:.2">workspace_premium</span>
                                    No certificate templates found.
                                    <a href="{{ route('accountant.certificate.add-template') }}">
                                        Create one now
                                    </a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">
                Showing {{ $templates->firstItem() ?? 0 }}–{{ $templates->lastItem() ?? 0 }}
                of {{ $templates->total() }}
            </small>
            {{ $templates->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== DELETE CONFIRM ===== --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                        </div>
                        <h6 class="fw-700">Delete Template?</h6>
                        <p class="text-muted small">This action cannot be undone. Associated images will also be deleted.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm"
                                wire:click="$set('confirmDelete', false)">
                            Cancel
                        </button>
                        <button class="btn btn-danger btn-sm"
                                wire:click="deleteRecord">
                            <span wire:loading wire:target="deleteRecord"
                                  class="spinner-border spinner-border-sm me-1"></span>
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
        .table > :not(caption) > * > * { padding: .7rem 1rem; }

        .badge-active   { background: rgba(34,197,94,.12);  color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }
        .badge-used     { background: rgba(59,130,246,.12);  color: #2563eb; }
        .badge-warning  { background: rgba(245,158,11,.12);  color: #d97706; }

        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select { border-radius: 8px; border: 1px solid var(--border); font-size: .875rem; padding: .45rem .75rem; transition: border-color .2s, box-shadow .2s; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }

        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }

        .custom-pagination { display: flex; gap: 8px; align-items: center; }
        .custom-pagination li { list-style: none; }
        .custom-pagination button { min-width: 38px; height: 38px; border-radius: 10px; border: 1px solid #e0e0e0; background: #f5f5f5; color: #444; font-weight: 600; cursor: pointer; transition: all .2s ease; }
        .custom-pagination button:hover { background: #eee; }
        .custom-pagination button.active { background: linear-gradient(195deg, #ec407a, #d81b60); color: #fff; border: none; box-shadow: 0 4px 12px rgba(216,27,96,.4); }
        .custom-pagination button:disabled { opacity: .5; cursor: not-allowed; }
    </style>
@endpush