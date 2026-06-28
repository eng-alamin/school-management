<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">assignment</span>All Homeworks</h5>
            <p>Manage homework records, view details, and organize easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">

                {{-- Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Search by title"
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
                        <option value="">{{ !$filterClass ? 'All Sections' : 'All Sections' }}</option>
                        @if(!empty($availableSections))
                            <option value="all">All Section</option>
                            @foreach ($availableSections as $s)
                                <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{-- Per page --}}
                @if($homeworks->total() > 10)
                    <div>
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

                <a href="{{ route('admin.homework.add') }}" class="btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> New Homework
                </a>

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('title')" style="cursor:pointer">
                                Title @if($sortField === 'title') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Subject</th>
                            <th wire:click="sortBy('homework_date')" style="cursor:pointer">
                                Homework Date @if($sortField === 'homework_date') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('submission_date')" style="cursor:pointer">
                                Submission Date @if($sortField === 'submission_date') {!! $sortDir === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($homeworks as $i => $homework)
                        <tr>
                            <td class="text-muted">{{ $homeworks->firstItem() + $i }}</td>
                            <td>{{ $homework->title }}</td>
                            <td>{{ $homework->class?->name ?? '—' }}</td>
                            <td>{{ $homework->section?->name ?? 'All' }}</td>
                            <td>{{ $homework->subject?->name ?? '—' }}</td>
                            <td>{{ $homework->homework_date ? \Carbon\Carbon::parse($homework->homework_date)->format('d M Y') : '—' }}</td>
                            <td>{{ $homework->submission_date ? \Carbon\Carbon::parse($homework->submission_date)->format('d M Y') : '—' }}</td>
                            <td>
                                @php
                                    $badge = match($homework->status) {
                                        'published' => 'success',
                                        'draft'     => 'secondary',
                                        'closed'    => 'danger',
                                        default     => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($homework->status) }}</span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    @if ($homework['attachment'])
                                        <a href="{{ Storage::url($homework['attachment']) }}" target="_blank" class="act-btn"><span class="material-icons-round">attachment</span></a>
                                    @endif
                                    <a href="{{ route('admin.homework.edit', ['id' => $homework->id]) }}"
                                       class="act-btn edit" title="Edit">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </a>
                                    <button class="act-btn delete" title="Delete"
                                            wire:click="confirmDeleteRecord({{ $homework->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.2">assignment</span>
                                No homeworks found.
                                <a href="{{ route('admin.homework.add') }}">Add one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $homeworks->firstItem() ?? 0 }}–{{ $homeworks->lastItem() ?? 0 }} of {{ $homeworks->total() }}</small>
            {{ $homeworks->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- Delete Confirm Modal --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <span class="material-icons-round text-danger" style="font-size:1.5rem;">warning</span>
                        </div>
                        <h6 class="fw-700">Delete Homework?</h6>
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