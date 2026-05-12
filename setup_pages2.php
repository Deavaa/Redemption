<?php
echo "Creating gallery page...\n";
 $c = <<<'HTML'
@extends('layouts.app')
@section('title','Gallery - School of Redemption')
@section('content')
<section class="hero" style="padding:80px 0"><div class="container position-relative"><h1>Our Gallery</h1><p>Explore moments from our school life and activities.</p></div></section>
<section class="section"><div class="container">
<h3 class="mb-3 gold-text"><i class="fas fa-video me-2"></i>Video Highlights</h3>
<div style="display:flex;gap:15px;overflow-x:auto;padding-bottom:15px" class="mb-5">
<div style="min-width:320px;flex-shrink:0"><div style="position:relative;padding-bottom:56.25%;height:0"><iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:10px" allowfullscreen></iframe></div><p class="mt-2 small text-muted">School Annual Ceremony 2024</p></div>
<div style="min-width:320px;flex-shrink:0"><div style="position:relative;padding-bottom:56.25%;height:0"><iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:10px" allowfullscreen></iframe></div><p class="mt-2 small text-muted">Science Fair Highlights</p></div>
<div style="min-width:320px;flex-shrink:0"><div style="position:relative;padding-bottom:56.25%;height:0"><iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:10px" allowfullscreen></iframe></div><p class="mt-2 small text-muted">Sports Day Celebration</p></div>
</div>
<h3 class="mb-3 gold-text"><i class="fas fa-images me-2"></i>Photo Gallery</h3>
<div class="row g-3">
<div class="col-md-3 col-6"><div class="card" style="cursor:pointer"><div style="height:180px;background:linear-gradient(135deg,#0d0d2b,#1a1a5e);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:2rem"><i class="fas fa-school"></i></div><div class="card-body p-2"><small class="text-muted">Campus View</small></div></div></div>
<div class="col-md-3 col-6"><div class="card" style="cursor:pointer"><div style="height:180px;background:linear-gradient(135deg,#1a1a5e,#0d0d2b);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:2rem"><i class="fas fa-flask"></i></div><div class="card-body p-2"><small class="text-muted">Science Lab</small></div></div></div>
<div class="col-md-3 col-6"><div class="card" style="cursor:pointer"><div style="height:180px;background:linear-gradient(135deg,#0d0d2b,#1a1a5e);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:2rem"><i class="fas fa-book-open"></i></div><div class="card-body p-2"><small class="text-muted">Library</small></div></div></div>
<div class="col-md-3 col-6"><div class="card" style="cursor:pointer"><div style="height:180px;background:linear-gradient(135deg,#1a1a5e,#0d0d2b);display:flex;align-items:center;justify-content:center;color:var(--gold);font-size:2rem"><i class="fas fa-running"></i></div><div class="card-body p-2"><small class="text-muted">Sports Ground</small></div></div></div>
</div></div></section>
@endsection
HTML;
file_put_contents('resources/views/gallery.blade.php', $c);
echo "DONE: gallery.blade.php\n";

echo "Creating contact page...\n";
 $c = <<<'HTML'
@extends('layouts.app')
@section('title','Contact Us - School of Redemption')
@section('content')
<section class="hero" style="padding:80px 0"><div class="container position-relative"><h1>Contact Us</h1><p>We would love to hear from you. Reach out to us anytime.</p></div></section>
<section class="section"><div class="container"><div class="row g-5">
<div class="col-lg-5">
<h4 class="mb-4">Get In Touch</h4>
<div class="mb-4"><i class="fas fa-map-marker-alt gold-text fa-lg me-3"></i><strong>Main Campus</strong><p class="text-muted mt-1 ms-4">123 Education Street, Addis Ababa, Ethiopia</p></div>
<div class="mb-4"><i class="fas fa-map-marker-alt gold-text fa-lg me-3"></i><strong>Branch 2</strong><p class="text-muted mt-1 ms-4">456 Knowledge Avenue, Addis Ababa, Ethiopia</p></div>
<div class="mb-3"><i class="fas fa-phone gold-text me-3"></i>+251-XXX-XXXXXX</div>
<div class="mb-3"><i class="fas fa-envelope gold-text me-3"></i>info@schoolofredemption.com</div>
<div class="mb-3"><i class="fas fa-clock gold-text me-3"></i>Mon - Fri: 8:00 AM - 5:00 PM</div>
<h5 class="mt-4 mb-3">Main Campus Location</h5>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.8!2d38.746!3d9.02!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMDEnMTIuMCJOIDM4wrA0NCc0NS42IkU!5e0!3m2!1sen!2set!4v1" width="100%" height="200" style="border:0;border-radius:10px" allowfullscreen></iframe>
<h5 class="mt-4 mb-3">Branch 2 Location</h5>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.8!2d38.746!3d9.02!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMDEnMTIuMCJOIDM4wrA0NCc0NS42IkU!5e0!3m2!1sen!2set!4v1" width="100%" height="200" style="border:0;border-radius:10px" allowfullscreen></iframe>
</div>
<div class="col-lg-7">
<div class="card p-4"><h4 class="mb-4">Send Us a Message</h4>
@if(session('success'))
<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>{{session('success')}}</div>
@endif
<form method="POST" action="{{url('contact')}}">
@csrf
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
<div class="col-md-6"><label class="form-label">Subject *</label><input type="text" name="subject" class="form-control" required></div>
<div class="col-12"><label class="form-label">Message *</label><textarea name="message" class="form-control" rows="5" required></textarea></div>
<div class="col-12"><button type="submit" class="btn btn-gold"><i class="fas fa-paper-plane me-2"></i>Send Message</button></div>
</div></form></div>
</div></div></div></section>
@endsection
HTML;
file_put_contents('resources/views/contact.blade.php', $c);
echo "DONE: contact.blade.php\n";

echo "Creating team page...\n";
 $c = <<<'HTML'
@extends('layouts.app')
@section('title','Our Team - School of Redemption')
@section('content')
<section class="hero" style="padding:80px 0"><div class="container position-relative"><h1>Our Team</h1><p>Meet the dedicated leaders who guide our school to excellence.</p></div></section>
<section class="section"><div class="container">
<div class="stitle"><h2>Leadership Team</h2></div>
<div class="row g-4">
<div class="col-md-3 col-6">
<div class="card text-center p-4"><div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#0d0d2b,#1a1a5e);margin:0 auto 15px;display:flex;align-items:center;justify-content:center"><i class="fas fa-user-tie fa-2x gold-text"></i></div><h6>Dr. Abraham Tesfaye</h6><small class="gold-text">Principal</small><p class="small text-muted mt-2">PhD in Education with 20+ years of leadership experience in academic excellence.</p></div>
</div>
<div class="col-md-3 col-6">
<div class="card text-center p-4"><div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#1a1a5e,#0d0d2b);margin:0 auto 15px;display:flex;align-items:center;justify-content:center"><i class="fas fa-user-tie fa-2x gold-text"></i></div><h6>Mrs. Sara Kebede</h6><small class="gold-text">Vice Principal</small><p class="small text-muted mt-2">Masters in Educational Administration, dedicated to student welfare and development.</p></div>
</div>
<div class="col-md-3 col-6">
<div class="card text-center p-4"><div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#0d0d2b,#1a1a5e);margin:0 auto 15px;display:flex;align-items:center;justify-content:center"><i class="fas fa-user-tie fa-2x gold-text"></i></div><h6>Mr. Daniel Haile</h6><small class="gold-text">Academic Director</small><p class="small text-muted mt-2">Leading curriculum development and ensuring the highest academic standards across all programs.</p></div>
</div>
<div class="col-md-3 col-6">
<div class="card text-center p-4"><div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#1a1a5e,#0d0d2b);margin:0 auto 15px;display:flex;align-items:center;justify-content:center"><i class="fas fa-user-tie fa-2x gold-text"></i></div><h6>Mrs. Tigist Mulugeta</h6><small class="gold-text">Admin Manager</small><p class="small text-muted mt-2">Overseeing school operations, finance, and human resources with efficiency and care.</p></div>
</div>
</div></div></section>
@endsection
HTML;
file_put_contents('resources/views/team.blade.php', $c);
echo "DONE: team.blade.php\n";
