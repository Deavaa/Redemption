@extends('layouts.admin')
@section('title', 'Video Library')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.library.index') }}">Digital Library</a></li>
                    <li class="active">Video Library</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            @if($canManage)
            <a href="{{ route('admin.video-library.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i>
                <span>Add Video</span>
            </a>
            @endif
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-red">
                <i class="fab fa-youtube"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalVideos }}</span>
                <span class="modern-stat-label">Total Videos</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-play-circle"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $activeVideos }}</span>
                <span class="modern-stat-label">Available</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-eye"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalViews }}</span>
                <span class="modern-stat-label">Total Views</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-purple">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $categories->count() }}</span>
                <span class="modern-stat-label">Categories</span>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.5rem;">
        <div class="modern-card-body" style="padding:1rem 1.5rem;">
            <form method="GET" action="{{ route('admin.video-library.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;color:#6b7280;">Search</label>
                    <div class="modern-search-box" style="width:100%;">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title, channel, category..." style="width:100%;">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;color:#6b7280;">Category</label>
                    <select name="category" class="form-select form-select-sm" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.55rem 0.75rem;font-size:0.875rem;">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;color:#6b7280;">Type</label>
                    <select name="video_type" class="form-select form-select-sm" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.55rem 0.75rem;font-size:0.875rem;">
                        <option value="">All Types</option>
                        <option value="single" {{ request('video_type') === 'single' ? 'selected' : '' }}>Single Video</option>
                        <option value="channel" {{ request('video_type') === 'channel' ? 'selected' : '' }}>Channel</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;color:#6b7280;">Access</label>
                    <select name="access_level" class="form-select form-select-sm" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.55rem 0.75rem;font-size:0.875rem;">
                        <option value="">All Levels</option>
                        <option value="all" {{ request('access_level') === 'all' ? 'selected' : '' }}>Everyone</option>
                        <option value="teacher" {{ request('access_level') === 'teacher' ? 'selected' : '' }}>Teachers Only</option>
                        <option value="student" {{ request('access_level') === 'student' ? 'selected' : '' }}>Students Only</option>
                        <option value="staff" {{ request('access_level') === 'staff' ? 'selected' : '' }}>Staff Only</option>
                        <option value="admin" {{ request('access_level') === 'admin' ? 'selected' : '' }}>Admin Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn-modern btn-modern-primary" style="width:100%;justify-content:center;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Videos Grid --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">Video Library</h2>
                <span class="modern-badge modern-badge-light">{{ $videos->total() }} videos</span>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.5rem;">
            @if(session('success'))
                <div class="modern-alert modern-alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($videos->count() > 0)
            <div class="video-library-grid">
                @foreach($videos as $video)
                <div class="video-card">
                    <div class="video-thumbnail" onclick="window.location.href='{{ route('admin.video-library.show', $video->id) }}'">
                        @if($video->getThumbnailUrl())
                        <img src="{{ $video->getThumbnailUrl() }}" alt="{{ $video->title }}" loading="lazy">
                        @else
                        <div class="video-thumbnail-placeholder">
                            <i class="fab fa-youtube"></i>
                        </div>
                        @endif
                        @if(!$video->is_active)
                        <div class="video-inactive-overlay">
                            <span>Inactive</span>
                        </div>
                        @endif
                        <div class="video-play-overlay">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        @if($video->getFormattedDuration())
                        <span class="video-duration">{{ $video->getFormattedDuration() }}</span>
                        @endif
                        @if($video->video_type === 'channel')
                        <span class="video-type-badge"><i class="fab fa-youtube"></i> Channel</span>
                        @endif
                    </div>
                    <div class="video-info">
                        <h3 class="video-title">{{ Str::limit($video->title, 55) }}</h3>
                        @if($video->channel_name)
                        <p class="video-channel"><i class="fab fa-youtube" style="font-size:0.65rem;margin-right:4px;color:#dc2626;"></i>{{ Str::limit($video->channel_name, 30) }}</p>
                        @endif
                        <div class="video-meta">
                            @if($video->category)
                            <span class="video-category"><i class="fas fa-tag"></i> {{ Str::limit($video->category, 15) }}</span>
                            @endif
                            <span class="video-views"><i class="fas fa-eye"></i> {{ $video->view_count }}</span>
                            <span class="video-access modern-badge {{ $video->access_level === 'all' ? 'modern-badge-success' : 'modern-badge-warning' }}" style="font-size:0.6rem;padding:1px 6px;">
                                {{ $video->access_level === 'all' ? 'Everyone' : ucfirst($video->access_level) }}
                            </span>
                        </div>
                        <div class="video-actions">
                            <a href="{{ route('admin.video-library.show', $video->id) }}" class="btn-watch" title="Watch Video">
                                <i class="fas fa-play"></i> Watch
                            </a>
                            @if($canManage)
                            <a href="{{ route('admin.video-library.edit', $video->id) }}" class="btn-video-action" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.video-library.destroy', $video->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this video?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-video-action btn-video-delete" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($videos->hasPages())
            <div class="modern-pagination-wrapper">
                {{ $videos->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fab fa-youtube"></i>
                </div>
                <h3>No Videos Found</h3>
                <p>@if(request()->filled('search')) Try adjusting your search or filters. @else Get started by adding your first YouTube video. @endif</p>
                @if($canManage)
                <a href="{{ route('admin.video-library.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> Add Video
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* Video Library Grid */
.video-library-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
}

.video-card {
    background: #fff;
    border: 1px solid #f0f0f0;
    border-radius: 14px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
}

.video-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}

.video-thumbnail {
    height: 170px;
    background: linear-gradient(135deg, #1a1a2e, #2d2d52);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.video-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-thumbnail-placeholder {
    text-align: center;
    color: rgba(255,255,255,0.5);
}

.video-thumbnail-placeholder i {
    font-size: 3rem;
    color: #dc2626;
}

.video-inactive-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
}

.video-inactive-overlay span {
    background: #dc2626;
    color: #fff;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.video-play-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    transition: opacity 0.2s;
}

.video-play-overlay i {
    font-size: 3rem;
    color: rgba(255,255,255,0.9);
    filter: drop-shadow(0 2px 8px rgba(0,0,0,0.4));
}

.video-card:hover .video-play-overlay {
    opacity: 1;
}

.video-duration {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0,0,0,0.8);
    color: #fff;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.video-type-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #dc2626;
    color: #fff;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 3px;
}

.video-info {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.video-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 0.35rem;
    line-height: 1.3;
}

.video-channel {
    font-size: 0.78rem;
    color: #6b7280;
    margin: 0 0 0.6rem;
}

.video-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.video-meta span {
    font-size: 0.7rem;
    color: #9ca3af;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.video-meta span i {
    font-size: 0.6rem;
}

.video-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: auto;
    padding-top: 0.75rem;
    border-top: 1px solid #f3f4f6;
}

.btn-watch {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1rem;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    background: #dc2626;
    color: #fff;
    text-decoration: none;
    transition: all 0.2s;
    flex: 1;
    justify-content: center;
}

.btn-watch:hover {
    background: #b91c1c;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(220,38,38,0.3);
}

.btn-video-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.78rem;
    background: #fefce8;
    color: #d97706;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-video-action:hover { background: #d97706; color: #fff; transform: translateY(-1px); }

.btn-video-delete {
    background: #fef2f2;
    color: #dc2626;
}

.btn-video-delete:hover { background: #dc2626; color: #fff; }

.video-category {
    background: #eef2ff;
    color: #4338ca;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 0.65rem !important;
    font-weight: 600;
}

@media (max-width: 768px) {
    .video-library-grid { grid-template-columns: 1fr; }
}
</style>
@endpush
@endsection
