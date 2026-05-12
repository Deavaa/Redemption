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