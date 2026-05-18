@extends('layouts.admin')
@section('title', $book->title)

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.library.index') }}">{{ __('app.library') ?? 'Library' }}</a></li>
                    <li class="active">{{ Str::limit($book->title, 30) }}</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.library.read', $book->id) }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-book-reader"></i>
                <span>Read Online</span>
            </a>
            @if($canUpload)
            <a href="{{ route('admin.library.edit', $book->id) }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-pen"></i>
                <span>Edit</span>
            </a>
            @endif
            <a href="{{ route('admin.library.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Book Cover & Info --}}
        <div class="col-md-4">
            <div class="modern-card">
                <div class="modern-card-body" style="padding:1.5rem;">
                    <div style="text-align:center;margin-bottom:1.5rem;">
                        @if($book->cover_image && $book->getCoverUrl())
                        <img src="{{ $book->getCoverUrl() }}" style="max-width:100%;max-height:300px;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                        @else
                        <div style="width:200px;height:280px;background:linear-gradient(135deg,#2d2d3a,#3d3d52);border-radius:12px;display:inline-flex;align-items:center;justify-content:center;flex-direction:column;color:rgba(255,255,255,0.6);">
                            <i class="fas fa-book" style="font-size:3rem;margin-bottom:0.75rem;"></i>
                            <span style="font-size:0.8rem;font-weight:700;letter-spacing:2px;">PDF</span>
                        </div>
                        @endif
                    </div>
                    <div style="text-align:center;">
                        <h3 style="font-size:1.1rem;font-weight:700;color:#1a1a2e;margin-bottom:0.25rem;">{{ $book->title }}</h3>
                        @if($book->author)
                        <p style="color:#6b7280;font-size:0.85rem;margin:0;">by {{ $book->author }}</p>
                        @endif
                    </div>
                    <div style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:0.5rem;justify-content:center;">
                        <span class="modern-badge {{ $book->is_active ? 'modern-badge-success' : 'modern-badge-danger' }}">
                            {{ $book->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="modern-badge modern-badge-info">
                            {{ $book->access_level === 'all' ? 'Everyone' : ucfirst($book->access_level) }}
                        </span>
                        @if($book->category)
                        <span class="modern-badge modern-badge-light">{{ $book->category }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Book Details --}}
        <div class="col-md-8">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h2 class="modern-card-title">Book Information</h2>
                </div>
                <div class="modern-card-body" style="padding:0;">
                    <table class="modern-table">
                        <tbody>
                            <tr><td style="width:180px;font-weight:600;color:#6b7280;">Title</td><td>{{ $book->title }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">Author</td><td>{{ $book->author ?? '-' }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">ISBN</td><td>{{ $book->isbn ?? '-' }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">Publisher</td><td>{{ $book->publisher ?? '-' }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">Category</td><td>{{ $book->category ?? '-' }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">File Name</td><td>{{ $book->file_name ?? '-' }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">File Size</td><td>{{ $book->getFormattedFileSize() }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">File Type</td><td>{{ $book->file_type ?? '-' }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">Branch</td><td>{{ $book->branch?->name ?? 'All Branches' }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">Access Level</td><td>{{ $book->access_level === 'all' ? 'Everyone' : ucfirst($book->access_level) }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">Read Count</td><td>{{ $book->read_count }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">Uploaded By</td><td>{{ $book->uploader?->name ?? '-' }}</td></tr>
                            <tr><td style="font-weight:600;color:#6b7280;">Uploaded On</td><td>{{ $book->created_at?->format('M d, Y H:i') ?? '-' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if($book->description)
            <div class="modern-card" style="margin-top:1rem;">
                <div class="modern-card-header">
                    <h2 class="modern-card-title">Description</h2>
                </div>
                <div class="modern-card-body" style="padding:1.5rem;">
                    <p style="color:#4b5563;line-height:1.7;white-space:pre-wrap;">{{ $book->description }}</p>
                </div>
            </div>
            @endif

            {{-- Copyright Notice --}}
            <div class="copyright-notice" style="margin-top:1rem;">
                <i class="fas fa-shield-alt"></i>
                <div>
                    <strong>Copyright Protection</strong>
                    <p>This book is protected against downloading, copying, and printing. It can only be read online through the secure viewer.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.btn-modern-outline {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600;
    font-size: 0.9rem; text-decoration: none; border: 1.5px solid #e5e7eb;
    background: #fff; color: #6b7280; cursor: pointer; transition: all 0.25s;
}
.btn-modern-outline:hover { border-color: #2d2d3a; color: #2d2d3a; background: #f9fafb; }

.copyright-notice {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: 1rem 1.25rem; background: #fff7ed;
    border: 1px solid #fed7aa; border-radius: 12px;
}
.copyright-notice > i { font-size: 1.5rem; color: #d97706; margin-top: 2px; }
.copyright-notice strong { display: block; margin-bottom: 0.25rem; color: #92400e; font-size: 0.88rem; }
.copyright-notice p { margin: 0; font-size: 0.8rem; color: #b45309; line-height: 1.5; }
</style>
@endpush
@endsection
