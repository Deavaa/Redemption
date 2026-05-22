@extends('layouts.admin')
@section('title', $video->title)

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.library.index') }}">Digital Library</a></li>
                    <li><a href="{{ route('admin.video-library.index') }}">Video Library</a></li>
                    <li class="active">{{ Str::limit($video->title, 40) }}</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            @if($canManage)
            <a href="{{ route('admin.video-library.edit', $video->id) }}" class="btn-modern btn-modern-secondary">
                <i class="fas fa-pen"></i>
                <span>Edit</span>
            </a>
            @endif
            <a href="{{ route('admin.video-library.index') }}" class="btn-modern btn-modern-secondary">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Main Video Player --}}
        <div class="col-lg-8">
            <div class="modern-card" style="overflow:hidden;">
                {{-- YouTube Embed --}}
                <div class="video-player-wrapper">
                    @if($video->video_type === 'single' && $video->youtube_video_id)
                    <iframe width="100%" height="450" src="{{ $video->getEmbedUrl() }}"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        style="display:block;">
                    </iframe>
                    @elseif($video->video_type === 'channel')
                    <div class="channel-embed-wrapper">
                        <div class="channel-embed-info">
                            <i class="fab fa-youtube" style="font-size:3rem;color:#dc2626;"></i>
                            <h3>{{ $video->channel_name ?? 'YouTube Channel' }}</h3>
                            <p>Visit the channel on YouTube to browse all videos</p>
                            <a href="{{ $video->youtube_url }}" target="_blank" rel="noopener noreferrer" class="btn-modern btn-modern-primary" style="background:#dc2626;">
                                <i class="fab fa-youtube"></i> Open Channel on YouTube
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="channel-embed-wrapper">
                        <div class="channel-embed-info">
                            <i class="fas fa-exclamation-triangle" style="font-size:3rem;color:#d97706;"></i>
                            <h3>Video Not Available</h3>
                            <p>The YouTube video ID could not be extracted from the provided URL.</p>
                            <a href="{{ $video->youtube_url }}" target="_blank" rel="noopener noreferrer" class="btn-modern btn-modern-primary">
                                <i class="fas fa-external-link-alt"></i> Open Link
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Video Info --}}
                <div style="padding:1.5rem;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                        <div style="flex:1;">
                            <h1 style="font-size:1.35rem;font-weight:700;color:#1a1a2e;margin:0 0 0.5rem;">{{ $video->title }}</h1>
                            @if($video->channel_name)
                            <p style="font-size:0.9rem;color:#6b7280;margin:0;">
                                <i class="fab fa-youtube" style="color:#dc2626;margin-right:4px;"></i>
                                {{ $video->channel_name }}
                                @if($video->channel_url)
                                <a href="{{ $video->channel_url }}" target="_blank" rel="noopener noreferrer" style="margin-left:6px;font-size:0.8rem;">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                @endif
                            </p>
                            @endif
                        </div>
                        <div style="display:flex;align-items:center;gap:1rem;">
                            <span style="font-size:0.85rem;color:#6b7280;"><i class="fas fa-eye" style="margin-right:4px;"></i>{{ $video->view_count }} views</span>
                            @if($video->getFormattedDuration())
                            <span style="font-size:0.85rem;color:#6b7280;"><i class="fas fa-clock" style="margin-right:4px;"></i>{{ $video->getFormattedDuration() }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Tags & Badges --}}
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-top:1rem;flex-wrap:wrap;">
                        @if($video->category)
                        <span class="modern-badge modern-badge-light" style="font-size:0.75rem;">
                            <i class="fas fa-tag"></i> {{ $video->category }}
                        </span>
                        @endif
                        <span class="modern-badge {{ $video->video_type === 'channel' ? 'modern-badge-danger' : 'modern-badge-info' }}" style="font-size:0.75rem;">
                            <i class="{{ $video->video_type === 'channel' ? 'fab fa-youtube' : 'fas fa-video' }}"></i>
                            {{ $video->video_type === 'channel' ? 'Channel' : 'Single Video' }}
                        </span>
                        <span class="modern-badge {{ $video->access_level === 'all' ? 'modern-badge-success' : 'modern-badge-warning' }}" style="font-size:0.75rem;">
                            <i class="fas fa-lock{{ $video->access_level === 'all' ? '-open' : '' }}"></i>
                            {{ $video->access_level === 'all' ? 'Everyone' : ucfirst($video->access_level) . ' Only' }}
                        </span>
                        @if(!$video->is_active)
                        <span class="modern-badge modern-badge-danger" style="font-size:0.75rem;">
                            <i class="fas fa-ban"></i> Inactive
                        </span>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if($video->description)
                    <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid #f3f4f6;">
                        <h3 style="font-size:0.9rem;font-weight:700;color:#374151;margin:0 0 0.5rem;">Description</h3>
                        <p style="font-size:0.875rem;color:#4b5563;line-height:1.6;white-space:pre-wrap;">{{ $video->description }}</p>
                    </div>
                    @endif

                    {{-- Video URL --}}
                    <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #f3f4f6;">
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <span style="font-size:0.8rem;color:#6b7280;"><i class="fas fa-link" style="margin-right:4px;"></i>Source:</span>
                            <a href="{{ $video->youtube_url }}" target="_blank" rel="noopener noreferrer" style="font-size:0.8rem;color:#2563eb;word-break:break-all;">
                                {{ Str::limit($video->youtube_url, 80) }}
                                <i class="fas fa-external-link-alt" style="font-size:0.65rem;margin-left:2px;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Video Details Card --}}
            <div class="modern-card" style="margin-bottom:1.25rem;">
                <div class="modern-card-header">
                    <h2 class="modern-card-title" style="font-size:0.95rem;">Details</h2>
                </div>
                <div class="modern-card-body" style="padding:1rem;">
                    <table style="width:100%;font-size:0.8rem;">
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:0.5rem 0;color:#6b7280;font-weight:600;">Uploaded By</td>
                            <td style="padding:0.5rem 0;text-align:right;">{{ $video->uploader?->name ?? 'Unknown' }}</td>
                        </tr>
                        @if($video->branch)
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:0.5rem 0;color:#6b7280;font-weight:600;">Branch</td>
                            <td style="padding:0.5rem 0;text-align:right;">{{ $video->branch->name }}</td>
                        </tr>
                        @endif
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <td style="padding:0.5rem 0;color:#6b7280;font-weight:600;">Added</td>
                            <td style="padding:0.5rem 0;text-align:right;">{{ $video->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td style="padding:0.5rem 0;color:#6b7280;font-weight:600;">Video ID</td>
                            <td style="padding:0.5rem 0;text-align:right;font-family:monospace;font-size:0.75rem;">{{ $video->youtube_video_id ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Related Videos --}}
            @if($relatedVideos->count() > 0)
            <div class="modern-card">
                <div class="modern-card-header">
                    <h2 class="modern-card-title" style="font-size:0.95rem;">Related Videos</h2>
                </div>
                <div class="modern-card-body" style="padding:0.75rem;">
                    @foreach($relatedVideos as $related)
                    <a href="{{ route('admin.video-library.show', $related->id) }}" class="related-video-item">
                        <div class="related-video-thumb">
                            @if($related->getThumbnailUrl())
                            <img src="{{ $related->getThumbnailUrl() }}" alt="{{ $related->title }}" loading="lazy">
                            @else
                            <div class="related-thumb-placeholder"><i class="fab fa-youtube"></i></div>
                            @endif
                        </div>
                        <div class="related-video-info">
                            <span class="related-video-title">{{ Str::limit($related->title, 40) }}</span>
                            <span class="related-video-channel">{{ $related->channel_name ?? 'Unknown' }}</span>
                            <span class="related-video-views"><i class="fas fa-eye"></i> {{ $related->view_count }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.video-player-wrapper {
    background: #000;
    position: relative;
}

.video-player-wrapper iframe {
    display: block;
}

.channel-embed-wrapper {
    min-height: 350px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1a1a2e, #2d2d52);
}

.channel-embed-info {
    text-align: center;
    color: #fff;
    padding: 2rem;
}

.channel-embed-info h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 1rem 0 0.5rem;
    color: #fff;
}

.channel-embed-info p {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.6);
    margin: 0 0 1.5rem;
}

.related-video-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.6rem;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.15s;
}

.related-video-item:hover {
    background: #f3f4f6;
}

.related-video-thumb {
    width: 100px;
    min-width: 100px;
    height: 56px;
    border-radius: 6px;
    overflow: hidden;
    background: #1a1a2e;
    display: flex;
    align-items: center;
    justify-content: center;
}

.related-video-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-thumb-placeholder {
    color: rgba(255,255,255,0.4);
    font-size: 1.2rem;
}

.related-video-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    overflow: hidden;
}

.related-video-title {
    font-size: 0.8rem;
    font-weight: 600;
    color: #1a1a2e;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.related-video-channel {
    font-size: 0.7rem;
    color: #6b7280;
}

.related-video-views {
    font-size: 0.65rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 3px;
}

@media (max-width: 992px) {
    .video-player-wrapper iframe {
        height: 300px;
    }
}
</style>
@endpush
@endsection
