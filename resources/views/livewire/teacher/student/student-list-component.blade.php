<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllStudents">All Students</h5>
            <p id="cardHeaderSubtitle">Manage students, filter by class and section.</p>
        </div>

        {{-- ===== FILTER ===== --}}
        <div class="form-section" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">school</span> Select Ground
            </div>
            <div class="row g-4">

                {{-- Class --}}
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Class</label>
                        <select wire:model.live="filterClass" class="form-select">
                            <option value="">Select Class</option>
                            @foreach ($classes as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('filterClass') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Section — class-e section thakle select, na thakle N/A --}}
                <div class="col-md-6">
                    @if($filterClassHasSection)
                        <div class="input-group input-group-outline">
                            <label class="form-label">Section</label>
                            <select wire:model.live="filterSection" class="form-select"
                                {{ empty($sections) ? 'disabled' : '' }}>
                                <option value="">{{ !$filterClass ? 'Select Class First' : 'Select Section' }}</option>
                                @if(!empty($sections))
                                    @if(count($sections) > 1)
                                        <option value="all">All Section</option>
                                    @endif
                                    @foreach ($sections as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @error('filterSection') <span class="text-danger small">{{ $message }}</span> @enderror
                    @else
                        <div class="input-group input-group-outline">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" value="N/A — this class has no sections" disabled>
                        </div>
                    @endif
                </div>

                {{-- Filter Button --}}
                <div class="col-md-12 text-center">
                    <button wire:click="filter"
                            wire:loading.attr="disabled"
                            wire:target="filter"
                            class="btn-pink w-100 d-flex justify-content-center align-items-center"
                            type="button">
                        <span wire:loading.remove wire:target="filter">
                            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">filter_alt</span> Filter
                        </span>
                        <span wire:loading wire:target="filter">
                            <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Filtering...
                        </span>
                    </button>
                </div>

            </div>
        </div>

        @if($hasFilter)
            {{-- ===== TOOLBAR ===== --}}
            <div class="card-header border-0">
                <div class="card-toolbar">
                    <div class="card-toolbar-title">
                        <div style="position:relative;display:inline-flex;align-items:center">
                            <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, reg no..." style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                        </div>
                    </div>

                    @if($students->total() > 10)
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" wire:model.live="perPage">
                                <option value="10">10 / page</option>
                                <option value="25">25 / page</option>
                                <option value="50">50 / page</option>
                            </select>
                        </div>
                    @endif

                    {{-- Print --}}
                    <button class="btn-outline" onclick="printTable()">
                        <span class="material-icons-round" style="font-size:16px">print</span> Print
                    </button>

                    {{-- Reset --}}
                    <button class="btn-outline" type="button" wire:click="resetForm">
                        <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
                    </button>
                </div>
            </div>

            {{-- ===== TABLE ===== --}}
            <div class="card-body pt-0" id="printArea">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="studentTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Gender</th>
                                <th>Register No</th>
                                <th>Roll No</th>
                                <th>Guardian</th>
                                <th class="no-print">Actions</th>
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
                                        <a href="{{ route('teacher.student.overview', ['id' => $student->id]) }}" target="_blank"
                                            class="act-btn view" title="View">
                                            <span class="material-icons-round">visibility</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No students found.
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
        @endif

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

</div>

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

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el }) => {
            setTimeout(() => {
                el.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    if (!select.nextElementSibling || !select.nextElementSibling.classList.contains('custom-select-wrapper')) {
                        if (typeof buildCustomSelect === 'function') buildCustomSelect(select);
                    }
                });

                el.querySelectorAll('.input-group-outline input').forEach(function(input) {
                    var group = input.closest('.input-group');
                    if (!group) return;
                    if (input.value && input.value.trim() !== '') {
                        group.classList.add('is-filled');
                    } else {
                        group.classList.remove('is-filled');
                    }
                    if (input._materialInit) return;
                    input._materialInit = true;
                    input.addEventListener('focus', function() { group.classList.add('is-focused'); });
                    input.addEventListener('blur', function() {
                        group.classList.remove('is-focused');
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                    input.addEventListener('input', function() {
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                });
            }, 0);
        });
    });
</script>
@endpush