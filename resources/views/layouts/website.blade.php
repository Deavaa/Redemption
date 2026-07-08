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
    <meta name="theme-color" content="#047857">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Redemption">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    <!-- Preconnect for faster CDN loading -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        /* ===== PAGE-HERO — inline so Blade processes the PHP color vars directly.
           This bypasses all external CSS caching issues and CSS variable resolution.
           Uses the SAME primary color as the rest of the page content. ===== */
        .page-hero {
            background: linear-gradient(135deg,
                rgba({{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }}, 0.96) 0%,
                rgba({{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }}, 0.82) 100%) !important;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/design-tokens.css') }}?v={{ filemtime(public_path('css/design-tokens.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/website.css') }}?v={{ filemtime(public_path('css/website.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modern-glass.css') }}?v={{ filemtime(public_path('css/modern-glass.css')) }}">
    @stack('styles')
</head>
<body>
    <!-- Custom Cursor (Desktop only) -->
    <div class="cursor-dot" id="cursorDot"></div>
    <div class="cursor-ring" id="cursorRing"></div>

    @yield('before-nav')

    <!-- ========== Modern Vibrant Gradient Navbar ========== -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="navbar">
        <div class="container-fluid nav-container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
                @if($settings['school_logo'] ?? '')
                    <img src="{{ asset('storage/' . $settings['school_logo']) }}" alt="{{ $settings['school_name'] }}" style="height: 42px; width: 42px; object-fit: contain; background: #ffffff; border-radius: 10px; padding: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); opacity: 1;" loading="lazy" onerror="this.style.display='none'">
                @endif
                <div>
                    <span class="brand-pre">{{ Str::beforeLast($settings['school_name'] ?? 'School', ' ') }}</span>
                    <span class="brand-name">{{ Str::afterLast($settings['school_name'] ?? 'School', ' ') }}</span>
                </div>
            </a>
            <!-- Mobile Login Button (visible on mobile only) -->
            <a href="{{ route('login') }}" class="mobile-login-pill" id="mobileLoginPill">
                <i class="fas fa-sign-in-alt"></i>
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
                        <a class="nav-link @if(request()->routeIs('gallery')) active @endif" href="{{ route('gallery') }}">Gallery</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('team')) active @endif" href="{{ route('team') }}">Our Team</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('contact')) active @endif" href="{{ route('contact') }}">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
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
            <li><a href="{{ route('gallery') }}" @if(request()->routeIs('gallery')) class="active" @endif>Gallery</a></li>
            <li><a href="{{ route('team') }}" @if(request()->routeIs('team')) class="active" @endif>Our Team</a></li>
            <li><a href="{{ route('contact') }}" @if(request()->routeIs('contact')) class="active" @endif>Contact</a></li>
        </ul>
        <a href="{{ route('login') }}" class="mobile-login-btn">
            <i class="fas fa-sign-in-alt me-2"></i>Login
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
                        @if($settings['school_logo'] ?? '')
                            <img src="{{ asset('storage/' . $settings['school_logo']) }}" alt="{{ $settings['school_name'] }}" style="height: 38px; width: 38px; object-fit: contain; background: #ffffff; border-radius: 8px; padding: 3px; opacity: 1;" onerror="this.style.display='none'">
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
                <!-- Quick Links -->
                <div class="col-lg-3 col-md-6">
                    <h5>Explore</h5>
                    <ul class="footer-links">
                        <li><a href="{{ route('about') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> About Us</a></li>
                        <li><a href="{{ route('gallery') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> Gallery</a></li>
                        <li><a href="{{ route('team') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> Our Team</a></li>
                        <li><a href="{{ route('contact') }}"><i class="fas fa-chevron-right me-1" style="font-size:0.6rem"></i> Contact</a></li>
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

    <!-- Bootstrap 5 JS (deferred for performance) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

    <script src="{{ asset('js/website.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
