<div>
    <div class="card">

      <div class="mat-card-header header-primary-gradient">
        <h5 id="cardHeaderTitleAllsections">Exam Schedule</h5>
        <p id="cardHeaderSubtitle">Manage exam schedules, create, update, and organize academic schedules easily.</p>
      </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                @if($schedules->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
                <a href="{{ route($routePrefix . 'exam.schedule.add') }}" class="btn btn-primary">
                    <span>
                        <span class="material-icons-round">add_circle</span>
                        <span id="newSectionBtn">Add Schedule</span>
                    </span>
                </a>

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th id="th-sl">SL</th>
                            <th id="th-exam-name" wire:click="sortBy('name')" style="cursor:pointer">
                                Exam Name @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th id="th-class">Class</th>
                            <th id="th-subjects">Subjects Scheduled</th>
                            <th id="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $i => $setup)
                        <tr wire:key="setup-schedule-{{ $setup->id }}">
                            <td class="text-muted">{{ $schedules->firstItem() + $i }}</td>
                            <td>{{ $setup->name }}</td>
                            <td>
                                {{-- FIX: class/section relation নাম ভুল ছিল — real relation
                                     academicClass/academicSection ব্যবহার করা হলো --}}
                                @if($setup->classAssign)
                                    <span class="badge bg-info-subtle text-dark">
                                        {{ $setup->classAssign->academicClass->name ?? '—' }}
                                        @if($setup->classAssign->academicSection)
                                            - {{ $setup->classAssign->academicSection->name }}
                                        @endif
                                    </span>
                                @else
                                    <span class="text-danger">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $setup->published_count }} / {{ $setup->total_subjects }} published
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $setup->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <a href="{{ route($routePrefix . 'exam.schedule.add') }}?exam={{ $setup->id }}" class="act-btn edit" title="Edit">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </a>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $setup->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No schedules found. <a href="{{ route($routePrefix . 'exam.schedule.add') }}">Create one now</a>.
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
                        <button class="btn-close" wire:click="$set('showViewModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <div id="scheduleDetailsPrintable">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th colspan="5" class="text-center">
                                            <h6 class="mb-0">Exam : {{ $viewRecord->name }}</h6>
                                            {{-- FIX: class/section relation নাম ভুল ছিল — real relation
                                                 academicClass/academicSection ব্যবহার করা হলো --}}
                                            <p class="mb-0">
                                                {{ $viewRecord->classAssign->academicClass->name ?? '—' }}
                                                @if($viewRecord->classAssign->academicSection)
                                                    ({{ $viewRecord->classAssign->academicSection->name }})
                                                @endif
                                            </p>
                                        </th>
                                    </tr>
                                </thead>
                                <tr>
                                    <th id="th-subject" class="text-muted">Subject</th>
                                    <th id="th-date" class="text-muted">Date</th>
                                    <th id="th-starting-time" class="text-muted">Starting Time</th>
                                    <th id="th-ending-time" class="text-muted">Ending Time</th>
                                    <th id="th-hall-room" class="text-muted">Class Room</th>
                                </tr>
                                @forelse($viewRecord->schedules as $sched)
                                    <tr wire:key="sched-view-{{ $sched->id }}">
                                        <td>{{ $sched->examSetupDetail->classAssignDetail->subject->name ?? '—' }}</td>
                                        <td>{{ $sched->exam_date?->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sched->start_time)->format('h:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sched->end_time)->format('h:i A') }}</td>
                                        <td>{{ $sched->class_room ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No schedule entries found.</td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" wire:click="$set('showViewModal',false)">Close</button>
                        <button type="button" onclick="printScheduleDetails()" class="btn bg-dark text-white">
                            <i class="bi bi-printer me-1"></i>Print
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
                        <h6 class="fw-700">Delete Schedule?</h6>
                        <p class="text-muted small">এই exam এর সব subject-এর schedule মুছে যাবে। এই action undo করা যাবে না।</p>
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

@push('scripts')
    <script>
        function printScheduleDetails() {
            const printableEl = document.getElementById('scheduleDetailsPrintable');

            if (!printableEl) {
                return;
            }

            const printContent = printableEl.innerHTML;
            const printWindow = window.open('', '_blank', 'width=900,height=650');

            if (!printWindow) {
                alert('Print window block hoye গেছে। Browser-er popup blocker check korun.');
                return;
            }

            printWindow.document.write(`
                <html>
                    <head>
                        <title>Exam Schedule</title>
                        <style>
                            * { box-sizing: border-box; }
                            body { font-family: Arial, Helvetica, sans-serif; padding: 28px; color: #222; }
                            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                            th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; font-size: 13px; }
                            thead th { background: #f5f5f5; }
                            h6 { margin: 0 0 2px 0; font-size: 16px; }
                            p { margin: 0; color: #555; font-size: 13px; }
                            .text-muted { color: #777 !important; text-transform: uppercase; font-size: 11px; }
                        </style>
                    </head>
                    <body>
                        ${printContent}
                    </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }
    </script>
@endpush