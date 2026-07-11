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
                            <th>Exam Name</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th wire:click="sortBy('exam_date')" style="cursor:pointer">
                                Date @if($sortField === 'exam_date') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('start_time')" style="cursor:pointer">
                                Time @if($sortField === 'start_time') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('class_room')" style="cursor:pointer">
                                Room @if($sortField === 'class_room') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $i => $schedule)
                        <tr>
                            <td class="text-muted">{{ $schedules->firstItem() + $i }}</td>
                            <td>{{ $schedule->examSetup?->name ?? '—' }}</td>
                            <td>{{ $schedule->examSetupDetail?->classAssignDetail?->subject?->name ?? '—' }}</td>
                            <td>
                                {{ $schedule->examSetup?->classAssign?->class?->name ?? '—' }}
                                @if($schedule->examSetup?->classAssign?->section)
                                    ({{ $schedule->examSetup->classAssign->section->name }})
                                @endif
                            </td>
                            <td>{{ $schedule->exam_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}
                                - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                            </td>
                            <td>{{ $schedule->class_room ?? '—' }}</td>
                            <td>
                                <button class="act-btn edit" title="View" wire:click="openView({{ $schedule->id }})">
                                    <span class="material-icons-round">visibility</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
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
                    <h5 class="modal-title">Exam Schedule Details</h5>
                    <button class="btn-close" wire:click="$set('showViewModal', false)"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th colspan="2" class="text-center">
                                    <h6 class="mb-0">{{ $viewRecord->examSetup?->name ?? '—' }}</h6>
                                    <p class="mb-0 text-muted">
                                        {{ $viewRecord->examSetup?->term?->name ?? '—' }}
                                        @if($viewRecord->examSetup?->type)
                                            / {{ $viewRecord->examSetup->type->name }}
                                        @endif
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th class="text-muted" style="width:35%">Class</th>
                                <td>
                                    {{ $viewRecord->examSetup?->classAssign?->class?->name ?? '—' }}
                                    @if($viewRecord->examSetup?->classAssign?->section)
                                        ({{ $viewRecord->examSetup->classAssign->section->name }})
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Subject</th>
                                <td>{{ $viewRecord->examSetupDetail?->classAssignDetail?->subject?->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Teacher</th>
                                <td>{{ $viewRecord->examSetupDetail?->classAssignDetail?->teacher?->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Date</th>
                                <td>{{ $viewRecord->exam_date?->format('d M Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Time</th>
                                <td>
                                    {{ \Carbon\Carbon::parse($viewRecord->start_time)->format('h:i A') }}
                                    - {{ \Carbon\Carbon::parse($viewRecord->end_time)->format('h:i A') }}
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Room</th>
                                <td>{{ $viewRecord->class_room ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Full Mark</th>
                                <td>{{ $viewRecord->examSetupDetail?->full_mark ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Pass Mark</th>
                                <td>{{ $viewRecord->examSetupDetail?->pass_mark ?? '—' }}</td>
                            </tr>
                            @if($viewRecord->remarks)
                            <tr>
                                <th class="text-muted">Remarks</th>
                                <td>{{ $viewRecord->remarks }}</td>
                            </tr>
                            @endif
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