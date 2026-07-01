<div>

    <div class="card">

      <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleAllsections">Exam Schedule</h5>
        <p id="cardHeaderSubtitle">Manage exam schedules, create, update, and organize academic schedules easily.</p>
      </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
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
                                @if($setup->classAssign)
                                    <span class="badge bg-info-subtle text-dark">
                                        {{ $setup->classAssign->class->name ?? '—' }}
                                        @if($setup->classAssign->section)
                                            - {{ $setup->classAssign->section->name }}
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
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No schedules found. <a href="{{ route('admin.exam.schedule.add') }}">Create one now</a>.
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
                                            <p class="mb-0">
                                                {{ $viewRecord->classAssign->class->name ?? '—' }}
                                                @if($viewRecord->classAssign->section)
                                                    ({{ $viewRecord->classAssign->section->name }})
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
                                    <tr>
                                        <td>{{ $sched->examSetupDetail->classAssignDetail->subject->name ?? '—' }}</td>  {{-- ✅ ঠিক --}}
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
                        <button class="btn btn-primary" type="button" onclick="printScheduleDetails()">
                            <i class="bi bi-printer me-1"></i>Print
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
        .card-header .card-title { font-size: .95rem; font-weight: 600; margin: 0; }

        .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); border-bottom: 2px solid var(--border); }
        .table td { vertical-align: middle; font-size: .875rem; }
        .table > :not(caption) > * > * { padding: .7rem 1rem; }

        .modal-header { border-bottom: 1px solid var(--border); }
        .modal-footer { border-top: 1px solid var(--border); }
        .modal-title { font-weight: 600; font-size: 1rem; }

        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select {
            border-radius: 8px; border: 1px solid var(--border);
            font-size: .875rem; padding: .45rem .75rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover, .btn-primary:focus { background: #d63e3e; border-color: #d63e3e; }
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 7px; }

        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-content { margin: 0; padding: 0; }
            .print-area { display: block !important; }
        }

        .alert { border-radius: 10px; font-size: .875rem; }

        .custom-pagination { display: flex; gap: 8px; align-items: center; }
        .custom-pagination li { list-style: none; }
        .custom-pagination button {
            min-width: 38px; height: 38px; border-radius: 10px;
            border: 1px solid #e0e0e0; background: #f5f5f5; color: #444;
            font-weight: 600; cursor: pointer; transition: all .2s ease;
        }
        .custom-pagination button:hover { background: #eee; }
        .custom-pagination button.active {
            background: linear-gradient(195deg, #ec407a, #d81b60);
            color: #fff; border: none; box-shadow: 0 4px 12px rgba(216,27,96,.4);
        }
        .custom-pagination button:disabled { opacity: .5; cursor: not-allowed; }
    </style>
@endpush

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