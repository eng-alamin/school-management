<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleAllEmployees">All Teachers</h5>
            <p id="cardHeaderSubtitle">View teacher details and browse easily.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search"
                               style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                @if($teachers->total() > 10)
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
                            <th>SL</th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">
                                Name @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th>Designation</th>
                            <th>Department</th>
                            <th wire:click="sortBy('email')" style="cursor:pointer">
                                Email @if($sortField === 'email') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                            <th wire:click="sortBy('phone')" style="cursor:pointer">
                                Phone @if($sortField === 'phone') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $i => $teacher)
                        <tr>
                            <td class="text-muted">{{ $teachers->firstItem() + $i }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $teacher->photo ? asset($teacher->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($teacher->name) . '&size=64&background=random' }}"
                                         alt="{{ $teacher->name }}"
                                         style="width:32px;height:32px;border-radius:50%;object-fit:cover;"/>
                                    <span>{{ $teacher->name }}</span>
                                </div>
                            </td>
                            <td>{{ $teacher->designation?->name ?? '—' }}</td>
                            <td>{{ $teacher->department?->name ?? '—' }}</td>
                            <td>{{ $teacher->email ?? '—' }}</td>
                            <td>{{ $teacher->phone ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No teachers found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $teachers->firstItem() ?? 0 }}–{{ $teachers->lastItem() ?? 0 }} of {{ $teachers->total() }}</small>
            {{ $teachers->links('vendor.pagination.custom') }}
        </div>

    </div>

</div>

@push('styles')
<style>
    :root {
        --primary: rgba(33, 37, 41);
        --primary-light: rgba(239,84,84,.12);
    }

    .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }

    .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
</style>
@endpush