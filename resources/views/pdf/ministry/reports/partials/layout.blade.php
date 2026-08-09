<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Ministry Report' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
        .header { text-align: center; border-bottom: 2px solid #2b6cb0; padding-bottom: 8px; margin-bottom: 16px; }
        .header h1 { font-size: 16px; margin: 0; color: #2b6cb0; }
        .meta { font-size: 10px; color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
        th { background: #f0f5fa; }
        .flagged { background: #fde8e8; color: #c53030; font-weight: bold; }
        .summary-cards { display: table; width: 100%; margin-bottom: 16px; }
        .summary-card { display: table-cell; border: 1px solid #ccc; padding: 8px; text-align: center; }
        .summary-card .value { font-size: 18px; font-weight: bold; color: #2b6cb0; }
        .footer { position: fixed; bottom: -20px; font-size: 9px; color: #999; text-align: center; width: 100%; }
        h3 { font-size: 13px; color: #2b6cb0; margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title ?? 'Ministry Report' }}</h1>
        <div class="meta">
            Generated: {{ $generatedAt->format('d M Y, h:i A') }}
            @if ($generatedBy) | By: {{ $generatedBy }} @endif
            @if ($division) | Division: {{ $division }} @endif
        </div>
    </div>

    @yield('content')

    <div class="footer">Ministry Panel — Confidential</div>
</body>
</html>