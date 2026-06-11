<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5>Exam Schedule</h5>
            <p>View your upcoming exam schedule.</p>
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

                @if($schedules->total() > 10)
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
                            <th wire:click="sortBy('exam_id')" style="cursor:pointer">
                                Exam Name @if($sortField === 'exam_id') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('class_id')" style="cursor:pointer">
                                Class @if($sortField === 'class_id') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('section_id')" style="cursor:pointer">
                                Section @if($sortField === 'section_id') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $i => $schedule)
                        <tr>
                            <td class="text-muted">{{ $schedules->firstItem() + $i }}</td>
                            <td>{{ $schedule->exam->name ?? '—' }}</td>
                            <td>{{ $schedule->class->name ?? '—' }}</td>
                            <td>{{ $schedule->section->name ?? '—' }}</td>
                            <td>
                                <button class="act-btn edit" title="View" wire:click="openView({{ $schedule->id }})">
                                    <span class="material-icons-round">visibility</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No exam schedule found for your class.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $schedules->firstItem() ?? 0 }}–{{ $schedules->lastItem() ?? 0 }} of {{ $schedules->total() }}</small>
            {{ $schedules->links('vendor.pagination.custom') }}
        </div>

    </div>


    {{-- ===== VIEW MODAL ===== --}}
    @if($showViewModal && $viewRecord)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Details</h5>
                    <button class="btn-close" wire:click="$set('showViewModal', false)"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th colspan="5" class="text-center">
                                    <h6 class="mb-0">Exam : {{ $viewRecord->exam->name }}</h6>
                                    <p class="mb-0">{{ $viewRecord->class->name }} ({{ $viewRecord->section->name }})</p>
                                </th>
                            </tr>
                            <tr>
                                <th class="text-muted">Subject</th>
                                <th class="text-muted">Date</th>
                                <th class="text-muted">Starting Time</th>
                                <th class="text-muted">Ending Time</th>
                                <th class="text-muted">Hall Room</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($viewRecord->data ?? [] as $detail)
                            <tr>
                                <td>{{ $detail['subject'] ?? '—' }}</td>
                                <td>{{ \Carbon\Carbon::parse($detail['exam_date'])->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($detail['start_time'])->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($detail['end_time'])->format('h:i A') }}</td>
                                <td>{{ $detail['hall_room'] ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light btn-sm" wire:click="$set('showViewModal', false)">Close</button>
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
    .modal-title { font-weight: 600; font-size: 1rem; }
    .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
</style>
@endpush