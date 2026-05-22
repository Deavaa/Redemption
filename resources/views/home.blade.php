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
<section class="section" style="background:linear-gradient(135deg,#1E90FF,#1565C0);color:#fff;text-align:center">
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