<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.login') }} - {{ __('app.school_name') }}</title>

    {{-- PWA & Mobile Integration --}}
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <meta name="theme-color" content="#047857">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Redemption">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-TileColor" content="#047857">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/design-tokens.css') }}" rel="stylesheet">
    <link href="{{ asset('css/portal.css') }}" rel="stylesheet">
    <style>
        /* ===== Login Page — Emerald & Gold Glassmorphism ===== */

        /* ===== Keyframe Animations ===== */
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes floatShape1 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -40px) scale(1.05); }
            66% { transform: translate(-20px, 25px) scale(0.95); }
        }

        @keyframes floatShape2 {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-35px, 30px) scale(0.97); }
            66% { transform: translate(25px, -20px) scale(1.03); }
        }

        @keyframes floatShape3 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(15px, -35px) rotate(180deg); }
        }

        /* ===== Base ===== */
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0C1F17 0%, #047857 50%, #065F46 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            font-family: var(--font-family);
            position: relative;
            overflow: hidden;
        }

        /* Geometric dot pattern overlay */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
            z-index: 0;
        }

        /* Floating decorative shape 1 */
        body::after {
            content: '';
            position: absolute;
            top: -120px; right: -120px;
            width: 420px; height: 420px;
            border-radius: 50%;
            background: rgba(4, 120, 87, 0.12);
            animation: floatShape1 22s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
        }

        /* ===== Login Box — Glassmorphism ===== */
        .login-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.3), 0 0 50px rgba(4, 120, 87, 0.08);
            width: 400px;
            max-width: 90%;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s ease-out both;
        }

        .login-box::before {
            content: '';
            position: absolute;
            bottom: -140px; left: -140px;
            width: 360px; height: 360px;
            border-radius: 50%;
            background: rgba(6, 95, 70, 0.07);
            animation: floatShape2 28s ease-in-out infinite;
            pointer-events: none;
            z-index: -1;
        }

        .login-box::after {
            content: '';
            position: absolute;
            top: -60px; left: -80px;
            width: 180px; height: 180px;
            border-radius: 50%;
            background: rgba(12, 31, 23, 0.06);
            animation: floatShape3 18s ease-in-out infinite;
            pointer-events: none;
            z-index: -1;
        }

        .login-box h2 {
            text-align: center;
            color: var(--color-sidebar-bg);
            margin-bottom: 5px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .login-box p {
            text-align: center;
            color: #888;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .login-box .icon {
            text-align: center;
            font-size: 50px;
            color: var(--color-primary);
            margin-bottom: 15px;
        }

        /* ===== Form Inputs ===== */
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block; font-weight: 600;
            margin-bottom: 6px; color: #444; font-size: 14px;
        }
        .form-group input {
            width: 100%; padding: 14px 16px;
            border: 1.5px solid #dde1e7;
            border-radius: 10px; font-size: 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.7); color: #333;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px var(--color-primary-light);
            background: #fff;
        }
        .form-group input::placeholder { color: #aab; }

        /* ===== Login Button ===== */
        .btn-login {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
            color: #fff; border: none; border-radius: 10px;
            font-size: 16px; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease;
            letter-spacing: 0.3px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(4, 120, 87, 0.35);
        }
        .btn-login:active {
            transform: translateY(0) scale(0.98);
            box-shadow: 0 4px 16px rgba(4, 120, 87, 0.25);
        }

        /* ===== Alerts ===== */
        .alert {
            background: #f8d7da; color: #721c24;
            padding: 10px 15px; border-radius: 8px;
            margin-bottom: 15px; font-size: 14px;
        }
        .alert-success {
            background: #d1fae5; color: #065f46;
            padding: 10px 15px; border-radius: 8px;
            margin-bottom: 15px; font-size: 14px;
        }

        /* ===== Language Switcher ===== */
        .lang-switcher {
            position: absolute; top: 20px; right: 20px;
            display: flex; gap: 6px; z-index: 10;
        }
        .lang-switcher a {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 8px 14px; border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff; text-decoration: none;
            font-size: 13px; font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .lang-switcher a:hover {
            background: rgba(4, 120, 87, 0.25);
            border-color: rgba(4, 120, 87, 0.4);
        }
        .lang-switcher a.active {
            background: rgba(4, 120, 87, 0.3);
            border-color: rgba(4, 120, 87, 0.5);
            font-weight: 700;
        }
        .lang-switcher a i { font-size: 12px; }

        /* ===== Links ===== */
        .forgot-link {
            display: block; text-align: center;
            margin-top: 15px; color: var(--color-primary);
            text-decoration: none; font-size: 14px; font-weight: 500;
            transition: all 0.3s ease;
        }
        .forgot-link:hover {
            color: var(--color-primary-hover);
            text-decoration: underline;
        }
        .back-link {
            display: inline-flex; align-items: center; gap: 4px;
            color: #6c757d; text-decoration: none;
            font-size: 13px; margin-bottom: 15px;
            transition: color 0.3s ease;
        }
        .back-link:hover { color: var(--color-primary); }

        /* ===== Mobile App Button ===== */
        .login-box > a[style] {
            background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)) !important;
            border-radius: 10px !important;
            transition: all 0.3s ease !important;
        }
        .login-box > a[style]:hover {
            opacity: 0.9 !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(4, 120, 87, 0.3);
        }
        .login-box > a[style]:active { transform: translateY(0) scale(0.98); }
    </style>
</head>

<body>
    {{-- Language Switcher --}}
    <div class="lang-switcher">
        @foreach (config('app.available_locales') as $code => $name)
            <a href="{{ route('lang.switch', $code) }}" class="{{ app()->getLocale() === $code ? 'active' : '' }}">
                <i class="fas fa-globe"></i>
                {{ strtoupper($code) }}
            </a>
        @endforeach
    </div>

    <div class="login-box">
        <div class="icon"><i class="bi bi-mortarboard-fill"></i></div>
        <h2>{{ __('app.school_name') }}</h2>

        @if (session('status'))
            <div class="alert-success">{{ session('status') }}</div>
        @endif

        @if (session('reset_success'))
            <div class="alert-success">{{ session('reset_success') }}</div>
        @endif

        {{-- EMAIL RESET: Link sent confirmation --}}
        @if (session('reset_link_sent'))
            <p style="margin-bottom:15px;">Check Your Email</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to Login</a>
            <div class="alert-success" style="text-align:center;">
                <i class="bi bi-envelope-check" style="font-size:24px;color:#047857;display:block;margin-bottom:8px;"></i>
                We've sent a password reset link to <strong>{{ session('reset_email_sent') }}</strong>.<br>
                <small style="color:#666;">Check your inbox and spam folder. The link expires in {{ config('auth.passwords.users.expire', 60) }} minutes.</small>
            </div>

        {{-- EMAIL RESET: New password form (from email link) --}}
        @elseif(session('show_email_reset'))
            <p style="margin-bottom:15px;">Set New Password</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to Login</a>
            @if ($errors->any())
                <div class="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.reset.token') }}">
                @csrf
                <input type="hidden" name="token" value="{{ session('reset_token') }}">
                <input type="hidden" name="email" value="{{ session('reset_email') }}">
                <div class="form-group">
                    <label><i class="bi bi-person"></i> Account</label>
                    <input type="text" value="{{ session('reset_user_name') }}" disabled
                        style="background:#f9fafb;color:#6c757d;">
                </div>
                <div class="form-group">
                    <label><i class="bi bi-lock"></i> New Password</label>
                    <input type="password" name="password" required placeholder="Enter new password" minlength="4" autofocus>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-lock-fill"></i> Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Confirm new password"
                        minlength="4">
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-check-circle"></i> Reset Password</button>
            </form>

        {{-- SECURITY QUESTION FORM --}}
        @elseif(session('show_reset_form'))
            <p style="margin-bottom:15px;">Reset your password</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to Login</a>
            @if ($errors->any())
                <div class="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.reset.submit') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('reset_email') }}">
                <div class="form-group">
                    <label><i class="bi bi-person"></i> Account</label>
                    <input type="text" value="{{ session('reset_user_name') }}" disabled
                        style="background:#f9fafb;color:#6c757d;">
                </div>
                <div class="form-group">
                    <label><i class="bi bi-lock"></i> New Password</label>
                    <input type="password" name="password" required placeholder="Enter new password" minlength="4">
                </div>
                <div class="form-group">
                    <label><i class="bi bi-lock-fill"></i> Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Confirm new password"
                        minlength="4">
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-check-circle"></i> Reset Password</button>
            </form>

            {{-- SECURITY QUESTION FORM --}}
        @elseif(session('show_security'))
            <p style="margin-bottom:15px;">Verify your identity</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to Login</a>
            @if ($errors->any())
                <div class="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.verify.security') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('security_email') }}">
                <div class="form-group">
                    <label><i class="bi bi-shield-lock"></i> {{ session('security_question') }}</label>
                    <input type="text" name="security_answer" required placeholder="Your answer" autofocus>
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-shield-check"></i> Verify</button>
            </form>

            {{-- EMAIL-BASED FORGOT PASSWORD FORM --}}
        @elseif(session('show_email_forgot'))
            <p style="margin-bottom:15px;">Reset via Email</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to Login</a>
            @if ($errors->any())
                <div class="alert">{{ $errors->first() }}</div>
            @endif
            <div style="text-align:center;margin-bottom:16px;">
                <i class="bi bi-envelope" style="font-size:32px;color:var(--color-primary);"></i>
                <p style="font-size:13px;color:#666;margin-top:8px;">Enter your email address and we'll send you a password reset link.</p>
            </div>
            <form method="POST" action="{{ route('password.email.send') }}">
                @csrf
                <div class="form-group">
                    <label><i class="bi bi-envelope"></i> Email Address</label>
                    <input type="email" name="email" required autofocus placeholder="Enter your email address">
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-send"></i> Send Reset Link</button>
            </form>
            <div style="text-align:center;margin-top:12px;">
                <a href="{{ route('password.forgot') }}" style="color:var(--color-primary);font-size:13px;text-decoration:none;">
                    <i class="bi bi-shield-lock"></i> Reset using security question instead
                </a>
            </div>

            {{-- FORGOT PASSWORD - FIND ACCOUNT FORM (security question) --}}
        @elseif(session('show_forgot'))
            <p style="margin-bottom:15px;">Find your account</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left"></i> Back to Login</a>
            @if ($errors->any())
                <div class="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.forgot.submit') }}">
                @csrf
                <div class="form-group">
                    <label><i class="bi bi-person"></i> Email / ID Number</label>
                    <input type="text" name="login" required autofocus placeholder="Enter your email or ID number">
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-search"></i> Find Account</button>
            </form>
            <div style="text-align:center;margin-top:12px;">
                <a href="{{ route('password.email') }}" style="color:var(--color-primary);font-size:13px;text-decoration:none;">
                    <i class="bi bi-envelope"></i> Reset via email instead
                </a>
            </div>

            {{-- DEFAULT LOGIN FORM --}}
        @else
            <p>{{ __('app.sign_in') }}</p>
            @if (session('error'))
                <div class="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert">{{ $errors->first('login') ?: ($errors->first('email') ?: $errors->first()) }}
                </div>
            @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                @if (request('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                @endif
                <div class="form-group">
                    <label><i class="bi bi-person"></i> {{ __('app.email_id_phone') }}</label>
                    <input type="text" name="login" value="{{ old('login', $login ?? '') }}" required autofocus
                        placeholder="Student ID / Employee ID / Email / Phone (0900000000)">
                    <small style="color:#888;font-size:11px;display:block;margin-top:4px;">Students: use your Student ID
                        (e.g. STD-2025-00001) with default password <strong>123456</strong></small>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-lock"></i> {{ __('app.password') }}</label>
                    <input type="password" name="password" required placeholder="{{ __('app.enter_password') }}">
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right"></i>
                    {{ __('app.login') }}</button>
            </form>
            <a href="{{ route('password.email') }}" class="forgot-link"><i class="bi bi-key"></i> Forgot
                Password?</a>
        @endif
        <a href="{{ route('app.download') }}" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:16px;padding:10px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;text-decoration:none;font-size:13px;font-weight:600;transition:opacity .2s;">
            <i class="bi bi-phone"></i> Download Mobile App
        </a>
    </div>

    {{-- PWA Service Worker Registration --}}
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('{{ asset('sw.js') }}', { scope: '/' })
                .then(function(reg) { console.log('[PWA] SW registered:', reg.scope); })
                .catch(function(err) { console.log('[PWA] SW registration failed:', err); });
        });
    }
    </script>
</body>

</html>
