<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5>Events</h5>
            <p>View upcoming academic events and holidays.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search"
                               style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                @if($events->total() > 10)
                    <div>
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $i => $event)
                        <tr>
                            <td class="text-muted">{{ $events->firstItem() + $i }}</td>
                            <td>{{ $event->title }}</td>
                            <td>{{ $event->type ?? '—' }}</td>
                            <td>
                                <span class="badge rounded-pill
                                    @if($event->audience === 'Everybody') badge-active
                                    @elseif($event->audience === 'Selected Class') badge-used
                                    @else badge-inactive @endif">
                                    {{ $event->audience ?? '—' }}
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
                                <button class="act-btn edit" title="View" wire:click="openDetail({{ $event->id }})">
                                    <span class="material-icons-round">visibility</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No events found.
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


    {{-- ===== DETAIL VIEW MODAL ===== --}}
    @if($showDetail && $detailRecord)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
        <div class="modal-dialog modal-md modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">
                        <span class="material-icons-round me-1" style="vertical-align:middle;font-size:1.1rem">event</span>
                        Event Details
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('showDetail', false)"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless mb-0" style="font-size:.875rem;">
                        <tbody>
                            <tr>
                                <td class="fw-600 text-nowrap" style="width:140px">Title :</td>
                                <td>{{ $detailRecord->title }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">Type :</td>
                                <td>{{ $detailRecord->type ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">Audience :</td>
                                <td>
                                    <span class="badge rounded-pill
                                        @if($detailRecord->audience === 'Everybody') badge-active
                                        @elseif($detailRecord->audience === 'Selected Class') badge-used
                                        @else badge-inactive @endif">
                                        {{ $detailRecord->audience ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-600">Date From :</td>
                                <td>{{ \Carbon\Carbon::parse($detailRecord->date_from)->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">Date To :</td>
                                <td>{{ $detailRecord->date_to ? \Carbon\Carbon::parse($detailRecord->date_to)->format('d M Y') : '—' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-600">Holiday :</td>
                                <td>
                                    @if($detailRecord->is_holiday)
                                        <span class="badge-active badge rounded-pill">Yes</span>
                                    @else
                                        <span class="badge-inactive badge rounded-pill">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-600">Website :</td>
                                <td>
                                    @if($detailRecord->show_website)
                                        <span class="badge-active badge rounded-pill">Yes</span>
                                    @else
                                        <span class="badge-inactive badge rounded-pill">No</span>
                                    @endif
                                </td>
                            </tr>
                            @if($detailRecord->description)
                            <tr>
                                <td class="fw-600 align-top">Description :</td>
                                <td style="white-space:pre-line">{{ $detailRecord->description }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light btn-sm" wire:click="$set('showDetail', false)">Close</button>
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
    .modal-title { font-weight: 600; font-size: 1rem; }
    .fw-600 { font-weight: 600; }
    .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
    .custom-pagination { display: flex; gap: 8px; align-items: center; }
    .custom-pagination li { list-style: none; }
    .custom-pagination button { min-width: 38px; height: 38px; border-radius: 10px; border: 1px solid #e0e0e0; background: #f5f5f5; color: #444; font-weight: 600; cursor: pointer; transition: all .2s ease; }
    .custom-pagination button:hover { background: #eee; }
    .custom-pagination button.active { background: linear-gradient(195deg, #ec407a, #d81b60); color: #fff; border: none; box-shadow: 0 4px 12px rgba(216,27,96,.4); }
    .custom-pagination button:disabled { opacity: .5; cursor: not-allowed; }
</style>
@endpush