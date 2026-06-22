<div>

    <div class="card">

      <!-- floating header -->
      <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleAllsections">Exam Schedule</h5>
        <p id="cardHeaderSubtitle">Manage exam schedules, create, update, and organize academic schedules easily.</p>
      </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                <!-- Right Side -->
                @if($schedules->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
                <a href="{{route('admin.exam.schedule.add')}}" class="btn-outline btn-sm bg-dark text-white" wire:click="openCreate">
                    <span class="material-icons-round">add</span> <span id="newSectionBtn">Add Schedule</span>
                </a>

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">Exam Name @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif </th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">Class @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif </th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">Section @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $i => $schedule)
                        <tr>
                            <td class="text-muted">{{ $schedules->firstItem() + $i }}</td>
                            <td> {{ $schedule->exam->name }} </td>
                            <td> {{ $schedule->class->name }} </td>
                            <td> {{ $schedule->section?->name ?? '-' }} </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="openView({{ $schedule->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $schedule->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No schedules found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
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
                        {{-- Eita wrap kora hoyeche jate print korar somoy shudhu eita print hoy --}}
                        <div id="scheduleDetailsPrintable">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th colspan="5" class="text-center">
                                            <h6 class="mb-0">Exam : {{ $viewRecord->exam->name }}</h6>
                                            <p class="mb-0">{{ $viewRecord->class->name }} @if($viewRecord->section?->name)({{ $viewRecord->section?->name }})@endif</p>
                                        </th>
                                    </tr>
                                </thead>
                                <tr>
                                    <th class="text-muted">Subject</th>
                                    <th class="text-muted">Date</th>
                                    <th class="text-muted">Starting Time</th>
                                    <th class="text-muted">Ending Time</th>
                                    <th class="text-muted">Hall Room</th>
                                </tr>
                                @foreach($viewRecord->data ?? [] as $detail)
                                    <tr>
                                        <td>{{ $detail['subject'] ?? '' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($detail['exam_date'])->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($detail['start_time'])->format('h:i A') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($detail['end_time'])->format('h:i A') }}</td>
                                        <td>{{ $detail['hall_room'] ?? '' }}</td>
                                    </tr>
                                @endforeach
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

        /* ── CARD ── */
        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
        .card-header .card-title { font-size: .95rem; font-weight: 600; margin: 0; }
 
        /* ── TABLE ── */
        .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); border-bottom: 2px solid var(--border); }
        .table td { vertical-align: middle; font-size: .875rem; }
        .table > :not(caption) > * > * { padding: .7rem 1rem; }
 
        /* ── BADGES ── */
        .badge-active { background: rgba(34,197,94,.12); color: #16a34a; }
        .badge-inactive { background: rgba(107,114,128,.12); color: #6b7280; }
        .badge-expired, .badge-cancelled, .badge-suspended { background: rgba(239,68,68,.12); color: #dc2626; }
        .badge-used { background: rgba(59,130,246,.12); color: #2563eb; }
 
        /* ── AVATAR ── */
        .avatar { width: 38px; height: 38px; border-radius: 8px; object-fit: cover; }
        .avatar-placeholder {
            width: 38px; height: 38px; border-radius: 8px;
            background: var(--primary-light); color: var(--primary);
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .875rem;
        }
 
        /* ── MODAL ── */
        .modal-header { border-bottom: 1px solid var(--border); }
        .modal-footer { border-top: 1px solid var(--border); }
        .modal-title { font-weight: 600; font-size: 1rem; }
 
        /* ── FORM ── */
        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select {
            border-radius: 8px; border: 1px solid var(--border);
            font-size: .875rem; padding: .45rem .75rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }
        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
 
        /* Color picker */
        .color-input-wrap { display: flex; align-items: center; gap: 8px; }
        .color-input-wrap input[type="color"] {
            width: 40px; height: 38px; padding: 2px; border-radius: 8px;
            cursor: pointer; border: 1px solid var(--border);
        }
 
        /* Buttons */
        .btn-primary { background: var(--primary); border-color: var(--primary); }
        .btn-primary:hover, .btn-primary:focus { background: #d63e3e; border-color: #d63e3e; }
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 7px; }
 
        /* Stat cards */
        .stat-card { border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 16px; }
        .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: grid; place-items: center; font-size: 1.4rem; }
        .stat-label { font-size: .75rem; color: var(--text-muted); font-weight: 500; }
        .stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1; }
 
        /* ID Card Preview */
        .id-card-preview {
            width: 325px; min-height: 200px; border-radius: 14px; overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,.15); margin: 0 auto;
            position: relative; font-family: 'Inter', sans-serif;
        }
        .id-card-preview .card-header-band { padding: 16px; text-align: center; }
        .id-card-preview .card-body-area { padding: 14px 16px; display: flex; gap: 14px; }
        .id-card-preview .card-photo {
            width: 80px; height: 95px; border-radius: 8px;
            object-fit: cover; border: 3px solid rgba(255,255,255,.5);
        }
 
        /* Print */
        @media print {
            .sidebar, .topbar, .no-print { display: none !important; }
            .main-content { margin: 0; padding: 0; }
            .print-area { display: block !important; }
        }
 
        .alert { border-radius: 10px; font-size: .875rem; }
 
        /* Subject rows */
        .subject-row { background: var(--bg); border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; }
 

        /* Pagination */
        .custom-pagination {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .custom-pagination li {
            list-style: none;
        }

        .custom-pagination button {
            min-width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            background: #f5f5f5;
            color: #444;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
        }

        /* Hover */
        .custom-pagination button:hover {
            background: #eee;
        }

        /* Active (Pink) */
        .custom-pagination button.active {
            background: linear-gradient(195deg, #ec407a, #d81b60);
            color: #fff;
            border: none;
            box-shadow: 0 4px 12px rgba(216,27,96,.4);
        }

        /* Disabled */
        .custom-pagination button:disabled {
            opacity: .5;
            cursor: not-allowed;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Modal-er bhitorer #scheduleDetailsPrintable content niye notun ekta window-e
        // shundor format kore print dialog open kore - sidebar/topbar/search kichu print hobe na
        function printScheduleDetails() {
            const printableEl = document.getElementById('scheduleDetailsPrintable');

            if (!printableEl) {
                return;
            }

            const printContent = printableEl.innerHTML;
            const printWindow = window.open('', '_blank', 'width=900,height=650');

            if (!printWindow) {
                alert('Print window block hoye gেছে। Browser-er popup blocker check korun.');
                return;
            }

            printWindow.document.write(`
                <html>
                    <head>
                        <title>Exam Schedule</title>
                        <style>
                            * { box-sizing: border-box; }
                            body {
                                font-family: Arial, Helvetica, sans-serif;
                                padding: 28px;
                                color: #222;
                            }
                            table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 10px;
                            }
                            th, td {
                                border: 1px solid #ddd;
                                padding: 8px 10px;
                                text-align: left;
                                font-size: 13px;
                            }
                            thead th {
                                background: #f5f5f5;
                            }
                            h6 {
                                margin: 0 0 2px 0;
                                font-size: 16px;
                            }
                            p {
                                margin: 0;
                                color: #555;
                                font-size: 13px;
                            }
                            .text-muted {
                                color: #777 !important;
                                text-transform: uppercase;
                                font-size: 11px;
                            }
                        </style>
                    </head>
                    <body>
                        ${printContent}
                    </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.focus();

            // Content load hoye jawar por print dialog open hobe
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        }
    </script>
@endpush