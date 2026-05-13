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