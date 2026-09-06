{{-- resources/views/livewire/admin/question-paper/question-paper-print-log-component.blade.php --}}
<div class="qpl-scope">

    <div class="qpl-topbar">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="qpl-topbar-icon">
                    <span class="material-icons-round">receipt_long</span>
                </span>
                <div>
                    <h1 class="qpl-topbar-title">Print Log</h1>
                    <p class="qpl-topbar-sub">Every question paper print event, with its watermark code — search a code found on a leaked copy to trace who printed it.</p>
                </div>
            </div>
            <a href="{{ route('admin.question-papers.print.authorization') }}" class="btn btn-sm qpl-btn-outline">
                <span class="material-icons-round" style="font-size:16px;">verified_user</span>
                <span>Print Authorizations</span>
            </a>
        </div>
    </div>

    <div class="qpl-filters">
        <div class="qpl-filter-field">
            <span class="material-icons-round">search</span>
            <input
                type="text"
                wire:model.live.debounce.400ms="search"
                placeholder="Search watermark code (e.g. QP-7F3K9-2LX8)"
            >
        </div>

        <select wire:model.live="examFilter" class="qpl-filter-select">
            <option value="">All exams</option>
            @foreach ($examOptions as $exam)
                <option value="{{ $exam->id }}">
                    {{ $exam->name }}
                    @if($exam->classAssign)
                        — {{ $exam->classAssign->academicClass->name ?? '' }}
                        @if($exam->classAssign->academicSection) ({{ $exam->classAssign->academicSection->name }}) @endif
                    @endif
                </option>
            @endforeach
        </select>

        @if ($search !== '' || $examFilter !== '')
            <button type="button" wire:click="clearFilters" class="btn btn-sm qpl-btn-outline">
                Clear
            </button>
        @endif
    </div>

    <div class="qpl-table-wrap">
        <table class="qpl-table">
            <thead>
                <tr>
                    <th>Watermark Code</th>
                    <th>Exam</th>
                    <th>Subject</th>
                    <th>Printed By</th>
                    <th>Copy #</th>
                    <th>Printed At</th>
                    <th>IP Address</th>
                    <th>Device</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td><span class="qpl-code">{{ $log->watermark_code }}</span></td>
                        <td>{{ $log->exam->name ?? '—' }} </td>
                        <td>{{ $log->subject->name ?? '—' }}</td>
                        <td>{{ $log->printedBy->name ?? '—' }}</td>
                        <td><span class="qpl-copy-tag">{{ $log->copy_count }}</span></td>
                        <td>{{ $log->printed_at?->format('d M Y, h:i A') }}</td>
                        <td class="text-muted">{{ $log->ip_address ?: '—' }}</td>
                        <td class="text-muted qpl-device">{{ $log->device_fingerprint ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="qpl-empty">
                                <span class="material-icons-round">receipt_long</span>
                                <p>
                                    @if ($search !== '' || $examFilter !== '')
                                        No print log matches this search.
                                    @else
                                        No question paper has been printed yet.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($logs->hasPages())
        <div class="qpl-pagination">
            {{ $logs->links() }}
        </div>
    @endif

</div>

@push('styles')
<style>
    .qpl-scope { display: block; }

    .qpl-topbar {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-sm);
        padding: 16px 20px;
        margin-bottom: 20px;
    }
    .qpl-topbar-icon {
        width: 38px; height: 38px; border-radius: 10px;
        background: var(--primary-100); color: var(--primary);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .qpl-topbar-title { font-size: 1rem; font-weight: 700; color: var(--heading); margin: 0; }
    .qpl-topbar-sub { font-size: 0.78rem; color: var(--muted); margin: 2px 0 0; max-width: 560px; }

    .qpl-btn-outline {
        border: 1px solid var(--border-strong);
        background: transparent;
        color: var(--heading);
        border-radius: var(--radius-btn);
        font-weight: 600;
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px;
    }
    .qpl-btn-outline:hover { background: var(--hover-tint-soft); color: var(--heading); }

    .qpl-filters {
        display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
        margin-bottom: 16px;
    }
    .qpl-filter-field {
        display: flex; align-items: center; gap: 8px;
        background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-btn);
        padding: 7px 12px; flex: 1; min-width: 260px;
    }
    .qpl-filter-field .material-icons-round { font-size: 18px; color: var(--muted); }
    .qpl-filter-field input { border: none; outline: none; background: transparent; flex: 1; font-size: 0.86rem; color: var(--heading); }
    .qpl-filter-select {
        background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-btn);
        padding: 7px 12px; font-size: 0.86rem; color: var(--heading); min-width: 180px;
    }

    .qpl-table-wrap {
        background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-card);
        box-shadow: var(--shadow-sm); overflow-x: auto;
    }
    .qpl-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .qpl-table thead th {
        text-align: left; font-weight: 700; color: var(--muted); font-size: 0.72rem;
        text-transform: uppercase; letter-spacing: 0.03em;
        padding: 10px 16px; border-bottom: 1px solid var(--border); white-space: nowrap;
    }
    .qpl-table tbody td { padding: 12px 16px; border-bottom: 1px solid var(--border); color: var(--heading); white-space: nowrap; }
    .qpl-table tbody tr:last-child td { border-bottom: none; }

    .qpl-code {
        font-family: 'Courier New', monospace; font-weight: 700; font-size: 0.82rem;
        color: var(--primary); background: var(--primary-100); border-radius: 6px; padding: 2px 8px;
    }
    .qpl-copy-tag {
        display: inline-block; min-width: 22px; text-align: center;
        font-weight: 700; font-size: 0.76rem; color: var(--primary);
        background: var(--primary-100); border-radius: 20px; padding: 2px 8px;
    }
    .qpl-device { max-width: 200px; overflow: hidden; text-overflow: ellipsis; }

    .qpl-empty { text-align: center; color: var(--muted); padding: 40px 0; white-space: normal; }
    .qpl-empty .material-icons-round { font-size: 30px; opacity: 0.4; }
    .qpl-empty p { margin-top: 8px; font-size: 0.88rem; }

    .qpl-pagination { margin-top: 16px; }
</style>
@endpush