@extends('layouts.website')

@section('title', 'Our Team - ' . ($settings['school_name'] ?? 'School'))

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
                            <img src="{{ asset('storage/' . $member->photo) }}" alt="{{ $member->name }}">
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
