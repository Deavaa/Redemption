<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        .container {
            text-align: center;
            padding: 60px 40px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }
        .error-code {
            font-size: 100px;
            font-weight: 800;
            color: #e74c3c;
            line-height: 1;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 12px;
        }
        .error-message {
            font-size: 15px;
            color: #7f8c8d;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            margin: 5px;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52,152,219,0.4);
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">&#128274;</div>
        <div class="error-code">403</div>
        <div class="error-title">Access Denied</div>
        <div class="error-message">
            {{ $exception->getMessage() ?: 'You do not have permission to access this section.' }}
        </div>
        @auth
            @if(auth()->user()->role === 'teacher')
            <div class="error-message" style="color: #e67e22; font-weight: 600; margin-top: -15px; margin-bottom: 25px;">
                Only home room teachers have this access.
            </div>
            @endif
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">&#127968; Back to Dashboard</a>
            <a href="javascript:history.back()" class="btn btn-secondary">&#8592; Go Back</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary">Go to Login</a>
        @endauth
    </div>
</body>
</html>
