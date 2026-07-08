<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password — {{ config('app.name', 'School') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f0fdfa 100%);
            padding: 1rem;
        }
        .force-change-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .force-change-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            padding: 2rem 2rem 1.5rem;
            text-align: center;
            color: #ffffff;
        }
        .force-change-header-icon {
            width: 64px;
            height: 64px;
            border-radius: 20px;
            background: rgba(255,255,255,0.25);
            border: 2px solid rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.75rem;
        }
        .force-change-header h1 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }
        .force-change-header p {
            font-size: 0.85rem;
            opacity: 0.9;
            line-height: 1.5;
        }
        .force-change-body {
            padding: 2rem;
        }
        .alert-warning {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            color: #92400e;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .alert-warning i { margin-top: 2px; }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.25s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16,185,129,0.10);
        }
        .password-hints {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            margin-top: 1rem;
            font-size: 0.78rem;
            color: #6b7280;
        }
        .password-hints-title {
            font-weight: 700;
            color: #374151;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .password-hints ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .password-hints li {
            padding: 2px 0;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        .password-hints li i { color: #10b981; font-size: 0.7rem; }
        .btn-change {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
            box-shadow: 0 4px 14px rgba(16,185,129,0.30);
        }
        .btn-change:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(16,185,129,0.40);
        }
        .btn-change:active { transform: translateY(0); }
        .error-message {
            color: #dc2626;
            font-size: 0.82rem;
            margin-top: 0.35rem;
        }
        .logout-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.82rem;
        }
        .logout-link a {
            color: #6b7280;
            text-decoration: none;
            transition: color 0.2s;
        }
        .logout-link a:hover { color: #dc2626; }
    </style>
</head>
<body>
    <div class="force-change-card">
        <div class="force-change-header">
            <div class="force-change-header-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1>Change Your Password</h1>
            <p>You're using the default password. Please set a new password to secure your account before continuing.</p>
        </div>
        <div class="force-change-body">
            @if(session('warning'))
            <div class="alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ session('warning') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="alert-warning" style="background:#fef2f2;border-color:#fecaca;color:#dc2626;">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('password.force-change.submit') }}">
                @csrf
                <input type="hidden" name="current_password_hint" value="123456">

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock me-1"></i>New Password</label>
                    <input type="password" id="password" name="password" required
                        placeholder="Enter your new password"
                        autocomplete="new-password"
                        minlength="8"
                        oninput="checkPasswordStrength(this.value)">
                </div>

                <div class="form-group">
                    <label for="password_confirmation"><i class="fas fa-lock me-1"></i>Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        placeholder="Re-enter your new password"
                        autocomplete="new-password"
                        minlength="8">
                </div>

                <div class="password-hints">
                    <div class="password-hints-title">
                        <i class="fas fa-info-circle"></i> Password requirements:
                    </div>
                    <ul>
                        <li id="len"><i class="fas fa-circle"></i> At least 8 characters</li>
                        <li id="upper"><i class="fas fa-circle"></i> At least one uppercase letter</li>
                        <li id="lower"><i class="fas fa-circle"></i> At least one lowercase letter</li>
                        <li id="number"><i class="fas fa-circle"></i> At least one number</li>
                        <li id="special"><i class="fas fa-circle"></i> At least one special character (recommended)</li>
                    </ul>
                </div>

                <button type="submit" class="btn-change">
                    <i class="fas fa-check-circle me-1"></i>Change Password & Continue
                </button>
            </form>

            <div class="logout-link">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout instead
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </div>
        </div>
    </div>

    <script>
    function checkPasswordStrength(pwd) {
        var checks = {
            len: pwd.length >= 8,
            upper: /[A-Z]/.test(pwd),
            lower: /[a-z]/.test(pwd),
            number: /[0-9]/.test(pwd),
            special: /[^A-Za-z0-9]/.test(pwd),
        };
        Object.keys(checks).forEach(function(key) {
            var el = document.getElementById(key);
            if (el) {
                var icon = el.querySelector('i');
                if (checks[key]) {
                    icon.className = 'fas fa-check-circle';
                    icon.style.color = '#10b981';
                    el.style.color = '#065f46';
                } else {
                    icon.className = 'fas fa-circle';
                    icon.style.color = '#d1d5db';
                    el.style.color = '#6b7280';
                }
            }
        });
    }
    </script>
</body>
</html>
