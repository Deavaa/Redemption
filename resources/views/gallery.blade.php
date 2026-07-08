@extends('layouts.website')

@section('title', 'Gallery - ' . ($settings['school_name'] ?? 'School'))

@push('styles')
<style>
    /* ========== Gallery Page Styles ========== */
    .gallery-hero {
        position: relative;
        padding: 0.8rem 0 0.5rem;
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.98) 0%, rgba(var(--primary-rgb), 0.92) 100%);
        overflow: hidden;
    }

    .gallery-hero::before {
        display: none;
    }

    .gallery-hero::after {
        display: none;
    }

    .gallery-hero h1 {
        font-size: 1rem;
        color: var(--white);
        margin-bottom: 0.1rem;
        text-shadow: none;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
    }

    .gallery-hero h1 span {
        color: var(--secondary-color);
    }

    .gallery-hero p {
        color: rgba(255,255,255,0.7);
        font-size: 0.75rem;
        margin-bottom: 0.1rem;
    }

    .gallery-hero .breadcrumb {
        background: none;
        padding: 0;
        margin-top: 0.75rem;
    }

    .gallery-hero .breadcrumb-item a {
        color: rgba(255,255,255,0.7);
        text-decoration: none;
    }

    .gallery-hero .breadcrumb-item a:hover {
        color: var(--secondary-color);
    }

    .gallery-hero .breadcrumb-item.active {
        color: var(--secondary-color);
    }

    .gallery-hero .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255,255,255,0.5);
    }

    /* Decorative shapes - hidden for compact header */
    .hero-deco { display: none; }
    .hero-deco-1 { display: none; }
    .hero-deco-2 { display: none; }

    /* Video Gallery */
    .video-highlights {
        padding: 5rem 0;
        background: var(--light-bg);
    }

    .video-highlight-card {
        border-radius: 20px;
        overflow: hidden;
        background: var(--white);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
        height: 100%;
    }

    .video-highlight-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }

    .video-highlight-thumb {
        position: relative;
        padding-bottom: 56.25%;
        background: var(--primary-color);
        overflow: hidden;
    }

    .video-highlight-thumb iframe {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        border: none;
    }

    .video-highlight-info {
        padding: 1.25rem;
    }

    .video-highlight-info h5 {
        font-size: 1rem;
        margin-bottom: 0.5rem;
        color: var(--primary-color);
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
    }

    .video-highlight-info .video-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.8rem;
        color: var(--text-light);
    }

    .video-highlight-info .video-category {
        background: rgba(212, 160, 23, 0.1);
        color: var(--secondary-color);
        padding: 0.15rem 0.6rem;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 600;
        border: 1px solid rgba(212, 160, 23, 0.2);
    }

    /* Gallery */
    .photo-gallery {
        padding: 5rem 0;
        background: var(--white);
    }

    .gallery-masonry {
        columns: 4;
        column-gap: 1rem;
    }

    .gallery-photo-item {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        cursor: pointer;
        margin-bottom: 1rem;
        break-inside: avoid;
        display: inline-block;
        width: 100%;
        transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
    }

    .gallery-photo-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }

    .gallery-photo-item img {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.5s ease;
    }

    .gallery-photo-item:hover img {
        transform: scale(1.05);
    }

    .gallery-photo-overlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.75) 0%, rgba(var(--primary-rgb), 0.4) 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-photo-item:hover .gallery-photo-overlay {
        opacity: 1;
    }

    .gallery-photo-overlay i {
        font-size: 2rem;
        color: var(--white);
        margin-bottom: 0.5rem;
    }

    .gallery-photo-overlay span {
        color: var(--white);
        font-size: 0.85rem;
        font-weight: 500;
        text-align: center;
        padding: 0 1rem;
    }

    .gallery-empty {
        text-align: center;
        padding: 5rem 2rem;
    }

    .gallery-empty i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 1.5rem;
    }

    .gallery-empty h4 {
        color: var(--text-light);
        margin-bottom: 0.5rem;
    }

    .gallery-empty p {
        color: var(--text-light);
        font-size: 0.95rem;
    }

    /* Lightbox */
    .lightbox-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.92);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        padding: 2rem;
    }

    .lightbox-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .lightbox-content {
        max-width: 90vw;
        max-height: 85vh;
        position: relative;
    }

    .lightbox-content img {
        max-width: 100%;
        max-height: 85vh;
        border-radius: 16px;
        object-fit: contain;
    }

    .lightbox-close {
        position: absolute;
        top: -45px; right: 0;
        background: none;
        border: none;
        color: var(--white);
        font-size: 1.75rem;
        cursor: pointer;
        transition: transform 0.3s ease;
        z-index: 10001;
    }

    .lightbox-close:hover {
        transform: rotate(90deg);
    }

    .lightbox-title {
        text-align: center;
        color: rgba(255,255,255,0.7);
        font-size: 0.9rem;
        margin-top: 1rem;
    }

    /* Modern Pagination */
    .pagination .page-link {
        border: none;
        border-radius: 12px;
        margin: 0 4px;
        padding: 0.5rem 1rem;
        color: var(--text-dark);
        font-weight: 500;
        transition: all 0.3s ease;
        background: var(--light-bg);
    }

    .pagination .page-link:hover {
        background: var(--primary-color);
        color: var(--white);
        transform: translateY(-2px);
    }

    .pagination .page-item.active .page-link {
        background: var(--secondary-color);
        color: var(--primary-color);
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(212, 160, 23, 0.35);
    }

    .pagination .page-item.disabled .page-link {
        background: var(--light-bg);
        color: var(--text-light);
        opacity: 0.5;
    }

    @media (max-width: 1199px) {
        .gallery-masonry { columns: 3; }
    }

    @media (max-width: 991px) {
        .gallery-hero h1 { font-size: 0.95rem; }
        .gallery-hero { padding: 0.6rem 0 0.4rem; }
        .gallery-masonry { columns: 2; }
    }

    @media (max-width: 575px) {
        .gallery-hero h1 { font-size: 0.9rem; }
        .gallery-hero { padding: 0.5rem 0 0.3rem; }
        .gallery-masonry { columns: 2; }
    }
</style>
@endpush

@section('content')
    <!-- ========== Gallery Hero Section ========== -->
    <section class="gallery-hero">
        <div class="hero-deco hero-deco-1"></div>
        <div class="hero-deco hero-deco-2"></div>
        <div class="container position-relative" style="z-index:2;">
            <h1>Our <span>Gallery</span></h1>
            <p>Explore moments from our school life, activities, and achievements.</p>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fas fa-home me-1"></i>Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Gallery</li>
                </ol>
            </nav>
        </div>
    </section>

    {{-- Video Gallery Section --}}
    @if($websiteVideos->count() > 0 || $galleryVideos->count() > 0)
    <section class="video-highlights">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge"><i class="fas fa-video me-2"></i>Video Gallery</span>
                <h2>Watch Our Stories</h2>
                <p>Catch a glimpse of our school events, educational content, and student achievements.</p>
            </div>
            <div class="row g-4">
                {{-- Videos from Video Library (admin video library with show_on_website) --}}
                @foreach($websiteVideos as $video)
                <div class="col-lg-4 col-md-6">
                    <div class="video-highlight-card reveal">
                        <div class="video-highlight-thumb">
                            @if($video->youtube_video_id)
                            <iframe src="https://www.youtube.com/embed/{{ $video->youtube_video_id }}"
                                allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                            </iframe>
                            @endif
                        </div>
                        <div class="video-highlight-info">
                            <h5>{{ $video->title }}</h5>
                            <div class="video-meta">
                                @if($video->channel_name)
                                <span><i class="fab fa-youtube text-danger me-1"></i>{{ $video->channel_name }}</span>
                                @endif
                                @if($video->category)
                                <span class="video-category">{{ $video->category }}</span>
                                @endif
                            </div>
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
                    <div class="video-highlight-card reveal">
                        <div class="video-highlight-thumb">
                            <iframe src="https://www.youtube.com/embed/{{ $gvVideoId }}"
                                allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                            </iframe>
                        </div>
                        <div class="video-highlight-info">
                            <h5>{{ $gv->title }}</h5>
                            @if($gv->description)
                            <div class="video-meta">
                                <span>{{ Str::limit($gv->description, 80) }}</span>
                            </div>
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

    {{-- Gallery Section --}}
    <section class="photo-gallery">
        <div class="container">
            <div class="section-header reveal">
                <span class="section-badge"><i class="fas fa-images me-2"></i>Gallery</span>
                <h2>Gallery</h2>
                <p>Explore our vibrant campus through these captured moments of learning, creativity, and achievement.</p>
            </div>

            @if($galleryImages->count() > 0)
            <div class="gallery-masonry">
                @foreach($galleryImages as $image)
                <div class="gallery-photo-item reveal" onclick="openLightbox(this)">
                    @php
                        // Resolve image URL — try public/gallery/ first (no symlink needed),
                        // then fall back to storage/
                        $imgUrl = null;
                        if ($image->image_path) {
                            $basename = basename($image->image_path);
                            if (file_exists(public_path('gallery/' . $basename))) {
                                $imgUrl = asset('gallery/' . $basename);
                            } elseif (file_exists(public_path('storage/' . $image->image_path))) {
                                $imgUrl = asset('storage/' . $image->image_path);
                            }
                        }
                    @endphp
                    @if($imgUrl)
                    <img src="{{ $imgUrl }}" alt="{{ $image->title ?? 'Gallery' }}" loading="lazy">
                    @else
                    <div style="height:200px;background:linear-gradient(135deg,var(--primary-color),#0D3B12);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-image" style="font-size:2rem;color:var(--secondary-color);"></i>
                    </div>
                    @endif
                    <div class="gallery-photo-overlay">
                        <i class="fas fa-search-plus"></i>
                        @if($image->title)
                        <span>{{ $image->title }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="gallery-empty">
                <i class="fas fa-images"></i>
                <h4>No Photos Yet</h4>
                <p>Check back soon — we're constantly adding new moments from campus life!</p>
            </div>
            @endif

            {{-- Photo Pagination --}}
            @if($galleryImages->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $galleryImages->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </section>

    <!-- Lightbox Overlay -->
    <div class="lightbox-overlay" id="lightboxOverlay">
        <div class="lightbox-content">
            <button class="lightbox-close" id="lightboxClose" aria-label="Close lightbox">
                <i class="fas fa-times"></i>
            </button>
            <img id="lightboxImage" src="" alt="Gallery image">
            <div class="lightbox-title" id="lightboxTitle"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // ========== Lightbox ==========
    function openLightbox(el) {
        var img = el.querySelector('img');
        var title = el.querySelector('.gallery-photo-overlay span');
        if (!img) return;

        var overlay = document.getElementById('lightboxOverlay');
        var lightboxImg = document.getElementById('lightboxImage');
        var lightboxTitle = document.getElementById('lightboxTitle');

        lightboxImg.src = img.src;
        lightboxTitle.textContent = title ? title.textContent : '';
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    (function() {
        var overlay = document.getElementById('lightboxOverlay');
        if (!overlay) return;
        var closeBtn = document.getElementById('lightboxClose');
        var lightboxImg = document.getElementById('lightboxImage');

        function closeLightbox() {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
            setTimeout(function() { lightboxImg.src = ''; }, 300);
        }

        closeBtn.addEventListener('click', closeLightbox);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeLightbox();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) {
                closeLightbox();
            }
        });
    })();
</script>
@endpush
