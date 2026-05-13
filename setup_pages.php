<?php
echo "Creating home page...\n";
 $c = <<<'HTML'
@extends('layouts.app')
@section('title','Home - School of Redemption')
@section('content')
<section class="hero">
<div class="container position-relative">
<h1>Welcome to <span class="gold-text">School of Redemption</span></h1>
<p>Empowering students with knowledge, character, and faith to become leaders of tomorrow.</p>
<a href="{{url('about')}}" class="btn btn-gold">Discover More <i class="fas fa-arrow-right ms-2"></i></a>
</div>
</section>
<section class="section bg-light">
<div class="container">
<div class="stitle"><h2>Why Choose Us</h2><p>Excellence in education, nurturing future leaders</p></div>
<div class="row g-4">
<div class="col-md-4"><div class="card h-100 p-4 text-center">
<div class="mb-3"><i class="fas fa-graduation-cap fa-3x gold-text"></i></div>
<h5>Academic Excellence</h5>
<p class="text-muted">Our rigorous curriculum and dedicated teachers ensure every student reaches their full academic potential and achieves outstanding results.</p>
</div></div>
<div class="col-md-4"><div class="card h-100 p-4 text-center">
<div class="mb-3"><i class="fas fa-heart fa-3x gold-text"></i></div>
<h5>Character Development</h5>
<p class="text-muted">We focus on building strong moral values, integrity, and compassion in every student through comprehensive character education programs.</p>
</div></div>
<div class="col-md-4"><div class="card h-100 p-4 text-center">
<div class="mb-3"><i class="fas fa-users fa-3x gold-text"></i></div>
<h5>Community</h5>
<p class="text-muted">Our supportive school community fosters collaboration, mutual respect, and lifelong friendships among students, parents, and staff.</p>
</div></div>
</div></div></section>
<section class="section"><div class="container">
<div class="stitle"><h2>Our Programs</h2></div>
<div class="row g-4">
<div class="col-md-3 col-6"><div class="card h-100 text-center p-3"><i class="fas fa-baby fa-2x gold-text mb-3"></i><h6>Kindergarten</h6><p class="small text-muted">Ages 4-6</p></div></div>
<div class="col-md-3 col-6"><div class="card h-100 text-center p-3"><i class="fas fa-child fa-2x gold-text mb-3"></i><h6>Primary</h6><p class="small text-muted">Grades 1-6</p></div></div>
<div class="col-md-3 col-6"><div class="card h-100 text-center p-3"><i class="fas fa-book-reader fa-2x gold-text mb-3"></i><h6>Secondary</h6><p class="small text-muted">Grades 7-10</p></div></div>
<div class="col-md-3 col-6"><div class="card h-100 text-center p-3"><i class="fas fa-university fa-2x gold-text mb-3"></i><h6>Preparatory</h6><p class="small text-muted">Grades 11-12</p></div></div>
</div></div></section>
<section class="section" style="background:linear-gradient(135deg,#0d0d2b,#1a1a5e);color:#fff;text-align:center">
<div class="container"><div class="row g-4">
<div class="col-md-3"><h2 class="gold-text">1500+</h2><p>Students Enrolled</p></div>
<div class="col-md-3"><h2 class="gold-text">120+</h2><p>Qualified Teachers</p></div>
<div class="col-md-3"><h2 class="gold-text">25+</h2><p>Years of Excellence</p></div>
<div class="col-md-3"><h2 class="gold-text">98%</h2><p>Pass Rate</p></div>
</div></div></section>
<section class="section"><div class="container text-center">
<h2 class="mb-4" style="font-family:'Playfair Display',serif">Ready to Join Our Family?</h2>
<p class="text-muted mb-4">Give your child the gift of quality education in a nurturing environment.</p>
<a href="{{url('contact')}}" class="btn btn-gold">Contact Us <i class="fas fa-arrow-right ms-2"></i></a>
</div></section>
@endsection
HTML;
file_put_contents('resources/views/home.blade.php', $c);
echo "DONE: home.blade.php\n";

echo "Creating about page...\n";
 $c = <<<'HTML'
@extends('layouts.app')
@section('title','About Us - School of Redemption')
@section('content')
<section class="hero" style="padding:80px 0">
<div class="container position-relative"><h1>About Us</h1><p>Learn about our mission, vision, and the values that drive us.</p></div>
</section>
<section class="section"><div class="container"><div class="row g-5">
<div class="col-lg-6">
<h2 class="mb-4" style="font-family:'Playfair Display',serif;color:#0d0d2b">Our Mission</h2>
<p style="line-height:1.8;color:#555">The School of Redemption is dedicated to providing transformative education that nurtures intellectual growth, moral character, and spiritual development. We strive to create a learning environment where every student discovers their unique potential and develops the skills and values needed to make a positive impact in the world.</p>
<h3 class="mt-4 mb-3" style="color:#1a1a5e">Our Vision</h3>
<p style="line-height:1.8;color:#555">To be a leading educational institution recognized for academic excellence, holistic development, and producing graduates who are compassionate leaders and responsible global citizens equipped for the challenges of tomorrow.</p>
</div>
<div class="col-lg-6">
<h2 class="mb-4" style="font-family:'Playfair Display',serif;color:#0d0d2b">Our History</h2>
<p style="line-height:1.8;color:#555">Founded over two decades ago, the School of Redemption began with a simple yet powerful vision: to provide quality education that goes beyond textbooks. What started with just a handful of students and dedicated teachers has grown into a thriving institution serving over 1,500 students across all grade levels.</p>
<p style="line-height:1.8;color:#555">Our journey has been marked by consistent academic achievements, community service initiatives, and the development of innovative teaching methodologies that prepare students for the challenges of the modern world.</p>
</div></div></div></section>
<section class="section bg-light"><div class="container">
<div class="stitle"><h2>Our Core Values</h2></div>
<div class="row g-4">
<div class="col-md-3 col-6 text-center"><div class="card p-4 h-100"><i class="fas fa-star fa-2x gold-text mb-3"></i><h6>Excellence</h6><p class="small text-muted">Pursuing the highest standards</p></div></div>
<div class="col-md-3 col-6 text-center"><div class="card p-4 h-100"><i class="fas fa-handshake fa-2x gold-text mb-3"></i><h6>Integrity</h6><p class="small text-muted">Honesty and accountability</p></div></div>
<div class="col-md-3 col-6 text-center"><div class="card p-4 h-100"><i class="fas fa-lightbulb fa-2x gold-text mb-3"></i><h6>Innovation</h6><p class="small text-muted">Creative teaching approaches</p></div></div>
<div class="col-md-3 col-6 text-center"><div class="card p-4 h-100"><i class="fas fa-globe fa-2x gold-text mb-3"></i><h6>Service</h6><p class="small text-muted">Making a difference</p></div></div>
</div></div></section>
@endsection
HTML;
file_put_contents('resources/views/about.blade.php', $c);
echo "DONE: about.blade.php\n";
