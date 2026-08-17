<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="30;url={{ route('login') }}">
    <title>Payment Result</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; 
               align-items: center; height: 100vh; margin: 0; background: #f5f5f5; }
        .box { background: white; padding: 2rem 3rem; border-radius: 8px; 
               text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .success { color: #16a34a; }
        .error   { color: #dc2626; }
    </style>
</head>
<body>
    <div class="box">
        @if(session('success'))
            <h2 class="success">✅ {{ session('success') }}</h2>
        @endif
        @if(session('error'))
            <h2 class="error">❌ {{ session('error') }}</h2>
        @endif
        <p>৩০ সেকেন্ডের মধ্যে login page এ নিয়ে যাওয়া হবে...</p>
        <a href="{{ route('login') }}">এখনই যান →</a>
    </div>
</body>
</html>