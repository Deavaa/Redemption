@extends('layouts.admin')
@section('title', 'Edit News')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        .note-editor { border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0 !important; }
        .note-toolbar {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%) !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 10px 14px !important;
        }
        .note-btn-group .note-btn {
            border-radius: 6px !important;
            margin: 0 2px !important;
            border: 1px solid transparent !important;
            transition: all 0.2s ease;
        }
        .note-btn-group .note-btn:hover {
            background: rgba(16, 185, 129, 0.12) !important;
            border-color: rgba(16, 185, 129, 0.25) !important;
        }
        .note-editable {
            padding: 18px 20px !important;
            min-height: 240px;
            font-family: 'Inter', sans-serif !important;
            font-size: 0.95rem;
            line-height: 1.7;
            color: #1f2937;
        }
        .note-editable:focus { outline: none; }
        .note-editable p { margin-bottom: 0.75rem; }
        .note-editable h1, .note-editable h2, .note-editable h3 { color: #0f172a; font-weight: 700; margin-top: 1.2rem; }
        .note-editable ul, .note-editable ol { padding-left: 1.5rem; margin-bottom: 0.75rem; }
        .note-editable a { color: #10B981; text-decoration: underline; }
        .note-editable blockquote {
            border-left: 4px solid #10B981;
            padding: 0.5rem 1rem;
            margin: 0.75rem 0;
            background: #f0fdf4;
            border-radius: 0 8px 8px 0;
            color: #065f46;
            font-style: italic;
        }
        .note-editable img { max-width: 100%; height: auto; border-radius: 10px; margin: 0.75rem 0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .note-editable code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.85em;
            color: #db2777;
        }

        .cover-upload-zone {
            position: relative;
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            padding: 28px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .cover-upload-zone:hover {
            border-color: #10b981;
            background: #f0fdf4;
            transform: translateY(-2px);
        }
        .cover-upload-zone.dragover {
            border-color: #10b981;
            background: #ecfdf5;
            border-style: solid;
        }
        .cover-upload-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.25);
        }
        .cover-upload-title {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.95rem;
            margin: 4px 0 0 0;
        }
        .cover-upload-hint {
            font-size: 0.78rem;
            color: #64748b;
            margin: 0;
        }
        .cover-upload-zone input[type="file"] {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            opacity: 0;
            cursor: pointer;
        }
        .cover-preview-wrapper {
            position: relative;
            margin-top: 12px;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            display: none;
            background: #f8fafc;
        }
        .cover-preview-wrapper.has-image { display: block; }
        .cover-preview-wrapper img {
            width: 100%;
            max-height: 280px;
            object-fit: cover;
            display: block;
        }
        .cover-preview-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
        }
        .cover-preview-actions button {
            background: rgba(15, 23, 42, 0.75);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            backdrop-filter: blur(8px);
            transition: all 0.2s ease;
        }
        .cover-preview-actions button:hover {
            background: rgba(15, 23, 42, 0.95);
        }
        .cover-preview-actions button.remove-btn:hover {
            background: #dc2626;
        }
        .cover-preview-meta {
            padding: 10px 14px;
            background: white;
            border-top: 1px solid #e2e8f0;
            font-size: 0.78rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cover-preview-meta .filename {
            font-weight: 600;
            color: #0f172a;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 200px;
        }
        .cover-preview-meta .filesize {
            color: #10b981;
            font-weight: 600;
        }
        .existing-cover {
            margin-top: 12px;
            border-radius: 14px;
            overflow: hidden;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
        }
        .existing-cover img { width: 100%; max-height: 200px; object-fit: cover; display: block; }
        .existing-cover-meta {
            padding: 10px 14px;
            background: white;
            border-top: 1px solid #e2e8f0;
            font-size: 0.78rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .note-upload-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            color: #059669;
            font-size: 0.85rem;
            font-weight: 600;
            margin: 6px 0;
        }
        .note-upload-status.error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ============ COVER IMAGE UPLOAD ============
        var coverZone = document.getElementById('coverUploadZone');
        var coverInput = document.getElementById('coverImageInput');
        var coverPreview = document.getElementById('coverPreviewWrapper');
        var coverPreviewImg = coverPreview.querySelector('img');
        var coverFilename = coverPreview.querySelector('.filename');
        var coverFilesize = coverPreview.querySelector('.filesize');
        var removeBtn = coverPreview.querySelector('.remove-btn');
        var existingCover = document.getElementById('existingCover');

        function formatBytes(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        function showCoverPreview(file) {
            if (!file || !file.type.startsWith('image/')) {
                alert('Please select a valid image file.');
                return;
            }
            var reader = new FileReader();
            reader.onload = function(e) {
                coverPreviewImg.src = e.target.result;
                coverFilename.textContent = file.name;
                coverFilesize.textContent = formatBytes(file.size);
                coverPreview.classList.add('has-image');
                coverZone.style.display = 'none';
                if (existingCover) existingCover.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        if (coverInput) {
            coverInput.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    showCoverPreview(e.target.files[0]);
                }
            });
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                coverInput.value = '';
                coverPreviewImg.src = '';
                coverPreview.classList.remove('has-image');
                coverZone.style.display = 'flex';
            });
        }
        if (coverZone) {
            ['dragenter', 'dragover'].forEach(function(evt) {
                coverZone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    coverZone.classList.add('dragover');
                });
            });
            ['dragleave', 'drop'].forEach(function(evt) {
                coverZone.addEventListener(evt, function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    coverZone.classList.remove('dragover');
                });
            });
            coverZone.addEventListener('drop', function(e) {
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    var file = e.dataTransfer.files[0];
                    var dt = new DataTransfer();
                    dt.items.add(file);
                    coverInput.files = dt.files;
                    showCoverPreview(file);
                }
            });
        }

        // ============ SUMMERNOTE EDITOR ============
        var textarea = document.querySelector('textarea[name="content"]');
        if (!textarea) return;

        function initEditor() {
            if (!window.jQuery || typeof $.fn.summernote !== 'function') {
                setTimeout(initEditor, 100);
                return;
            }
            try {
                $(textarea).summernote({
                    height: 280,
                    minHeight: 220,
                    maxHeight: 600,
                    placeholder: 'Write the news content here...',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph', 'height', 'blockquote']],
                        ['insert', ['link', 'picture', 'hr']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'blockquote', 'pre'],
                    fontSizes: ['8', '10', '12', '14', '16', '18', '20', '24', '28', '36'],
                    dialogFade: true,
                    callbacks: {
                        onImageUpload: function(files) {
                            handleEditorImageUpload(files, $(this));
                        }
                    }
                });
            } catch (e) {
                console.error('Summernote init failed:', e);
            }
        }

        function handleEditorImageUpload(files, editor) {
            if (!files || !files.length) return;
            var file = files[0];
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                return;
            }

            var $status = $('<div class="note-upload-status"><i class="fas fa-spinner fa-spin"></i> Uploading ' + file.name + '...</div>');
            editor.summernote('insertNode', $status[0]);

            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            var token = csrfMeta ? csrfMeta.getAttribute('content') : null;
            if (!token) {
                $status.addClass('error').html('<i class="fas fa-exclamation-triangle"></i> No CSRF token — using inline preview');
                insertBase64(file, editor, $status);
                return;
            }

            var formData = new FormData();
            formData.append('file', file);
            formData.append('_token', token);

            fetch('{{ route("admin.news.upload-image") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(function(r) {
                if (!r.ok) return r.text().then(function(t) { throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 200)); });
                return r.json();
            })
            .then(function(data) {
                $status.remove();
                if (data.url) {
                    editor.summernote('insertImage', data.url);
                } else {
                    $status.addClass('error').html('<i class="fas fa-exclamation-triangle"></i> Server error — using inline preview');
                    insertBase64(file, editor, $status);
                }
            })
            .catch(function(err) {
                console.error('Editor image upload error:', err);
                $status.addClass('error').html('<i class="fas fa-exclamation-triangle"></i> Upload failed — using inline preview');
                insertBase64(file, editor, $status);
            });
        }

        function insertBase64(file, editor, $status) {
            var reader = new FileReader();
            reader.onload = function(e) {
                editor.summernote('insertImage', e.target.result);
                if ($status) $status.remove();
            };
            reader.onerror = function() {
                if ($status) $status.addClass('error').html('<i class="fas fa-times"></i> Failed to read image');
            };
            reader.readAsDataURL(file);
        }

        initEditor();
    });
    </script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-newspaper me-2"></i>Edit News</h2>
        <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger">
        <strong><i class="fas fa-exclamation-triangle"></i> Please fix these errors:</strong>
        <ul style="margin:0.5rem 0 0 1.5rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.news.update', $news) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-lg" value="{{ old('title', $news->title) }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Cover Image</label>
                    <p class="text-muted small mb-2">This image appears as the thumbnail on news cards across the website.</p>

                    @php
                        $existingCoverUrl = null;
                        if ($news->image_path) {
                            $basename = basename($news->image_path);
                            if (file_exists(public_path('news-images/' . $basename))) {
                                $existingCoverUrl = asset('news-images/' . $basename);
                            } elseif (\Storage::disk('public')->exists($news->image_path)) {
                                $existingCoverUrl = asset('storage/' . $news->image_path);
                            }
                        }
                    @endphp

                    @if($existingCoverUrl)
                    <div class="existing-cover" id="existingCover">
                        <img src="{{ $existingCoverUrl }}" alt="Current cover">
                        <div class="existing-cover-meta">
                            <span><i class="fas fa-check-circle text-success"></i> Current cover image</span>
                            <span class="text-muted" style="font-size:0.72rem;">{{ basename($news->image_path) }}</span>
                        </div>
                    </div>
                    @elseif($news->image_path)
                    <div class="alert alert-warning" style="font-size:0.85rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Cover image file not found at: <code>{{ $news->image_path }}</code>
                        <br>Visit <a href="{{ route('news.debug') }}" target="_blank">debug page</a> for diagnostics.
                    </div>
                    @endif

                    <div class="cover-upload-zone" id="coverUploadZone" style="@if($existingCoverUrl) margin-top:12px; @endif">
                        <div class="cover-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <p class="cover-upload-title">Click to upload new image or drag & drop</p>
                        <p class="cover-upload-hint">PNG, JPG, GIF, WEBP up to 10MB</p>
                        <input type="file" name="image" id="coverImageInput" accept="image/*">
                    </div>

                    <div class="cover-preview-wrapper" id="coverPreviewWrapper">
                        <img src="" alt="New cover preview">
                        <div class="cover-preview-actions">
                            <button type="button" class="remove-btn"><i class="fas fa-trash-alt me-1"></i>Remove</button>
                        </div>
                        <div class="cover-preview-meta">
                            <span class="filename">No file selected</span>
                            <span class="filesize">—</span>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Content</label>
                    <p class="text-muted small mb-2">Write the full news article. Use the <i class="fas fa-image"></i> picture icon to insert images inside the content.</p>
                    <textarea name="content" class="form-control" rows="8">{{ old('content', $news->content) }}</textarea>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Active</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $news->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">Show on website</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Show Until</label>
                        <input type="datetime-local" name="show_until" class="form-control" value="{{ old('show_until', $news->show_until ? $news->show_until->format('Y-m-d\TH:i') : '') }}">
                        <small class="text-muted">Leave empty to show for 2 days</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Priority</label>
                        <input type="number" name="priority" class="form-control" value="{{ old('priority', $news->priority) }}" min="0">
                        <small class="text-muted">Higher = shown first</small>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3">
                    <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i> Update News</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
