<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('app.school_name'))</title>

    {{-- PWA & Mobile Integration --}}
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Redemption">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')

    <style>
        :root {
            --navy-950: #0a0f1e;
            --navy-900: #0f172a;
            --navy-800: #1e293b;
            --navy-700: #334155;
            --navy-600: #475569;
            --amber-500: #f59e0b;
            --amber-400: #fbbf24;
            --amber-600: #d97706;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --indigo-500: #6366f1;
            --indigo-600: #4f46e5;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ===== NARROW TOP NAVBAR ===== */
        .top-bar {
            background: var(--navy-950);
            position: sticky;
            top: 0;
            z-index: 1050;
            border-bottom: 2px solid var(--amber-500);
        }

        .top-bar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 46px;
        }

        /* Brand: Logo LEFT of School Name */
        .brand {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #fff;
            flex-shrink: 0;
            transition: opacity .2s;
        }
        .brand:hover { color: #fff; opacity: .9; }

        .brand-logo {
            height: 30px;
            width: 30px;
            object-fit: contain;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .brand-text { line-height: 1.1; }
        .brand-pre {
            display: block;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .25em;
            color: var(--amber-400);
            font-weight: 700;
        }
        .brand-name {
            display: block;
            font-size: .95rem;
            font-weight: 800;
            letter-spacing: -.01em;
            color: #fff;
            line-height: 1.15;
        }

        /* Desktop nav */
        .nav-links {
            display: none;
            align-items: center;
            gap: 1px;
        }

        .nav-link-custom {
            color: var(--slate-300);
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: .8rem;
            font-weight: 500;
            transition: background .2s, color .2s;
            white-space: nowrap;
            letter-spacing: .01em;
        }
        .nav-link-custom:hover {
            background: rgba(255,255,255,.07);
            color: #fff;
        }
        .nav-link-custom i {
            font-size: .65rem;
            margin-right: 4px;
            opacity: .5;
        }

        /* Language switcher */
        .lang-dropdown {
            position: relative;
            margin-left: 6px;
        }
        .lang-btn {
            display: flex;
            align-items: center;
            gap: 4px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px;
            padding: 3px 10px;
            font-size: .7rem;
            font-weight: 600;
            color: var(--slate-200);
            cursor: pointer;
            transition: background .2s, border-color .2s;
        }
        .lang-btn:hover {
            background: rgba(255,255,255,.12);
            border-color: rgba(255,255,255,.15);
        }
        .lang-btn i.fa-globe { font-size: .6rem; }
        .lang-btn i.fa-chevron-down { font-size: .45rem; margin-left: 1px; }

        .lang-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 4px);
            min-width: 150px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 8px 30px rgba(0,0,0,.18);
            border: 1px solid #e2e8f0;
            padding: 3px 0;
            display: none;
            z-index: 999;
            animation: dropIn .12s ease-out;
        }
        .lang-menu.show { display: block; }

        @keyframes dropIn {
            from { opacity: 0; transform: translateY(-4px) scale(.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .lang-menu a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            font-size: .8rem;
            color: #334155;
            text-decoration: none;
            transition: background .15s;
        }
        .lang-menu a:hover { background: #f1f5f9; }
        .lang-menu a.active {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
        }
        .lang-menu a .check-icon { width: 12px; text-align: center; font-size: .65rem; }
        .lang-menu a .spacer { width: 12px; }

        /* Login button */
        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-left: 8px;
            padding: 4px 16px;
            border-radius: 16px;
            background: var(--amber-500);
            color: #fff;
            font-size: .78rem;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 1px 6px rgba(245,158,11,.35);
            transition: background .2s, box-shadow .2s, transform .15s;
        }
        .btn-login:hover {
            background: var(--amber-400);
            color: #fff;
            box-shadow: 0 2px 12px rgba(245,158,11,.45);
            transform: translateY(-1px);
        }
        .btn-login i { font-size: .6rem; }

        /* Mobile controls */
        .mobile-controls {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .mobile-lang-btn {
            display: flex;
            align-items: center;
            gap: 3px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px;
            padding: 3px 8px;
            font-size: .65rem;
            font-weight: 600;
            color: var(--slate-200);
            text-decoration: none;
            transition: background .2s;
        }
        .mobile-lang-btn:hover { background: rgba(255,255,255,.12); color: #fff; }
        .mobile-lang-btn i { font-size: .55rem; }

        .hamburger-btn {
            background: none;
            border: none;
            color: var(--slate-200);
            font-size: 1.1rem;
            padding: 3px 5px;
            cursor: pointer;
            border-radius: 4px;
            transition: background .2s, color .2s;
        }
        .hamburger-btn:hover { background: rgba(255,255,255,.07); color: #fff; }

        /* Mobile dropdown */
        .mobile-menu {
            display: none;
            background: var(--navy-800);
            border-top: 1px solid rgba(255,255,255,.05);
            padding: 10px 14px 14px;
            animation: slideDown .2s ease-out;
        }
        .mobile-menu.show { display: block; }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .mobile-menu a {
            display: block;
            padding: 9px 10px;
            border-radius: 6px;
            color: var(--slate-200);
            text-decoration: none;
            font-size: .85rem;
            font-weight: 500;
            transition: background .2s, color .2s;
        }
        .mobile-menu a:hover { background: rgba(255,255,255,.05); color: #fff; }
        .mobile-menu a i { font-size: .65rem; margin-right: 7px; opacity: .45; width: 14px; text-align: center; }

        .mobile-login-btn {
            display: block;
            margin-top: 8px;
            padding: 9px 14px;
            border-radius: 16px;
            background: var(--amber-500);
            color: #fff;
            text-align: center;
            font-size: .85rem;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 1px 6px rgba(245,158,11,.3);
            transition: background .2s;
        }
        .mobile-login-btn:hover { background: var(--amber-400); color: #fff; }
        .mobile-login-btn i { font-size: .65rem; margin-right: 4px; }

        /* Desktop only */
        @media (min-width: 992px) {
            .nav-links { display: flex; }
            .mobile-controls { display: none; }
        }

        /* ===== MAIN ===== */
        main {
            flex: 1;
            padding: 2rem 1rem;
        }
        @media (min-width: 640px) { main { padding: 2rem 1.5rem; } }
        @media (min-width: 1024px) { main { padding: 2.5rem 2rem; } }

        /* ===== FOOTER ===== */
        .site-footer {
            background: var(--navy-950);
            color: var(--slate-300);
        }
        .footer-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 3.5rem 1.5rem 1.5rem;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        @media (min-width: 768px) { .footer-grid { grid-template-columns: 1fr 1fr; } }
        @media (min-width: 992px) { .footer-grid { grid-template-columns: 1.4fr 1fr 1fr 1.2fr; } }

        .footer-heading {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.05rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: .85rem;
        }
        .footer-heading img {
            height: 26px;
            object-fit: contain;
            border-radius: 3px;
        }

        .footer-about-text {
            font-size: .875rem;
            line-height: 1.75;
            color: var(--slate-400);
        }

        .social-links {
            display: flex;
            gap: 8px;
            margin-top: 1rem;
        }
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            background: rgba(255,255,255,.05);
            color: var(--slate-400);
            font-size: .8rem;
            transition: background .2s, color .2s, transform .15s;
            text-decoration: none;
        }
        .social-links a:hover {
            background: rgba(255,255,255,.1);
            color: #fff;
            transform: translateY(-2px);
        }

        .footer-col-heading {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
            position: relative;
            padding-bottom: .6rem;
        }
        .footer-col-heading::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 24px;
            height: 2px;
            background: var(--amber-500);
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-links li { margin-bottom: .5rem; }
        .footer-links a {
            color: var(--slate-400);
            text-decoration: none;
            font-size: .875rem;
            transition: color .2s, padding-left .2s;
        }
        .footer-links a:hover { color: #fff; padding-left: 4px; }

        .footer-contact-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-contact-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: .7rem;
            font-size: .875rem;
            color: var(--slate-400);
        }
        .footer-contact-list li i {
            color: var(--amber-500);
            margin-top: 3px;
            font-size: .75rem;
            width: 14px;
            text-align: center;
            flex-shrink: 0;
        }

        .footer-bottom {
            margin-top: 2.5rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255,255,255,.06);
            text-align: center;
            font-size: .78rem;
            color: var(--slate-400);
        }
    </style>
</head>

<body>

    {{-- ===== NARROW TOP NAVIGATION ===== --}}
    <nav class="top-bar" id="topNav">
        <div class="top-bar-inner">
            {{-- Logo LEFT of School Name --}}
            <a href="{{ url('/') }}" class="brand">
                @php $logoUrl = \App\Models\Setting::getLogoUrl(); @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ __('app.school_name') }}" class="brand-logo">
                @endif
                <div class="brand-text">
                    <span class="brand-pre">{{ __('app.brand_pre') }}</span>
                    <span class="brand-name">{{ __('app.brand_name') }}</span>
                </div>
            </a>

            {{-- Desktop Nav Links + Lang + Login --}}
            <div class="nav-links">
                <a class="nav-link-custom" href="{{ url('/') }}">
                    <i class="fas fa-home"></i>{{ __('app.home') }}
                </a>
                <a class="nav-link-custom" href="{{ url('about') }}">
                    <i class="fas fa-info-circle"></i>{{ __('app.about') }}
                </a>
                <a class="nav-link-custom" href="{{ url('gallery') }}">
                    <i class="fas fa-images"></i>{{ __('app.gallery') }}
                </a>
                <a class="nav-link-custom" href="{{ url('contact') }}">
                    <i class="fas fa-envelope"></i>{{ __('app.contact') }}
                </a>
                <a class="nav-link-custom" href="{{ url('team') }}">
                    <i class="fas fa-users"></i>{{ __('app.team') }}
                </a>

                {{-- Language Switcher --}}
                <div class="lang-dropdown">
                    <button class="lang-btn" onclick="toggleLangMenu()" aria-label="Switch language">
                        <i class="fas fa-globe"></i>
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="lang-menu" id="langMenu">
                        @foreach(config('app.available_locales') as $code => $name)
                            <a href="{{ route('lang.switch', $code) }}"
                               class="{{ app()->getLocale() === $code ? 'active' : '' }}">
                                @if(app()->getLocale() === $code)
                                    <span class="check-icon"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="spacer"></span>
                                @endif
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Login Button --}}
                <a href="{{ url('login') }}" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>{{ __('app.login') }}
                </a>
                <a href="{{ route('app.download') }}" class="btn-login" style="background:var(--indigo-500);box-shadow:0 1px 6px rgba(99,102,241,.35);">
                    <i class="fas fa-mobile-alt"></i>Get App
                </a>
            </div>

            {{-- Mobile Controls --}}
            <div class="mobile-controls">
                <a href="{{ route('lang.switch', app()->getLocale() === 'en' ? 'am' : 'en') }}"
                   class="mobile-lang-btn">
                    <i class="fas fa-globe"></i>
                    {{ app()->getLocale() === 'en' ? 'አማ' : 'EN' }}
                </a>
                <button class="hamburger-btn" onclick="toggleMobileMenu()" aria-label="Toggle menu" id="hamburgerBtn">
                    <i class="fas fa-bars" id="menuIconOpen"></i>
                    <i class="fas fa-times" id="menuIconClose" style="display:none;"></i>
                </button>
            </div>
        </div>

        {{-- Mobile Dropdown --}}
        <div class="mobile-menu" id="mobileMenu">
            <a href="{{ url('/') }}"><i class="fas fa-home"></i>{{ __('app.home') }}</a>
            <a href="{{ url('about') }}"><i class="fas fa-info-circle"></i>{{ __('app.about') }}</a>
            <a href="{{ url('gallery') }}"><i class="fas fa-images"></i>{{ __('app.gallery') }}</a>
            <a href="{{ url('contact') }}"><i class="fas fa-envelope"></i>{{ __('app.contact') }}</a>
            <a href="{{ url('team') }}"><i class="fas fa-users"></i>{{ __('app.team') }}</a>
            <a href="{{ url('login') }}" class="mobile-login-btn">
                <i class="fas fa-sign-in-alt"></i>{{ __('app.login') }}
            </a>
            <a href="{{ route('app.download') }}" class="mobile-login-btn" style="margin-top:8px;background:var(--indigo-500);box-shadow:0 1px 6px rgba(99,102,241,.3);">
                <i class="fas fa-mobile-alt"></i>Get the App
            </a>
        </div>
    </nav>

    {{-- ===== MAIN CONTENT ===== --}}
    <main>
        @yield('content')
    </main>

    {{-- ===== FOOTER ===== --}}
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-grid">
                {{-- School Info --}}
                <div>
                    <h5 class="footer-heading">
                        @php $logoUrl = \App\Models\Setting::getLogoUrl(); @endphp
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ __('app.school_name') }}">
                        @endif
                        <span>{{ __('app.school_name') }}</span>
                    </h5>
                    <p class="footer-about-text">{{ __('app.footer_about') }}</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h5 class="footer-col-heading">{{ __('app.quick_links') }}</h5>
                    <ul class="footer-links">
                        <li><a href="{{ url('/') }}">{{ __('app.home') }}</a></li>
                        <li><a href="{{ url('about') }}">{{ __('app.about') }}</a></li>
                        <li><a href="{{ url('gallery') }}">{{ __('app.gallery') }}</a></li>
                        <li><a href="{{ url('contact') }}">{{ __('app.contact') }}</a></li>
                    </ul>
                </div>

                {{-- Academics --}}
                <div>
                    <h5 class="footer-col-heading">{{ __('app.academics') }}</h5>
                    <ul class="footer-links">
                        <li><a href="#">{{ __('app.programs') }}</a></li>
                        <li><a href="{{ url('contact') }}">{{ __('app.admissions') }}</a></li>
                        <li><a href="#">{{ __('app.calendar') }}</a></li>
                        <li><a href="#">{{ __('app.results') }}</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h5 class="footer-col-heading">{{ __('app.contact_us') }}</h5>
                    <ul class="footer-contact-list">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Education St, City</li>
                        <li><i class="fas fa-phone"></i> +251-XXX-XXXXXX</li>
                        <li><i class="fas fa-envelope"></i> info@schoolofredemption.com</li>
                        <li><i class="fas fa-clock"></i> Mon-Fri: 8AM-5PM</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                &copy; {{ date('Y') }} {{ __('app.school_name') }}. {{ __('app.all_rights_reserved') }}
            </div>
        </div>
    </footer>

    {{-- Custom JS for menu toggles --}}
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const iconOpen = document.getElementById('menuIconOpen');
            const iconClose = document.getElementById('menuIconClose');
            menu.classList.toggle('show');
            iconOpen.style.display = menu.classList.contains('show') ? 'none' : 'inline';
            iconClose.style.display = menu.classList.contains('show') ? 'inline' : 'none';
        }

        function toggleLangMenu() {
            document.getElementById('langMenu').classList.toggle('show');
        }

        // Close language menu when clicking outside
        document.addEventListener('click', function(e) {
            const langDropdown = document.querySelector('.lang-dropdown');
            const langMenu = document.getElementById('langMenu');
            if (langDropdown && !langDropdown.contains(e.target)) {
                langMenu.classList.remove('show');
            }
        });
    </script>

    @stack('scripts')
</body>

</html>
