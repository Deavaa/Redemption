@extends('layouts.website')

@section('title', 'About Us - ' . ($settings['school_name'] ?? 'School'))

@section('content')
    <!-- ========== About Hero ========== -->
    <section class="page-hero">
        <div class="container">
            <h1>About <span>Us</span></h1>
            <p>Learn about our mission, vision, and the values that drive us.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">About Us</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- ========== Mission & Vision ========== -->
    <section style="padding:3rem 0 4rem;background:var(--white);">
        <div class="container">
            <div class="row g-5 align-items-center">
                <div class="col-lg-6 reveal-left">
                    <span class="section-badge">Our Mission</span>
                    <h2 style="color:var(--primary-color);margin-bottom:1.5rem;">A Legacy of Academic Excellence</h2>
                    <p style="color:var(--text-light);line-height:1.8;margin-bottom:1.5rem;">{{ $settings['about_description'] ?? 'The School of Redemption is dedicated to providing transformative education that nurtures intellectual growth, moral character, and spiritual development.' }}</p>
                    <h3 style="color:var(--primary-color);margin-bottom:1rem;font-size:1.5rem;">Our Vision</h3>
                    <p style="color:var(--text-light);line-height:1.8;">{{ $settings['about_vision'] ?? 'To be a leading educational institution recognized for academic excellence, holistic development, and producing graduates who are compassionate leaders and responsible global citizens.' }}</p>
                </div>
                <div class="col-lg-6 reveal-right">
                    <div style="border-radius:24px;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.15);">
                        @if(!empty($settings['about_image']))
                            <img src="{{ asset('storage/' . $settings['about_image']) }}" alt="About {{ $settings['school_name'] ?? 'our school' }}" style="width:100%;height:auto;min-height:350px;object-fit:cover;">
                        @else
                            <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="About our school" style="width:100%;height:auto;min-height:350px;object-fit:cover;">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== About Stats ========== -->
    <section class="about-counters-section" style="padding:5rem 0;background:var(--primary-color);position:relative;overflow:hidden;">
        <div style="position:absolute;top:0;left:0;right:0;bottom:0;background-image:radial-gradient(rgba(212,160,23,0.04) 1px,transparent 1px);background-size:25px 25px;pointer-events:none;"></div>
        <div class="container" style="position:relative;z-index:1;">
            <div class="section-header reveal" style="margin-bottom:3rem;">
                <span class="section-badge" style="background:rgba(212,160,23,0.12);color:var(--secondary-color);border-color:rgba(212,160,23,0.35);">Our Impact</span>
                <h2 style="color:var(--white);">Numbers That Speak</h2>
                <p style="color:rgba(255,255,255,0.7);">Our achievements reflect our commitment to excellence in education.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="about-counter-item reveal" style="text-align:center;">
                        <div style="width:70px;height:70px;background:rgba(212,160,23,0.12);border:1px solid rgba(212,160,23,0.35);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                            <i class="fas fa-user-graduate" style="font-size:1.75rem;color:var(--secondary-color);"></i>
                        </div>
                        <h3 style="font-size:3rem;color:var(--secondary-color);font-weight:800;margin-bottom:0.5rem;font-family:'Playfair Display',serif;">
                            {{ $settings['about_students_count'] ?? '500+' }}
                        </h3>
                        <p style="color:rgba(255,255,255,0.7);font-size:1rem;margin:0;">Students Enrolled</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="about-counter-item reveal" style="text-align:center;">
                        <div style="width:70px;height:70px;background:rgba(212,160,23,0.12);border:1px solid rgba(212,160,23,0.35);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                            <i class="fas fa-clock" style="font-size:1.75rem;color:var(--secondary-color);"></i>
                        </div>
                        <h3 style="font-size:3rem;color:var(--secondary-color);font-weight:800;margin-bottom:0.5rem;font-family:'Playfair Display',serif;">
                            {{ $settings['about_years_experience'] ?? '15+' }}
                        </h3>
                        <p style="color:rgba(255,255,255,0.7);font-size:1rem;margin:0;">Years of Experience</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="about-counter-item reveal" style="text-align:center;">
                        <div style="width:70px;height:70px;background:rgba(212,160,23,0.12);border:1px solid rgba(212,160,23,0.35);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                            <i class="fas fa-book-open" style="font-size:1.75rem;color:var(--secondary-color);"></i>
                        </div>
                        <h3 style="font-size:3rem;color:var(--secondary-color);font-weight:800;margin-bottom:0.5rem;font-family:'Playfair Display',serif;">
                            {{ $settings['about_programs'] ?? '8' }}
                        </h3>
                        <p style="color:rgba(255,255,255,0.7);font-size:1rem;margin:0;">Academic Programs</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="about-counter-item reveal" style="text-align:center;">
                        <div style="width:70px;height:70px;background:rgba(212,160,23,0.12);border:1px solid rgba(212,160,23,0.35);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                            <i class="fas fa-trophy" style="font-size:1.75rem;color:var(--secondary-color);"></i>
                        </div>
                        <h3 style="font-size:3rem;color:var(--secondary-color);font-weight:800;margin-bottom:0.5rem;font-family:'Playfair Display',serif;">
                            {{ $settings['about_success_rate'] ?? '95%' }}
                        </h3>
                        <p style="color:rgba(255,255,255,0.7);font-size:1rem;margin:0;">Success Rate</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== Core Values ========== -->
    <section style="padding:5rem 0;background:var(--light-bg);">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Core Values</span>
                <h2>What We Stand For</h2>
                <p>The principles that guide everything we do at {{ $settings['school_name'] ?? 'our school' }}.</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card reveal" style="height:100%;">
                        <div class="feature-icon"><i class="fas fa-star"></i></div>
                        <h4>Excellence</h4>
                        <p>Pursuing the highest standards in everything we do.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card reveal" style="height:100%;">
                        <div class="feature-icon"><i class="fas fa-handshake"></i></div>
                        <h4>Integrity</h4>
                        <p>Honesty, accountability, and ethical conduct at all times.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card reveal" style="height:100%;">
                        <div class="feature-icon"><i class="fas fa-lightbulb"></i></div>
                        <h4>Innovation</h4>
                        <p>Creative teaching approaches and forward-thinking solutions.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="feature-card reveal" style="height:100%;">
                        <div class="feature-icon"><i class="fas fa-globe"></i></div>
                        <h4>Service</h4>
                        <p>Making a positive difference in our community and beyond.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
(function() {
    // Animate stat numbers when they come into view
    var statItems = document.querySelectorAll('.about-counter-item h3');
    var observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                var el = entry.target;
                var text = el.textContent.trim();
                var numericPart = parseInt(text.replace(/[^0-9]/g, ''));
                var suffix = text.replace(/[0-9]/g, '');

                if (!isNaN(numericPart) && numericPart > 0) {
                    var duration = 2000;
                    var startTime = null;

                    function animate(currentTime) {
                        if (!startTime) startTime = currentTime;
                        var progress = Math.min((currentTime - startTime) / duration, 1);
                        var ease = 1 - Math.pow(1 - progress, 3);
                        var current = Math.floor(ease * numericPart);
                        el.textContent = current + suffix;
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        } else {
                            el.textContent = text;
                        }
                    }

                    el.textContent = '0' + suffix;
                    requestAnimationFrame(animate);
                }
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });

    statItems.forEach(function(item) {
        observer.observe(item);
    });
})();
</script>
@endpush
