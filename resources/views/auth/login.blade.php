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
    {{-- Auth page styles extracted to a dedicated stylesheet for maintainability --}}
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
</head>

<body>
    {{-- Skip link for keyboard / screen-reader users --}}
    <a href="#login-form" class="sr-only" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;" onfocus="this.style.cssText='position:absolute;left:8px;top:8px;width:auto;height:auto;padding:8px 16px;background:#047857;color:#fff;border-radius:6px;z-index:100;'">{{ __('app.skip_to_login') }}</a>

    {{-- Language Switcher --}}
    <nav class="lang-switcher" aria-label="{{ __('app.language') }}">
        @foreach (config('app.available_locales') as $code => $name)
            <a href="{{ route('lang.switch', $code) }}"
               class="{{ app()->getLocale() === $code ? 'active' : '' }}"
               aria-current="{{ app()->getLocale() === $code ? 'true' : 'false' }}"
               lang="{{ $code }}">
                <i class="fas fa-globe" aria-hidden="true"></i>
                {{ strtoupper($code) }}
            </a>
        @endforeach
    </nav>

    <main class="login-box">
        <div class="icon" aria-hidden="true"><i class="bi bi-mortarboard-fill"></i></div>
        <h2>{{ __('app.school_name') }}</h2>

        @if (session('status'))
            <div class="alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if (session('reset_success'))
            <div class="alert-success" role="status">{{ session('reset_success') }}</div>
        @endif

        {{-- EMAIL RESET: Link sent confirmation --}}
        @if (session('reset_link_sent'))
            <p style="margin-bottom:15px;">{{ __('app.check_your_email') }}</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ __('app.back_to_login') }}</a>
            <div class="alert-success" style="text-align:center;">
                <i class="bi bi-envelope-check" style="font-size:24px;color:#047857;display:block;margin-bottom:8px;" aria-hidden="true"></i>
                {!! __('app.reset_link_sent_text', ['email' => '<strong>' . e(session('reset_email_sent')) . '</strong>']) !!}<br>
                <small style="color:#666;">{{ __('app.reset_link_expiry', ['minutes' => config('auth.passwords.users.expire', 60)]) }}</small>
            </div>

        {{-- EMAIL RESET: New password form (from email link) --}}
        @elseif(session('show_email_reset'))
            <p style="margin-bottom:15px;">{{ __('app.set_new_password') }}</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ __('app.back_to_login') }}</a>
            @if (isset($errors) && is_object($errors) && method_exists($errors, "any") && $errors->any())
                <div class="alert" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.reset.token') }}">
                @csrf
                <input type="hidden" name="token" value="{{ session('reset_token') }}">
                <input type="hidden" name="email" value="{{ session('reset_email') }}">
                <div class="form-group">
                    <label for="reset-account"><i class="bi bi-person" aria-hidden="true"></i> {{ __('app.account') }}</label>
                    <input type="text" id="reset-account" value="{{ session('reset_user_name') }}" disabled
                        style="background:#f9fafb;color:#6c757d;" autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="reset-password"><i class="bi bi-lock" aria-hidden="true"></i> {{ __('app.new_password') }}</label>
                    <input type="password" id="reset-password" name="password" required
                        placeholder="{{ __('app.enter_new_password') }}"
                        minlength="8" autocomplete="new-password" autofocus
                        aria-describedby="reset-password-help">
                    <small id="reset-password-help" class="helper-text">{{ __('app.password_requirements') }}</small>
                </div>
                <div class="form-group">
                    <label for="reset-password-confirm"><i class="bi bi-lock-fill" aria-hidden="true"></i> {{ __('app.confirm_password') }}</label>
                    <input type="password" id="reset-password-confirm" name="password_confirmation" required
                        placeholder="{{ __('app.confirm_new_password') }}"
                        minlength="8" autocomplete="new-password">
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-check-circle" aria-hidden="true"></i> {{ __('app.reset_password') }}</button>
            </form>

        {{-- SECURITY QUESTION RESET FORM --}}
        @elseif(session('show_reset_form'))
            <p style="margin-bottom:15px;">{{ __('app.reset_your_password') }}</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ __('app.back_to_login') }}</a>
            @if (isset($errors) && is_object($errors) && method_exists($errors, "any") && $errors->any())
                <div class="alert" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.reset.submit') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('reset_email') }}">
                <div class="form-group">
                    <label for="security-account"><i class="bi bi-person" aria-hidden="true"></i> {{ __('app.account') }}</label>
                    <input type="text" id="security-account" value="{{ session('reset_user_name') }}" disabled
                        style="background:#f9fafb;color:#6c757d;" autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="security-password"><i class="bi bi-lock" aria-hidden="true"></i> {{ __('app.new_password') }}</label>
                    <input type="password" id="security-password" name="password" required
                        placeholder="{{ __('app.enter_new_password') }}"
                        minlength="8" autocomplete="new-password"
                        aria-describedby="security-password-help">
                    <small id="security-password-help" class="helper-text">{{ __('app.password_requirements') }}</small>
                </div>
                <div class="form-group">
                    <label for="security-password-confirm"><i class="bi bi-lock-fill" aria-hidden="true"></i> {{ __('app.confirm_password') }}</label>
                    <input type="password" id="security-password-confirm" name="password_confirmation" required
                        placeholder="{{ __('app.confirm_new_password') }}"
                        minlength="8" autocomplete="new-password">
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-check-circle" aria-hidden="true"></i> {{ __('app.reset_password') }}</button>
            </form>

        {{-- SECURITY QUESTION FORM --}}
        @elseif(session('show_security'))
            <p style="margin-bottom:15px;">{{ __('app.verify_your_identity') }}</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ __('app.back_to_login') }}</a>
            @if (isset($errors) && is_object($errors) && method_exists($errors, "any") && $errors->any())
                <div class="alert" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.verify.security') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('security_email') }}">
                <div class="form-group">
                    <label for="security-answer-input"><i class="bi bi-shield-lock" aria-hidden="true"></i> {{ session('security_question') }}</label>
                    <input type="text" id="security-answer-input" name="security_answer" required
                        placeholder="{{ __('app.your_answer') }}" autocomplete="off" autofocus>
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-shield-check" aria-hidden="true"></i> {{ __('app.verify') }}</button>
            </form>

        {{-- EMAIL-BASED FORGOT PASSWORD FORM --}}
        @elseif(session('show_email_forgot'))
            <p style="margin-bottom:15px;">{{ __('app.reset_via_email') }}</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ __('app.back_to_login') }}</a>
            @if (isset($errors) && is_object($errors) && method_exists($errors, "any") && $errors->any())
                <div class="alert" role="alert">{{ $errors->first() }}</div>
            @endif
            <div style="text-align:center;margin-bottom:16px;">
                <i class="bi bi-envelope" style="font-size:32px;color:var(--color-primary);" aria-hidden="true"></i>
                <p style="font-size:13px;color:#666;margin-top:8px;">{{ __('app.reset_email_instructions') }}</p>
            </div>
            <form method="POST" action="{{ route('password.email.send') }}">
                @csrf
                <div class="form-group">
                    <label for="email-forgot-input"><i class="bi bi-envelope" aria-hidden="true"></i> {{ __('app.email_address') }}</label>
                    <input type="email" id="email-forgot-input" name="email" required autofocus
                        placeholder="{{ __('app.enter_your_email') }}" autocomplete="email">
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-send" aria-hidden="true"></i> {{ __('app.send_reset_link') }}</button>
            </form>
            <div style="text-align:center;margin-top:12px;">
                <a href="{{ route('password.forgot') }}" style="color:var(--color-primary);font-size:13px;text-decoration:none;">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i> {{ __('app.reset_via_security_question') }}
                </a>
            </div>

        {{-- FORGOT PASSWORD - FIND ACCOUNT FORM (security question) --}}
        @elseif(session('show_forgot'))
            <p style="margin-bottom:15px;">{{ __('app.find_your_account') }}</p>
            <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ __('app.back_to_login') }}</a>
            @if (isset($errors) && is_object($errors) && method_exists($errors, "any") && $errors->any())
                <div class="alert" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('password.forgot.submit') }}">
                @csrf
                <div class="form-group">
                    <label for="find-account-input"><i class="bi bi-person" aria-hidden="true"></i> {{ __('app.email_or_id_number') }}</label>
                    <input type="text" id="find-account-input" name="login" required autofocus
                        placeholder="{{ __('app.enter_email_or_id') }}" autocomplete="username">
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-search" aria-hidden="true"></i> {{ __('app.find_account') }}</button>
            </form>
            <div style="text-align:center;margin-top:12px;">
                <a href="{{ route('password.email') }}" style="color:var(--color-primary);font-size:13px;text-decoration:none;">
                    <i class="bi bi-envelope" aria-hidden="true"></i> {{ __('app.reset_via_email_instead') }}
                </a>
            </div>

        {{-- DEFAULT LOGIN FORM --}}
        @else
            <p>{{ __('app.sign_in') }}</p>
            @if (session('error'))
                <div class="alert" role="alert">{{ session('error') }}</div>
            @endif
            @if (isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any())
                <div class="alert" role="alert">{{ $errors->first('login') ?: ($errors->first('email') ?: $errors->first()) }}</div>
            @endif
            <form id="login-form" method="POST" action="{{ route('login') }}">
                @csrf
                @if (request('redirect'))
                    <input type="hidden" name="redirect" value="{{ request('redirect') }}">
                @endif
                <div class="form-group">
                    <label for="login-input"><i class="bi bi-person" aria-hidden="true"></i> {{ __('app.email_id_phone') }}</label>
                    <input type="text" id="login-input" name="login"
                        value="{{ old('login', $login ?? '') }}" required autofocus
                        placeholder="{{ __('app.login_placeholder') }}"
                        autocomplete="username" autocapitalize="none">
                    <small class="helper-text">{{ __('app.login_helper') }}</small>
                </div>
                <div class="form-group">
                    <label for="password-input"><i class="bi bi-lock" aria-hidden="true"></i> {{ __('app.password') }}</label>
                    <input type="password" id="password-input" name="password" required
                        placeholder="{{ __('app.enter_password') }}"
                        autocomplete="current-password">
                </div>
                <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                    {{ __('app.login') }}</button>
            </form>
            <a href="{{ route('password.email') }}" class="forgot-link"><i class="bi bi-key" aria-hidden="true"></i> {{ __('app.forgot_password') }}</a>
        @endif
        <a href="{{ route('app.download') }}" class="app-download-link">
            <i class="bi bi-phone" aria-hidden="true"></i> {{ __('app.download_mobile_app') }}
        </a>
    </main>

    {{-- PWA Service Worker Registration --}}
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register(@json(asset('sw.js')), { scope: '/' })
                .then(function(reg) { console.log('[PWA] SW registered:', reg.scope); })
                .catch(function(err) { console.log('[PWA] SW registration failed:', err); });
        });
    }
    </script>

    {{-- LOOP BUG DEFENSE: If the page was somehow served from bfcache / SW
         cache / browser HTTP cache with a STALE CSRF token, the next form
         submit would 419 and trigger the "session expired" redirect loop.
         We detect a stale token by comparing the form's _token (rendered
         server-side when the page was generated) against the meta tag
         (also rendered server-side at the same time). If they ever differ,
         we force a reload to fetch a fresh page with a fresh token. --}}
    <script>
    (function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        var form = document.getElementById('login-form');
        if (!meta || !form) return;
        var formTokenInput = form.querySelector('input[name="_token"]');
        if (!formTokenInput) return;

        // On submit, double-check the token is still valid by comparing it
        // to the meta tag. If they're identical, the page is internally
        // consistent and the submit should succeed.
        form.addEventListener('submit', function (e) {
            var metaToken = meta.getAttribute('content');
            var formToken = formTokenInput.value;
            if (!metaToken || !formToken || metaToken !== formToken) {
                e.preventDefault();
                console.warn('[Login] CSRF token mismatch detected — reloading page for fresh token.');
                // Reload the page to get a fresh form + token. Don't lose the
                // redirect query param if present.
                window.location.reload();
            }
        }, true);  // capture phase, so we run before any other submit handlers

        // If the user navigates back to this page via bfcache, the form's
        // _token will be the old one. Force a reload to refresh it.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) {
                // Page was restored from bfcache — reload to get a fresh token.
                window.location.reload();
            }
        });
    })();
    </script>
</body>

</html>
