<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    {{-- Global Session Keepalive v3 — Bulletproof session management --}}
    <script>
    (function() {
        var loginUrl = '{{ route("login") }}';
        var keepaliveUrl = '{{ route("admin.keepalive") }}';
        var sessionExpired = false;
        var lastKeepaliveTime = Date.now();
        var KEEPALIVE_INTERVAL = 60 * 1000;  // 60 seconds — balanced between reliability and resource usage
        var ACTIVITY_THRESHOLD = 30 * 1000;  // 30 seconds of inactivity before activity-driven keepalive

        // ===== 1. GLOBAL CSRF TOKEN HELPERS =====
        window.getGlobalCSRFToken = function() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        };

        function updateCSRFToken(newToken) {
            if (!newToken) return;
            var meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', newToken);
            if (typeof window.CSRF !== 'undefined') window.CSRF = newToken;
        }

        // ===== 2. KEEPALIVE — Using XMLHttpRequest (more reliable on XAMPP HTTPS) =====
        function fireKeepalive() {
            if (sessionExpired) return;

            try {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', keepaliveUrl, true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.timeout = 8000; // 8 second timeout (reduced from 15s to prevent accumulation)

                xhr.onload = function() {
                    if (sessionExpired) return;

                    // If redirected to login page, session is expired
                    if (xhr.responseURL && xhr.responseURL.indexOf('/login') !== -1) {
                        handleSessionExpired('keepalive redirected to login');
                        return;
                    }

                    if (xhr.status === 200) {
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.csrf_token) {
                                updateCSRFToken(data.csrf_token);
                            }
                            lastKeepaliveTime = Date.now();
                        } catch(e) {
                            // Response is HTML (login page) — session expired
                            if (xhr.responseText && xhr.responseText.indexOf('<html') !== -1) {
                                handleSessionExpired('keepalive returned HTML');
                                return;
                            }
                        }
                    } else if (xhr.status === 401 || xhr.status === 419) {
                        // Don't give up immediately on 419 — try to refresh CSRF and retry
                        console.warn('[Keepalive] Got 419, attempting CSRF refresh...');
                        refreshCSRFAndRetry();
                    } else {
                        console.warn('[Keepalive] Unexpected status:', xhr.status);
                    }
                };

                xhr.onerror = function() {
                    console.warn('[Keepalive] Network error (will retry)');
                };

                xhr.ontimeout = function() {
                    console.warn('[Keepalive] Timeout (will retry)');
                };

                xhr.send();
            } catch(e) {
                console.warn('[Keepalive] XHR error:', e.message);
            }
        }

        // ===== 3. CSRF REFRESH — Fetch a fresh CSRF token without full page reload =====
        function refreshCSRFAndRetry() {
            // Use a separate request to get a fresh token
            try {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', keepaliveUrl, true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.timeout = 10000;

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.csrf_token) {
                                updateCSRFToken(data.csrf_token);
                                lastKeepaliveTime = Date.now();
                                console.log('[Keepalive] CSRF token refreshed successfully');
                            }
                        } catch(e) {}
                    } else if (xhr.status === 419 || xhr.status === 401 ||
                               (xhr.responseURL && xhr.responseURL.indexOf('/login') !== -1)) {
                        // Session is truly gone
                        handleSessionExpired('CSRF refresh failed (HTTP ' + xhr.status + ')');
                    }
                };

                xhr.send();
            } catch(e) {}
        }

        // ===== 4. GLOBAL FETCH 419 HANDLER — Auto-retry on CSRF token mismatch =====
        // Only intercepts fetch() calls that return 419 status. Uses a lightweight
        // check to minimize overhead on successful requests.
        (function() {
            var originalFetch = window.fetch;
            window.fetch = function(input, init) {
                return originalFetch.apply(this, arguments).then(function(response) {
                    // Only process 419 errors — successful requests pass through with zero overhead
                    if (response.status !== 419 || sessionExpired) return response;

                    console.warn('[Fetch 419 Handler] CSRF token expired, refreshing and retrying...');
                    return refreshCSRFViaFetch().then(function(newToken) {
                        if (newToken && init && init.body) {
                            var body = init.body;
                            if (typeof body === 'string') {
                                body = body.replace(/_token=[^&]+/, '_token=' + encodeURIComponent(newToken));
                                init = Object.assign({}, init, { body: body, headers: Object.assign({}, init.headers, { 'X-CSRF-TOKEN': newToken }) });
                            }
                            console.log('[Fetch 419 Handler] Retrying request with fresh token...');
                            return originalFetch.apply(this, [input, init]);
                        }
                        return response;
                    }).catch(function() { return response; });
                });
            };

            function refreshCSRFViaFetch() {
                return originalFetch(keepaliveUrl, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function(r) {
                    if (r.status === 419 || r.status === 401) {
                        handleSessionExpired('CSRF refresh via fetch failed (HTTP ' + r.status + ')');
                        return null;
                    }
                    return r.json().then(function(data) {
                        if (data.csrf_token) {
                            updateCSRFToken(data.csrf_token);
                            lastKeepaliveTime = Date.now();
                            return data.csrf_token;
                        }
                        return null;
                    });
                }).catch(function() { return null; });
            }
        })();

        // ===== 5. SESSION EXPIRED HANDLER =====
        function handleSessionExpired(source) {
            if (sessionExpired) return;
            sessionExpired = true;
            console.error('[Keepalive] Session expired detected from:', source);

            // Try to backup any mark entry data to localStorage before redirecting
            try {
                if (typeof backupMarksToLocalStorage === 'function') {
                    backupMarksToLocalStorage();
                }
            } catch(e) {}

            alert('Your session has expired. You will be redirected to the login page.\n\nYour unsaved marks have been backed up and will be restored after you log back in.');

            // Use RELATIVE PATH (app-root-relative, without subdirectory prefix) instead of
            // full URL for the redirect parameter.
            // window.location.pathname includes the subdirectory (e.g., /redemption/admin/...)
            // which would cause the double-path bug when redirect()->intended() prepends the
            // base URL again. We strip the base path from APP_URL to get the app-root-relative path.
            var basePath = '';
            try {
                var appUrl = '{{ config("app.url") }}';
                var parsed = new URL(appUrl);
                basePath = parsed.pathname.replace(/\/$/, '');
            } catch(e) {}
            var currentPath = window.location.pathname + window.location.search;
            // Strip the subdirectory prefix if present (e.g., /redemption/admin/... → /admin/...)
            if (basePath && currentPath.startsWith(basePath + '/')) {
                currentPath = currentPath.substring(basePath.length) || '/';
            }
            var returnUrl = encodeURIComponent(currentPath);
            window.location.href = loginUrl + '?redirect=' + returnUrl;
        }

        // ===== 6. PERIODIC KEEPALIVE =====
        function scheduleKeepalive() {
            setInterval(function() {
                if (!sessionExpired) fireKeepalive();
            }, KEEPALIVE_INTERVAL);
        }

        // ===== 7. ACTIVITY-DRIVEN KEEPALIVE =====
        // Throttle activity checks to avoid excessive CPU usage.
        // Only check once per ACTIVITY_THRESHOLD period, not on every single event.
        var lastActivityCheck = 0;
        var activityEvents = ['input', 'click', 'keydown', 'touchstart'];
        activityEvents.forEach(function(evtName) {
            document.addEventListener(evtName, function() {
                if (sessionExpired) return;
                var now = Date.now();
                // Throttle: only check once per ACTIVITY_THRESHOLD period
                if (now - lastActivityCheck < ACTIVITY_THRESHOLD) return;
                lastActivityCheck = now;
                var elapsed = now - lastKeepaliveTime;
                if (elapsed > ACTIVITY_THRESHOLD) {
                    fireKeepalive();
                }
            }, { passive: true });
        });

        // ===== 8. START KEEPALIVE =====
        setTimeout(function() {
            fireKeepalive();
            scheduleKeepalive();
        }, 2000);
    })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>@yield('title', __('app.dashboard')) - Redemption School</title>

    {{-- PWA & Mobile Integration --}}
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <meta name="theme-color" content="#047857">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Redemption">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-TileColor" content="#047857">
    <meta name="msapplication-navbutton-color" content="#047857">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    {{-- Bootstrap Icons — needed by subject-assignments and other views that use bi bi-* classes --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/design-tokens.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    <link href="{{ asset('css/modern-components.css') }}" rel="stylesheet">
    {{-- Redesign layer — must come AFTER admin.css and modern-components.css so it can override. --}}
    <link href="{{ asset('css/admin-redesign.css') }}" rel="stylesheet">
    <style>html,body{overflow-x:hidden!important;max-width:100vw!important;width:100%!important;}*{box-sizing:border-box;}.admin-wrapper,.admin-main,.admin-content{max-width:100vw!important;overflow-x:hidden!important;box-sizing:border-box!important;}.admin-topbar{max-width:100vw!important;overflow:visible!important;box-sizing:border-box!important;}</style>
    @stack('styles')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="role-admin">
<div class="admin-wrapper">
    {{-- Modernized consolidated sidebar (replaces the inline ~500-line nav block).
         See resources/views/layouts/partials/sidebar.blade.php --}}
    @include('layouts.partials.sidebar')

    <div class="sidebar-backdrop d-none" id="sidebarBackdrop"></div>
    <div class="admin-main">
        {{-- Announcement Banner — $activeAnnouncements is shared via AppServiceProvider view composer --}}
        <div id="adminAnnouncementBar" class="announcement-banner">
            <div class="announcement-banner-inner">
                <a href="{{ route('admin.announcements.index') }}" class="announcement-badge" style="text-decoration:none;"><i class="fas fa-bullhorn"></i>&ensp;Announcements</a>
                @if($activeAnnouncements->count() > 0)
                <div class="announcement-ticker-wrap">
                    <div class="announcement-ticker">
                        @foreach($activeAnnouncements as $ann)
                        <span class="announcement-chip">
                            <strong>{{ $ann->title }}</strong>
                            @if($ann->category)<span class="announcement-cat">{{ ucfirst($ann->category) }}</span>@endif
                            @if($ann->start_date)<span class="announcement-date-inline"><i class="fas fa-calendar-alt"></i> {{ $ann->start_date->format('M d') }}</span>@endif
                            @if($ann->description)<span class="announcement-desc-inline">&mdash; {{ Str::limit(strip_tags($ann->description), 80) }}</span>@endif
                        </span>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="announcement-ticker-wrap">
                    <div class="announcement-ticker">
                        <span class="announcement-chip" style="opacity:0.7;"><i class="fas fa-info-circle"></i>&ensp;No active announcements &mdash; <a href="{{ route('admin.announcements.index') }}" style="color:#93c5fd;text-decoration:underline;">Create one</a></span>
                    </div>
                </div>
                @endif
                <button onclick="document.getElementById('adminAnnouncementBar').style.display='none'" class="announcement-close" title="Dismiss"><i class="fas fa-times"></i></button>
            </div>
        </div>

        {{-- Announcement Splash Modal --}}
        @if($activeAnnouncements->count() > 0)
        <div class="announcement-splash-overlay" id="announcementSplash">
            <div class="announcement-splash-modal">
                <div class="announcement-splash-header">
                    <div class="announcement-splash-icon"><i class="fas fa-bullhorn"></i></div>
                    <h2>Announcements</h2>
                    <span class="announcement-splash-count">{{ $activeAnnouncements->count() }} active</span>
                </div>
                <div class="announcement-splash-body">
                    @foreach($activeAnnouncements as $splashAnn)
                    <div class="announcement-splash-item">
                        <div class="announcement-splash-item-dot" style="background:{{ $splashAnn->color ?? '#4361ee' }}"></div>
                        <div class="announcement-splash-item-content">
                            <div class="announcement-splash-item-title">{{ $splashAnn->title }}</div>
                            @if($splashAnn->category)
                            <span class="announcement-splash-item-cat" style="background:{{ $splashAnn->color ?? '#4361ee' }}20;color:{{ $splashAnn->color ?? '#4361ee' }}">{{ ucfirst($splashAnn->category) }}</span>
                            @endif
                            @if($splashAnn->start_date)
                            <span class="announcement-splash-item-date"><i class="fas fa-calendar-alt"></i> {{ $splashAnn->start_date->format('M d, Y') }}</span>
                            @endif
                            @if($splashAnn->description)
                            <p class="announcement-splash-item-desc">{{ Str::limit(strip_tags($splashAnn->description), 150) }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="announcement-splash-footer">
                    <a href="{{ route('admin.announcements.index') }}" class="announcement-splash-viewall"><i class="fas fa-list"></i> View All Announcements</a>
                    <button onclick="closeAnnouncementSplash()" class="announcement-splash-dismiss"><i class="fas fa-check"></i> Dismiss</button>
                </div>
            </div>
        </div>
        @endif

        <style>
        .announcement-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            position: relative;
            z-index: 50;
            border-bottom: 2px solid #3b82f6;
        }
        .announcement-banner-inner {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 0 16px;
            height: 40px;
        }
        .announcement-badge {
            font-weight: 700;
            font-size: .82rem;
            background: #3b82f6;
            padding: 3px 12px;
            border-radius: 20px;
            white-space: nowrap;
            letter-spacing: .5px;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .announcement-ticker-wrap {
            flex: 1;
            overflow: hidden;
            position: relative;
            mask-image: linear-gradient(90deg, transparent 0%, #000 3%, #000 97%, transparent 100%);
            -webkit-mask-image: linear-gradient(90deg, transparent 0%, #000 3%, #000 97%, transparent 100%);
        }
        .announcement-ticker {
            display: flex;
            align-items: center;
            gap: 12px;
            white-space: nowrap;
        }
        .announcement-ticker.scrolling {
            animation: ticker-scroll var(--ticker-duration, 60s) linear infinite;
        }
        .announcement-ticker.scrolling:hover { animation-play-state: paused; }
        @keyframes ticker-scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .announcement-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: .9rem;
            padding: 0 24px;
            border-right: 1px solid rgba(255,255,255,.15);
        }
        .announcement-chip:last-child { border-right: none; }
        .announcement-chip strong { font-weight: 600; }
        .announcement-cat {
            font-size: .72rem;
            font-weight: 600;
            background: rgba(255,255,255,.25);
            padding: 1px 8px;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        .announcement-date-inline {
            font-size: .8rem;
            opacity: .75;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .announcement-desc-inline {
            font-size: .84rem;
            opacity: .8;
        }
        .announcement-close {
            background: none;
            border: none;
            color: rgba(255,255,255,.6);
            cursor: pointer;
            font-size: 14px;
            padding: 4px 6px;
            border-radius: 50%;
            transition: all .2s;
            flex-shrink: 0;
        }
        .announcement-close:hover {
            background: rgba(255,255,255,.2);
            color: #fff;
        }

        /* ===== Announcement Splash Modal ===== */
        .announcement-splash-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            display: none;  /* Hidden by default — JS shows it on page load if not dismissed */
            align-items: center;
            justify-content: center;
            padding: 16px;
            backdrop-filter: blur(4px);
        }
        .announcement-splash-overlay.splash-show {
            display: flex;
            animation: splashFadeIn 0.3s ease;
        }
        @keyframes splashFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .announcement-splash-modal {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: splashSlideUp 0.3s ease;
            overflow: hidden;
        }
        @keyframes splashSlideUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .announcement-splash-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .announcement-splash-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #3b82f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .announcement-splash-header h2 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            flex: 1;
        }
        .announcement-splash-count {
            font-size: 12px;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
            padding: 3px 10px;
            border-radius: 20px;
        }
        .announcement-splash-body {
            padding: 16px 20px;
            overflow-y: auto;
            flex: 1;
        }
        .announcement-splash-item {
            display: flex;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .announcement-splash-item:last-child { border-bottom: none; }
        .announcement-splash-item-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .announcement-splash-item-content {
            flex: 1;
            min-width: 0;
        }
        .announcement-splash-item-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
            line-height: 1.3;
        }
        .announcement-splash-item-cat {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 6px;
            margin-right: 8px;
        }
        .announcement-splash-item-date {
            font-size: 11px;
            color: #9ca3af;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .announcement-splash-item-desc {
            font-size: 13px;
            color: #6b7280;
            margin: 8px 0 0;
            line-height: 1.5;
        }
        .announcement-splash-footer {
            padding: 16px 20px;
            border-top: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .announcement-splash-viewall {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #3b82f6;
            text-decoration: none;
        }
        .announcement-splash-viewall:hover { text-decoration: underline; }
        .announcement-splash-dismiss {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 20px;
            border-radius: 10px;
            background: #3b82f6;
            color: #fff;
            border: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .announcement-splash-dismiss:hover { background: #2563eb; }

        @media (max-width: 768px) {
            /* Mobile: stack badge on line 1, ticker on line 2 */
            .announcement-banner { max-width: 100%; overflow: hidden; box-sizing: border-box; }
            .announcement-banner-inner {
                max-width: 100%;
                overflow: hidden;
                flex-wrap: wrap;
                height: auto;
                min-height: 40px;
                padding: 6px 10px;
                gap: 4px 10px;
                align-items: center;
            }
            .announcement-badge { font-size: .72rem; padding: 2px 8px; }
            .announcement-close { order: 2; }
            .announcement-ticker-wrap {
                min-width: 0;
                flex-basis: 100%;
                order: 3;
                overflow: hidden;
            }
            .announcement-chip { font-size: .8rem; white-space: normal; word-break: break-word; line-height: 1.3; }

            .announcement-splash-overlay { padding: 8px; z-index: 10001; }
            .announcement-splash-modal { max-width: 100%; max-height: 90vh; border-radius: 12px; }
            .announcement-splash-header { padding: 16px; }
            .announcement-splash-header h2 { font-size: 16px; }
            .announcement-splash-body { padding: 12px 16px; }
            .announcement-splash-footer { padding: 12px 16px; flex-direction: column; }
            .announcement-splash-viewall, .announcement-splash-dismiss {
                width: 100%; justify-content: center;
                min-height: 44px;  /* Touch-friendly */
            }
            .announcement-splash-dismiss { font-size: 15px; font-weight: 700; }
        }
        @media print { .announcement-banner, .announcement-splash-overlay { display: none !important; } }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.announcement-ticker-wrap').forEach(function(wrap) {
                var ticker = wrap.querySelector('.announcement-ticker');
                if (!ticker) return;
                // Only scroll if content overflows the container
                if (ticker.scrollWidth > wrap.clientWidth + 10) {
                    // Clone the content for seamless infinite scroll
                    var clone = ticker.innerHTML;
                    ticker.insertAdjacentHTML('beforeend', clone);
                    var duration = Math.max(ticker.scrollWidth / 2 / 25, 50);
                    ticker.style.setProperty('--ticker-duration', duration + 's');
                    ticker.classList.add('scrolling');
                }
            });

            // Announcement Splash: show ONCE PER DAY per user.
            // Uses localStorage with today's date key so it reappears the next day.
            var splash = document.getElementById('announcementSplash');
            if (splash) {
                var today = new Date().toISOString().slice(0, 10); // YYYY-MM-DD
                var splashKey = 'announcement_splash_dismissed_' + today;
                var alreadyShownToday = localStorage.getItem(splashKey);
                if (!alreadyShownToday) {
                    splash.classList.add('splash-show');
                }
            }

        });

        function closeAnnouncementSplash() {
            var splash = document.getElementById('announcementSplash');
            if (splash) {
                splash.style.opacity = '0';
                splash.style.transition = 'opacity 0.3s';
                setTimeout(function() {
                    splash.classList.remove('splash-show');
                    splash.style.opacity = '';
                    splash.style.transition = '';
                }, 300);
                var today = new Date().toISOString().slice(0, 10);
                localStorage.setItem('announcement_splash_dismissed_' + today, '1');
            }
        }
        </script>
        {{-- Modernized topbar with breadcrumbs + global search (replaces the inline ~130-line nav block). 
             See resources/views/layouts/partials/topbar.blade.php --}}
        @include('layouts.partials.topbar')


        <div class="admin-content">
            @if(session('success'))
                <div class="global-alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="global-alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif
            @if(session('warning'))
                <div class="global-alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}</div>
            @endif
            @if(session('info'))
                <div class="global-alert alert-info"><i class="fas fa-info-circle"></i> {{ session('info') }}</div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

{{-- Mobile Topbar Overlay Panel — OUTSIDE admin-wrapper to ensure proper fixed positioning --}}
<div id="mobileTopbarPanel" class="mobile-topbar-panel" style="display:none;">
    <div class="mobile-topbar-panel-backdrop" id="mobileTopbarPanelBackdrop"></div>
    <div class="mobile-topbar-panel-content" id="mobileTopbarPanelContent">
        <div class="mobile-topbar-panel-header">
            <span id="mobileTopbarPanelTitle">Menu</span>
            <button type="button" class="mobile-topbar-panel-close" id="mobileTopbarPanelClose">&times;</button>
        </div>
        <div class="mobile-topbar-panel-body" id="mobileTopbarPanelBody">
            {{-- Dropdown content gets injected here dynamically --}}
        </div>
    </div>
</div>

{{-- Swipe Indicator for Mobile --}}
<div class="swipe-indicator" id="swipeIndicator"></div>

{{-- Mobile Bottom Navigation --}}
<nav class="mobile-bottom-nav" id="mobileBottomNav">
    <div class="mobile-bottom-nav-inner">
        {{-- 1. Home — always first --}}
        <a href="{{ route('admin.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-th-large"></i>
            <span>Home</span>
        </a>
        {{-- 2. Mark Entry — second item for all roles that can access it --}}
        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'teacher']))
        <a href="{{ route('admin.mark-entries.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}">
            <i class="fas fa-pen"></i>
            <span>Marks</span>
        </a>
        @endif
        {{-- 3. Attendance Taking — third item for all roles that can access it --}}
        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'teacher']))
        <a href="{{ route('admin.attendance.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
            <i class="fas fa-clipboard-check"></i>
            <span>Attend.</span>
        </a>
        @endif
        {{-- 4. Role-specific item --}}
        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'registrar']))
        <a href="{{ route('admin.students.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
            <i class="fas fa-user-graduate"></i>
            <span>Students</span>
        </a>
        @elseif(in_array(($menuLevel ?? 'full'), ['finance', 'cashier']))
        <a href="{{ route('admin.fee-payments.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}">
            <i class="fas fa-credit-card"></i>
            <span>Payments</span>
        </a>
        @elseif(($menuLevel ?? 'full') === 'librarian')
        <a href="{{ route('admin.video-library.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.library.*') || request()->routeIs('admin.video-library.*') ? 'active' : '' }}">
            <i class="fas fa-book-open"></i>
            <span>Library</span>
        </a>
        @elseif(($menuLevel ?? 'full') === 'hr')
        @endif
        {{-- More — always last --}}
        <div class="mobile-nav-item mobile-nav-more" id="mobileNavMore" onclick="toggleMobileMenu()">
            <i class="fas fa-ellipsis-h"></i>
            <span>More</span>
        </div>
    </div>
</nav>

{{-- Mobile Menu Sheet (slides up from bottom) --}}
<div class="mobile-menu-sheet-backdrop" id="mobileMenuBackdrop" onclick="toggleMobileMenu()"></div>
<div class="mobile-menu-sheet" id="mobileMenuSheet">
    <div class="mobile-menu-sheet-handle"></div>
    <div class="mobile-menu-sheet-title">Quick Access</div>
    <div class="mobile-menu-sheet-links">
        <a href="{{ route('admin.dashboard') }}" class="mobile-menu-link">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'registrar']))
        <a href="{{ route('admin.academic-years.index') }}" class="mobile-menu-link">
            <i class="fas fa-calendar"></i>
            <span>Academic Yr</span>
        </a>
        <a href="{{ route('admin.classrooms.index') }}" class="mobile-menu-link">
            <i class="fas fa-building"></i>
            <span>Classes</span>
        </a>
        <a href="{{ route('admin.subjects.index') }}" class="mobile-menu-link">
            <i class="fas fa-book"></i>
            <span>Subjects</span>
        </a>
        @endif
        @if($isAdmin)
        <a href="{{ route('admin.staff.index') }}" class="mobile-menu-link">
            <i class="fas fa-id-badge"></i>
            <span>Staff</span>
        </a>
        @endif
        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'registrar']))
        <a href="{{ route('admin.students.index') }}" class="mobile-menu-link">
            <i class="fas fa-user-graduate"></i>
            <span>Students</span>
        </a>
        <a href="{{ route('admin.parents.index') }}" class="mobile-menu-link">
            <i class="fas fa-user-friends"></i>
            <span>Parents</span>
        </a>
        @endif
        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager']))
        <a href="{{ route('admin.fees.index') }}" class="mobile-menu-link">
            <i class="fas fa-money-bill-wave"></i>
            <span>Fees</span>
        </a>
        <a href="{{ route('admin.budgets.index') }}" class="mobile-menu-link">
            <i class="fas fa-chart-pie"></i>
            <span>Budgets</span>
        </a>
        <a href="{{ route('admin.payrolls.index') }}" class="mobile-menu-link">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Payroll</span>
        </a>
        <a href="{{ route('admin.leaves.index') }}" class="mobile-menu-link">
            <i class="fas fa-calendar-minus"></i>
            <span>Leaves</span>
        </a>
        @endif
        @if(in_array(($menuLevel ?? 'full'), ['finance', 'hr']))
        <a href="{{ route('admin.fees.index') }}" class="mobile-menu-link">
            <i class="fas fa-money-bill-wave"></i>
            <span>Fees</span>
        </a>
        @endif
        @if(($menuLevel ?? 'full') === 'hr')
        <a href="{{ route('admin.leaves.index') }}" class="mobile-menu-link">
            <i class="fas fa-calendar-minus"></i>
            <span>Leaves</span>
        </a>
        <a href="{{ route('admin.payrolls.index') }}" class="mobile-menu-link">
            <i class="fas fa-file-invoice-dollar"></i>
            <span>Payroll</span>
        </a>
        @endif
        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'registrar']))
        <a href="{{ route('admin.exams.index') }}" class="mobile-menu-link">
            <i class="fas fa-file-alt"></i>
            <span>Exams</span>
        </a>
        <a href="{{ route('admin.mark-entries.index') }}" class="mobile-menu-link">
            <i class="fas fa-pen"></i>
            <span>Mark Entry</span>
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="mobile-menu-link">
            <i class="fas fa-clipboard-check"></i>
            <span>Attendance</span>
        </a>
        <a href="{{ route('admin.attendance-delegation.index') }}" class="mobile-menu-link">
            <i class="fas fa-user-check"></i>
            <span>Delegate</span>
        </a>
        @endif
        @if(($menuLevel ?? 'full') === 'teacher')
        <a href="{{ route('admin.mark-entries.index') }}" class="mobile-menu-link">
            <i class="fas fa-pen"></i>
            <span>Mark Entry</span>
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="mobile-menu-link">
            <i class="fas fa-clipboard-check"></i>
            <span>Attendance</span>
        </a>
        <a href="{{ route('admin.attendance-delegation.index') }}" class="mobile-menu-link">
            <i class="fas fa-user-check"></i>
            <span>Delegate</span>
        </a>
        <a href="{{ route('admin.mark-roster.index') }}" class="mobile-menu-link">
            <i class="fas fa-list-ol"></i>
            <span>Mark Roster</span>
        </a>
        <a href="{{ route('admin.report-exchange.index') }}" class="mobile-menu-link">
            <i class="fas fa-exchange-alt"></i>
            <span>Report Exchange</span>
        </a>
        @endif
        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'teacher', 'librarian']))
        <a href="{{ route('admin.library.index') }}" class="mobile-menu-link">
            <i class="fas fa-book"></i>
            <span>Books</span>
        </a>
        <a href="{{ route('admin.video-library.index') }}" class="mobile-menu-link">
            <i class="fab fa-youtube"></i>
            <span>Videos</span>
        </a>
        @endif
        <a href="{{ route('admin.calendar.index') }}" class="mobile-menu-link">
            <i class="fas fa-calendar-alt"></i>
            <span>Calendar</span>
        </a>
        <a href="{{ route('admin.announcements.index') }}" class="mobile-menu-link">
            <i class="fas fa-bullhorn"></i>
            <span>Announce</span>
        </a>
        <a href="{{ route('admin.chat.index') }}" class="mobile-menu-link">
            <i class="fas fa-comment-dots"></i>
            <span>Chat</span>
        </a>
        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager', 'branch_principal', 'registrar']))
        <a href="{{ route('admin.report-card.index') }}" class="mobile-menu-link">
            <i class="fas fa-id-card"></i>
            <span>Reports</span>
        </a>
        <a href="{{ route('admin.certificate-generate.index') }}" class="mobile-menu-link">
            <i class="fas fa-award"></i>
            <span>Certs</span>
        </a>
        @endif
        @if(in_array(($menuLevel ?? 'full'), ['full', 'general_manager']))
        <a href="{{ route('admin.branches.index') }}" class="mobile-menu-link">
            <i class="fas fa-map-marker-alt"></i>
            <span>Branches</span>
        </a>
        <a href="{{ route('admin.settings.index') }}" class="mobile-menu-link">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        @endif
        <a href="{{ route('admin.staff.edit', ['staff' => auth()->id()]) }}" class="mobile-menu-link">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
        <a href="#" class="mobile-menu-link" onclick="event.preventDefault();document.getElementById('logoutForm').submit();">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
<form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display:none">@csrf</form>
<script>
// Global user context for branch principal locking
window.currentUser = {
    role: '{{ Auth::user()?->role ?? '' }}',
    branchId: {{ Auth::user()?->branch_id ?? 'null' }},
    branchName: '{{ $userBranch?->name ?? '' }}'
};

// Auto-lock branch dropdowns for branch_principal
document.addEventListener('DOMContentLoaded', function() {
    if (window.currentUser.role === 'branch_principal' && window.currentUser.branchId) {
        document.querySelectorAll('select[name="branch_id"]').forEach(function(sel) {
            // Set the value to the user's branch
            sel.value = window.currentUser.branchId;
            // Disable the select
            sel.disabled = true;
            // Add a hidden input with the same name and value so it's still submitted
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = sel.name;
            hidden.value = window.currentUser.branchId;
            sel.parentNode.insertBefore(hidden, sel.nextSibling);
            // Add visual indicator
            sel.style.opacity = '0.7';
            sel.style.cursor = 'not-allowed';
        });
    }
});
</script>
<script>
(function() {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggle = document.getElementById('sidebarToggle');
    const isMobile = () => window.innerWidth < 768;

    var _sidebarJustOpened = false;
    function showSidebar(show) {
        if (!sidebar) return;
        if (show) {
            sidebar.classList.add('show');
            sidebar.removeAttribute('style');
            _sidebarJustOpened = true;
            // Prevent backdrop from immediately closing sidebar on same touch
            setTimeout(function() { _sidebarJustOpened = false; }, 400);
        } else {
            sidebar.classList.remove('show');
        }
        if (backdrop) {
            if (show) {
                backdrop.classList.remove('d-none');
                // Force reflow before adding show class for transition
                void backdrop.offsetWidth;
                backdrop.classList.add('show');
            } else {
                backdrop.classList.remove('show');
                // Hide after transition
                setTimeout(() => {
                    if (!backdrop.classList.contains('show')) {
                        backdrop.classList.add('d-none');
                    }
                }, 300);
            }
        }
    }

    if (toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showSidebar(!sidebar.classList.contains('show'));
        });
    }
    if (backdrop) backdrop.addEventListener('click', function(e) {
        // Ignore click if sidebar was just opened (prevents auto-close from same touch)
        if (_sidebarJustOpened) return;
        showSidebar(false);
    });

    // Close sidebar on non-submenu link click (mobile)
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't close for submenu toggles — let Bootstrap handle them
            if (link.hasAttribute('data-bs-toggle')) return;
            if (window.innerWidth < 768) showSidebar(false);
        });
    });

    // Auto-scroll to active menu item
    var activeItem = document.querySelector('.sidebar-menu a.active');
    if (activeItem) {
        setTimeout(function() {
            activeItem.scrollIntoView({behavior:'smooth', block:'nearest'});
        }, 200);
    }

    // Accordion behavior: collapse other sidebar groups when one expands
    document.querySelectorAll('.sidebar-menu .collapse').forEach(function(menu) {
        menu.addEventListener('show.bs.collapse', function() {
            // Collapse all other sibling menus
            document.querySelectorAll('.sidebar-menu .collapse.show').forEach(function(other) {
                if (other !== menu) {
                    // Use getOrCreateInstance to handle uninitialized collapses
                    var bsCollapse = bootstrap.Collapse.getOrCreateInstance(other, {toggle: false});
                    if (bsCollapse) bsCollapse.hide();
                }
            });
        });
    });

    // Auto-dismiss alerts — but keep error/warning visible longer when there are bulk error details
    document.querySelectorAll('.global-alert').forEach(a => {
        var hasBulkErrors = document.querySelector('.sl-bulk-error-panel');
        var delay = 4000;
        if (hasBulkErrors && (a.classList.contains('alert-danger') || a.classList.contains('alert-warning'))) {
            delay = 15000; // 15 seconds for errors when bulk error details are shown
        }
        setTimeout(() => {
            a.style.transition = 'opacity 0.3s';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 300);
        }, delay);
    });

    // ===== MOBILE TOPBAR OVERLAY PANEL =====
    // On mobile/WebView, Bootstrap dropdowns don't work reliably because:
    // 1. Touch/click events fire differently in WebView
    // 2. The bottom fixed nav covers dropdown menus
    // 3. Overflow:hidden on parents clips dropdowns
    // SOLUTION: On mobile, we intercept the toggle click BEFORE Bootstrap,
    // prevent Bootstrap from opening its dropdown, and show a full-screen
    // overlay panel instead. On desktop, Bootstrap handles everything natively.
    (function() {
        var panel = document.getElementById('mobileTopbarPanel');
        var panelBackdrop = document.getElementById('mobileTopbarPanelBackdrop');
        var panelTitle = document.getElementById('mobileTopbarPanelTitle');
        var panelBody = document.getElementById('mobileTopbarPanelBody');
        var panelClose = document.getElementById('mobileTopbarPanelClose');

        if (!panel) return;

        function isMobile() {
            // Use multiple signals: narrow viewport OR touch-primary device OR WebView/Capacitor
            var hasTouchScreen = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
            var isNarrowViewport = window.innerWidth <= 768;
            // Detect Capacitor/WebView: modern Android WebView may not contain 'wv' in UA.
            // Capacitor apps set window.Capacitor or have specific UA patterns.
            var isWebView = /wv|android.*browser/i.test(navigator.userAgent) && hasTouchScreen;
            var isCapacitor = !!(window.Capacitor || (window.webkit && window.webkit.messageHandlers));
            // Also check for standalone PWA mode (display: standalone)
            var isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                               window.navigator.standalone === true;
            return isNarrowViewport || isWebView || isCapacitor || (isStandalone && hasTouchScreen);
        }

        function closePanel() {
            panel.classList.remove('show');
            panel.style.display = 'none';
            document.body.style.overflow = '';
            var bottomNav = document.getElementById('mobileBottomNav');
            if (bottomNav) bottomNav.style.display = '';
        }

        function openPanel(title, contentHtml) {
            panelTitle.textContent = title;
            panelBody.innerHTML = contentHtml;
            panel.style.display = 'block';
            void panel.offsetHeight; // Force reflow
            panel.classList.add('show');
            document.body.style.overflow = 'hidden';
            var bottomNav = document.getElementById('mobileBottomNav');
            if (bottomNav) bottomNav.style.display = 'none';
        }

        // Close panel events
        panelClose.addEventListener('click', function(e) {
            e.preventDefault();
            closePanel();
        });
        panelBackdrop.addEventListener('click', function(e) {
            e.preventDefault();
            closePanel();
        });

        // ===== MOBILE: Intercept Bootstrap dropdown toggles =====
        // We listen in the CAPTURING phase (true) so we can intercept
        // BEFORE Bootstrap's listener (which is in bubbling phase).
        // On mobile only, we stop the event and show the overlay panel.
        // On desktop, we let the event through to Bootstrap.

        var dropdownConfig = [
            { id: 'langDropdown', toggleSel: '.topbar-icon-toggle', menuSel: '.dropdown-menu', title: '{{ __("app.language") }}' },
            { id: 'notifDropdown', toggleSel: '.topbar-icon-toggle', menuSel: '.dropdown-menu', title: '{{ __("app.notifications") }}' },
            { id: 'userDropdown', toggleSel: '.topbar-avatar', menuSel: '.dropdown-menu', title: '{{ Auth::user()?->name ?? 'User' }}' }
        ];

        var panelJustOpened = false;

        dropdownConfig.forEach(function(cfg) {
            var toggle = document.querySelector('#' + cfg.id + ' ' + cfg.toggleSel);
            if (!toggle) return;

            // Intercept click in capturing phase — runs BEFORE Bootstrap
            toggle.addEventListener('click', function(e) {
                if (!isMobile()) return; // Let Bootstrap handle on desktop

                // Prevent Bootstrap from opening its dropdown
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                if (panelJustOpened) return;

                var menu = document.querySelector('#' + cfg.id + ' ' + cfg.menuSel);
                if (menu) {
                    panelJustOpened = true;
                    // Close any open Bootstrap dropdown first
                    var bsInstance = bootstrap.Dropdown.getInstance(toggle);
                    if (bsInstance) bsInstance.hide();
                    openPanel(cfg.title, menu.innerHTML);
                    setTimeout(function() { panelJustOpened = false; }, 300);
                }
            }, true); // capturing phase

            // Also handle touchend for WebViews that don't fire click properly
            toggle.addEventListener('touchend', function(e) {
                if (!isMobile()) return;
                // Only intercept if click didn't already fire (detail=0 = touch-only)
                // Removed the window.innerWidth <= 768 check since isMobile() already
                // handles Capacitor/WebView detection regardless of viewport width
                if (e.detail === 0) {
                    e.preventDefault();
                    e.stopPropagation();

                    if (panelJustOpened) return;

                    var menu = document.querySelector('#' + cfg.id + ' ' + cfg.menuSel);
                    if (menu) {
                        panelJustOpened = true;
                        var bsInstance = bootstrap.Dropdown.getInstance(toggle);
                        if (bsInstance) bsInstance.hide();
                        openPanel(cfg.title, menu.innerHTML);
                        setTimeout(function() { panelJustOpened = false; }, 300);
                    }
                }
            }, { passive: false });
        });

        // ===== FALLBACK: If Bootstrap opens a dropdown on mobile, override with overlay panel =====
        // On some WebViews, the capturing phase click handler above may not fire
        // (e.g., if Bootstrap uses touch events internally). This MutationObserver
        // detects when Bootstrap adds .show to a dropdown and immediately replaces
        // it with the overlay panel on mobile devices.
        try {
            var dropdownParents = document.querySelectorAll('#notifDropdown, #userDropdown, #langDropdown');
            dropdownParents.forEach(function(parent) {
                var observer = new MutationObserver(function(mutations) {
                    if (!isMobile()) return;
                    mutations.forEach(function(m) {
                        if (m.type === 'attributes' && m.attributeName === 'class') {
                            var target = m.target;
                            // If Bootstrap just added .show class to a dropdown
                            if (target.classList.contains('show')) {
                                var menu = target.querySelector('.dropdown-menu');
                                var toggleEl = target.querySelector('[data-bs-toggle="dropdown"]');
                                if (menu && toggleEl) {
                                    // Find the config for this dropdown
                                    var cfg = dropdownConfig.find(function(c) { return c.id === target.id; });
                                    var title = cfg ? cfg.title : 'Menu';

                                    // Close the Bootstrap dropdown immediately
                                    var bsInstance = bootstrap.Dropdown.getInstance(toggleEl);
                                    if (bsInstance) bsInstance.hide();
                                    else {
                                        target.classList.remove('show');
                                        var ddMenu = target.querySelector('.dropdown-menu');
                                        if (ddMenu) ddMenu.classList.remove('show');
                                    }

                                    // Show the overlay panel instead
                                    if (!panelJustOpened) {
                                        panelJustOpened = true;
                                        openPanel(title, menu.innerHTML);
                                        setTimeout(function() { panelJustOpened = false; }, 300);
                                    }
                                }
                            }
                        }
                    });
                });
                observer.observe(parent, { attributes: true, subtree: true, attributeFilter: ['class'] });
            });
        } catch(e) {
            // MutationObserver not supported — graceful fallback
        }

        // Close panel on resize to desktop
        window.addEventListener('resize', function() {
            if (!isMobile()) closePanel();
        });

        // ===== DESKTOP: Ensure Bootstrap dropdowns close when clicking outside =====
        // Use Bootstrap's Dropdown API instead of manually toggling .show class
        document.addEventListener('click', function(e) {
            if (isMobile()) return;
            // If click is outside all dropdowns, close any open ones using Bootstrap API
            var isInsideDropdown = e.target.closest('#langDropdown, #notifDropdown, #userDropdown');
            if (!isInsideDropdown) {
                ['langDropdown', 'notifDropdown', 'userDropdown'].forEach(function(id) {
                    var dropdownEl = document.querySelector('#' + id + ' [data-bs-toggle="dropdown"]');
                    if (dropdownEl) {
                        var bsInstance = bootstrap.Dropdown.getInstance(dropdownEl);
                        if (bsInstance) bsInstance.hide();
                    }
                });
            }
        });
    })();
})();

// Mobile Menu Sheet Toggle
function toggleMobileMenu() {
    const sheet = document.getElementById('mobileMenuSheet');
    const backdrop = document.getElementById('mobileMenuBackdrop');
    const isOpen = sheet.classList.contains('show');
    sheet.classList.toggle('show', !isOpen);
    backdrop.classList.toggle('show', !isOpen);
    document.body.style.overflow = isOpen ? '' : 'hidden';
}

// Swipe-to-open sidebar on mobile
(function() {
    var sidebar = document.getElementById('adminSidebar');
    var backdrop = document.getElementById('sidebarBackdrop');
    var swipeIndicator = document.getElementById('swipeIndicator');
    var touchStartX = 0;
    var touchStartY = 0;
    var isSwiping = false;

    document.addEventListener('touchstart', function(e) {
        if (window.innerWidth >= 769) return;
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        isSwiping = false;
    }, { passive: true });

    document.addEventListener('touchmove', function(e) {
        if (window.innerWidth >= 769) return;
        var dx = e.touches[0].clientX - touchStartX;
        var dy = e.touches[0].clientY - touchStartY;

        // Only detect horizontal swipe from the left edge (within 30px)
        if (!isSwiping && touchStartX < 30 && Math.abs(dx) > Math.abs(dy) && dx > 10) {
            isSwiping = true;
        }
    }, { passive: true });

    document.addEventListener('touchend', function(e) {
        if (window.innerWidth >= 769 || !isSwiping) return;
        var dx = e.changedTouches[0].clientX - touchStartX;

        // Swipe right from left edge opens sidebar
        if (dx > 60 && sidebar && !sidebar.classList.contains('show')) {
            sidebar.classList.add('show');
            if (backdrop) {
                backdrop.classList.remove('d-none');
                backdrop.classList.add('show');
            }
        }
        isSwiping = false;
    }, { passive: true });

    // Hide swipe indicator after 3 seconds
    if (swipeIndicator) {
        setTimeout(function() {
            swipeIndicator.style.opacity = '0';
            setTimeout(function() { swipeIndicator.style.display = 'none'; }, 300);
        }, 3000);
    }
})();

// ===== PULL-TO-REFRESH for Mobile =====
(function() {
    var pullIndicator = document.getElementById('pullToRefreshIndicator');
    if (!pullIndicator) {
        // Create pull-to-refresh indicator element
        pullIndicator = document.createElement('div');
        pullIndicator.id = 'pullToRefreshIndicator';
        pullIndicator.style.cssText = 'position:fixed;top:0;left:0;right:0;height:0;background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:600;z-index:9999;transition:height 0.2s ease;overflow:hidden;pointer-events:none;';
        pullIndicator.innerHTML = '<i class="fas fa-sync-alt" style="margin-right:6px;"></i> <span id="ptrText">Pull to refresh</span>';
        document.body.appendChild(pullIndicator);
    }

    var ptrText = document.getElementById('ptrText');
    var startY = 0;
    var pulling = false;
    var threshold = 80;
    var isScrolledToTop = function() {
        return window.scrollY <= 0;
    };

    document.addEventListener('touchstart', function(e) {
        if (window.innerWidth >= 769) return;
        if (!isScrolledToTop()) return;
        startY = e.touches[0].clientY;
        pulling = false;
    }, { passive: true });

    document.addEventListener('touchmove', function(e) {
        if (window.innerWidth >= 769) return;
        if (startY === 0) return;
        if (!isScrolledToTop() && !pulling) return;

        var currentY = e.touches[0].clientY;
        var diff = currentY - startY;

        if (diff > 10 && isScrolledToTop()) {
            pulling = true;
            var height = Math.min(diff * 0.5, threshold);
            pullIndicator.style.height = height + 'px';

            if (height >= threshold) {
                ptrText.textContent = 'Release to refresh';
                pullIndicator.querySelector('i').classList.add('fa-spin');
            } else {
                ptrText.textContent = 'Pull to refresh';
                pullIndicator.querySelector('i').classList.remove('fa-spin');
            }
        }
    }, { passive: true });

    document.addEventListener('touchend', function(e) {
        if (window.innerWidth >= 769 || !pulling) return;

        var currentHeight = parseInt(pullIndicator.style.height) || 0;

        if (currentHeight >= threshold) {
            // Trigger refresh
            ptrText.textContent = 'Refreshing...';
            pullIndicator.style.height = '40px';
            pullIndicator.querySelector('i').classList.add('fa-spin');
            setTimeout(function() {
                window.location.reload();
            }, 500);
        } else {
            // Cancel
            pullIndicator.style.height = '0';
        }
        pulling = false;
        startY = 0;
    }, { passive: true });
})();
</script>
<style>
/* ===== Topbar Icon Buttons (Chat, Notifications, Language) ===== */
.topbar-icon-btn {
    position: relative;
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    transition: var(--transition);
    font-size: 15px;
}
.topbar-icon-btn:hover {
    background: var(--body-bg);
    color: var(--text-dark);
}
.topbar-icon-link {
    text-decoration: none;
}
.topbar-icon-toggle {
    background: none;
    border: none;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: inherit;
    font-size: inherit;
    cursor: pointer;
    border-radius: var(--radius-sm);
    transition: var(--transition);
}
.topbar-icon-toggle:hover {
    color: var(--text-dark);
}

/* Badge on icon */
.topbar-icon-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    font-size: 9px;
    font-weight: 700;
    line-height: 1;
    min-width: 16px;
    height: 16px;
    padding: 2px 4px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}
.topbar-icon-badge.lang-code {
    position: static;
    background: var(--primary-light);
    color: var(--primary);
    min-width: auto;
    height: auto;
    padding: 1px 4px;
    font-size: 9px;
    border-radius: 3px;
    margin-left: -2px;
    margin-top: -6px;
}
.topbar-icon-badge.badge-danger {
    background: var(--danger);
    color: #fff;
    top: 0;
    right: 0;
    animation: badge-pulse 2s infinite;
}
@keyframes badge-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Notification Dropdown */
.topbar-icon-dropdown {
    min-width: 180px;
}
.topbar-icon-dropdown .dropdown-item {
    font-size: 13px;
    padding: 8px 14px;
}
.topbar-icon-dropdown .dropdown-item.active {
    background-color: #f0f7ff;
    font-weight: 600;
}

.topbar-notif-dropdown {
    width: 320px;
    max-width: 90vw;
    max-height: 400px;
    overflow-y: auto;
}
.notif-item {
    padding: 10px 14px !important;
    border-bottom: 1px solid #f3f4f6;
}
.notif-item.unread {
    background: #f0f7ff;
}
.notif-item .notif-title {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-dark);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.notif-item .notif-time {
    font-size: 11px;
    color: var(--text-muted);
    margin-top: 2px;
}

/* ===== Print Styles ===== */
@media print {
    .admin-sidebar, .sidebar-backdrop, .admin-topbar, .sidebar-footer, .sidebar-toggle,
    .no-print, .mr-filter-card, .mr-header, .mr-actions, .me-filter-card, .me-header,
    .me-keyboard-hint, .fms-card:first-of-type, .global-alert, .mobile-bottom-nav,
    .swipe-indicator, #adminAnnouncementBar {
        display: none !important;
    }
    .admin-wrapper {
        margin: 0 !important;
        padding: 0 !important;
        display: block !important;
    }
    .admin-main {
        margin: 0 !important;
        margin-left: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        overflow: visible !important;
    }
    .admin-content {
        padding: 4mm !important;
        margin: 0 !important;
        overflow: visible !important;
        max-width: 100% !important;
        width: 100% !important;
    }
    body {
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
    }
}
</style>
@stack('scripts')
@yield('scripts')

{{-- PWA Install Prompt --}}
<script src="{{ asset('js/pwa-install.js') }}"></script>

{{-- PWA Service Worker Registration & Notification Permission --}}
<script>
// Register service worker for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('{{ asset('sw.js') }}')
            .then(function(registration) {
                console.log('SW registered:', registration.scope);

                // Check for updates periodically
                setInterval(function() {
                    registration.update();
                }, 60 * 60 * 1000); // every hour
            })
            .catch(function(error) {
                console.log('SW registration failed:', error);
            });
    });
}

// Request notification permission for mobile integration
function requestNotificationPermission() {
    if (!('Notification' in window)) {
        console.log('This browser does not support notifications');
        return;
    }

    if (Notification.permission === 'default') {
        // Don't auto-request — let user trigger it
        window._redemptionCanRequestNotifications = true;
    }
}

// Call on first user interaction (click/tap) to comply with browser policies
function setupNotificationOnInteraction() {
    if (!window._redemptionNotificationSetupDone) {
        window._redemptionNotificationSetupDone = true;
        document.addEventListener('click', function requestOnFirstClick() {
            if (Notification.permission === 'default') {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        console.log('Notification permission granted');
                        // Subscribe to push notifications if available
                        subscribeToPushNotifications();
                    }
                });
            }
            document.removeEventListener('click', requestOnFirstClick);
        }, { once: false });
    }
}

// Subscribe to push notifications via service worker
function subscribeToPushNotifications() {
    if (!('PushManager' in window)) return;

    navigator.serviceWorker.ready.then(function(registration) {
        registration.pushManager.getSubscription().then(function(subscription) {
            if (!subscription) {
                // Create a new subscription
                // The public key needs to be generated on server and set in settings
                // For now, we prepare the infrastructure
                console.log('Push subscription not yet configured on server');
            }
        });
    });
}

// Mobile-specific enhancements
function initMobileIntegration() {
    // Vibration API support
    window.redemptionVibrate = function(pattern) {
        if ('vibrate' in navigator) {
            navigator.vibrate(pattern || [100]);
        }
    };

    // Share API
    window.redemptionShare = function(data) {
        if (navigator.share) {
            navigator.share(data).catch(function(err) {
                console.log('Share failed:', err);
            });
        }
    };

    // Network status
    window.addEventListener('online', function() {
        // Show online toast
        showToast('Back online', 'success');
        // Trigger background sync
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            navigator.serviceWorker.ready.then(function(reg) {
                return reg.sync.register('data-sync');
            });
        }
    });

    window.addEventListener('offline', function() {
        showToast('You are offline. Changes will sync when you reconnect.', 'warning');
    });

    // Request notification permission on interaction
    setupNotificationOnInteraction();
    requestNotificationPermission();
}

// Toast notification helper
function showToast(message, type) {
    type = type || 'info';
    var colors = {
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#3b82f6'
    };
    var icons = {
        success: 'fa-check-circle',
        warning: 'fa-exclamation-triangle',
        danger: 'fa-times-circle',
        info: 'fa-info-circle'
    };

    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:16px;right:16px;z-index:9999;background:' + (colors[type] || colors.info) + ';color:#fff;padding:12px 20px;border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,0.15);font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;max-width:90vw;opacity:0;transform:translateY(-10px);transition:all 0.3s;';
    toast.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + '"></i><span>' + message + '</span>';
    document.body.appendChild(toast);

    requestAnimationFrame(function() {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-10px)';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

// Initialize mobile integration on DOM ready
document.addEventListener('DOMContentLoaded', initMobileIntegration);

// Make functions globally available for other scripts
window.showToast = showToast;

// ── Capacitor Native Bridge Integration ──
// This runs inside the native Android/iOS WebView and connects to native features
(function() {
    // Check if running inside Capacitor native app
    var isCapacitor = !!(window.Capacitor && window.Capacitor.isNativePlatform && window.Capacitor.isNativePlatform());
    if (!isCapacitor) return;

    console.log('[Redemption] Running inside Capacitor native app');

    // ── Push Notifications ──
    if (window.Capacitor.Plugins && window.Capacitor.Plugins.PushNotifications) {
        var PushNotifications = window.Capacitor.Plugins.PushNotifications;

        // Request notification permission
        PushNotifications.requestPermissions().then(function(result) {
            if (result.receive === 'granted') {
                PushNotifications.register();
                console.log('[Redemption] Push notifications registered');
            }
        });

        // Handle notification received while app is in foreground
        PushNotifications.addListener('pushNotificationReceived', function(notification) {
            console.log('[Redemption] Notification received:', notification);
            // Show in-app toast
            if (notification.title && notification.body) {
                showToast(notification.body, 'info');
                // Vibrate on notification
                if (window.Capacitor.Plugins.Haptics) {
                    window.Capacitor.Plugins.Haptics.notification();
                }
            }
        });

        // Handle notification clicked/tapped
        PushNotifications.addListener('pushNotificationActionPerformed', function(action) {
            console.log('[Redemption] Notification action:', action);
            var data = action.notification && action.notification.data;
            if (data && data.url) {
                window.location.href = data.url;
            }
        });
    }

    // ── Network Status ──
    if (window.Capacitor.Plugins && window.Capacitor.Plugins.Network) {
        var Network = window.Capacitor.Plugins.Network;

        Network.getStatus().then(function(status) {
            if (status.connected === false) {
                showToast('No internet connection', 'warning');
            }
        });

        Network.addListener('networkStatusChange', function(status) {
            if (status.connected) {
                showToast('Back online', 'success');
            } else {
                showToast('You are offline', 'warning');
            }
        });
    }

    // ── Local Notifications (for in-app notifications) ──
    if (window.Capacitor.Plugins && window.Capacitor.Plugins.LocalNotifications) {
        var LocalNotifications = window.Capacitor.Plugins.LocalNotifications;

        // Request permission
        LocalNotifications.requestPermissions().then(function(result) {
            console.log('[Redemption] Local notifications permission:', result.display);
        });

        // Handle local notification tap
        LocalNotifications.addListener('localNotificationActionPerformed', function(action) {
            console.log('[Redemption] Local notification action:', action);
            var data = action.notification && action.notification.extra;
            if (data && data.url) {
                window.location.href = data.url;
            }
        });
    }

    // ── Native Loading Indicator Bridge ──
    // Listen for custom loading events injected by the native MainActivity
    document.addEventListener('redemption-loading', function(e) {
        // Show a subtle in-app loading indicator for AJAX requests
        var loader = document.getElementById('redemption-native-loader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'redemption-native-loader';
            loader.style.cssText = 'position:fixed;top:0;left:0;right:0;height:3px;z-index:99999;background:linear-gradient(90deg,#1a237e,#6366f1,#1a237e);background-size:200% 100%;animation:redemptionSlide 1.5s ease-in-out infinite;';
            var style = document.createElement('style');
            style.textContent = '@keyframes redemptionSlide{0%{background-position:200% 0}100%{background-position:-200% 0}}';
            document.head.appendChild(style);
            document.body.appendChild(loader);
        }
        loader.style.display = 'block';
    });

    document.addEventListener('redemption-loaded', function(e) {
        var loader = document.getElementById('redemption-native-loader');
        if (loader) {
            loader.style.display = 'none';
        }
    });
})();
</script>
</body>
</html>
