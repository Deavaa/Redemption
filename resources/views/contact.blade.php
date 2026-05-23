@extends('layouts.website')

@section('title', 'Contact Us - ' . ($settings['school_name'] ?? 'School'))

@section('content')
    <!-- ========== Contact Hero ========== -->
    <section class="page-hero">
        <div class="container">
            <h1>Contact <span>Us</span></h1>
            <p>We'd love to hear from you. Reach out anytime.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Contact</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- ========== Contact Content ========== -->
    <section style="padding:5rem 0;background:var(--white);">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5 reveal-left">
                    <h4 style="font-family:'Montserrat',sans-serif;font-weight:700;margin-bottom:1.5rem;color:var(--primary-color);">Get In Touch</h4>

                    <div class="contact-info-card" style="margin-bottom:1.25rem;">
                        <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h5>Address</h5>
                            <p>{{ $settings['school_address'] ?? 'Addis Ababa, Ethiopia' }}</p>
                        </div>
                    </div>

                    <div class="contact-info-card" style="margin-bottom:1.25rem;">
                        <div class="info-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <h5>Phone</h5>
                            <p>{{ $settings['school_phone'] ?? '+251 11 234 5678' }}</p>
                        </div>
                    </div>

                    <div class="contact-info-card" style="margin-bottom:1.25rem;">
                        <div class="info-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h5>Email</h5>
                            <p>{{ $settings['school_email'] ?? 'info@schoolofredemption.edu' }}</p>
                        </div>
                    </div>

                    <div class="contact-info-card" style="margin-bottom:1.25rem;">
                        <div class="info-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <h5>Office Hours</h5>
                            <p>Mon - Fri: 8:00 AM - 5:00 PM</p>
                        </div>
                    </div>

                    <div class="social-links" style="margin-top:1.5rem;">
                        @if($settings['facebook_url'] ?? '')
                        <a href="{{ $settings['facebook_url'] }}" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                        @endif
                        @if($settings['twitter_url'] ?? '')
                        <a href="{{ $settings['twitter_url'] }}" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
                        @endif
                        @if($settings['youtube_url'] ?? '')
                        <a href="{{ $settings['youtube_url'] }}" target="_blank" rel="noopener"><i class="fab fa-youtube"></i></a>
                        @endif
                        @if($settings['telegram_url'] ?? '')
                        <a href="{{ $settings['telegram_url'] }}" target="_blank" rel="noopener"><i class="fab fa-telegram-plane"></i></a>
                        @endif
                    </div>
                </div>

                <div class="col-lg-7 reveal-right">
                    <div class="contact-form-wrapper">
                        <h4 style="font-family:'Montserrat',sans-serif;font-weight:700;margin-bottom:1.5rem;color:var(--primary-color);">Send Us a Message</h4>
                        @if(session('success'))
                        <div class="alert alert-success" style="border-radius:12px;"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
                        @endif
                        <form method="POST" action="{{ route('contact.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Full Name *</label>
                                    <input type="text" name="name" class="form-control" placeholder="Your full name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Email *</label>
                                    <input type="email" name="email" class="form-control" placeholder="Your email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Phone</label>
                                    <input type="text" name="phone" class="form-control" placeholder="Your phone number">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Subject *</label>
                                    <input type="text" name="subject" class="form-control" placeholder="Subject" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" style="font-weight:500;font-size:0.9rem;">Message *</label>
                                    <textarea name="message" class="form-control" rows="5" placeholder="Your message" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-hero-primary" style="padding:0.75rem 2rem;">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
