@extends('layouts.admin')
@section('title', __('app.upload_book') ?? 'Upload Book')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.library.index') }}">{{ __('app.library') ?? 'Library' }}</a></li>
                    <li class="active">{{ __('app.upload_book') ?? 'Upload Book' }}</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">{{ __('app.upload_book') ?? 'Upload Book' }}</h1>
            <p class="modern-page-subtitle">Upload softcopy books for online reading (download is disabled for copyright protection)</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.library.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Library</span>
            </a>
        </div>
    </div>

    {{-- Upload Form --}}
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

            <form method="POST" action="{{ route('admin.library.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    {{-- Title --}}
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Book Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Enter book title" required style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                    </div>

                    {{-- Author --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Author</label>
                        <input type="text" name="author" value="{{ old('author') }}" class="form-control" placeholder="Author name" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                    </div>

                    {{-- ISBN --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">ISBN</label>
                        <input type="text" name="isbn" value="{{ old('isbn') }}" class="form-control" placeholder="ISBN number" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                    </div>

                    {{-- Publisher --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Publisher</label>
                        <input type="text" name="publisher" value="{{ old('publisher') }}" class="form-control" placeholder="Publisher name" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                    </div>

                    {{-- Category --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category</label>
                        <input type="text" name="category" value="{{ old('category') }}" class="form-control" list="categoryList" placeholder="Select or type a category" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                        <datalist id="categoryList">
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>

                    {{-- Description --}}
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Brief description of the book..." style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">{{ old('description') }}</textarea>
                    </div>

                    {{-- Book File --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Book File (PDF/EPUB) <span class="text-danger">*</span></label>
                        <div class="upload-zone" id="bookFileZone">
                            <input type="file" name="file" id="bookFile" accept=".pdf,.epub" required style="display:none;">
                            <div class="upload-zone-content" onclick="document.getElementById('bookFile').click()">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Click to select PDF or EPUB file</p>
                                <span>Max file size: 100MB</span>
                            </div>
                            <div class="upload-zone-preview" id="bookFilePreview" style="display:none;">
                                <i class="fas fa-file-pdf"></i>
                                <span id="bookFileName"></span>
                                <span id="bookFileSize" class="text-muted"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearBookFile()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Cover Image --}}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cover Image (Optional)</label>
                        <div class="upload-zone" id="coverZone">
                            <input type="file" name="cover_image" id="coverImage" accept="image/jpeg,image/png,image/webp" style="display:none;">
                            <div class="upload-zone-content" onclick="document.getElementById('coverImage').click()">
                                <i class="fas fa-image"></i>
                                <p>Click to select cover image</p>
                                <span>JPG, PNG, or WebP — Max 5MB</span>
                            </div>
                            <div class="upload-zone-preview" id="coverPreview" style="display:none;">
                                <img id="coverThumbnail" style="max-height:60px;border-radius:6px;">
                                <span id="coverFileName"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearCover()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Branch --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Branch</label>
                        <select name="branch_id" class="form-select" style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Access Level --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Access Level <span class="text-danger">*</span></label>
                        <select name="access_level" class="form-select" required style="border-radius:10px;border:1.5px solid #e5e7eb;padding:0.65rem 1rem;">
                            <option value="all" {{ old('access_level') === 'all' ? 'selected' : '' }}>Everyone (All Members)</option>
                            <option value="teacher" {{ old('access_level') === 'teacher' ? 'selected' : '' }}>Teachers Only</option>
                            <option value="student" {{ old('access_level') === 'student' ? 'selected' : '' }}>Students Only</option>
                            <option value="staff" {{ old('access_level') === 'staff' ? 'selected' : '' }}>Staff Only</option>
                            <option value="admin" {{ old('access_level') === 'admin' ? 'selected' : '' }}>Admin Only</option>
                        </select>
                        <div class="form-text" style="font-size:0.75rem;">Controls who can read this book online</div>
                    </div>

                    {{-- Active --}}
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <div class="form-check form-switch" style="padding-top:0.5rem;">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" checked style="width:3em;height:1.5em;">
                            <label class="form-check-label" for="isActive" style="font-weight:600;">Active & Available</label>
                        </div>
                    </div>
                </div>

                {{-- Copyright Notice --}}
                <div class="copyright-notice" style="margin-top:1.5rem;">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>Copyright Protection Notice</strong>
                        <p>Books uploaded here are protected against downloading. Members can only read books online through the built-in viewer. Download, copy, print, and right-click are disabled to protect authors' and publishers' copyright.</p>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn-modern btn-modern-primary">
                        <i class="fas fa-upload"></i> Upload Book
                    </button>
                    <a href="{{ route('admin.library.index') }}" class="btn-modern btn-modern-outline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.upload-zone {
    border: 2px dashed #e5e7eb;
    border-radius: 14px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.2s;
    background: #fafbfc;
    min-height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-zone:hover {
    border-color: #2d2d3a;
    background: #f5f5f8;
}

.upload-zone.drag-over {
    border-color: #2d2d3a;
    background: #ececf0;
}

.upload-zone-content {
    cursor: pointer;
    color: #9ca3af;
}

.upload-zone-content i {
    font-size: 2rem;
    display: block;
    margin-bottom: 0.5rem;
    color: #d1d5db;
}

.upload-zone-content p {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
    color: #6b7280;
}

.upload-zone-content span {
    font-size: 0.78rem;
    color: #adb5bd;
}

.upload-zone-preview {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.85rem;
    font-weight: 500;
    color: #374151;
}

.upload-zone-preview i {
    font-size: 1.5rem;
    color: #dc2626;
}

.copyright-notice {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    border-radius: 12px;
}

.copyright-notice > i {
    font-size: 1.5rem;
    color: #d97706;
    margin-top: 2px;
}

.copyright-notice strong {
    display: block;
    margin-bottom: 0.25rem;
    color: #92400e;
    font-size: 0.88rem;
}

.copyright-notice p {
    margin: 0;
    font-size: 0.8rem;
    color: #b45309;
    line-height: 1.5;
}

.btn-modern-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-outline:hover {
    border-color: #2d2d3a;
    color: #2d2d3a;
    background: #f9fafb;
}
</style>
@endpush

@push('scripts')
<script>
// Book file handling
const bookFileInput = document.getElementById('bookFile');
const bookFilePreview = document.getElementById('bookFilePreview');
const bookFileContent = document.querySelector('#bookFileZone .upload-zone-content');

bookFileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        const file = this.files[0];
        document.getElementById('bookFileName').textContent = file.name;
        document.getElementById('bookFileSize').textContent = formatBytes(file.size);
        bookFileContent.style.display = 'none';
        bookFilePreview.style.display = 'flex';
    }
});

function clearBookFile() {
    bookFileInput.value = '';
    bookFileContent.style.display = '';
    bookFilePreview.style.display = 'none';
}

// Cover image handling
const coverInput = document.getElementById('coverImage');
const coverPreview = document.getElementById('coverPreview');
const coverContent = document.querySelector('#coverZone .upload-zone-content');

coverInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        const file = this.files[0];
        document.getElementById('coverFileName').textContent = file.name;
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('coverThumbnail').src = e.target.result;
        };
        reader.readAsDataURL(file);
        coverContent.style.display = 'none';
        coverPreview.style.display = 'flex';
    }
});

function clearCover() {
    coverInput.value = '';
    coverContent.style.display = '';
    coverPreview.style.display = 'none';
}

// Drag and drop
['bookFileZone', 'coverZone'].forEach(id => {
    const zone = document.getElementById(id);
    if (!zone) return;
    zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('drag-over'); });
    zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        const input = zone.querySelector('input[type="file"]');
        if (e.dataTransfer.files.length > 0) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
});

function formatBytes(bytes) {
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' B';
}
</script>
@endpush
@endsection
