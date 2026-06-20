<div class="card queue-monitor-card">

    <div class="text-center p-5">
        <h5>
            <span class="material-icons-round" style="font-size:28px;vertical-align:middle;margin-right:6px">inventory_2</span>
            Queue Monitor
        </h5>
    </div>

    <div class="queue-body">

        <div class="queue-stat">
            <span class="material-icons-round queue-icon">pending_actions</span>
            <div class="queue-info">
                <span class="queue-label">Pending Jobs</span>
                <span class="queue-count">{{ $queueSize }}</span>
            </div>
        </div>

        <button wire:click="refreshQueue"
                wire:loading.attr="disabled"
                wire:target="refreshQueue"
                class="btn-pink">

            <span wire:loading.remove wire:target="refreshQueue">
                <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">refresh</span>
                Refresh
            </span>

            <span wire:loading wire:target="refreshQueue">
                <span class="material-icons-round"
                      style="font-size:16px;animation:spin .7s linear infinite;vertical-align:middle;margin-right:4px">
                    sync
                </span>
                Refreshing...
            </span>
        </button>

    </div>

</div>

@push('styles')
<style>
    .queue-monitor-card {
        border: 1px solid var(--border, #eee);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
        margin-bottom: 24px;
    }

    .queue-body {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 20px;
        padding: 32px 20px;
        text-align: center;
    }

    .queue-stat {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .queue-icon {
        font-size: 28px;
        color: #fff;
        background: linear-gradient(135deg, #ff6b81, #ef5454);
        border-radius: 10px;
        padding: 10px;
    }

    .queue-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .queue-label {
        font-size: .78rem;
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .queue-count {
        font-size: 1.8rem;
        font-weight: 700;
        color: #2c2c2c;
        line-height: 1.2;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }
</style>
@endpush