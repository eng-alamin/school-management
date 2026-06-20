<div>

    <div class="card">

        <div class="card-header-floating card-header-gradient">
            <h5 id="cardHeaderTitleAllStudents">All Parents</h5>
            <p id="cardHeaderSubtitle">Manage parents, search by name, email or mobile.</p>
        </div>

        {{-- ===== TOOLBAR ===== --}}
        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, email, mobile..." style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                @if($parents->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                {{-- Export CSV --}}
                <button class="btn-outline" onclick="exportParentCSV()">
                    <span class="material-icons-round" style="font-size:16px">download</span> Export CSV
                </button>

                {{-- Print --}}
                <button class="btn-outline" onclick="printTable()">
                    <span class="material-icons-round" style="font-size:16px">print</span> Print
                </button>

                <a href="{{ route('admin.parent.add') }}" class="btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> New Parent
                </a>
            </div>
        </div>

        {{-- ===== TABLE ===== --}}
        <div class="card-body pt-0" id="printArea">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="parentTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Occupation</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Students</th>
                            <th class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parents as $parent)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $parent->photo ? asset('storage/' . $parent->photo) : asset('assets/img/parent.jpg') }}"
                                        style="width:36px;height:36px;border-radius:8px;object-fit:cover;" alt="">
                                    <span class="fw-500">{{ $parent->name }}</span>
                                </div>
                            </td>
                            <td>{{ $parent->occupation ?? '—' }}</td>
                            <td>{{ $parent->email ?? '—' }}</td>
                            <td>{{ $parent->mobile ?? '—' }}</td>
                            <td>
                                @if($parent->students->count())
                                    <span class="badge bg-light text-dark border" style="font-size:.72rem">
                                        {{ $parent->students->count() }} student(s)
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="no-print">
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.parent.overview', ['id' => $parent->id]) }}" target="_blank"
                                        class="act-btn view" title="View">
                                        <span class="material-icons-round">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.parent.edit', ['id' => $parent->id]) }}" target="_blank"
                                        class="act-btn edit" title="Edit">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </a>
                                    <button class="act-btn delete" title="Delete"
                                        wire:click="confirmDeleteRecord({{ $parent->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No parents found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $parents->firstItem() ?? 0 }}–{{ $parents->lastItem() ?? 0 }} of {{ $parents->total() }}</small>
            {{ $parents->links('vendor.pagination.custom') }}
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
                        <h6 class="fw-700">Delete Parent?</h6>
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
    .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
    .form-control, .form-select { border-radius: 8px; border: 1px solid var(--border); font-size: .875rem; padding: .45rem .75rem; }
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
    function exportParentCSV() {
        const table = document.getElementById('parentTable');
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
        a.download = 'parents.csv';
        a.click();
    }

    function printTable() {
        const table = document.getElementById('parentTable');
        if (!table) return;

        const clone = table.cloneNode(true);
        clone.querySelectorAll('.no-print').forEach(el => el.remove());

        const win = window.open('', '', 'width=900,height=700');
        win.document.write(`
            <html><head><title>Parent List</title>
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