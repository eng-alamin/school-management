<div class="card">
    <h3>📦 Queue Monitor</h3>

    <p>Pending Jobs: <strong>{{ $queueSize }}</strong></p>

    <button wire:click="refreshQueue">Refresh</button>
</div>