<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download App - School of Redemption</title>

    {{-- PWA & Mobile Integration --}}
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Redemption">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="msapplication-TileColor" content="#6366f1">
    <meta name="msapplication-navbutton-color" content="#6366f1">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192x192.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #e0e7ff;
            --accent: #f59e0b;
            --accent-light: #fef3c7;
            --dark: #0f172a;
            --dark-800: #1e293b;
            --dark-600: #475569;
            --gray: #94a3b8;
            --gray-light: #f1f5f9;
            --green: #10b981;
            --green-light: #d1fae5;
            --blue: #3b82f6;
            --blue-light: #dbeafe;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark);
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ===== NAVBAR ===== */
        .app-nav {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .app-nav-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .app-nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #fff;
        }
        .app-nav-brand:hover { color: #fff; }
        .app-nav-icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
        }
        .app-nav-text {
            font-weight: 700;
            font-size: 16px;
            letter-spacing: -0.3px;
        }
        .app-nav-text span { color: var(--primary); }
        .btn-nav-login {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 20px;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-nav-login:hover {
            background: var(--primary-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        /* ===== HERO ===== */
        .hero {
            text-align: center;
            padding: 60px 20px 40px;
            position: relative;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-icon-wrap {
            position: relative;
            display: inline-block;
            margin-bottom: 24px;
        }
        .hero-icon {
            width: 100px;
            height: 100px;
            border-radius: 26px;
            background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            color: #fff;
            box-shadow: 0 16px 48px rgba(99, 102, 241, 0.35);
            position: relative;
            z-index: 1;
        }
        .hero-icon-ring {
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            border-radius: 30px;
            border: 2px solid rgba(99, 102, 241, 0.25);
            animation: pulse-ring 2s ease-out infinite;
        }
        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.15); opacity: 0; }
        }
        .hero h1 {
            font-size: 2.2rem;
            font-weight: 900;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
            line-height: 1.2;
        }
        .hero h1 span { color: var(--primary); }
        .hero p {
            font-size: 1.05rem;
            color: var(--gray);
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .hero-badges {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--gray);
        }
        .hero-badge i { font-size: 10px; }
        .hero-badge.free { color: var(--green); border-color: rgba(16,185,129,0.2); background: rgba(16,185,129,0.08); }
        .hero-badge.secure { color: var(--blue); border-color: rgba(59,130,246,0.2); background: rgba(59,130,246,0.08); }

        /* ===== INSTALL CARDS ===== */
        .install-section {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }
        .install-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 640px) {
            .install-grid { grid-template-columns: 1fr; }
        }
        .install-card {
            background: var(--dark-800);
            border-radius: 20px;
            padding: 32px 28px;
            border: 1px solid rgba(255,255,255,0.06);
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .install-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.3);
        }
        .install-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }
        .install-card.android::before {
            background: linear-gradient(90deg, #3DDC84, #00C853);
        }
        .install-card.ios::before {
            background: linear-gradient(90deg, #007AFF, #5856D6);
        }
        .install-card-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
        }
        .install-card.android .install-card-icon {
            background: rgba(61, 220, 132, 0.12);
            color: #3DDC84;
        }
        .install-card.ios .install-card-icon {
            background: rgba(0, 122, 255, 0.12);
            color: #007AFF;
        }
        .install-card h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .install-card .subtitle {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 20px;
        }
        .install-steps {
            list-style: none;
            padding: 0;
            margin: 0 0 24px;
        }
        .install-steps li {
            display: flex;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            font-size: 14px;
            line-height: 1.5;
            color: #cbd5e1;
        }
        .install-steps li:last-child { border-bottom: none; }
        .step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .android .step-num {
            background: rgba(61, 220, 132, 0.15);
            color: #3DDC84;
        }
        .ios .step-num {
            background: rgba(0, 122, 255, 0.15);
            color: #007AFF;
        }
        .install-steps li i {
            font-size: 12px;
            margin: 0 2px;
            opacity: 0.5;
        }
        .btn-install {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-install:hover { transform: translateY(-1px); }
        .btn-install.android-btn {
            background: linear-gradient(135deg, #3DDC84, #00C853);
            color: #0f172a;
            box-shadow: 0 4px 16px rgba(61, 220, 132, 0.3);
        }
        .btn-install.android-btn:hover {
            box-shadow: 0 6px 24px rgba(61, 220, 132, 0.4);
        }
        .btn-install.ios-btn {
            background: linear-gradient(135deg, #007AFF, #5856D6);
            color: #fff;
            box-shadow: 0 4px 16px rgba(0, 122, 255, 0.3);
        }
        .btn-install.ios-btn:hover {
            box-shadow: 0 6px 24px rgba(0, 122, 255, 0.4);
        }
        .btn-install:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ===== FEATURES ===== */
        .features-section {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .features-header {
            text-align: center;
            margin-bottom: 36px;
        }
        .features-header h2 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .features-header p {
            color: var(--gray);
            font-size: 14px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        @media (max-width: 768px) {
            .features-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .features-grid { grid-template-columns: 1fr; }
        }
        .feature-card {
            background: var(--dark-800);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.2s;
        }
        .feature-card:hover {
            border-color: rgba(99, 102, 241, 0.2);
            transform: translateY(-2px);
        }
        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-bottom: 14px;
            background: var(--primary-light);
            color: var(--primary);
        }
        .feature-card h4 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .feature-card p {
            font-size: 12px;
            color: var(--gray);
            line-height: 1.5;
        }

        /* ===== FAQ ===== */
        .faq-section {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px 20px 60px;
        }
        .faq-section h2 {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 28px;
        }
        .faq-item {
            background: var(--dark-800);
            border-radius: 12px;
            margin-bottom: 10px;
            border: 1px solid rgba(255,255,255,0.05);
            overflow: hidden;
        }
        .faq-question {
            padding: 16px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #e2e8f0;
            transition: color 0.2s;
        }
        .faq-question:hover { color: var(--primary); }
        .faq-question i {
            font-size: 12px;
            transition: transform 0.2s;
            color: var(--gray);
        }
        .faq-item.open .faq-question i { transform: rotate(180deg); color: var(--primary); }
        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            font-size: 13px;
            color: var(--gray);
            line-height: 1.6;
        }
        .faq-item.open .faq-answer {
            padding: 0 20px 16px;
            max-height: 200px;
        }

        /* ===== FOOTER ===== */
        .app-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.06);
            color: var(--gray);
            font-size: 13px;
        }
        .app-footer a {
            color: var(--primary);
            text-decoration: none;
        }
        .app-footer a:hover { text-decoration: underline; }

        /* ===== INSTALL BANNER (shown when PWA install is available) ===== */
        .pwa-install-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, var(--primary), #8b5cf6);
            color: #fff;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            box-shadow: 0 -4px 20px rgba(99, 102, 241, 0.4);
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        .pwa-install-banner.show {
            transform: translateY(0);
        }
        .pwa-install-banner-text {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .pwa-install-banner-text i { font-size: 20px; }
        .pwa-install-banner-text div { font-size: 14px; font-weight: 600; }
        .pwa-install-banner-text small { font-size: 11px; opacity: 0.8; display: block; }
        .btn-install-banner {
            padding: 8px 20px;
            border-radius: 8px;
            background: #fff;
            color: var(--primary);
            font-weight: 700;
            font-size: 13px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-install-banner:hover {
            background: #f0f0ff;
            transform: scale(1.02);
        }
        .btn-dismiss-banner {
            background: none;
            border: none;
            color: rgba(255,255,255,0.6);
            font-size: 16px;
            cursor: pointer;
            padding: 4px 8px;
            margin-left: 8px;
        }

        /* ===== DETECTION BANNER (for non-PWA browsers) ===== */
        .detect-banner {
            max-width: 800px;
            margin: 0 auto 20px;
            padding: 0 20px;
        }
        .detect-banner-inner {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #a5b4fc;
        }
        .detect-banner-inner i { font-size: 16px; color: var(--primary); }
        .detect-banner-inner strong { color: #fff; }
    </style>
</head>
<body>

{{-- NAVBAR --}}
<nav class="app-nav">
    <div class="app-nav-inner">
        <a href="{{ url('/') }}" class="app-nav-brand">
            <div class="app-nav-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="app-nav-text">School of <span>Redemption</span></div>
        </a>
        <a href="{{ route('login') }}" class="btn-nav-login">
            <i class="fas fa-sign-in-alt"></i> Login
        </a>
    </div>
</nav>

{{-- HERO --}}
<section class="hero">
    <div class="hero-icon-wrap">
        <div class="hero-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="hero-icon-ring"></div>
    </div>
    <h1>Get the <span>Redemption</span> App</h1>
    <p>Install the School of Redemption app on your phone for instant access to marks, attendance, finance, and everything you need.</p>
    <div class="hero-badges">
        <span class="hero-badge free"><i class="fas fa-check-circle"></i> Free</span>
        <span class="hero-badge secure"><i class="fas fa-shield-alt"></i> Secure</span>
        <span class="hero-badge"><i class="fas fa-bolt"></i> Fast</span>
        <span class="hero-badge"><i class="fas fa-wifi"></i> Works Offline</span>
    </div>
</section>

{{-- DETECTION BANNER --}}
<div class="detect-banner" id="detectBanner" style="display:none;">
    <div class="detect-banner-inner">
        <i class="fas fa-info-circle"></i>
        <span id="detectText"></span>
    </div>
</div>

{{-- INSTALL CARDS --}}
<section class="install-section">
    <div class="install-grid">
        {{-- ANDROID --}}
        <div class="install-card android">
            <div class="install-card-icon"><i class="fab fa-android"></i></div>
            <h3>For Android</h3>
            <p class="subtitle">Download & install the native app</p>
            <ul class="install-steps">
                <li>
                    <span class="step-num">1</span>
                    <span>Tap <strong>"Download APK"</strong> below to download the app</span>
                </li>
                <li>
                    <span class="step-num">2</span>
                    <span>Open the downloaded <strong>.apk</strong> file from your notification bar or Downloads</span>
                </li>
                <li>
                    <span class="step-num">3</span>
                    <span>If prompted, allow <strong>"Install from unknown sources"</strong> in Settings</span>
                </li>
                <li>
                    <span class="step-num">4</span>
                    <span>Tap <strong>"Install"</strong> then <strong>"Open"</strong> to launch the app</span>
                </li>
            </ul>
            <a href="{{ route('app.download.apk') }}" class="btn-install android-btn">
                <i class="fas fa-download"></i> Download APK (3 MB)
            </a>
            <div style="text-align:center;margin-top:12px;">
                <small style="color:var(--gray);font-size:11px;">Alternative: Open schoolofredemption.net/login in Chrome &rarr; Menu &rarr; "Install app"</small>
            </div>
        </div>

        {{-- iPHONE / iOS --}}
        <div class="install-card ios">
            <div class="install-card-icon"><i class="fab fa-apple"></i></div>
            <h3>For iPhone & iPad</h3>
            <p class="subtitle">Add to Home Screen from Safari</p>
            <ul class="install-steps">
                <li>
                    <span class="step-num">1</span>
                    <span>Open <strong>schoolofredemption.net/login</strong> in <strong>Safari</strong> browser</span>
                </li>
                <li>
                    <span class="step-num">2</span>
                    <span>Tap the <i class="fas fa-share-alt"></i> Share button at the bottom</span>
                </li>
                <li>
                    <span class="step-num">3</span>
                    <span>Scroll down and tap <strong>"Add to Home Screen"</strong></span>
                </li>
                <li>
                    <span class="step-num">4</span>
                    <span>Tap <strong>"Add"</strong> in the top right corner</span>
                </li>
            </ul>
            <a href="{{ url('/login') }}" class="btn-install ios-btn">
                <i class="fas fa-external-link-alt"></i> Open in Safari
            </a>
            <div style="text-align:center;margin-top:12px;">
                <small style="color:var(--gray);font-size:11px;">iOS native app coming soon to the App Store</small>
            </div>
        </div>
    </div>
</section>

{{-- FEATURES --}}
<section class="features-section">
    <div class="features-header">
        <h2>Everything You Need</h2>
        <p>Full access to all school management features right from your phone</p>
    </div>
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon"><i class="fas fa-pen-alt"></i></div>
            <h4>Mark Entry</h4>
            <p>Enter and manage student marks quickly with auto-save</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#d1fae5;color:#10b981;"><i class="fas fa-clipboard-check"></i></div>
            <h4>Attendance</h4>
            <p>Take attendance digitally with instant sync</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#dbeafe;color:#3b82f6;"><i class="fas fa-chart-line"></i></div>
            <h4>Performance</h4>
            <p>Track student progress with detailed analytics</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#fef3c7;color:#f59e0b;"><i class="fas fa-wallet"></i></div>
            <h4>Fee Management</h4>
            <p>View and manage fee payments and records</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#fce7f3;color:#ec4899;"><i class="fas fa-user-graduate"></i></div>
            <h4>Student Portal</h4>
            <p>Students can view marks, progress, and fees</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#e0e7ff;color:#6366f1;"><i class="fas fa-comments"></i></div>
            <h4>Communication</h4>
            <p>Chat, announcements, and calendar in one place</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-file-alt"></i></div>
            <h4>Report Cards</h4>
            <p>Generate and view report cards instantly</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-id-badge"></i></div>
            <h4>ID Cards & Certificates</h4>
            <p>Generate ID cards and certificates on the go</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon" style="background:#fee2e2;color:#ef4444;"><i class="fas fa-bell"></i></div>
            <h4>Notifications</h4>
            <p>Get push notifications for important updates</p>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="faq-section">
    <h2>Frequently Asked Questions</h2>
    <div class="faq-item open">
        <div class="faq-question" onclick="this.parentElement.classList.toggle('open')">
            Is the app free? <i class="fas fa-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Yes, the School of Redemption app is completely free. There are no in-app purchases or subscriptions required. Simply install it from your browser and log in with your school account.
        </div>
    </div>
    <div class="faq-item">
        <div class="faq-question" onclick="this.parentElement.classList.toggle('open')">
            Does it work offline? <i class="fas fa-chevron-down"></i>
        </div>
        <div class="faq-answer">
            The app caches key pages for faster loading. However, since the app requires real-time data (marks, attendance, etc.), an internet connection is needed for most features. Previously loaded pages may be viewable offline.
        </div>
    </div>
    <div class="faq-item">
        <div class="faq-question" onclick="this.parentElement.classList.toggle('open')">
            Is my data secure? <i class="fas fa-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Absolutely. The app uses the same security measures as the website, including encrypted connections (HTTPS), secure authentication, and role-based access control. Your data is protected at all times.
        </div>
    </div>
    <div class="faq-item">
        <div class="faq-question" onclick="this.parentElement.classList.toggle('open')">
            Will I get updates automatically? <i class="fas fa-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Yes! Since the app is a Progressive Web App (PWA), it updates automatically whenever you open it. There's no need to visit an app store or manually download updates. You always have the latest version.
        </div>
    </div>
    <div class="faq-item">
        <div class="faq-question" onclick="this.parentElement.classList.toggle('open')">
            Can I use it on my computer too? <i class="fas fa-chevron-down"></i>
        </div>
        <div class="faq-answer">
            Yes, you can install the app on desktop Chrome and Edge browsers too. It works the same way — just click the install icon in the browser address bar or use the menu to install it as a desktop application.
        </div>
    </div>
</section>

{{-- FOOTER --}}
<footer class="app-footer">
    &copy; {{ date('Y') }} School of Redemption. All rights reserved. <a href="{{ url('/') }}">Visit Website</a>
</footer>

{{-- PWA INSTALL BANNER --}}
<div class="pwa-install-banner" id="pwaInstallBanner">
    <div class="pwa-install-banner-text">
        <i class="fas fa-mobile-alt"></i>
        <div>
            Install Redemption App
            <small>Quick access from your home screen</small>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:4px;">
        <button class="btn-install-banner" onclick="installPWA()">Install</button>
        <button class="btn-dismiss-banner" onclick="dismissBanner()"><i class="fas fa-times"></i></button>
    </div>
</div>

{{-- SERVICE WORKER REGISTRATION --}}
<script>
    // Register service worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('{{ asset("sw.js") }}', { scope: '/' })
                .then(function(registration) {
                    console.log('[PWA] Service Worker registered:', registration.scope);
                })
                .catch(function(error) {
                    console.log('[PWA] Service Worker registration failed:', error);
                });
        });
    }

    // PWA Install Prompt
    let deferredPrompt = null;

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        
        // Show the install banner
        const banner = document.getElementById('pwaInstallBanner');
        if (banner) banner.classList.add('show');
        
        // Enable the Android install button
        const androidBtn = document.getElementById('androidInstallBtn');
        if (androidBtn) androidBtn.disabled = false;
        
        // Show detection banner
        const detectBanner = document.getElementById('detectBanner');
        const detectText = document.getElementById('detectText');
        if (detectBanner && detectText) {
            detectText.innerHTML = 'Great! Your browser supports app installation. <strong>Click "Install Now"</strong> below or use the banner at the bottom.';
            detectBanner.style.display = 'block';
        }
    });

    function installPWA() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('[PWA] User accepted the install prompt');
                    dismissBanner();
                } else {
                    console.log('[PWA] User dismissed the install prompt');
                }
                deferredPrompt = null;
            });
        } else {
            // Fallback: direct to login page so they can install from there
            window.location.href = '{{ url("/login") }}';
        }
    }

    function dismissBanner() {
        const banner = document.getElementById('pwaInstallBanner');
        if (banner) banner.classList.remove('show');
    }

    // Detect platform
    document.addEventListener('DOMContentLoaded', function() {
        const detectBanner = document.getElementById('detectBanner');
        const detectText = document.getElementById('detectText');
        
        if (!detectBanner || !detectText) return;
        
        const ua = navigator.userAgent;
        const isAndroid = /Android/i.test(ua);
        const isIOS = /iPhone|iPad|iPod/i.test(ua);
        const isSafari = /Safari/i.test(ua) && !/Chrome/i.test(ua);
        const isChrome = /Chrome/i.test(ua);
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        
        if (isStandalone) {
            detectText.innerHTML = 'You\'re already using the <strong>Redemption App</strong>! Enjoy the full experience.';
            detectBanner.style.display = 'block';
        } else if (isIOS && isSafari) {
            detectText.innerHTML = 'You\'re on iPhone/iPad. Use the <strong>Share button <i class="fas fa-share-alt"></i></strong> below, then tap <strong>"Add to Home Screen"</strong>.';
            detectBanner.style.display = 'block';
        } else if (isAndroid && isChrome) {
            // beforeinstallprompt will handle this
            if (!deferredPrompt) {
                detectText.innerHTML = 'Open this page in <strong>Chrome</strong> and tap the menu <i class="fas fa-ellipsis-v"></i> to install the app.';
                detectBanner.style.display = 'block';
            }
        } else if (isIOS && !isSafari) {
            detectText.innerHTML = 'For the best experience on iPhone, please open this page in <strong>Safari browser</strong>.';
            detectBanner.style.display = 'block';
        }
    });
</script>

</body>
</html>
