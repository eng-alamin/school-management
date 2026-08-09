<div>

    <div class="card">

        <div class="card-header-floating card-header-gradient">
            <h5>Events</h5>
            <p>Manage events, create, update, and organize academic events easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search" class="tb-search"/>
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

                <a href="{{ route('admin.event.types') }}" class="btn-sm btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> Add Types
                </a>
                <a href="{{ route('admin.event.add') }}" class="btn-sm btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> Add Event
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-title" wire:click="sortBy('title')" style="cursor:pointer">
                                Title @if($sortField === 'title') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-type">Type</th>
                            <th id="th-audience">Audience</th>
                            <th id="th-date" wire:click="sortBy('date_from')" style="cursor:pointer">
                                Date @if($sortField === 'date_from') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-holiday">Holiday</th>
                            <th id="th-website">Website</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $i => $event)
                            <tr>
                                <td class="text-muted">{{ $events->firstItem() + $i }}</td>
                                <td>{{ $event->title }}</td>
                                {{--
                                    BUG FIX: 'type' was a free-text column that has been
                                    replaced with the event_type_id foreign key. We now
                                    show the related EventType name (eager-loaded in
                                    ListComponent::render() via ->with('eventType') to
                                    avoid N+1 queries).
                                --}}
                                <td>{{ $event->eventType->name ?? '-' }}</td>
                                <td>
                                    {{--
                                        BUG FIX: previously compared against 'Everybody',
                                        'Selected Class', 'Selected Section' which never
                                        matched the actual stored enum values
                                        ('everyone', 'class', 'section'), so the badge
                                        always fell into the "else" (inactive) branch.
                                    --}}
                                    <span class="badge rounded-pill
                                        @if($event->audience === 'everyone') badge-active
                                        @elseif($event->audience === 'class') badge-used
                                        @else badge-inactive @endif">
                                        @if($event->audience === 'everyone')
                                            Everybody
                                        @elseif($event->audience === 'class')
                                            Selected Class
                                        @else
                                            Selected Section
                                        @endif
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
                                        <a href="{{ route('admin.event.edit', ['id' => $event->id]) }}"
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
                                    <a href="{{ route('admin.event.add') }}">Create one now</a>.
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