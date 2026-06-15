<div class="card">
    <h3>📄 Error Logs</h3>

    <div style="max-height:400px; overflow:auto;">
        @foreach($logs as $log)
            <div style="border-bottom:1px solid #ddd; padding:5px;">
                {{ $log }}
            </div>
        @endforeach
    </div>
</div>