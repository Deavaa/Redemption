@extends('layouts.website')

@section('title', 'Contact Us - ' . ($settings['school_name'] ?? 'School'))

@push('styles')
<style>
    /* Branch Cards */
    .branch-card {
        background: var(--white);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .branch-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.12);
    }
    .branch-map-frame {
        width: 100%;
        height: 220px;
        border: none;
        border-radius: 20px 20px 0 0;
    }
    .branch-map-placeholder {
        width: 100%;
        height: 220px;
        background: linear-gradient(135deg, #e8f5e9, #f3e5f5);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--text-muted);
        border-radius: 20px 20px 0 0;
    }
    .branch-map-placeholder i { font-size: 2.5rem; margin-bottom: 0.5rem; opacity: 0.4; }
    .branch-map-placeholder span { font-size: 0.85rem; }
    .branch-info {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .branch-info h4 {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.75rem;
        font-family: 'Montserrat', sans-serif;
    }
    .branch-info .branch-detail {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        margin-bottom: 0.6rem;
        font-size: 0.9rem;
        color: var(--text-light);
    }
    .branch-info .branch-detail i {
        color: var(--secondary-color);
        margin-top: 0.15rem;
        width: 16px;
        text-align: center;
        flex-shrink: 0;
    }
    .branch-badge {
        display: inline-block;
        background: rgba(212, 160, 23, 0.12);
        color: var(--secondary-color);
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.2rem 0.7rem;
        border-radius: 20px;
        border: 1px solid rgba(212, 160, 23, 0.25);
        margin-bottom: 0.75rem;
    }
    .branch-directions-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        margin-top: auto;
        padding: 0.5rem 1rem;
        background: var(--primary-color);
        color: var(--white);
        border-radius: 10px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .branch-directions-btn:hover {
        background: var(--secondary-color);
        color: var(--primary-color);
        transform: translateY(-2px);
    }
</style>
@endpush

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

    <!-- ========== Our Campuses / Branch Map Section ========== -->
    @isset($branches)
    @if($branches->count() > 0)
    <section style="padding:5rem 0;background:var(--light-bg);">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Our Campuses</span>
                <h2>Find Us</h2>
                <p>Visit any of our campuses. We are here to serve you and your children.</p>
            </div>
            <div class="row g-4">
                @foreach($branches as $branch)
                <div class="col-lg-{{ $branches->count() === 1 ? '8 offset-lg-2' : ($branches->count() === 2 ? '6' : '4') }} col-md-6">
                    <div class="branch-card reveal">
                        {{-- Map embed or GPS-based map --}}
                        @if($branch->map_embed_url)
                        <iframe class="branch-map-frame" src="{{ $branch->map_embed_url }}" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        @elseif($branch->gps_lat && $branch->gps_lng)
                        <iframe class="branch-map-frame" src="https://maps.google.com/maps?q={{ $branch->gps_lat }},{{ $branch->gps_lng }}&z=15&output=embed" allowfullscreen loading="lazy"></iframe>
                        @else
                        <div class="branch-map-placeholder">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Map not available</span>
                        </div>
                        @endif

                        <div class="branch-info">
                            @if($branch->is_headquarters)
                            <span class="branch-badge"><i class="fas fa-star me-1"></i>Headquarters</span>
                            @endif
                            <h4>{{ $branch->name }}</h4>
                            @if($branch->address)
                            <div class="branch-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>{{ $branch->address }}</span>
                            </div>
                            @endif
                            @if($branch->phone)
                            <div class="branch-detail">
                                <i class="fas fa-phone"></i>
                                <span>{{ $branch->phone }}</span>
                            </div>
                            @endif
                            @if($branch->email)
                            <div class="branch-detail">
                                <i class="fas fa-envelope"></i>
                                <span>{{ $branch->email }}</span>
                            </div>
                            @endif
                            @if($branch->gps_lat && $branch->gps_lng)
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $branch->gps_lat }},{{ $branch->gps_lng }}" target="_blank" rel="noopener" class="branch-directions-btn">
                                <i class="fas fa-directions"></i> Get Directions
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
    @endisset
@endsection
