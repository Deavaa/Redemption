@extends('layouts.admin')
@section('title', 'Edit News')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        .note-editor { border-radius: 8px; overflow: hidden; }
        .note-toolbar {
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 8px 12px !important;
        }
        .note-btn-group .note-btn {
            border-radius: 4px !important;
            margin: 0 1px !important;
        }
        .note-btn-group .note-btn:hover {
            background: rgba(16, 185, 129, 0.10) !important;
        }
        .note-editable {
            padding: 14px 16px !important;
            min-height: 220px;
            font-family: 'Inter', sans-serif !important;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #1f2937;
        }
        .note-editable p { margin-bottom: 0.6rem; }
        .note-editable h1, .note-editable h2, .note-editable h3 { color: #0f172a; font-weight: 700; }
        .note-editable ul, .note-editable ol { padding-left: 1.5rem; margin-bottom: 0.6rem; }
        .note-editable a { color: #10B981; text-decoration: underline; }
        .note-editable blockquote {
            border-left: 3px solid #10B981;
            padding-left: 1rem;
            margin: 0.6rem 0;
            color: #475569;
            font-style: italic;
        }
        .note-editable img { max-width: 100%; border-radius: 8px; margin: 0.5rem 0; }
        .note-editable code {
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.85em;
            color: #db2777;
        }
        .note-upload-status {
            display: inline-block;
            padding: 6px 14px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            color: #059669;
            font-size: 0.82rem;
            font-weight: 600;
            margin: 4px 0;
        }
        .note-upload-status.error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #dc2626;
        }
        .cover-preview {
            margin-top: 8px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e5e7eb;
            max-width: 300px;
            display: none;
        }
        .cover-preview img { width: 100%; display: block; }
        .cover-preview-label {
            font-size: 0.72rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 10px;
            background: #f9fafb;
            display: block;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var textarea = document.querySelector('textarea[name="content"]');
        if (!textarea) return;

        function initEditor() {
            if (!window.jQuery || typeof $.fn.summernote !== 'function') {
                setTimeout(initEditor, 100);
                return;
            }

            try {
                // Set the textarea content BEFORE initializing Summernote
                // so the editor shows existing HTML content on edit.
                $(textarea).summernote({
                    height: 260,
                    minHeight: 200,
                    maxHeight: 600,
                    placeholder: 'Write the news content here. You can use bold, italic, headings, lists, links, quotes, and images...',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
                        ['fontname', ['fontname']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph', 'height', 'blockquote']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'hr']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    fontNames: ['Inter', 'Arial', 'Times New Roman', 'Courier New', 'Verdana', 'Georgia'],
                    fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '28', '36', '48'],
                    styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'blockquote', 'pre'],
                    dialogFade: true,
                    disableLinkTarget: false,
                    callbacks: {
                        onImageUpload: function(files) {
                            handleImageUpload(files, $(this));
                        }
                    }
                });
                console.log('[Summernote] Editor initialized successfully');
            } catch (e) {
                console.error('[Summernote] Initialization failed:', e);
            }
        }

        function handleImageUpload(files, editor) {
            if (!files || !files.length) return;
            var file = files[0];
            console.log('[News Image] Upload started:', file.name, file.type, file.size + ' bytes');

            if (!file.type.startsWith('image/')) {
                alert('Please select an image file.');
                return;
            }

            var $status = $('<div class="note-upload-status"><i class="fas fa-spinner fa-spin"></i> Uploading ' + file.name + '...</div>');
            editor.summernote('insertNode', $status[0]);

            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            var token = csrfToken ? csrfToken.getAttribute('content') : null;
            if (!token) {
                $status.removeClass().addClass('note-upload-status error').html('<i class="fas fa-exclamation-triangle"></i> CSRF token not found — using inline preview');
                insertAsBase64(file, editor, $status);
                return;
            }

            var formData = new FormData();
            formData.append('file', file);
            formData.append('_token', token);

            var uploadUrl = '{{ route("admin.news.upload-image") }}';
            console.log('[News Image] Uploading to:', uploadUrl);

            fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                }
            })
            .then(function(response) {
                console.log('[News Image] Server response status:', response.status);
                if (!response.ok) {
                    return response.text().then(function(text) {
                        throw new Error('HTTP ' + response.status + ': ' + text.substring(0, 200));
                    });
                }
                return response.json();
            })
            .then(function(data) {
                console.log('[News Image] Server response data:', data);
                $status.remove();
                if (data.url) {
                    editor.summernote('insertImage', data.url);
                    console.log('[News Image] Image inserted from server URL:', data.url);
                } else {
                    console.warn('[News Image] Server returned error:', data.error);
                    $status.removeClass().addClass('note-upload-status error').html('<i class="fas fa-exclamation-triangle"></i> Server upload failed — using inline preview');
                    insertAsBase64(file, editor, $status);
                }
            })
            .catch(function(err) {
                console.error('[News Image] Upload error:', err);
                $status.removeClass().addClass('note-upload-status error').html('<i class="fas fa-exclamation-triangle"></i> Network error — using inline preview');
                insertAsBase64(file, editor, $status);
            });
        }

        function insertAsBase64(file, editor, $status) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var base64Url = e.target.result;
                editor.summernote('insertImage', base64Url);
                if ($status) $status.remove();
                console.log('[News Image] Image inserted as base64 (fallback)');
            };
            reader.onerror = function() {
                if ($status) {
                    $status.removeClass().addClass('note-upload-status error').html('<i class="fas fa-times"></i> Failed to read image file');
                }
                alert('Failed to read the image file. Please try a different image.');
            };
            reader.readAsDataURL(file);
        }

        initEditor();

        // ---- Cover image preview ----
        var coverInput = document.querySelector('input[name="image"]');
        var coverPreview = document.getElementById('coverPreview');
        if (coverInput && coverPreview) {
            coverInput.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(ev) {
                        var img = coverPreview.querySelector('img');
                        img.src = ev.target.result;
                        coverPreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else {
                    coverPreview.style.display = 'none';
                }
            });
        }
    });
    </script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-newspaper me-2"></i>Edit News</h2>
        <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.news.update', $news) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $news->title) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        Content
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle me-1"></i>
                            Rich text editor — supports bold, italic, headings, lists, links, quotes, and images.
                            Click the <i class="fas fa-image"></i> picture icon to insert an image.
                        </small>
                    </label>
                    <textarea name="content" class="form-control" rows="6">{{ old('content', $news->content) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cover Image</label>
                    @php
                        $existingCoverUrl = null;
                        if ($news->image_path) {
                            $basename = basename($news->image_path);
                            if (\Storage::disk('public')->exists($news->image_path)) {
                                $existingCoverUrl = asset('storage/' . $news->image_path);
                            } elseif (file_exists(public_path('news-images/' . $basename))) {
                                $existingCoverUrl = asset('news-images/' . $basename);
                            }
                        }
                    @endphp
                    @if($existingCoverUrl)
                        <div style="margin-bottom:0.5rem;">
                            <img src="{{ $existingCoverUrl }}" alt="Current cover" style="max-height:100px;border-radius:8px;border:2px solid #e5e7eb;">
                            <div style="font-size:0.75rem;color:#6b7280;margin-top:4px;">Current cover image</div>
                        </div>
                    @elseif($news->image_path)
                        <div style="padding:0.75rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;color:#dc2626;font-size:0.85rem;margin-bottom:0.5rem;">
                            <i class="fas fa-exclamation-triangle"></i>
                            Cover image file not found at: {{ $news->image_path }}
                            <br>Run <code>php artisan storage:link</code> or visit the <a href="{{ route('news.debug') }}" target="_blank">debug page</a>.
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">Upload a new image to replace the current one</small>
                    <div class="cover-preview" id="coverPreview">
                        <span class="cover-preview-label">New image preview</span>
                        <img src="" alt="Cover preview">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Active</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $news->is_active ? 'checked' : '' }}>
                            <label class="form-check-label">Show on website</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Show Until</label>
                        <input type="datetime-local" name="show_until" class="form-control" value="{{ old('show_until', $news->show_until ? $news->show_until->format('Y-m-d\TH:i') : '') }}">
                        <small class="text-muted">Leave empty to show for 2 days or until newer news is posted</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Priority</label>
                        <input type="number" name="priority" class="form-control" value="{{ old('priority', $news->priority) }}" min="0">
                        <small class="text-muted">Higher = shown first</small>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update News</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
