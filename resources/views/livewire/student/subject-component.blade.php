<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5>My Subjects</h5>
            <p>Subjects assigned to your class.</p>
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

                @if(isset($subjects) && $subjects instanceof \Illuminate\Pagination\LengthAwarePaginator && $subjects->total() > 10)
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
                            <th>Subject Name</th>
                            <th>Class Name</th>
                            <th>Class Teacher</th>
                            <th>Subject Code</th>
                            <th>Subject Type</th>
                            <th>Subject Author</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$assign)
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No class assigned to your account.
                            </td>
                        </tr>
                        @else
                            @forelse($subjects as $i => $subject)
                            <tr>
                                <td class="text-muted">{{ $subjects->firstItem() + $i }}</td>
                                <td>{{ $subject->name }}</td>
                                <td>{{ $assign->class?->name ?? '—' }} / {{ $assign->section?->name ?? '—' }}</td>
                                <td>{{ $teacher?->name ?? '—' }}</td>
                                <td>{{ $subject->code ?? '—' }}</td>
                                <td>{{ $subject->type ?? '—' }}</td>
                                <td>{{ $subject->author ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No subjects found.
                                </td>
                            </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        @if($assign && $subjects instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $subjects->firstItem() ?? 0 }}–{{ $subjects->lastItem() ?? 0 }} of {{ $subjects->total() }}</small>
            {{ $subjects->links('vendor.pagination.custom') }}
        </div>
        @endif

    </div>

</div>

@push('styles')
<style>
    .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
</style>
@endpush