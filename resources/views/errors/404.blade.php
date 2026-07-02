<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Not Found - 404</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Hind Siliguri', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        body::before {
            width: 500px;
            height: 500px;
            top: -150px;
            left: -150px;
        }

        body::after {
            width: 350px;
            height: 350px;
            bottom: -100px;
            right: -100px;
        }

        .error-page {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 56px 48px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
            position: relative;
            z-index: 1;
            animation: floatUp 0.6s ease-out;
        }

        @keyframes floatUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-wrap {
            width: 110px;
            height: 110px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);
            animation: floatIcon 2.6s ease-in-out infinite;
        }

        @keyframes floatIcon {
            0%, 100% { transform: translateY(0) rotate(-3deg); }
            50% { transform: translateY(-8px) rotate(3deg); }
        }

        .icon-wrap svg {
            width: 52px;
            height: 52px;
            stroke: #fff;
        }

        .code {
            font-size: 88px;
            font-weight: 700;
            line-height: 1;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            letter-spacing: -2px;
        }

        .title {
            font-size: 22px;
            font-weight: 600;
            color: #2d2d2d;
            margin-bottom: 12px;
        }

        .message {
            font-size: 16px;
            color: #6b6b6b;
            line-height: 1.7;
            margin-bottom: 32px;
        }

        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            padding: 14px 32px;
            border-radius: 50px;
            box-shadow: 0 10px 24px rgba(102, 126, 234, 0.35);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(102, 126, 234, 0.45);
        }

        .btn-home svg {
            width: 18px;
            height: 18px;
            stroke: #fff;
        }

        @media (max-width: 480px) {
            .error-page { padding: 40px 28px; }
            .code { font-size: 64px; }
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="icon-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                <path d="M9.5 10.5h4M11.5 8.5v4" opacity="0.7"/>
            </svg>
        </div>
        <div class="code">404</div>
        <div class="title">পেজটি খুঁজে পাওয়া যায়নি</div>
        <p class="message">দুঃখিত, আপনি যে পেজটি খুঁজছেন সেটি হয়তো সরিয়ে ফেলা হয়েছে অথবা এই নামে কোনো পেজ নেই।</p>
       <a href="javascript:void(0)" onclick="history.back()" class="btn-home">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"/>
            </svg>
            আগের পেজে ফিরে যান
        </a>
    </div>
</body>
</html>