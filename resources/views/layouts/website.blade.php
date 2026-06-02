@php
    $primaryHex = $settings['primary_color'] ?? '#1B5E20';
    $primaryR = hexdec(substr($primaryHex, 1, 2));
    $primaryG = hexdec(substr($primaryHex, 3, 2));
    $primaryB = hexdec(substr($primaryHex, 5, 2));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $settings['school_name'] ?? 'School' }} - {{ $settings['school_tagline'] ?? '' }}">
    <title>@yield('title', ($settings['school_name'] ?? 'School') . ' - ' . ($settings['school_tagline'] ?? ''))</title>

    {{-- PWA & Mobile Integration --}}
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Redemption">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: {{ $primaryHex }};
            --secondary-color: {{ $settings['secondary_color'] ?? '#D4A017' }};
            --primary-rgb: {{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }};
            --accent-color: #2E7D32;
            --text-dark: #1a1a2e;
            --text-light: #6c757d;
            --white: #ffffff;
            --light-bg: #f8f9fa;
            --glass-bg: rgba(255,255,255,0.08);
            --glass-border: rgba(255,255,255,0.18);
            --glass-shadow: 0 8px 32px rgba(0,0,0,0.12);
            --nav-height: 80px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        h1, h2 {
            letter-spacing: 0.5px;
        }

        /* ========== Custom Cursor ========== */
        .cursor-dot {
            width: 8px;
            height: 8px;
            background: var(--secondary-color);
            border-radius: 50%;
            position: fixed;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 99999;
            transition: transform 0.1s ease;
            mix-blend-mode: difference;
        }

        .cursor-ring {
            width: 36px;
            height: 36px;
            border: 2px solid rgba(212, 160, 23, 0.5);
            border-radius: 50%;
            position: fixed;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 99998;
            transition: transform 0.15s ease, width 0.2s, height 0.2s, border-color 0.2s;
        }

        @media (max-width: 991px) {
            .cursor-dot, .cursor-ring { display: none !important; }
        }

        @media (prefers-reduced-motion: reduce) {
            .cursor-dot, .cursor-ring { display: none !important; }
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        /* ========== Elegant Dark Green Navbar ========== */
        .navbar {
            background: rgba({{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }}, 0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 2px solid rgba(212, 160, 23, 0.3);
            padding: 1rem 0;
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
        }

        .navbar.scrolled {
            padding: 0.5rem 0;
            background: rgba({{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }}, 0.98);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 4px 30px rgba(0,0,0,0.2);
            border-bottom-color: rgba(212, 160, 23, 0.5);
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            line-height: 1;
        }

        .navbar-brand .brand-pre {
            color: rgba(255,255,255,0.8);
            font-weight: 400;
            font-size: 0.7rem;
            display: block;
            line-height: 1.1;
            letter-spacing: 1px;
        }

        .navbar-brand .brand-name {
            color: var(--secondary-color);
            font-weight: 800;
            font-size: 1.3rem;
        }

        .nav-link {
            color: rgba(255,255,255,0.85) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--secondary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--secondary-color), transparent);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 80%;
        }

        .nav-link.active {
            color: var(--secondary-color) !important;
        }

        .nav-link.active::after {
            width: 80%;
        }

        .btn-nav-portal {
            background: var(--secondary-color);
            color: var(--primary-color) !important;
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-nav-portal:hover {
            background: #E8B82E;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(212, 160, 23, 0.5);
        }

        /* ========== Mobile Drawer ========== */
        .mobile-drawer-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1049;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .mobile-drawer-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .mobile-drawer {
            position: fixed;
            top: 0;
            right: -320px;
            width: 300px;
            max-width: 85vw;
            height: 100vh;
            background: var(--primary-color);
            z-index: 1050;
            transition: right 0.4s cubic-bezier(0.4,0,0.2,1);
            padding: 2rem;
            overflow-y: auto;
        }

        .mobile-drawer.active {
            right: 0;
        }

        .mobile-drawer-close {
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            position: absolute;
            top: 1rem;
            right: 1rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mobile-drawer .mobile-nav-links {
            list-style: none;
            padding: 0;
            margin-top: 3rem;
        }

        .mobile-drawer .mobile-nav-links li {
            margin-bottom: 0.25rem;
        }

        .mobile-drawer .mobile-nav-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 500;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .mobile-drawer .mobile-nav-links a:hover,
        .mobile-drawer .mobile-nav-links a.active {
            background: rgba(255,255,255,0.1);
            color: var(--secondary-color);
        }

        .mobile-drawer .mobile-login-btn {
            display: block;
            margin-top: 1.5rem;
            background: var(--secondary-color);
            color: var(--primary-color);
            text-align: center;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .mobile-drawer .mobile-login-btn:hover {
            background: #E8B82E;
        }

        .hamburger-btn {
            display: none;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        @media (max-width: 991px) {
            .hamburger-btn { display: block; }
            .navbar-collapse { display: none !important; }
        }

        /* ========== Page Hero Banner (for sub-pages) ========== */
        .page-hero {
            position: relative;
            padding: 10rem 0 4rem;
            background: linear-gradient(135deg, rgba({{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }}, 0.92) 0%, rgba({{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }}, 0.75) 100%);
            color: var(--white);
            overflow: hidden;
        }

        .page-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(rgba(212, 160, 23, 0.06) 1px, transparent 1px);
            background-size: 30px 30px;
            pointer-events: none;
        }

        .page-hero::after {
            content: '✦';
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(212, 160, 23, 0.3);
            font-size: 1.5rem;
            letter-spacing: 1rem;
            pointer-events: none;
        }

        .page-hero h1 {
            font-size: 3rem;
            color: var(--white);
            margin-bottom: 0.75rem;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .page-hero h1 span {
            color: var(--secondary-color);
        }

        .page-hero p {
            font-size: 1.15rem;
            color: rgba(255,255,255,0.85);
            margin-bottom: 1rem;
        }

        .page-hero .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }

        .page-hero .breadcrumb-item a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .page-hero .breadcrumb-item a:hover {
            color: var(--secondary-color);
        }

        .page-hero .breadcrumb-item.active {
            color: var(--secondary-color);
        }

        .page-hero .breadcrumb-item + .breadcrumb-item::before {
            color: rgba(255,255,255,0.5);
        }

        /* ========== Section Headers ========== */
        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-badge {
            display: inline-block;
            background: rgba(212, 160, 23, 0.1);
            color: var(--secondary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: 1px solid rgba(212, 160, 23, 0.2);
        }

        .section-header h2 {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            letter-spacing: 0.5px;
        }

        .section-header h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 2px;
            background: var(--secondary-color);
            margin: 0.75rem auto 0;
        }

        .section-header p {
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        /* ========== Ornamental Divider ========== */
        .ornament-divider {
            text-align: center;
            margin: 2rem 0;
            color: var(--secondary-color);
            font-size: 1.2rem;
            letter-spacing: 0.5rem;
            opacity: 0.6;
        }
        .ornament-divider::before,
        .ornament-divider::after {
            content: '';
            display: inline-block;
            width: 60px;
            height: 1px;
            background: var(--secondary-color);
            vertical-align: middle;
            margin: 0 1rem;
        }

        /* ========== Footer (Elegant Layout) ========== */
        .footer {
            background: linear-gradient(180deg, var(--primary-color) 0%, #0D3B12 100%);
            color: var(--white);
            padding: 4rem 0 2rem;
            border-top: 3px solid rgba(212, 160, 23, 0.3);
        }

        .footer-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .footer-brand .brand-pre {
            font-weight: 400;
        }

        .footer-brand .brand-name {
            color: var(--secondary-color);
            font-weight: 700;
        }

        .footer p {
            color: rgba(255,255,255,0.7);
            font-size: 0.9rem;
        }

        .footer h5 {
            color: var(--white);
            font-size: 1rem;
            margin-bottom: 1.5rem;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }

        .footer-links a:hover {
            color: var(--secondary-color);
            transform: translateX(4px);
        }

        .social-links {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--secondary-color);
            color: var(--primary-color);
            transform: translateY(-3px);
            border-color: var(--secondary-color);
        }

        /* Newsletter */
        .newsletter-form {
            display: flex;
            gap: 0.5rem;
        }

        .newsletter-form input {
            flex: 1;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 0.65rem 1rem;
            color: var(--white);
            font-size: 0.9rem;
        }

        .newsletter-form input::placeholder {
            color: rgba(255,255,255,0.4);
        }

        .newsletter-form input:focus {
            outline: none;
            border-color: var(--secondary-color);
        }

        .newsletter-form button {
            background: var(--secondary-color);
            color: var(--primary-color);
            border: none;
            border-radius: 12px;
            padding: 0.65rem 1.25rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .newsletter-form button:hover {
            background: #E8B82E;
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(212, 160, 23, 0.2);
            padding-top: 2rem;
            margin-top: 3rem;
        }

        .footer-bottom p {
            margin: 0;
            font-size: 0.85rem;
        }

        /* ========== Back to Top Button ========== */
        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 50px;
            height: 50px;
            background: var(--secondary-color);
            color: var(--primary-color);
            border: none;
            border-radius: 14px;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.4s ease;
            box-shadow: 0 6px 20px rgba(212, 160, 23, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            background: #E8B82E;
            transform: translateY(-3px);
        }

        /* ========== Scroll Reveal ========== */
        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.4,0,0.2,1);
        }

        .reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-left {
            opacity: 0;
            transform: translateX(-40px);
            transition: all 0.8s cubic-bezier(0.4,0,0.2,1);
        }

        .reveal-left.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .reveal-right {
            opacity: 0;
            transform: translateX(40px);
            transition: all 0.8s cubic-bezier(0.4,0,0.2,1);
        }

        .reveal-right.revealed {
            opacity: 1;
            transform: translateX(0);
        }

        .reveal-scale {
            opacity: 0;
            transform: scale(0.9);
            transition: all 0.8s cubic-bezier(0.4,0,0.2,1);
        }

        .reveal-scale.revealed {
            opacity: 1;
            transform: scale(1);
        }

        /* ========== Responsive ========== */
        @media (max-width: 991px) {
            .page-hero h1 {
                font-size: 2.25rem;
            }

            .section-header h2 {
                font-size: 2rem;
            }
        }

        @media (max-width: 575px) {
            .page-hero h1 {
                font-size: 1.85rem;
            }

            .section-header h2 {
                font-size: 1.75rem;
            }

            .newsletter-form {
                flex-direction: column;
            }
        }

        /* ========== Animations ========== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Custom Cursor (Desktop only) -->
    <div class="cursor-dot" id="cursorDot"></div>
    <div class="cursor-ring" id="cursorRing"></div>

    @yield('before-nav')

    <!-- ========== Elegant Dark Green Navbar ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                @if(($settings['school_logo'] ?? '') && file_exists(public_path('storage/' . $settings['school_logo'])))
                    <img src="{{ asset('storage/' . $settings['school_logo']) }}" alt="{{ $settings['school_name'] }}" style="height: 45px; opacity: 0.85;">
                @endif
                <div>
                    <span class="brand-pre">{{ Str::beforeLast($settings['school_name'] ?? 'School', ' ') }}</span>
                    <span class="brand-name">{{ Str::afterLast($settings['school_name'] ?? 'School', ' ') }}</span>
                </div>
            </a>
            <!-- Desktop nav -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('about')) active @endif" href="{{ route('about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#programs">Programs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('gallery')) active @endif" href="{{ route('gallery') }}">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('team')) active @endif" href="{{ route('team') }}">Our Team</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('contact')) active @endif" href="{{ route('contact') }}">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-nav-portal" href="{{ route('app.download') }}">
                            <i class="fas fa-mobile-alt me-2"></i>Get App
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-nav-portal" href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Hamburger for mobile -->
            <button class="hamburger-btn" id="hamburgerBtn" type="button" aria-label="Open menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Drawer Overlay -->
    <div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>
    <!-- Mobile Drawer -->
    <div class="mobile-drawer" id="mobileDrawer">
        <button class="mobile-drawer-close" id="mobileDrawerClose" aria-label="Close menu">
            <i class="fas fa-times"></i>
        </button>
        <ul class="mobile-nav-links">
            <li><a href="{{ route('home') }}" @if(request()->routeIs('home')) class="active" @endif>Home</a></li>
            <li><a href="{{ route('about') }}" @if(request()->routeIs('about')) class="active" @endif>About</a></li>
            <li><a href="{{ route('home') }}#programs">Programs</a></li>
            <li><a href="{{ route('gallery') }}" @if(request()->routeIs('gallery')) class="active" @endif>Gallery</a></li>
            <li><a href="{{ route('team') }}" @if(request()->routeIs('team')) class="active" @endif>Our Team</a></li>
            <li><a href="{{ route('contact') }}" @if(request()->routeIs('contact')) class="active" @endif>Contact</a></li>
        </ul>
        <a href="{{ route('login') }}" class="mobile-login-btn">
            <i class="fas fa-sign-in-alt me-2"></i>Login
        </a>
        <a href="{{ route('app.download') }}" class="mobile-login-btn" style="margin-top:8px;background:#6366f1;">
            <i class="fas fa-mobile-alt me-2"></i>Get the App
        </a>
    </div>

    @yield('after-nav')

    <!-- ========== Main Content ========== -->
    <main>
        @yield('content')
    </main>

    <!-- ========== Footer (Modern Layout) ========== -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <!-- Brand Column -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand d-flex align-items-center gap-2">
                        @if(($settings['school_logo'] ?? '') && file_exists(public_path('storage/' . $settings['school_logo'])))
                            <img src="{{ asset('storage/' . $settings['school_logo']) }}" alt="{{ $settings['school_name'] }}" style="height: 40px; opacity: 0.85;">
                        @endif
                        <div>
                            <span class="brand-pre">{{ Str::beforeLast($settings['school_name'] ?? 'School', ' ') }}</span>
                            <span class="brand-name"> {{ Str::afterLast($settings['school_name'] ?? 'School', ' ') }}</span>
                        </div>
                    </div>
                    <p>{{ $settings['school_description'] ?? 'Nurturing each student\'s potential through excellence in education, character development, and innovative learning.' }}</p>
                    <div class="social-links">
                        @if($settings['facebook_url'] ?? '')
                            <a href="{{ $settings['facebook_url'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        @endif
                        @if($settings['twitter_url'] ?? '')
                            <a href="{{ $settings['twitter_url'] }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        @endif
                        @if($settings['youtube_url'] ?? '')
                            <a href="{{ $settings['youtube_url'] }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        @endif
                        @if($settings['telegram_url'] ?? '')
                            <a href="{{ $settings['telegram_url'] }}" target="_blank" rel="noopener" aria-label="Telegram"><i class="fab fa-telegram-plane"></i></a>
                        @endif
                        @if($settings['instagram_url'] ?? '')
                            <a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        @endif
                        @if($settings['linkedin_url'] ?? '')
                            <a href="{{ $settings['linkedin_url'] }}" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        @endif
                    </div>
                </div>
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">
                    <h5>Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> Home</a></li>
                        <li><a href="{{ route('about') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> About Us</a></li>
                        <li><a href="{{ route('gallery') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> Gallery</a></li>
                        <li><a href="{{ route('team') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> Our Team</a></li>
                        <li><a href="{{ route('contact') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> Contact</a></li>
                    </ul>
                </div>
                <!-- Programs -->
                <div class="col-lg-3 col-md-6">
                    <h5>Programs</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}#programs"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> Kindergarten</a></li>
                        <li><a href="{{ route('home') }}#programs"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> Primary School</a></li>
                        <li><a href="{{ route('home') }}#programs"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> Secondary School</a></li>
                        <li><a href="{{ route('home') }}#programs"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> High School</a></li>
                    </ul>
                </div>
                <!-- Newsletter -->
                <div class="col-lg-3 col-md-6">
                    <h5>Newsletter</h5>
                    <p style="margin-bottom:1rem">Subscribe to our newsletter for the latest updates.</p>
                    <form class="newsletter-form" onsubmit="event.preventDefault();">
                        <input type="email" placeholder="Your email address">
                        <button type="submit"><i class="fas fa-paper-plane"></i></button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center">
                <p>&copy; {{ date('Y') }} {{ $settings['footer_text'] ?? ($settings['school_name'] ?? 'School') }}. All rights reserved.</p>
                <p>
                    <a href="{{ route('home') }}" style="color:rgba(255,255,255,0.5);text-decoration:none">Privacy Policy</a>
                    <span style="color:rgba(255,255,255,0.3);margin:0 0.5rem">|</span>
                    <a href="{{ route('home') }}" style="color:rgba(255,255,255,0.5);text-decoration:none">Terms of Service</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ========== Navbar Scroll Shrink ==========
        (function() {
            var navbar = document.getElementById('navbar');
            if (!navbar) return;
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        })();

        // ========== Scroll Reveal ==========
        (function() {
            var revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
            if (!revealElements.length) return;
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
            revealElements.forEach(function(el) { observer.observe(el); });
        })();

        // ========== Mobile Drawer ==========
        (function() {
            var hamburgerBtn = document.getElementById('hamburgerBtn');
            var mobileDrawer = document.getElementById('mobileDrawer');
            var mobileDrawerOverlay = document.getElementById('mobileDrawerOverlay');
            var mobileDrawerClose = document.getElementById('mobileDrawerClose');

            function closeMobileDrawer() {
                var mobileDrawer = document.getElementById('mobileDrawer');
                var mobileDrawerOverlay = document.getElementById('mobileDrawerOverlay');
                if (mobileDrawer) {
                    mobileDrawer.classList.remove('active');
                    mobileDrawerOverlay.classList.remove('active');
                }
            }

            if (hamburgerBtn && mobileDrawer) {
                hamburgerBtn.addEventListener('click', function() {
                    mobileDrawer.classList.add('active');
                    mobileDrawerOverlay.classList.add('active');
                });
                mobileDrawerClose.addEventListener('click', closeMobileDrawer);
                mobileDrawerOverlay.addEventListener('click', closeMobileDrawer);

                mobileDrawer.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', closeMobileDrawer);
                });
            }
        })();

        // ========== Back to Top ==========
        (function() {
            var backToTop = document.getElementById('backToTop');
            if (!backToTop) return;
            window.addEventListener('scroll', function() {
                if (window.scrollY > 500) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            });
            backToTop.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        })();

        // ========== Custom Cursor (Desktop only) ==========
        (function() {
            if (window.innerWidth <= 991) return;
            var dot = document.getElementById('cursorDot');
            var ring = document.getElementById('cursorRing');
            if (!dot || !ring) return;

            var mouseX = 0, mouseY = 0;
            var ringX = 0, ringY = 0;

            document.addEventListener('mousemove', function(e) {
                mouseX = e.clientX;
                mouseY = e.clientY;
                dot.style.left = mouseX + 'px';
                dot.style.top = mouseY + 'px';
            });

            function animateRing() {
                ringX += (mouseX - ringX) * 0.15;
                ringY += (mouseY - ringY) * 0.15;
                ring.style.left = ringX + 'px';
                ring.style.top = ringY + 'px';
                requestAnimationFrame(animateRing);
            }
            animateRing();

            // Hover effect on interactive elements
            var interactiveElements = document.querySelectorAll('a, button, .btn, input, textarea, select');
            interactiveElements.forEach(function(el) {
                el.addEventListener('mouseenter', function() {
                    ring.style.width = '50px';
                    ring.style.height = '50px';
                    ring.style.borderColor = 'rgba(212, 160, 23, 0.6)';
                    dot.style.transform = 'scale(1.5)';
                });
                el.addEventListener('mouseleave', function() {
                    ring.style.width = '36px';
                    ring.style.height = '36px';
                    ring.style.borderColor = 'rgba(212, 160, 23, 0.5)';
                    dot.style.transform = 'scale(1)';
                });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
