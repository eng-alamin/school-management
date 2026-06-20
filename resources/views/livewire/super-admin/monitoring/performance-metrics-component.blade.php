<div class="card perf-metrics-card">

    <div class="text-center p-5">
        <h5>
            <span class="material-icons-round" style="font-size:28px;vertical-align:middle;margin-right:6px">speed</span>
            Performance Metrics
        </h5>
    </div>

    <div class="metrics-body">

        @php
            $memoryPercent = (int) filter_var($memory, FILTER_SANITIZE_NUMBER_INT);
            $cpuPercent    = (int) filter_var($cpu, FILTER_SANITIZE_NUMBER_INT);

            $memoryLevel = $memoryPercent >= 80 ? 'level-high' : ($memoryPercent >= 50 ? 'level-mid' : 'level-low');
            $cpuLevel    = $cpuPercent    >= 80 ? 'level-high' : ($cpuPercent    >= 50 ? 'level-mid' : 'level-low');
        @endphp

        <div class="metric-item">
            <div class="metric-head">
                <span class="material-icons-round metric-icon">memory</span>
                <span class="metric-label">Memory Usage</span>
                <span class="metric-value">{{ $memory }}</span>
            </div>
            <div class="metric-bar-track">
                <div class="metric-bar-fill {{ $memoryLevel }}" style="width: {{ min($memoryPercent, 100) }}%"></div>
            </div>
        </div>

        <div class="metric-item">
            <div class="metric-head">
                <span class="material-icons-round metric-icon">developer_board</span>
                <span class="metric-label">CPU Load</span>
                <span class="metric-value">{{ $cpu }}</span>
            </div>
            <div class="metric-bar-track">
                <div class="metric-bar-fill {{ $cpuLevel }}" style="width: {{ min($cpuPercent, 100) }}%"></div>
            </div>
        </div>

    </div>

</div>

@push('styles')
<style>
    .perf-metrics-card {
        border: 1px solid var(--border, #eee);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
        margin-bottom: 24px;
    }

    .metrics-body {
        display: flex;
        flex-direction: column;
        gap: 22px;
        padding: 28px 24px;
    }

    .metric-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .metric-head {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .metric-icon {
        font-size: 20px;
        color: #ef5454;
    }

    .metric-label {
        font-size: .85rem;
        font-weight: 600;
        color: #555;
        flex: 1;
    }

    .metric-value {
        font-size: .9rem;
        font-weight: 700;
        color: #2c2c2c;
    }

    .metric-bar-track {
        width: 100%;
        height: 10px;
        background: #f1f1f1;
        border-radius: 6px;
        overflow: hidden;
    }

    .metric-bar-fill {
        height: 100%;
        border-radius: 6px;
        transition: width .4s ease;
    }

    .metric-bar-fill.level-low  { background: linear-gradient(90deg, #2ecc71, #27ae60); }
    .metric-bar-fill.level-mid  { background: linear-gradient(90deg, #f9c74f, #f3a712); }
    .metric-bar-fill.level-high { background: linear-gradient(90deg, #ff6b6b, #e74c3c); }
</style>
@endpush