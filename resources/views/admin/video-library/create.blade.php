@extends('layouts.admin')
@section('title', 'Add Video')

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
                    <li class="active">Add Video</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title"><i class="fab fa-youtube" style="color:#dc2626;margin-right:8px;"></i>Add YouTube Video</h2>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.5rem;">
            @if($errors->any())
                <div class="modern-alert modern-alert-danger" style="margin-bottom:1.5rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul style="margin:0.5rem 0 0 1rem;padding:0;list-style:disc;">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.video-library.store') }}" id="videoForm">
                @csrf

                {{-- Video Type Toggle --}}
                <div class="form-group mb-4">
                    <label class="form-label" style="font-weight:600;color:#374151;font-size:0.9rem;">Video Type</label>
                    <div class="video-type-toggle">
                        <label class="video-type-option active" id="typeSingle">
                            <input type="radio" name="video_type" value="single" checked style="display:none;">
                            <i class="fas fa-video"></i>
                            <span>Single Video</span>
                        </label>
                        <label class="video-type-option" id="typeChannel">
                            <input type="radio" name="video_type" value="channel" style="display:none;">
                            <i class="fab fa-youtube"></i>
                            <span>YouTube Channel</span>
                        </label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        {{-- YouTube URL --}}
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">
                                <span id="urlLabel">YouTube Video URL</span> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:#fef2f2;border-color:#fca5a5;"><i class="fab fa-youtube" style="color:#dc2626;"></i></span>
                                <input type="url" name="youtube_url" id="youtube_url" value="{{ old('youtube_url') }}"
                                    class="form-control" style="border-radius:0 10px 10px 0;"
                                    placeholder="https://www.youtube.com/watch?v=..." required>
                            </div>
                            <small class="text-muted" style="font-size:0.75rem;" id="urlHint">
                                Paste any YouTube video link (watch, short, embed, or youtu.be)
                            </small>
                            <div id="videoPreview" class="mt-2" style="display:none;">
                                <div class="video-preview-container">
                                    <iframe id="previewIframe" width="100%" height="280" frameborder="0" allowfullscreen style="border-radius:10px;"></iframe>
                                </div>
                            </div>
                        </div>

                        {{-- Title --}}
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-control" style="border-radius:10px;" placeholder="Enter video title" required maxlength="500">
                        </div>

                        {{-- Description --}}
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">Description</label>
                            <textarea name="description" class="form-control" style="border-radius:10px;min-height:100px;" placeholder="Brief description of the video content..." maxlength="5000">{{ old('description') }}</textarea>
                        </div>

                        {{-- Channel Info --}}
                        <div class="row" id="channelFields" style="display:none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">Channel Name</label>
                                <input type="text" name="channel_name" value="{{ old('channel_name') }}" class="form-control" style="border-radius:10px;" placeholder="e.g., Khan Academy">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">Channel URL</label>
                                <input type="url" name="channel_url" value="{{ old('channel_url') }}" class="form-control" style="border-radius:10px;" placeholder="https://www.youtube.com/@channel">
                            </div>
                        </div>

                        {{-- Duration --}}
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">Duration (optional)</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="number" name="duration_minutes" id="durationMin" class="form-control" style="border-radius:10px;" placeholder="Min" min="0" value="{{ old('duration_minutes') }}">
                                </div>
                                <div class="col-4">
                                    <input type="number" name="duration_seconds_input" id="durationSec" class="form-control" style="border-radius:10px;" placeholder="Sec" min="0" max="59" value="{{ old('duration_seconds_input') }}">
                                </div>
                                <div class="col-4 d-flex align-items-center">
                                    <input type="hidden" name="duration_seconds" id="durationTotal" value="{{ old('duration_seconds') }}">
                                    <small class="text-muted">mm:ss</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        {{-- Category --}}
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">Category</label>
                            <input type="text" name="category" value="{{ old('category') }}" class="form-control" style="border-radius:10px;" list="categoryList" placeholder="e.g., Mathematics, Science">
                            <datalist id="categoryList">
                                @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                                @endforeach
                            </datalist>
                        </div>

                        {{-- Branch --}}
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">Branch</label>
                            <select name="branch_id" class="form-select" style="border-radius:10px;">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Access Level --}}
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">Access Level <span class="text-danger">*</span></label>
                            <select name="access_level" class="form-select" style="border-radius:10px;" required>
                                <option value="all" {{ old('access_level') === 'all' ? 'selected' : '' }}>Everyone</option>
                                <option value="teacher" {{ old('access_level') === 'teacher' ? 'selected' : '' }}>Teachers Only</option>
                                <option value="student" {{ old('access_level') === 'student' ? 'selected' : '' }}>Students Only</option>
                                <option value="staff" {{ old('access_level') === 'staff' ? 'selected' : '' }}>Staff Only</option>
                                <option value="admin" {{ old('access_level') === 'admin' ? 'selected' : '' }}>Admin Only</option>
                            </select>
                        </div>

                        {{-- Active Toggle --}}
                        <div class="form-group mb-3">
                            <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">Status</label>
                            <div class="form-check form-switch" style="padding-left:2.5rem;">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" style="width:3rem;height:1.5rem;" checked id="isActive" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label ms-2" for="isActive" style="font-weight:600;color:{{ old('is_active', true) ? '#059669' : '#9ca3af' }};" id="activeLabel">
                                    {{ old('is_active', true) ? 'Active' : 'Inactive' }}
                                </label>
                            </div>
                        </div>

                        {{-- Show on Website Toggle --}}
                        <div class="form-group mb-4">
                            <label class="form-label" style="font-weight:600;color:#374151;font-size:0.875rem;">Website Visibility</label>
                            <div class="form-check form-switch" style="padding-left:2.5rem;">
                                <input type="checkbox" name="show_on_website" value="1" class="form-check-input" style="width:3rem;height:1.5rem;" id="showOnWebsite" {{ old('show_on_website') ? 'checked' : '' }}>
                                <label class="form-check-label ms-2" for="showOnWebsite" style="font-weight:600;color:{{ old('show_on_website') ? '#2563eb' : '#9ca3af' }};" id="websiteLabel">
                                    {{ old('show_on_website') ? 'Show on Website' : 'Admin Only' }}
                                </label>
                            </div>
                            <small class="text-muted" style="font-size:0.72rem;">When enabled, this video will appear on the public website gallery page. Only "Everyone" access level videos can be shown on the website.</small>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="display:flex;gap:1rem;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #e5e7eb;">
                    <button type="submit" class="btn-modern btn-modern-primary">
                        <i class="fas fa-plus"></i> Add Video
                    </button>
                    <a href="{{ route('admin.video-library.index') }}" class="btn-modern btn-modern-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.video-type-toggle {
    display: flex;
    gap: 1rem;
}

.video-type-option {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 600;
    color: #6b7280;
    background: #fff;
}

.video-type-option:hover {
    border-color: #dc2626;
    color: #dc2626;
}

.video-type-option.active {
    border-color: #dc2626;
    background: #fef2f2;
    color: #dc2626;
}

.video-preview-container {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Video type toggle
    const typeSingle = document.getElementById('typeSingle');
    const typeChannel = document.getElementById('typeChannel');
    const channelFields = document.getElementById('channelFields');
    const urlLabel = document.getElementById('urlLabel');
    const urlHint = document.getElementById('urlHint');

    function setType(type) {
        if (type === 'single') {
            typeSingle.classList.add('active');
            typeChannel.classList.remove('active');
            typeSingle.querySelector('input').checked = true;
            channelFields.style.display = 'none';
            urlLabel.textContent = 'YouTube Video URL';
            urlHint.textContent = 'Paste any YouTube video link (watch, short, embed, or youtu.be)';
            document.getElementById('youtube_url').placeholder = 'https://www.youtube.com/watch?v=...';
        } else {
            typeChannel.classList.add('active');
            typeSingle.classList.remove('active');
            typeChannel.querySelector('input').checked = true;
            channelFields.style.display = '';
            urlLabel.textContent = 'YouTube Channel URL';
            urlHint.textContent = 'Paste a YouTube channel link (e.g., youtube.com/@channelname or youtube.com/channel/UC...)';
            document.getElementById('youtube_url').placeholder = 'https://www.youtube.com/@channelname';
        }
    }

    typeSingle.addEventListener('click', () => setType('single'));
    typeChannel.addEventListener('click', () => setType('channel'));

    // YouTube URL preview
    const urlInput = document.getElementById('youtube_url');
    const preview = document.getElementById('videoPreview');
    const previewIframe = document.getElementById('previewIframe');

    function extractVideoId(url) {
        const patterns = [
            /(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/v\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/,
            /^([a-zA-Z0-9_-]{11})$/,
        ];
        for (const pattern of patterns) {
            const match = url.match(pattern);
            if (match) return match[1];
        }
        return null;
    }

    let previewTimeout;
    urlInput.addEventListener('input', function() {
        clearTimeout(previewTimeout);
        previewTimeout = setTimeout(() => {
            const videoType = document.querySelector('input[name="video_type"]:checked').value;
            if (videoType === 'single') {
                const videoId = extractVideoId(this.value);
                if (videoId) {
                    previewIframe.src = `https://www.youtube.com/embed/${videoId}`;
                    preview.style.display = 'block';
                } else {
                    preview.style.display = 'none';
                }
            } else {
                preview.style.display = 'none';
            }
        }, 500);
    });

    // Duration calculation
    const durationMin = document.getElementById('durationMin');
    const durationSec = document.getElementById('durationSec');
    const durationTotal = document.getElementById('durationTotal');

    function updateDuration() {
        const mins = parseInt(durationMin.value) || 0;
        const secs = parseInt(durationSec.value) || 0;
        durationTotal.value = (mins * 60) + secs;
    }

    durationMin.addEventListener('input', updateDuration);
    durationSec.addEventListener('input', updateDuration);

    // Active toggle
    const isActive = document.getElementById('isActive');
    const activeLabel = document.getElementById('activeLabel');
    isActive.addEventListener('change', function() {
        activeLabel.textContent = this.checked ? 'Active' : 'Inactive';
        activeLabel.style.color = this.checked ? '#059669' : '#9ca3af';
    });

    // Show on Website toggle
    const showOnWebsite = document.getElementById('showOnWebsite');
    const websiteLabel = document.getElementById('websiteLabel');
    showOnWebsite.addEventListener('change', function() {
        websiteLabel.textContent = this.checked ? 'Show on Website' : 'Admin Only';
        websiteLabel.style.color = this.checked ? '#2563eb' : '#9ca3af';
    });
});
</script>
@endpush
@endsection
