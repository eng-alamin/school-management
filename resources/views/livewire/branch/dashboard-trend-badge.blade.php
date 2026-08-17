{{-- resources/views/livewire/admin/partials/dash-trend-badge.blade.php --}}
@php
    $suffix = $suffix ?? '%';
@endphp

<span class="dash-stat-badge {{ $trend['direction'] === 'up' ? 'text-success' : 'text-danger' }}">
    <span class="material-icons-round" style="font-size:11px;vertical-align:middle;">
        {{ $trend['direction'] === 'up' ? 'arrow_upward' : 'arrow_downward' }}
    </span>{{ $trend['direction'] === 'up' ? '+' : '-' }}{{ $trend['percent'] }}{{ $suffix }}
</span>