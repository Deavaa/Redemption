@extends('layouts.website')

@section('title', 'Our Team - ' . ($settings['school_name'] ?? 'School'))

@push('styles')
<style>
    /* ========== Team Page Styles ========== */
    .team-card {
        background: var(--white);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
        text-align: center;
        padding: 2.5rem 1.5rem 2rem;
        height: 100%;
        position: relative;
    }

    .team-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.12);
    }

    .team-avatar {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto 1.5rem;
        border: 4px solid rgba(212, 160, 23, 0.25);
        position: relative;
        transition: border-color 0.3s ease;
        background: linear-gradient(135deg, #dbeafe, #ede9fe);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .team-card:hover .team-avatar {
        border-color: var(--secondary-color);
    }

    .team-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .team-card:hover .team-avatar img {
        transform: scale(1.08);
    }

    /* Placeholder initial when no photo */
    .team-avatar-initial {
        font-size: 3rem;
        font-weight: 700;
        color: var(--primary);
        font-family: 'Playfair Display', serif;
    }

    /* Social overlay on hover */
    .team-social-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .team-card:hover .team-social-overlay {
        opacity: 1;
    }

    .team-social-overlay a {
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }

    .team-social-overlay a:hover {
        background: var(--secondary-color);
        color: var(--primary-color);
        transform: scale(1.15);
    }

    .team-content h4 {
        font-size: 1.15rem;
        margin-bottom: 0.25rem;
        color: var(--primary-color);
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
    }

    .team-content p {
        color: var(--secondary-color);
        font-size: 0.9rem;
        margin: 0;
    }

    @media (max-width: 575px) {
        .team-card {
            padding: 2rem 1rem 1.5rem;
        }
        .team-avatar {
            width: 110px;
            height: 110px;
        }
    }
</style>
@endpush

@section('content')
    <!-- ========== Team Hero ========== -->
    <section class="page-hero">
        <div class="container">
            <h1>Our <span>Team</span></h1>
            <p>Meet the dedicated leaders who guide our school to excellence.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Our Team</li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- ========== Team Content ========== -->
    <section style="padding:5rem 0;background:var(--light-bg);">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge">Leadership Team</span>
                <h2>Meet Our Educators</h2>
                <p>Our dedicated team of experienced educators and administrators is committed to nurturing each student's potential.</p>
            </div>

            @isset($teamMembers)
            <div class="row g-4">
                @foreach($teamMembers as $member)
                <div class="col-lg-3 col-md-6">
                    <div class="team-card reveal">
                        <div class="team-avatar">
                            @if($member->photo)
                            <img src="{{ asset('storage/' . $member->photo) }}" alt="{{ $member->name }}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                            <span class="team-avatar-initial" style="display:none;">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                            @else
                            <span class="team-avatar-initial">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                            @endif
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
                @endforeach
            </div>
            @else
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="team-card reveal">
                        <div class="team-avatar">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Team member">
                            <div class="team-social-overlay">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                        <div class="team-content">
                            <h4>School Principal</h4>
                            <p>Leadership & Administration</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-card reveal">
                        <div class="team-avatar">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Team member">
                            <div class="team-social-overlay">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                        <div class="team-content">
                            <h4>Vice Principal</h4>
                            <p>Academic Affairs</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-card reveal">
                        <div class="team-avatar">
                            <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Team member">
                            <div class="team-social-overlay">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                        <div class="team-content">
                            <h4>Head of Student Affairs</h4>
                            <p>Student Welfare</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="team-card reveal">
                        <div class="team-avatar">
                            <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Team member">
                            <div class="team-social-overlay">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                        <div class="team-content">
                            <h4>Athletics Director</h4>
                            <p>Sports & Activities</p>
                        </div>
                    </div>
                </div>
            </div>
            @endisset
        </div>
    </section>
@endsection
