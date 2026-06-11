<div>

    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5>Events</h5>
            <p>Manage events, create, update, and organize academic events easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                {{-- Right side --}}
                @if($events->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <a href="{{ route('admin.event.types', ['tenant' => tenant('id')]) }}" class="btn-sm btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> Add Types
                </a>
                <a href="{{ route('admin.event.add', ['tenant' => tenant('id')]) }}" class="btn-sm btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> Add Event
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('title')" style="cursor:pointer">
                                Title @if($sortField === 'title') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Type</th>
                            <th>Audience</th>
                            <th wire:click="sortBy('date_from')" style="cursor:pointer">
                                Date @if($sortField === 'date_from') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Holiday</th>
                            <th>Website</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $i => $event)
                            <tr>
                                <td class="text-muted">{{ $events->firstItem() + $i }}</td>
                                <td>{{ $event->title }}</td>
                                <td>{{ $event->type }}</td>
                                <td>
                                    <span class="badge rounded-pill
                                        @if($event->audience === 'Everybody') badge-active
                                        @elseif($event->audience === 'Selected Class') badge-used
                                        @else badge-inactive @endif">
                                        {{ $event->audience }}
                                    </span>
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($event->date_from)->format('d M Y') }}
                                    @if($event->date_to)
                                        — {{ \Carbon\Carbon::parse($event->date_to)->format('d M Y') }}
                                    @endif
                                </td>
                                <td>
                                    @if($event->is_holiday)
                                        <span class="badge-active badge rounded-pill">Yes</span>
                                    @else
                                        <span class="badge-inactive badge rounded-pill">No</span>
                                    @endif
                                </td>
                                <td>
                                    @if($event->show_website)
                                        <span class="badge-active badge rounded-pill">Yes</span>
                                    @else
                                        <span class="badge-inactive badge rounded-pill">No</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.event.edit', ['tenant' => tenant('id'), 'id' => $event->id]) }}"
                                           class="act-btn edit" title="Edit">
                                            <span class="material-icons-round">drive_file_rename_outline</span>
                                        </a>
                                        <button class="act-btn delete" title="Delete"
                                                wire:click="confirmDeleteRecord({{ $event->id }})">
                                            <span class="material-icons-round">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No events found.
                                    <a href="{{ route('admin.event.add', ['tenant' => tenant('id')]) }}">Create one now</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $events->firstItem() ?? 0 }}–{{ $events->lastItem() ?? 0 }} of {{ $events->total() }}</small>
            {{ $events->links('vendor.pagination.custom') }}
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
                        <h6 class="fw-700">Delete Event?</h6>
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

        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }

        .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); border-bottom: 2px solid var(--border); }
        .table td { vertical-align: middle; font-size: .875rem; }
        .table > :not(caption) > * > * { padding: .7rem 1rem; }

        .badge-active   { background: rgba(34,197,94,.12);  color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }
        .badge-used     { background: rgba(59,130,246,.12);  color: #2563eb; }

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