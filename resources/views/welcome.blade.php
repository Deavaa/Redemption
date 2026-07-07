@extends('layouts.website')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/homepage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modern-glass.css') }}">
@endpush

@section('before-nav')
    <!-- Announcement Ticker Bar -->
    <div id="announcementTicker" style="display:none;background:linear-gradient(135deg,#1B5E20 0%,#0D3B12 100%);color:#fff;overflow:hidden;position:fixed;top:0;left:0;right:0;z-index:9999;">
        <div style="display:flex;align-items:center;height:36px;">
            <div style="background:rgba(255,255,255,0.18);padding:0 14px;height:100%;display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;flex-shrink:0;border-right:1px solid rgba(255,255,255,0.15);">
                <i class="fas fa-bullhorn" style="font-size:13px;"></i> <span>Announcements</span>
            </div>
            <div style="flex:1;overflow:hidden;position:relative;height:100%;">
                <div id="tickerTrack" style="display:flex;align-items:center;height:100%;white-space:nowrap;"></div>
            </div>
            <button id="tickerClose" style="background:rgba(255,255,255,0.12);border:none;color:#fff;width:36px;height:100%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;border-left:1px solid rgba(255,255,255,0.15);" title="Dismiss"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <style>
    @keyframes ticker-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
    .ticker-item { display:inline-flex;align-items:center;gap:8px;padding:0 24px;font-size:13px;font-weight:500;white-space:nowrap; }
    .ticker-dot { width:6px;height:6px;border-radius:50%;flex-shrink:0; }
    .ticker-cat { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;opacity:0.7; }
    .ticker-date { font-size:11px;opacity:0.6; }
    #tickerTrack.scrolling { animation: ticker-scroll 120s linear infinite; }
    #tickerTrack.scrolling:hover { animation-play-state:paused; }
    @media(max-width:768px) { .ticker-item { padding:0 14px;font-size:12px; } }
    </style>
    <script>
    // Adjust navbar top when ticker is visible
    (function() {
        var navbar = document.getElementById('navbar');
        var tickerEl = document.getElementById('announcementTicker');
        if (navbar) {
            // Start at top, the website.js will adjust if ticker is visible
            navbar.style.top = '0';
        }
    })();
    </script>
@endsection

@section('content')
    <!-- ========== Hero Slider Section ========== -->
    <section class="hero-slider" id="home">

        <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-pause="false">
            <div class="carousel-indicators">
                @forelse($sliders as $index => $slider)
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}" @if($index === 0) class="active" @endif></button>
                @empty
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                @endforelse
            </div>
            <div class="carousel-inner">
                @forelse($sliders as $index => $slider)
                    <div class="carousel-item @if($index === 0) active @endif" data-bs-interval="6000">
                        <div class="hero-slide" data-parallax style="background-image: url('{{ asset('storage/' . $slider->image_path) }}')">
                            <div class="hero-overlay"></div>
                            <div class="hero-dot-grid"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8 hero-text-reveal">
                                        @if($slider->title)
                                            <span class="hero-badge">
                                                <i class="fas fa-graduation-cap me-2"></i>{{ $slider->title }}
                                            </span>
                                        @endif
                                        @if($slider->subtitle)
                                            <h1>{{ $slider->subtitle }}</h1>
                                        @endif
                                        @if($slider->description ?? null)
                                            <p>{{ $slider->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Default Slide 1 -->
                    <div class="carousel-item active" data-bs-interval="6000">
                        <div class="hero-slide" data-parallax style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')">
                            <div class="hero-overlay"></div>
                            <div class="hero-dot-grid"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8 hero-text-reveal">
                                        <span class="hero-badge">
                                            <i class="fas fa-graduation-cap me-2"></i>Excellence in Education
                                        </span>
                                        <h1>Empowering Minds, <span>Shaping Futures</span></h1>
                                        <p>{{ $settings['school_description'] }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Default Slide 2 -->
                    <div class="carousel-item" data-bs-interval="6000">
                        <div class="hero-slide" data-parallax style="background-image: url('https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')">
                            <div class="hero-overlay"></div>
                            <div class="hero-dot-grid"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8 hero-text-reveal">
                                        <span class="hero-badge">
                                            <i class="fas fa-book-reader me-2"></i>Excellence in Education
                                        </span>
                                        <h1>Where Learning <span>Becomes Discovery</span></h1>
                                        <p>Our innovative curriculum combines academic rigor with creative exploration, preparing students to think critically and solve real-world problems with confidence.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Default Slide 3 -->
                    <div class="carousel-item" data-bs-interval="6000">
                        <div class="hero-slide" data-parallax style="background-image: url('https://images.unsplash.com/photo-1544531586-fde5298cdd40?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80')">
                            <div class="hero-overlay"></div>
                            <div class="hero-dot-grid"></div>
                            <div class="container h-100">
                                <div class="row h-100 align-items-center">
                                    <div class="col-lg-8 hero-text-reveal">
                                        <span class="hero-badge">
                                            <i class="fas fa-users me-2"></i>Join Our Community
                                        </span>
                                        <h1>Building <span>Tomorrow's Leaders</span></h1>
                                        <p>With {{ $settings['total_students'] }} students, {{ $settings['total_teachers'] }} expert teachers, and a {{ $settings['university_acceptance'] }} university acceptance rate, {{ $settings['school_name'] }} is where excellence meets opportunity.</p>
                                        <div class="hero-stats">
                                            <div class="stat-item">
                                                <h3><span class="counter" data-target="{{ $settings['total_students'] }}">0</span>+</h3>
                                                <p>Students</p>
                                            </div>
                                            <div class="stat-item">
                                                <h3><span class="counter" data-target="{{ $settings['total_teachers'] }}">0</span>+</h3>
                                                <p>Expert Teachers</p>
                                            </div>
                                            <div class="stat-item">
                                                <h3><span class="counter" data-target="{{ preg_replace('/[^0-9]/', '', $settings['university_acceptance']) }}">0</span>%</h3>
                                                <p>University Acceptance</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>

        <!-- Mobile-only Login Button at bottom of slider (hidden on desktop) -->
        <a href="{{ route('login') }}" class="hero-mobile-login-btn" aria-label="Login">
            <i class="fas fa-sign-in-alt me-1"></i>Login
        </a>

        <!-- Bottom Overlay: Alerts (Left) + News (Right) -->
        @if($sliderAlerts->count() > 0)
        <div class="slider-bottom-overlay">
            <div class="slider-bottom-overlay-inner">
                <!-- Left: Slider Alerts -->
                <div class="slider-bottom-alerts">
                    <div class="slider-bottom-label">
                        <i class="fas fa-bullhorn"></i>
                        <span>Alerts</span>
                    </div>
                    <div class="slider-bottom-alerts-list">
                        @foreach($sliderAlerts as $alert)
                            <span class="slider-bottom-alert-item" style="background:{{ $alert->bg_color }};color:{{ $alert->text_color }};">
                                <i class="fas {{ $alert->icon }}"></i>
                                {{ $alert->message }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- ========== Right-Side News Splash Panel (Modern Glass) ========== -->
        @isset($latestNews)
        @if($latestNews->count() > 0)
        <div class="news-splash-panel" id="newsSplashPanel" aria-label="Latest school news" role="dialog">
            <div class="news-splash-panel-header">
                <div class="news-splash-panel-icon">
                    <i class="fas fa-newspaper"></i>
                </div>
                <span class="news-splash-panel-label">News</span>
                <button class="news-splash-panel-close" id="newsSplashPanelClose" aria-label="Close news panel" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="news-splash-panel-body">
                @foreach($latestNews as $newsItem)
                    <a href="javascript:void(0)" class="news-splash-card" onclick="return false;">
                        @if($newsItem->image_path)
                            <div class="news-splash-card-img">
                                <img src="{{ asset('storage/' . $newsItem->image_path) }}" alt="{{ $newsItem->title }}" loading="lazy">
                            </div>
                        @else
                            <div class="news-splash-card-img news-splash-card-placeholder">
                                <i class="fas fa-newspaper"></i>
                            </div>
                        @endif
                        <div class="news-splash-card-body">
                            <span class="news-splash-card-date">{{ $newsItem->created_at->format('M d, Y') }}</span>
                            <h4>{{ $newsItem->title }}</h4>
                            @if($newsItem->content)
                                <p>{{ Str::limit(strip_tags($newsItem->content), 110) }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="news-splash-panel-footer">
                <button type="button" id="newsSplashPanelDismiss" style="background:none;border:none;color:inherit;padding:0;font:inherit;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-check"></i> Dismiss for this session
                </button>
            </div>
        </div>

        <!-- Toggle chip to re-open the news panel after dismissing -->
        <button type="button" class="news-splash-toggle" id="newsSplashToggle" aria-label="Open news panel">
            <i class="fas fa-newspaper"></i>
            <span>News</span>
        </button>

        <script>
        (function(){
            var panel  = document.getElementById('newsSplashPanel');
            var closeBtn   = document.getElementById('newsSplashPanelClose');
            var dismissBtn = document.getElementById('newsSplashPanelDismiss');
            var toggleBtn  = document.getElementById('newsSplashToggle');
            var heroSlider = document.querySelector('.hero-slider');
            if (!panel) return;

            // Tell the slider to narrow hero text when panel is open
            function setHeroNarrow(on) {
                if (heroSlider) {
                    if (on) heroSlider.classList.add('has-news-panel');
                    else   heroSlider.classList.remove('has-news-panel');
                }
            }

            function openPanel() {
                panel.classList.add('active');
                setHeroNarrow(true);
                if (toggleBtn) toggleBtn.classList.remove('visible');
                sessionStorage.removeItem('news_splash_dismissed');
            }
            function closePanel(showToggle) {
                panel.classList.remove('active');
                setHeroNarrow(false);
                if (showToggle && toggleBtn) toggleBtn.classList.add('visible');
            }
            function dismissPanel() {
                closePanel(true);
                sessionStorage.setItem('news_splash_dismissed', '1');
            }

            // Auto-open after 2s unless user has dismissed this session
            setTimeout(function(){
                if (!sessionStorage.getItem('news_splash_dismissed')) {
                    openPanel();
                } else if (toggleBtn) {
                    // Show toggle chip so user can re-open
                    toggleBtn.classList.add('visible');
                }
            }, 1800);

            if (closeBtn)   closeBtn.addEventListener('click', function(){ dismissPanel(); });
            if (dismissBtn) dismissBtn.addEventListener('click', function(){ dismissPanel(); });
            if (toggleBtn)  toggleBtn.addEventListener('click', function(){ openPanel(); });

            // ESC closes (but does not dismiss — user can re-open via toggle)
            document.addEventListener('keydown', function(e){
                if (e.key === 'Escape' && panel.classList.contains('active')) {
                    closePanel(true);
                }
            });
        })();
        </script>
        @endif
        @endisset
    </section>

    <!-- ========== Animated Counters Section ========== -->
    <section class="counters-section section-divider-top" id="counters">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="counter-item reveal">
                        <div class="counter-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3><span class="counter" data-target="{{ $settings['total_students'] }}">0</span>+</h3>
                        <p>Students</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="counter-item reveal">
                        <div class="counter-icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3><span class="counter" data-target="{{ $settings['total_teachers'] }}">0</span>+</h3>
                        <p>Expert Teachers</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="counter-item reveal">
                        <div class="counter-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h3><span class="counter" data-target="{{ $settings['years_of_excellence'] }}">0</span>+</h3>
                        <p>Years of Excellence</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-6">
                    <div class="counter-item reveal">
                        <div class="counter-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <h3><span class="counter" data-target="{{ preg_replace('/[^0-9]/', '', $settings['university_acceptance']) }}">0</span>%</h3>
                        <p>University Acceptance</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== Features Section (Glassmorphic Cards) ========== -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Why Choose Us</span>
                <h2>{{ $settings['wcu_section_title'] }}</h2>
                <p>{{ $settings['wcu_section_subtitle'] }}</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_1_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_1_title'] }}</h4>
                        <p>{{ $settings['wcu_1_description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_2_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_2_title'] }}</h4>
                        <p>{{ $settings['wcu_2_description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_3_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_3_title'] }}</h4>
                        <p>{{ $settings['wcu_3_description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_4_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_4_title'] }}</h4>
                        <p>{{ $settings['wcu_4_description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_5_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_5_title'] }}</h4>
                        <p>{{ $settings['wcu_5_description'] }}</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card reveal">
                        <div class="feature-icon">
                            <i class="{{ $settings['wcu_6_icon'] }}"></i>
                        </div>
                        <h4>{{ $settings['wcu_6_title'] }}</h4>
                        <p>{{ $settings['wcu_6_description'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== About Section (Split Layout) ========== -->
    <section class="about" id="about">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 about-image-col reveal-left">
                    <div class="about-image-wrapper">
                        <img src="{{ isset($settings['about_image']) && $settings['about_image'] && file_exists(public_path('storage/' . $settings['about_image'])) ? asset('storage/' . $settings['about_image']) : 'https://images.unsplash.com/photo-1544531586-fde5298cdd40?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80' }}" alt="About {{ $settings['school_name'] }}">
                        <div class="about-image-badge">
                            <h3>{{ $settings['about_years_experience'] }}</h3>
                            <p>Years of<br>Excellence</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 about-text-col reveal-right">
                    <div class="about-content">
                        <span class="section-badge">About Our School</span>
                        <h2>A Legacy of Academic Excellence</h2>
                        <p>{{ $settings['about_description'] }}</p>
                        <p>{{ $settings['about_mission'] }}</p>
                        <ul class="about-features">
                            <li><i class="fas fa-check-circle"></i> Accredited by National Education Board</li>
                            <li><i class="fas fa-check-circle"></i> Award-winning STEM programs</li>
                            <li><i class="fas fa-check-circle"></i> Comprehensive extracurricular activities</li>
                            <li><i class="fas fa-check-circle"></i> Strong alumni network worldwide</li>
                        </ul>
                        <div class="mt-3">
                            <a href="#contact" class="btn btn-hero-primary">
                                <i class="fas fa-arrow-right me-2"></i>Discover More
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== Programs Section (Horizontal Scroll) ========== -->
    <section class="programs" id="programs">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Academic Programs</span>
                <h2>{{ $settings['programs_section_title'] }}</h2>
                <p>{{ $settings['programs_section_subtitle'] }}</p>
            </div>
            <div class="programs-scroll-wrapper reveal" id="programsScrollWrapper">
                <div class="programs-scroll-track">
                    @php
                        $programsCount = (int) ($settings['programs_count'] ?? 4);
                        $visiblePrograms = 0;
                    @endphp
                    @for ($i = 1; $i <= $programsCount; $i++)
                        @php
                            $pVisible = ($settings["program_{$i}_visible"] ?? '1') === '1';
                            $pImage = $settings["program_{$i}_image"] ?? '';
                            $pTag = $settings["program_{$i}_tag"] ?? '';
                            $pTitle = $settings["program_{$i}_title"] ?? '';
                            $pDesc = $settings["program_{$i}_description"] ?? '';
                            $hasImage = !empty($pImage) && file_exists(public_path('storage/' . $pImage));
                        @endphp
                        @if($pVisible && !empty($pTitle))
                            @php $visiblePrograms++; @endphp
                            <div class="program-card">
                                <div class="program-image" style="background-image: url('{{ $hasImage ? asset('storage/' . $pImage) : 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80' }}')"></div>
                                <div class="program-content">
                                    <span class="program-tag">{{ $pTag }}</span>
                                    <h4>{{ $pTitle }}</h4>
                                    <p>{{ $pDesc }}</p>
                                    <a href="#" class="program-link">Learn More <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        @endif
                    @endfor
                    @if($visiblePrograms === 0)
                        <div style="text-align:center;padding:3rem;color:var(--text-light);">
                            <i class="fas fa-graduation-cap" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:1rem;"></i>
                            <p>No programs configured yet. Add programs via Admin → Web Content.</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="programs-scroll-nav">
                <button id="programsScrollLeft" aria-label="Scroll left"><i class="fas fa-chevron-left"></i></button>
                <button id="programsScrollRight" aria-label="Scroll right"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </section>

    <!-- ========== Gallery Section (Masonry) ========== -->
    <section class="gallery" id="gallery">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Photo Gallery</span>
                <h2>Campus Life & Moments</h2>
                <p>Explore our vibrant campus through these captured moments of learning, creativity, and achievement.</p>
            </div>
            <div class="gallery-masonry reveal">
                @forelse($galleryImages as $image)
                    <div class="gallery-item">
                        <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->title ?? 'Gallery Image' }}" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                @empty
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1544531586-fde5298cdd40?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Students in classroom" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1509062522246-3755977927d7?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Science laboratory" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1571260899304-425eee4c7efc?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="School library" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Sports activities" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Art class" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Graduation ceremony" loading="lazy">
                        <div class="gallery-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                @endforelse
            </div>
            @if($galleryImages->count() > 0)
            <div class="text-center mt-4 reveal">
                <a href="{{ route('gallery') }}" class="btn btn-hero-primary" style="padding:0.75rem 2rem;">View Full Gallery</a>
            </div>
            @endif
        </div>
    </section>

    <!-- ========== Video Section (NEW) ========== -->
    @isset($videos)
    @if($videos->count() > 0)
    <section class="video-section" id="videos">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Video Gallery</span>
                <h2>Video Highlights</h2>
                <p>Watch our school events, educational content, and student achievements come to life.</p>
            </div>
            <div class="row g-4">
                @foreach($videos as $video)
                <div class="col-lg-4 col-md-6">
                    <div class="video-card reveal" data-video-id="{{ $video->youtube_video_id }}" onclick="openVideoModal('{{ $video->youtube_video_id }}')">
                        <div class="video-thumb">
                            @if($video->thumbnail)
                                <img src="{{ $video->thumbnail }}" alt="{{ $video->title }}" loading="lazy">
                            @else
                                <img src="https://img.youtube.com/vi/{{ $video->youtube_video_id }}/hqdefault.jpg" alt="{{ $video->title }}" loading="lazy">
                            @endif
                            <div class="video-play-btn">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="video-info">
                            <h5>{{ $video->title }}</h5>
                            <div class="video-meta">
                                @if($video->channel_name)
                                    <span><i class="fab fa-youtube me-1" style="color:#ff0000;"></i>{{ $video->channel_name }}</span>
                                @endif
                                @if($video->category)
                                    <span class="video-category">{{ $video->category }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endisset

    {{-- Legacy video section for backward compat --}}
    @isset($websiteVideos)
    @if(isset($videos) && $videos->count() > 0)
    @elseif($websiteVideos->count() > 0 || (isset($galleryVideos) && $galleryVideos->count() > 0))
    <section class="video-section" id="videos">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Video Gallery</span>
                <h2>Video Highlights</h2>
                <p>Watch our school events, educational content, and student achievements.</p>
            </div>
            <div class="row g-4">
                @foreach($websiteVideos as $video)
                <div class="col-lg-4 col-md-6">
                    <div class="video-card reveal" onclick="openVideoModal('{{ $video->youtube_video_id }}')">
                        <div class="video-thumb">
                            @if($video->thumbnail)
                                <img src="{{ $video->thumbnail }}" alt="{{ $video->title }}" loading="lazy">
                            @else
                                <img src="https://img.youtube.com/vi/{{ $video->youtube_video_id }}/hqdefault.jpg" alt="{{ $video->title }}" loading="lazy">
                            @endif
                            <div class="video-play-btn">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="video-info">
                            <h5>{{ $video->title }}</h5>
                            <div class="video-meta">
                                @if($video->channel_name)
                                    <span><i class="fab fa-youtube me-1" style="color:#ff0000;"></i>{{ $video->channel_name }}</span>
                                @endif
                                @if($video->category)
                                    <span class="video-category">{{ $video->category }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                @isset($galleryVideos)
                @foreach($galleryVideos as $gv)
                @php
                    $gvVideoId = null;
                    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $gv->video_url, $m)) {
                        $gvVideoId = $m[1];
                    }
                @endphp
                @if($gvVideoId)
                <div class="col-lg-4 col-md-6">
                    <div class="video-card reveal" onclick="openVideoModal('{{ $gvVideoId }}')">
                        <div class="video-thumb">
                            <img src="https://img.youtube.com/vi/{{ $gvVideoId }}/hqdefault.jpg" alt="{{ $gv->title }}" loading="lazy">
                            <div class="video-play-btn">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="video-info">
                            <h5>{{ $gv->title }}</h5>
                            @if($gv->description)
                            <div class="video-meta">
                                <span>{{ Str::limit($gv->description, 60) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
                @endisset
            </div>
        </div>
    </section>
    @endif
    @endisset

    <!-- Video Modal -->
    <div class="video-modal-overlay" id="videoModal">
        <div class="video-modal-content">
            <button class="video-modal-close" id="videoModalClose" aria-label="Close video">
                <i class="fas fa-times"></i>
            </button>
            <div class="ratio ratio-16x9">
                <iframe id="videoModalIframe" src="" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
            </div>
        </div>
    </div>

    <!-- ========== Team Section (Rounded Avatar) ========== -->
    <section class="team" id="team">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Leadership Team</span>
                <h2>Meet Our Educators</h2>
                <p>Our dedicated team of experienced educators and administrators is committed to nurturing each student's potential.</p>
            </div>
            <div class="row g-4">
                @forelse($teamMembers as $member)
                    @php
                        $memberPhoto = $member->photo && file_exists(public_path('storage/' . $member->photo)) 
                            ? asset('storage/' . $member->photo) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($member->name) . '&background=1B5E20&color=D4A017&size=200&font-size=0.4&bold=true';
                    @endphp
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card reveal">
                            <div class="team-avatar">
                                <img src="{{ $memberPhoto }}" alt="{{ $member->name }}" loading="lazy">
                                <div class="team-social-overlay">
                                    @if($member->email)
                                        <a href="mailto:{{ $member->email }}" title="Email"><i class="fas fa-envelope"></i></a>
                                    @endif
                                    @if($member->phone)
                                        <a href="tel:{{ $member->phone }}" title="Call"><i class="fas fa-phone"></i></a>
                                    @endif
                                </div>
                            </div>
                            <div class="team-content">
                                <h4>{{ $member->name }}</h4>
                                <p>{{ $member->designation }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card reveal">
                            <div class="team-avatar">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Dr. Sarah Johnson">
                                <div class="team-social-overlay">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="team-content">
                                <h4>Dr. Sarah Johnson</h4>
                                <p>Principal</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card reveal">
                            <div class="team-avatar">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Prof. Michael Chen">
                                <div class="team-social-overlay">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="team-content">
                                <h4>Prof. Michael Chen</h4>
                                <p>Vice Principal - Academics</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card reveal">
                            <div class="team-avatar">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Ms. Emily Davis">
                                <div class="team-social-overlay">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="team-content">
                                <h4>Ms. Emily Davis</h4>
                                <p>Head of Student Affairs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card reveal">
                            <div class="team-avatar">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Mr. David Wilson">
                                <div class="team-social-overlay">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                    <a href="#"><i class="fas fa-envelope"></i></a>
                                </div>
                            </div>
                            <div class="team-content">
                                <h4>Mr. David Wilson</h4>
                                <p>Athletics Director</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- ========== CTA Section (Animated Gradient) ========== -->
    <section class="cta section-divider-top" id="cta">
        <div class="container">
            <h2 class="reveal">Visit Us in Person</h2>
            <p class="reveal">Applications are accepted in person only. Visit our campus to enroll and begin your educational journey with us.</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap reveal">
                <a href="#contact" class="btn btn-hero-primary">
                    <i class="fas fa-map-marker-alt me-2"></i>Find Our Campus
                </a>
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $settings['school_phone']) }}" class="btn btn-hero-secondary">
                    <i class="fas fa-phone me-2"></i>Call Us
                </a>
            </div>
        </div>
    </section>

    <!-- ========== Contact Section (Two-Column) ========== -->
    <section class="contact-section" id="contact">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Get in Touch</span>
                <h2>Contact Us</h2>
                <p>We would love to hear from you. Reach out and let us help you on your educational journey.</p>
            </div>
            <div class="row g-5">
                <div class="col-lg-7 reveal-left">
                    <div class="contact-form-wrapper">
                        <h4 style="font-family:'Montserrat',sans-serif;font-weight:700;margin-bottom:1.5rem;color:var(--primary-color);">Send Us a Message</h4>
                        <form action="{{ route('contact.store') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Full Name</label>
                                    <input type="text" name="name" class="form-control" placeholder="Your full name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Email Address</label>
                                    <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" placeholder="+1 234 567 890">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Subject</label>
                                    <select name="subject" class="form-select">
                                        <option value="General Inquiry">General Inquiry</option>
                                        <option value="Admissions">Admissions</option>
                                        <option value="Academics">Academics</option>
                                        <option value="Fee Structure">Fee Structure</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Message</label>
                                    <textarea name="message" class="form-control" rows="5" placeholder="How can we help you?" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-hero-primary w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5 reveal-right">
                    <div class="contact-info-cards">
                        <div class="contact-info-card">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h5>Our Location</h5>
                                <p>{{ $settings['school_address'] }}</p>
                            </div>
                        </div>
                        <div class="contact-info-card">
                            <div class="info-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <h5>Call Us</h5>
                                <p>{{ $settings['school_phone'] }}</p>
                            </div>
                        </div>
                        <div class="contact-info-card">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h5>Email Us</h5>
                                <p>{{ $settings['school_email'] }}</p>
                            </div>
                        </div>
                        <div class="contact-info-card">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <h5>Working Hours</h5>
                                <p>Mon - Fri: 7:30 AM - 4:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script src="{{ asset('js/homepage.js') }}"></script>
@endpush
