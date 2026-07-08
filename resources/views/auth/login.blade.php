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

        {{-- DEFAULT LOGIN FORM --}}
        <p>{{ __('app.sign_in') }}</p>
        @if (session('error'))
            <div class="alert" role="alert">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert-success" role="status">{{ session('success') }}</div>
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
            </div>
            <div class="form-group">
                <label for="password-input"><i class="bi bi-lock" aria-hidden="true"></i> {{ __('app.password') }}</label>
                <input type="password" id="password-input" name="password" required
                    placeholder="{{ __('app.enter_password') }}"
                    autocomplete="current-password">
            </div>
            <div class="form-check" style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem;">
                <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input" style="width:18px;height:18px;cursor:pointer;accent-color:var(--primary-color,#10b981);">
                <label for="remember" style="font-size:0.85rem;color:#6b7280;cursor:pointer;margin:0;display:flex;align-items:center;gap:0.35rem;">
                    <i class="bi bi-shield-check" style="font-size:0.8rem;"></i> Keep me logged in
                </label>
            </div>
            <button type="submit" class="btn-login"><i class="bi bi-box-arrow-in-right" aria-hidden="true"></i>
                {{ __('app.login') }}</button>
        </form>
        {{-- Forgot password note — users must contact admin/branch principal --}}
        <div style="text-align:center;margin-top:1rem;padding:0.75rem;background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;font-size:0.78rem;color:#6b7280;">
            <i class="bi bi-info-circle" style="color:#10b981;margin-right:4px;"></i>
            Forgot your password? Please contact your <strong>Branch Principal</strong> or the <strong>Admin office</strong> to reset it.
        </div>
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
