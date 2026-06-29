<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            font-family: Segoe UI, Tahoma, sans-serif;
            margin: 0;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #4361ee;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="spinner"></div>
    <script>
        // Silent redirect — no "session expired" message shown to user.
        // Store current URL so we can return after re-login.
        var loginUrl = '{{ route("login") }}';
        var currentPath = window.location.pathname + window.location.search;
        if (currentPath && currentPath !== '/login') {
            loginUrl += '?redirect=' + encodeURIComponent(currentPath);
        }
        window.location.href = loginUrl;
    </script>
</body>
</html>
