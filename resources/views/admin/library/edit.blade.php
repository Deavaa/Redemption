@extends('layouts.admin')
@section('title', 'Edit Book: ' . $book->title)

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.library.index') }}">{{ __('app.library') ?? 'Library' }}</a></li>
                    <li class="active">Edit Book</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.library.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Library</span>
            </a>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">Book Details</h2>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.5rem;">
            @if($errors->any())
                <div class="modern-alert modern-alert-danger" style="margin-bottom:1.5rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul style="margin:0.5rem 0 0;padding-left:1.2rem;font-size:0.85rem;">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.library.update', $book->id) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Book Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $book->title) }}" class="form-control" required style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Author</label>
                        <input type="text" name="author" value="{{ old('author', $book->author) }}" class="form-control" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ISBN</label>
                        <input type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}" class="form-control" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Publisher</label>
                        <input type="text" name="publisher" value="{{ old('publisher', $book->publisher) }}" class="form-control" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category</label>
                        <input type="text" name="category" value="{{ old('category', $book->category) }}" class="form-control" list="categoryList" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                        <datalist id="categoryList">
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="3" class="form-control" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">{{ old('description', $book->description) }}</textarea>
                    </div>

                    {{-- Current file info --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Current File</label>
                        <div style="padding:0.75rem;background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:10px;display:flex;align-items:center;gap:0.75rem;">
                            <i class="fas fa-file-pdf" style="font-size:1.5rem;color:#dc2626;"></i>
                            <div>
                                <div style="font-weight:600;font-size:0.85rem;">{{ $book->file_name ?? 'document.pdf' }}</div>
                                <div style="font-size:0.75rem;color:#9ca3af;">{{ $book->getFormattedFileSize() }}</div>
                            </div>
                        </div>
                        <div class="form-text" style="font-size:0.75rem;">Leave new file empty to keep the current file</div>
                        <input type="file" name="file" accept=".pdf,.epub" class="form-control mt-2" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.55rem 1rem;">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cover Image</label>
                        @if($book->cover_image && $book->getCoverUrl())
                        <div style="margin-bottom:0.5rem;">
                            <img src="{{ $book->getCoverUrl() }}" style="max-height:80px;border-radius:8px;border:1px solid #e5e7eb;">
                        </div>
                        @endif
                        <div class="form-text" style="font-size:0.75rem;">Leave empty to keep the current cover</div>
                        <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="form-control mt-2" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.55rem 1rem;" data-compress="1920" data-maxsize="1500">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Branch</label>
                        <select name="branch_id" class="form-select" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $book->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Access Level <span class="text-danger">*</span></label>
                        <select name="access_level" class="form-select" required style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                            <option value="all" {{ old('access_level', $book->access_level) === 'all' ? 'selected' : '' }}>Everyone</option>
                            <option value="teacher" {{ old('access_level', $book->access_level) === 'teacher' ? 'selected' : '' }}>Teachers Only</option>
                            <option value="student" {{ old('access_level', $book->access_level) === 'student' ? 'selected' : '' }}>Students Only</option>
                            <option value="staff" {{ old('access_level', $book->access_level) === 'staff' ? 'selected' : '' }}>Staff Only</option>
                            <option value="admin" {{ old('access_level', $book->access_level) === 'admin' ? 'selected' : '' }}>Admin Only</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <div class="form-check form-switch" style="padding-top:0.5rem;">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', $book->is_active) ? 'checked' : '' }} style="width:3em;height:1.5em;">
                            <label class="form-check-label" for="isActive" style="font-weight:600;">Active & Available</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn-modern btn-modern-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.library.index') }}" class="btn-modern btn-modern-outline">Cancel</a>
                </div>
            </form>
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
</style>
@endpush
@push('scripts')
    <script src="{{ asset('js/client-compress.js') }}"></script>
@endpush
@endsection
