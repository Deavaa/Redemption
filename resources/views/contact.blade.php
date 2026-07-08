@extends('layouts.website')

@section('title', 'Contact Us - ' . ($settings['school_name'] ?? 'School'))

@push('styles')
<style>
    /* ===== Branch Cards ===== */
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

    /* ===== Modern Contact Info Cards ===== */
    .contact-info-card {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.1rem 1.25rem;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        position: relative;
        overflow: hidden;
    }
    .contact-info-card::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--primary-color), var(--secondary-color));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .contact-info-card:hover {
        border-color: rgba(16, 185, 129, 0.3);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.08);
        transform: translateX(4px);
    }
    .contact-info-card:hover::before { opacity: 1; }
    .contact-info-card .info-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    }
    .contact-info-card h5 {
        margin: 0 0 0.25rem 0;
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #6b7280;
        font-family: 'Montserrat', sans-serif;
    }
    .contact-info-card p {
        margin: 0;
        font-size: 0.95rem;
        color: #1f2937;
        font-weight: 500;
        line-height: 1.5;
    }

    /* ===== Modern Contact Form ===== */
    .contact-form-wrapper {
        background: linear-gradient(160deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 24px;
        padding: 2.5rem;
        box-shadow:
            0 1px 0 rgba(255,255,255,0.8) inset,
            0 20px 50px rgba(0,0,0,0.06),
            0 4px 12px rgba(0,0,0,0.03);
        border: 1px solid #e5e7eb;
        position: relative;
        overflow: hidden;
    }
    .contact-form-wrapper::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color) 0%, var(--secondary-color) 50%, var(--primary-color) 100%);
    }
    .contact-form-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.75rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .contact-form-header-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(16,185,129,0.12) 0%, rgba(212,160,23,0.12) 100%);
        border: 1px solid rgba(16,185,129,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .contact-form-header h4 {
        margin: 0;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        color: #0f172a;
        font-size: 1.25rem;
        line-height: 1.2;
    }
    .contact-form-header p {
        margin: 0.15rem 0 0 0;
        font-size: 0.82rem;
        color: #6b7280;
    }

    /* Form field group — floating label style */
    .form-field {
        position: relative;
        margin-bottom: 1.1rem;
    }
    .form-field label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.4rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-field label .req {
        color: #ef4444;
        font-size: 0.7rem;
    }
    .form-field label i {
        color: var(--primary-color);
        font-size: 0.75rem;
    }
    .form-field input,
    .form-field textarea {
        width: 100%;
        padding: 0.8rem 1rem 0.8rem 2.75rem;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        font-size: 0.95rem;
        color: #1f2937;
        background: #ffffff;
        transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
        font-family: 'Montserrat', sans-serif;
    }
    .form-field textarea {
        padding-left: 1rem;
        padding-top: 0.8rem;
        resize: vertical;
        min-height: 130px;
    }
    .form-field input::placeholder,
    .form-field textarea::placeholder {
        color: #c5c9d2;
        font-size: 0.9rem;
    }
    .form-field input:focus,
    .form-field textarea:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow:
            0 0 0 4px rgba(16, 185, 129, 0.10),
            0 4px 12px rgba(16, 185, 129, 0.08);
        background: #ffffff;
    }
    .form-field .field-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.95rem;
        pointer-events: none;
        transition: color 0.25s ease;
        margin-top: 0.85rem; /* offset for the label above */
    }
    .form-field:focus-within .field-icon {
        color: var(--primary-color);
    }

    /* Submit button — gradient with shine */
    .contact-submit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.95rem 2rem;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: #ffffff;
        border: none;
        border-radius: 12px;
        font-size: 0.95rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        box-shadow:
            0 1px 0 rgba(255,255,255,0.25) inset,
            0 6px 18px rgba(16, 185, 129, 0.30);
        position: relative;
        overflow: hidden;
    }
    .contact-submit-btn::before {
        content: '';
        position: absolute;
        top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
        transition: left 0.6s ease;
    }
    .contact-submit-btn:hover {
        transform: translateY(-2px);
        box-shadow:
            0 1px 0 rgba(255,255,255,0.30) inset,
            0 10px 25px rgba(16, 185, 129, 0.40);
    }
    .contact-submit-btn:hover::before { left: 100%; }
    .contact-submit-btn:active { transform: translateY(0); }

    /* Get In Touch header */
    .get-in-touch-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    .get-in-touch-header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    }
    .get-in-touch-header h4 {
        margin: 0;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        color: var(--primary-color);
        font-size: 1.25rem;
        line-height: 1.2;
    }
    .get-in-touch-header p {
        margin: 0.15rem 0 0 0;
        font-size: 0.82rem;
        color: #6b7280;
    }

    /* Social links — modern pills */
    .social-links {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }
    .social-links a {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        color: var(--primary-color);
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
        text-decoration: none;
    }
    .social-links a:hover {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: #ffffff;
        border-color: transparent;
        transform: translateY(-3px);
        box-shadow: 0 8px 18px rgba(16, 185, 129, 0.30);
    }

    @media (max-width: 768px) {
        .contact-form-wrapper { padding: 1.5rem; border-radius: 18px; }
        .contact-form-header h4 { font-size: 1.1rem; }
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
    <section style="padding:3rem 0 4rem;background:var(--white);">
        <div class="container">
            <div class="row g-4 g-lg-5">
                {{-- Left: Contact Info --}}
                <div class="col-lg-5 reveal-left">
                    <div class="get-in-touch-header">
                        <div class="get-in-touch-header-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div>
                            <h4>Get In Touch</h4>
                            <p>Reach us through any of these channels</p>
                        </div>
                    </div>

                    <div class="contact-info-card" style="margin-bottom:1rem;">
                        <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h5><i class="fas fa-location-dot"></i>Address</h5>
                            <p>{{ $settings['school_address'] ?? 'Addis Ababa, Ethiopia' }}</p>
                        </div>
                    </div>

                    <div class="contact-info-card" style="margin-bottom:1rem;">
                        <div class="info-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <h5><i class="fas fa-phone-volume"></i>Phone</h5>
                            <p>{{ $settings['school_phone'] ?? '+251 11 234 5678' }}</p>
                        </div>
                    </div>

                    <div class="contact-info-card" style="margin-bottom:1rem;">
                        <div class="info-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h5><i class="fas fa-at"></i>Email</h5>
                            <p>{{ $settings['school_email'] ?? 'info@schoolofredemption.edu' }}</p>
                        </div>
                    </div>

                    <div class="contact-info-card" style="margin-bottom:1.5rem;">
                        <div class="info-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <h5><i class="fas fa-business-time"></i>Office Hours</h5>
                            <p>Mon - Fri: 8:00 AM - 5:00 PM</p>
                        </div>
                    </div>

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
                    </div>
                </div>

                {{-- Right: Contact Form --}}
                <div class="col-lg-7 reveal-right">
                    <div class="contact-form-wrapper">
                        <div class="contact-form-header">
                            <div class="contact-form-header-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div>
                                <h4>Send Us a Message</h4>
                                <p>Fill in the form below and we'll get back to you shortly</p>
                            </div>
                        </div>

                        @if(session('success'))
                        <div class="alert alert-success" style="border-radius:12px;border:none;background:linear-gradient(135deg,#ecfdf5 0%,#f0fdf4 100%);color:#065f46;font-weight:600;padding:1rem 1.25rem;display:flex;align-items:center;gap:0.5rem;margin-bottom:1.5rem;">
                            <i class="fas fa-check-circle" style="font-size:1.2rem;"></i>{{ session('success') }}
                        </div>
                        @endif

                        <form method="POST" action="{{ route('contact.store') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-field">
                                        <label><i class="fas fa-user"></i>Full Name <span class="req">*</span></label>
                                        <i class="fas fa-user-circle field-icon"></i>
                                        <input type="text" name="name" placeholder="e.g. John Doe" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-field">
                                        <label><i class="fas fa-envelope"></i>Email <span class="req">*</span></label>
                                        <i class="fas fa-at field-icon"></i>
                                        <input type="email" name="email" placeholder="e.g. john@example.com" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-field">
                                        <label><i class="fas fa-phone"></i>Phone</label>
                                        <i class="fas fa-phone-alt field-icon"></i>
                                        <input type="text" name="phone" placeholder="e.g. +251 91 234 5678">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-field">
                                        <label><i class="fas fa-tag"></i>Subject <span class="req">*</span></label>
                                        <i class="fas fa-heading field-icon"></i>
                                        <input type="text" name="subject" placeholder="What is this about?" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-field">
                                        <label><i class="fas fa-comment-dots"></i>Message <span class="req">*</span></label>
                                        <textarea name="message" rows="5" placeholder="Write your message here..." required></textarea>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="contact-submit-btn">
                                        <i class="fas fa-paper-plane"></i>Send Message
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
