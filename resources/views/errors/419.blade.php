<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired - Redirecting...</title>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            font-family: Segoe UI, Tahoma, sans-serif;
            margin: 0;
        }
        .message-box {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .3);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .message-box .icon {
            font-size: 48px;
            color: #f39c12;
            margin-bottom: 15px;
        }
        .message-box h2 {
            color: #2c3e50;
            margin: 0 0 10px;
        }
        .message-box p {
            color: #666;
            margin: 0 0 20px;
            font-size: 14px;
        }
        .message-box a {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: opacity .3s;
        }
        .message-box a:hover {
            opacity: .9;
        }
        .countdown {
            color: #999;
            font-size: 12px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <div class="icon">&#9200;</div>
        <h2>Session Expired</h2>
        <p>Your session has expired due to inactivity. You will be redirected to the login page automatically.</p>
        <a href="{{ route('login') }}">Go to Login Now</a>
        <div class="countdown">Redirecting in <span id="timer">3</span> seconds...</div>
    </div>
    <script>
        // Auto-redirect to login page after 3 seconds
        var seconds = 3;
        var timerEl = document.getElementById('timer');
        var loginUrl = '{{ route("login") }}';

        // Store the current page URL so we can redirect back after login
        var currentPath = window.location.pathname + window.location.search;
        if (currentPath && currentPath !== '/login') {
            loginUrl += '?redirect=' + encodeURIComponent(currentPath);
        }

        var countdown = setInterval(function() {
            seconds--;
            if (timerEl) timerEl.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = loginUrl;
            }
        }, 1000);
    </script>
</body>
</html>
