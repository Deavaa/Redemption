<?php
echo "Creating main layout...\n";
 $v = 'resources/views/layouts/app.blade.php';
 $c = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','School of Redemption')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--navy:#0d0d2b;--navy2:#1a1a5e;--gold:#c9a84c;--gold-l:#f0d78c}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Poppins',sans-serif;color:#333}
.navbar{background:var(--navy)!important;padding:10px 0;transition:.3s}
.navbar-brand{display:flex;flex-direction:column;line-height:1.2;text-decoration:none}
.brand-sm{font-size:10px;color:var(--gold);text-transform:uppercase;letter-spacing:3px}
.brand-lg{font-size:22px;color:#fff;font-family:'Playfair Display',serif;font-weight:700;letter-spacing:2px}
.nav-link{color:rgba(255,255,255,.8)!important;font-weight:500;margin:0 5px;font-size:14px;transition:.3s}
.nav-link:hover,.nav-link.active{color:var(--gold)!important}
.hero{background:linear-gradient(135deg,var(--navy),var(--navy2));color:#fff;padding:120px 0;text-align:center;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(201,168,76,.08)0%,transparent 50%);animation:pulse 4s ease-in-out infinite}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.1)}}
.hero h1{font-family:'Playfair Display',serif;font-size:3.5rem;margin-bottom:20px;position:relative}
.hero p{font-size:1.1rem;opacity:.9;max-width:600px;margin:0 auto 30px;position:relative}
.btn-gold{background:var(--gold);color:var(--navy);border:none;padding:12px 35px;font-weight:600;border-radius:50px;transition:.3s;display:inline-block;text-decoration:none}
.btn-gold:hover{background:var(--gold-l);transform:translateY(-2px);color:var(--navy)}
.section{padding:80px 0}
.stitle{text-align:center;margin-bottom:50px}
.stitle h2{font-family:'Playfair Display',serif;color:var(--navy);font-size:2.2rem;position:relative;display:inline-block}
.stitle h2::after{content:'';width:60px;height:3px;background:var(--gold);display:block;margin:12px auto 0}
.stitle p{color:#666;margin-top:10px}
.gold-text{color:var(--gold)!important}
footer{background:var(--navy);color:rgba(255,255,255,.7);padding:50px 0 20px}
footer h5{color:var(--gold);margin-bottom:15px;font-size:16px}
footer a{color:rgba(255,255,255,.7);text-decoration:none;font-size:14px;transition:.3s}
footer a:hover{color:var(--gold)}
.fbot{border-top:1px solid rgba(255,255,255,.1);padding-top:15px;margin-top:30px;text-align:center;font-size:13px}
.card{border:none;border-radius:12px;overflow:hidden;transition:.3s;box-shadow:0 2px 15px rgba(0,0,0,.08)}
.card:hover{transform:translateY(-5px);box-shadow:0 10px 30px rgba(0,0,0,.15)}
.form-control:focus{border-color:var(--gold);box-shadow:0 0 0 .2rem rgba(201,168,76,.25)}
</style>
@stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
<div class="container">
<a class="navbar-brand" href="{{url('/')}}">
<span class="brand-sm">School of</span><span class="brand-lg">REDEMPTION</span>
</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nv"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="nv">
<ul class="navbar-nav ms-auto">
<li class="nav-item"><a class="nav-link {{request()->is('/')?'active':''}}" href="{{url('/')}}">Home</a></li>
<li class="nav-item"><a class="nav-link {{request()->is('about')?'active':''}}" href="{{url('about')}}">About</a></li>
<li class="nav-item"><a class="nav-link {{request()->is('gallery')?'active':''}}" href="{{url('gallery')}}">Gallery</a></li>
<li class="nav-item"><a class="nav-link {{request()->is('contact')?'active':''}}" href="{{url('contact')}}">Contact</a></li>
<li class="nav-item"><a class="nav-link {{request()->is('team')?'active':''}}" href="{{url('team')}}">Team</a></li>
<li class="nav-item ms-2"><a class="btn btn-gold btn-sm py-1 px-3" href="{{url('login')}}"><i class="fas fa-sign-in-alt me-1"></i>Login</a></li>
</ul>
</div>
</div>
</nav>
@yield('content')
<footer class="mt-auto">
<div class="container">
<div class="row g-4">
<div class="col-lg-4">
<h5>School of Redemption</h5>
<p style="font-size:14px;line-height:1.8">Nurturing minds and building futures with excellence in education and character development since our founding.</p>
<div class="mt-3">
<a href="#" class="me-3 text-white"><i class="fab fa-facebook-f"></i></a>
<a href="#" class="me-3 text-white"><i class="fab fa-twitter"></i></a>
<a href="#" class="me-3 text-white"><i class="fab fa-instagram"></i></a>
<a href="#" class="me-3 text-white"><i class="fab fa-youtube"></i></a>
</div>
</div>
<div class="col-lg-2">
<h5>Quick Links</h5>
<ul class="list-unstyled" style="font-size:14px">
<li class="mb-2"><a href="{{url('/')}}"><i class="fas fa-angle-right me-1"></i>Home</a></li>
<li class="mb-2"><a href="{{url('about')}}"><i class="fas fa-angle-right me-1"></i>About</a></li>
<li class="mb-2"><a href="{{url('gallery')}}"><i class="fas fa-angle-right me-1"></i>Gallery</a></li>
<li class="mb-2"><a href="{{url('contact')}}"><i class="fas fa-angle-right me-1"></i>Contact</a></li>
</ul>
</div>
<div class="col-lg-3">
<h5>Academics</h5>
<ul class="list-unstyled" style="font-size:14px">
<li class="mb-2"><a href="#"><i class="fas fa-angle-right me-1"></i>Programs</a></li>
<li class="mb-2"><a href="#"><i class="fas fa-angle-right me-1"></i>Admissions</a></li>
<li class="mb-2"><a href="#"><i class="fas fa-angle-right me-1"></i>Calendar</a></li>
<li class="mb-2"><a href="#"><i class="fas fa-angle-right me-1"></i>Results</a></li>
</ul>
</div>
<div class="col-lg-3">
<h5>Contact Us</h5>
<ul class="list-unstyled" style="font-size:14px">
<li class="mb-2"><i class="fas fa-map-marker-alt gold-text me-2"></i>123 Education St, City</li>
<li class="mb-2"><i class="fas fa-phone gold-text me-2"></i>+251-XXX-XXXXXX</li>
<li class="mb-2"><i class="fas fa-envelope gold-text me-2"></i>info@schoolofredemption.com</li>
<li class="mb-2"><i class="fas fa-clock gold-text me-2"></i>Mon-Fri: 8AM-5PM</li>
</ul>
</div>
</div>
<div class="fbot">&copy; {{date('Y')}} School of Redemption. All rights reserved.</div>
</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
HTML;
@mkdir('resources/views/layouts', 0755, true);
file_put_contents($v, $c);
echo "DONE: $v\n";
