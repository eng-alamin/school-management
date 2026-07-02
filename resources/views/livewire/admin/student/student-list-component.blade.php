<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllStudents">
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">groups</span>
                All Students
            </h5>
            <p id="cardHeaderSubtitle">Manage students, filter by class and section.</p>
        </div>

        {{-- ===== TOOLBAR (search + live filter + actions) ===== --}}
        <div class="card-header border-0">
            <div class="card-toolbar">

                {{-- Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Search name, reg no..."
                            style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                {{-- Class filter --}}
                <div>
                    <select wire:model.live="filterClass" class="form-select form-select-sm" style="min-width:140px">
                        <option value="">All Classes</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Section filter --}}
                <div>
                    <select wire:model.live="filterSection" class="form-select form-select-sm" style="min-width:140px"
                        {{ empty($availableSections) ? 'disabled' : '' }}>
                        <option value="">All Sections</option>
                        @if(!empty($availableSections))
                            @foreach ($availableSections as $s)
                                <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Per page --}}
                @if($students->total() > 10)
                    <div>
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                {{-- Reset --}}
                <button class="btn-outline" type="button" wire:click="resetForm">
                    <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
                </button>

                {{-- Import --}}
                <button class="btn-outline" data-bs-toggle="modal" data-bs-target="#importModal">
                    <span class="material-icons-round" style="font-size:16px">upload</span> Import
                </button>

                {{-- Export CSV --}}
                <button class="btn-outline" onclick="exportStudentCSV()">
                    <span class="material-icons-round" style="font-size:16px">download</span> Export CSV
                </button>

                {{-- Print --}}
                <button class="btn-outline" onclick="printTable()">
                    <span class="material-icons-round" style="font-size:16px">print</span> Print
                </button>

                <a href="{{ route('admin.student.add') }}" class="btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> Add Student
                </a>

            </div>
        </div>

        {{-- ===== TABLE (always visible, live-filtered) ===== --}}
        <div class="card-body pt-0" id="printArea">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="studentTable">
                    <thead>
                        <tr>
                            <th id="th-name">Name</th>
                            <th id="th-class">Class</th>
                            <th id="th-section">Section</th>
                            <th id="th-gender">Gender</th>
                            <th id="th-register-no">Register No</th>
                            <th id="th-roll-no">Roll No</th>
                            <th id="th-guardian">Guardian</th>
                            <th id="th-actions" class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $student->photo ? asset('storage/' . $student->photo) : asset('assets/img/boy.jpg') }}"
                                        style="width:36px;height:36px;border-radius:8px;object-fit:cover;" alt="">
                                    <span class="fw-500">{{ $student->name }}</span>
                                </div>
                            </td>
                            <td>{{ $student->class?->name ?? '—' }}</td>
                            <td>{{ $student->section?->name ?? '—' }}</td>
                            <td>{{ $student->gender ?? '—' }}</td>
                            <td>{{ $student->register_no }}</td>
                            <td>{{ $student->roll_no ?? '—' }}</td>
                            <td>{{ $student->guardians->first()?->name ?? '—' }}</td>
                            <td class="no-print">
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.student.overview', ['id' => $student->id]) }}" target="_blank"
                                        class="act-btn view" title="View">
                                        <span class="material-icons-round">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.student.edit', ['id' => $student->id]) }}"
                                        class="act-btn edit" title="Edit">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </a>
                                    <button class="act-btn delete" title="Delete"
                                        wire:click="confirmDeleteRecord({{ $student->user?->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.2">groups</span>
                                No students found.
                                <a href="{{ route('admin.student.add') }}">Add one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }} of {{ $students->total() }}</small>
            {{ $students->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== IMPORT MODAL ===== --}}
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Import CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div style="border:2px dashed var(--border);border-radius:12px;padding:32px;text-align:center;cursor:pointer"
                        onclick="document.getElementById('csvFile').click()">
                        <span class="material-icons-round" style="font-size:2.5rem;color:var(--muted)">file_upload</span>
                        <p class="mt-2 mb-1" style="font-weight:600;font-size:.85rem">Click to browse or drag & drop</p>
                        <p style="font-size:.75rem;color:var(--muted)">CSV files only</p>
                    </div>
                    <input type="file" id="csvFile" accept=".csv" style="display:none"/>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="termsCheck">
                        <label class="form-check-label" for="termsCheck" style="font-size:.8rem">
                            I accept the terms and conditions
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0 gap-2">
                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button class="btn bg-dark text-white">
                        <span class="material-icons-round" style="font-size:16px">upload</span> Upload
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== DELETE CONFIRM ===== --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <span class="material-icons-round text-danger" style="font-size:1.5rem;">warning</span>
                        </div>
                        <h6 class="fw-700">Delete Student?</h6>
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
    :root { --primary: rgba(33,37,41); --primary-light: rgba(239,84,84,.12); }
    .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
    .form-select { border-radius: 8px; border: 1px solid var(--border); font-size: .875rem; }
    .table th { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); }
    .table td { vertical-align: middle; font-size: .875rem; }

    @@media print {
        .no-print, .card-header, .card-footer { display: none !important; }
        .card { box-shadow: none; border: none; }
    }
</style>
@endpush

@push('scripts')
<script>
    function exportStudentCSV() {
        const table = document.getElementById('studentTable');
        if (!table) return;
        let csv = [];
        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            const cols = row.querySelectorAll('th:not(.no-print), td:not(.no-print)');
            const rowData = Array.from(cols).map(col => `"${col.innerText.trim()}"`);
            csv.push(rowData.join(','));
        });
        const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'students.csv';
        a.click();
    }

    function printTable() {
        const table = document.getElementById('studentTable');
        if (!table) return;

        const clone = table.cloneNode(true);
        clone.querySelectorAll('.no-print').forEach(el => el.remove());

        const win = window.open('', '', 'width=900,height=700');
        win.document.write(`
            <html><head><title>Student List</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
            <style>
                body { padding: 20px; font-size: 13px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #dee2e6; padding: 8px 10px; font-size: 12px; }
                th { background: #f8f9fa; font-weight: 600; }
            </style>
            </head><body>${clone.outerHTML}</body></html>
        `);
        win.document.close();
        win.focus();
        win.print();
        win.close();
    }
</script>
@endpush