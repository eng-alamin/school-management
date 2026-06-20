<div class="card error-log-card">

    <div class="text-center p-5">
        <h5>
            <span class="material-icons-round" style="font-size:28px;vertical-align:middle;margin-right:6px">error</span>
            Error Logs
        </h5>
    </div>

    <div class="log-viewer">
        @forelse ($logs as $index => $log)
            @php
                $lower = strtolower($log);
                $level = str_contains($lower, 'error')
                    ? 'level-error'
                    : (str_contains($lower, 'warning')
                        ? 'level-warning'
                        : (str_contains($lower, 'info') ? 'level-info' : 'level-default'));
            @endphp
            <div class="log-line {{ $level }}">
                <span class="log-number">{{ $index + 1 }}</span>
                <span class="log-text">{{ $log }}</span>
            </div>
        @empty
            <div class="log-empty">
                <span class="material-icons-round" style="font-size:32px;display:block;margin-bottom:8px">check_circle</span>
                No logs found. Everything looks clean.
            </div>
        @endforelse
    </div>

</div>

@push('styles')
<style>
    .error-log-card {
        border: 1px solid var(--border, #eee);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
        margin-bottom: 24px;
    }

    .log-viewer {
        max-height: 400px;
        overflow-y: auto;
        background: #1e1e2e;
        font-family: 'Consolas', 'Courier New', monospace;
        font-size: .82rem;
        padding: 6px 0;
    }

    .log-line {
        display: flex;
        gap: 14px;
        padding: 6px 18px;
        border-bottom: 1px solid rgba(255,255,255,.05);
        color: #d4d4d4;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .log-line:hover {
        background: rgba(255,255,255,.04);
    }

    .log-number {
        color: #6c6c8a;
        min-width: 28px;
        text-align: right;
        user-select: none;
        flex-shrink: 0;
    }

    .log-text {
        flex: 1;
    }

    .log-line.level-error .log-text   { color: #ff6b6b; }
    .log-line.level-warning .log-text { color: #ffd166; }
    .log-line.level-info .log-text    { color: #4dd4ac; }

    .log-empty {
        text-align: center;
        color: #888;
        padding: 40px 20px;
        font-family: inherit;
    }

    /* scrollbar styling */
    .log-viewer::-webkit-scrollbar {
        width: 8px;
    }
    .log-viewer::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,.15);
        border-radius: 4px;
    }
    .log-viewer::-webkit-scrollbar-track {
        background: transparent;
    }
</style>
@endpush