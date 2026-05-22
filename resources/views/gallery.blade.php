@extends('layouts.app')
@section('title','Gallery - School of Redemption')
@section('content')
<section class="hero" style="padding:80px 0">
    <div class="container position-relative">
        <h1>Our Gallery</h1>
        <p>Explore moments from our school life and activities.</p>
    </div>
</section>

{{-- Video Highlights Section --}}
@if($websiteVideos->count() > 0 || $galleryVideos->count() > 0)
<section class="section">
    <div class="container">
        <h3 class="mb-3 gold-text"><i class="fas fa-video me-2"></i>Video Highlights</h3>
        <div class="row g-4 mb-5">
            {{-- Videos from Video Library (admin video library with show_on_website) --}}
            @foreach($websiteVideos as $video)
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                    <div style="position:relative;padding-bottom:56.25%;height:0;background:#0d0d2b;">
                        @if($video->youtube_video_id)
                        <iframe src="https://www.youtube.com/embed/{{ $video->youtube_video_id }}"
                            style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"
                            allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                        </iframe>
                        @endif
                    </div>
                    <div class="card-body">
                        <h6 class="mb-1" style="font-weight:700;color:#0d0d2b;">{{ $video->title }}</h6>
                        @if($video->channel_name)
                        <small class="text-muted"><i class="fab fa-youtube text-danger me-1"></i>{{ $video->channel_name }}</small>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Videos from GalleryVideo (website gallery management) --}}
            @foreach($galleryVideos as $gv)
            @php
                $gvVideoId = null;
                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $gv->video_url, $m)) {
                    $gvVideoId = $m[1];
                }
            @endphp
            @if($gvVideoId)
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;">
                    <div style="position:relative;padding-bottom:56.25%;height:0;background:#0d0d2b;">
                        <iframe src="https://www.youtube.com/embed/{{ $gvVideoId }}"
                            style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;"
                            allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                        </iframe>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-1" style="font-weight:700;color:#0d0d2b;">{{ $gv->title }}</h6>
                        @if($gv->description)
                        <small class="text-muted">{{ Str::limit($gv->description, 80) }}</small>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Photo Gallery Section --}}
<section class="section">
    <div class="container">
        <h3 class="mb-3 gold-text"><i class="fas fa-images me-2"></i>Photo Gallery</h3>
        <div class="row g-3">
            @forelse($galleryImages as $image)
            <div class="col-lg-3 col-md-4 col-6">
                <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden;cursor:pointer;">
                    @if($image->image_path && file_exists(public_path('storage/' . $image->image_path)))
                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $image->title ?? 'Gallery' }}"
                        style="width:100%;height:200px;object-fit:cover;" loading="lazy">
                    @else
                    <div style="height:200px;background:linear-gradient(135deg,#0d0d2b,#1a1a5e);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-image" style="font-size:2rem;color:var(--secondary-color, #c9a84c);"></i>
                    </div>
                    @endif
                    @if($image->title)
                    <div class="card-body p-2">
                        <small class="text-muted">{{ $image->title }}</small>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-12 text-center py-5">
                <i class="fas fa-images" style="font-size:3rem;color:#ccc;"></i>
                <p class="mt-2 text-muted">No photos available yet.</p>
            </div>
            @endforelse
        </div>

        {{-- Photo Pagination --}}
        @if($galleryImages->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $galleryImages->withQueryString()->links() }}
        </div>
        @endif
    </div>
</section>
@endsection
