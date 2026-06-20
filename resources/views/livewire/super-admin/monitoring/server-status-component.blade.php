<div class="card server-status-card">

    <div class="text-center p-5">
        <h5>
            <span class="material-icons-round" style="font-size:28px;vertical-align:middle;margin-right:6px">monitor_heart</span>
            Server Status
        </h5>
    </div>

    <div class="status-grid">
        @foreach ($status as $key => $value)
            @php
                $isHealthy = ! str_contains(strtolower($value), 'error')
                          && ! str_contains(strtolower($value), 'fail')
                          && ! str_contains(strtolower($value), 'down');

                $icons = [
                    'app'      => 'apps',
                    'database' => 'storage',
                    'cache'    => 'memory',
                    'storage'  => 'sd_storage',
                ];
                $icon = $icons[$key] ?? 'check_circle';
            @endphp

            <div class="status-item {{ $isHealthy ? 'status-ok' : 'status-error' }}">
                <span class="material-icons-round status-icon">{{ $icon }}</span>
                <div class="status-info">
                    <span class="status-label">{{ ucfirst($key) }}</span>
                    <span class="status-badge">
                        <span class="status-dot"></span>
                        {{ $value }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

</div>

@push('styles')
<style>
    .server-status-card {
        border: 1px solid var(--border, #eee);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
        margin-bottom: 24px;
    }

    .status-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        padding: 28px 20px;
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 14px 20px;
        min-width: 190px;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .status-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(0,0,0,.07);
    }

    .status-icon {
        font-size: 26px;
        color: #fff;
        background: linear-gradient(135deg, #ff6b81, #ef5454);
        border-radius: 10px;
        padding: 8px;
    }

    .status-item.status-error .status-icon {
        background: linear-gradient(135deg, #ff4d4d, #c0392b);
    }

    .status-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .status-label {
        font-size: .78rem;
        font-weight: 600;
        color: #888;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .88rem;
        font-weight: 600;
        color: #2e7d32;
    }

    .status-item.status-error .status-badge {
        color: #c0392b;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #2ecc71;
        box-shadow: 0 0 0 3px rgba(46,204,113,.2);
    }

    .status-item.status-error .status-dot {
        background: #e74c3c;
        box-shadow: 0 0 0 3px rgba(231,76,60,.2);
    }
</style>
@endpush