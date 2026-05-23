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
    <section style="padding:5rem 0;background:var(--white);">
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
                        <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="About our school" style="width:100%;height:auto;min-height:350px;object-fit:cover;">
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
