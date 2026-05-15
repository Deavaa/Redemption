@extends('layouts.admin')
@section('title', __('app.library') ?? 'Library')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">{{ __('app.library') ?? 'Library' }}</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">{{ __('app.library') ?? 'Digital Library' }}</h1>
            <p class="modern-page-subtitle">Browse and read softcopy books — online reading only, no downloads</p>
        </div>
        <div class="modern-page-header-right">
            @if($canUpload)
            <a href="{{ route('admin.library.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-upload"></i>
                <span>{{ __('app.upload_book') ?? 'Upload Book' }}</span>
            </a>
            @endif
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-book"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalBooks }}</span>
                <span class="modern-stat-label">Total Books</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $activeBooks }}</span>
                <span class="modern-stat-label">Available</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-eye"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalReads }}</span>
                <span class="modern-stat-label">Total Reads</span>
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
            <form method="GET" action="{{ route('admin.library.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;color:#6b7280;">Search</label>
                    <div class="modern-search-box" style="width:100%;">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title, author, ISBN..." style="width:100%;">
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
                    <label class="form-label" style="font-size:0.78rem;font-weight:600;color:#6b7280;">Branch</label>
                    <select name="branch_id" class="form-select form-select-sm" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.55rem 0.75rem;font-size:0.875rem;">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                        @endforeach
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
                <div class="col-md-2">
                    <button type="submit" class="btn-modern btn-modern-primary" style="width:100%;justify-content:center;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Books Grid --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">Library Books</h2>
                <span class="modern-badge modern-badge-light">{{ $books->total() }} books</span>
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

            @if($books->count() > 0)
            <div class="library-grid">
                @foreach($books as $book)
                <div class="library-book-card">
                    <div class="book-cover">
                        @if($book->cover_image && $book->getCoverUrl())
                            <img src="{{ $book->getCoverUrl() }}" alt="{{ $book->title }}">
                        @else
                            <div class="book-cover-placeholder">
                                <i class="fas fa-book"></i>
                                <span>PDF</span>
                            </div>
                        @endif
                        @if(!$book->is_active)
                        <div class="book-inactive-overlay">
                            <span>Inactive</span>
                        </div>
                        @endif
                    </div>
                    <div class="book-info">
                        <h3 class="book-title">{{ Str::limit($book->title, 45) }}</h3>
                        @if($book->author)
                        <p class="book-author"><i class="fas fa-user-pen" style="font-size:0.65rem;margin-right:4px;"></i>{{ Str::limit($book->author, 30) }}</p>
                        @endif
                        <div class="book-meta">
                            @if($book->category)
                            <span class="book-category"><i class="fas fa-tag"></i> {{ Str::limit($book->category, 15) }}</span>
                            @endif
                            <span class="book-size"><i class="fas fa-file"></i> {{ $book->getFormattedFileSize() }}</span>
                        </div>
                        <div class="book-meta" style="margin-top:4px;">
                            <span class="book-reads"><i class="fas fa-eye"></i> {{ $book->read_count }} reads</span>
                            <span class="book-access modern-badge {{ $book->access_level === 'all' ? 'modern-badge-success' : 'modern-badge-warning' }}" style="font-size:0.6rem;padding:1px 6px;">
                                {{ $book->access_level === 'all' ? 'Everyone' : ucfirst($book->access_level) }}
                            </span>
                        </div>
                        <div class="book-actions">
                            <a href="{{ route('admin.library.read', $book->id) }}" class="btn-read" title="Read Online">
                                <i class="fas fa-book-reader"></i> Read
                            </a>
                            @if($canUpload)
                            <a href="{{ route('admin.library.edit', $book->id) }}" class="btn-book-action" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.library.destroy', $book->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this book?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-book-action btn-book-delete" title="Delete">
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
            @if($books->hasPages())
            <div class="modern-pagination-wrapper">
                {{ $books->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3>No Books Found</h3>
                <p>@if(request()->filled('search')) Try adjusting your search or filters. @else Get started by uploading your first book. @endif</p>
                @if($canUpload)
                <a href="{{ route('admin.library.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-upload"></i> Upload Book
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
/* Library Grid */
.library-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.25rem;
}

.library-book-card {
    background: #fff;
    border: 1px solid #f0f0f0;
    border-radius: 14px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
}

.library-book-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
}

.book-cover {
    height: 160px;
    background: linear-gradient(135deg, #2d2d3a, #3d3d52);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.book-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-cover-placeholder {
    text-align: center;
    color: rgba(255,255,255,0.6);
}

.book-cover-placeholder i {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 0.5rem;
}

.book-cover-placeholder span {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    opacity: 0.7;
}

.book-inactive-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
}

.book-inactive-overlay span {
    background: #dc2626;
    color: #fff;
    padding: 4px 12px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.book-info {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.book-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 0.35rem;
    line-height: 1.3;
}

.book-author {
    font-size: 0.78rem;
    color: #6b7280;
    margin: 0 0 0.6rem;
}

.book-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.book-meta span {
    font-size: 0.7rem;
    color: #9ca3af;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.book-meta span i {
    font-size: 0.6rem;
}

.book-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: auto;
    padding-top: 0.75rem;
    border-top: 1px solid #f3f4f6;
}

.btn-read {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.45rem 1rem;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 600;
    background: linear-gradient(135deg, #2d2d3a, #3d3d52);
    color: #fff;
    text-decoration: none;
    transition: all 0.2s;
    flex: 1;
    justify-content: center;
}

.btn-read:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(45,45,58,0.3);
    color: #fff;
}

.btn-book-action {
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

.btn-book-action:hover { background: #d97706; color: #fff; transform: translateY(-1px); }

.btn-book-delete {
    background: #fef2f2;
    color: #dc2626;
}

.btn-book-delete:hover { background: #dc2626; color: #fff; }

.book-category {
    background: #eef2ff;
    color: #4338ca;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 0.65rem !important;
    font-weight: 600;
}

.book-access {
    font-size: 0.6rem;
}

@media (max-width: 768px) {
    .library-grid { grid-template-columns: 1fr; }
}
</style>
@endpush
@endsection
