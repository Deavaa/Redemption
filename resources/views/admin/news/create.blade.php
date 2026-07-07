@extends('layouts.admin')
@section('title', 'Add News')

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
        .note-editor.note-airframe .note-editing-area .note-editable-content,
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
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var textarea = document.querySelector('textarea[name="content"]');
        if (textarea && window.jQuery && typeof $.fn.summernote === 'function') {
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
                codemirror: { theme: 'monokai' },
                // Upload images to the server instead of embedding as base64.
                // This keeps the HTML content small and lets the news splash
                // panel extract a real <img src="http://..."> URL for the
                // card thumbnail.
                callbacks: {
                    onImageUpload: function(files) {
                        var editor = $(this);
                        var formData = new FormData();
                        formData.append('file', files[0]);
                        var csrfToken = document.querySelector('meta[name="csrf-token"]');
                        if (!csrfToken) {
                            alert('CSRF token meta tag not found. Image upload cannot proceed.');
                            return;
                        }
                        formData.append('_token', csrfToken.getAttribute('content'));
                        // Show a placeholder while uploading
                        var $placeholder = $('<div class="note-image-uploading" style="padding:8px 12px;background:#ecfdf5;border-radius:6px;color:#059669;font-size:0.85rem;"><i class="fas fa-spinner fa-spin"></i> Uploading image...</div>');
                        editor.summernote('insertNode', $placeholder[0]);
                        var uploadUrl = '{{ route("admin.news.upload-image") }}';
                        fetch(uploadUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                            }
                        })
                        .then(function(r) {
                            if (!r.ok) {
                                return r.text().then(function(t) {
                                    throw new Error('HTTP ' + r.status + ': ' + t);
                                });
                            }
                            return r.json();
                        })
                        .then(function(data) {
                            $placeholder.remove();
                            if (data.url) {
                                editor.summernote('insertImage', data.url, function($image) {
                                    $image.css('max-width', '100%');
                                    $image.css('height', 'auto');
                                    $image.css('border-radius', '8px');
                                });
                            } else {
                                alert('Image upload failed: ' + (data.error || 'Unknown error. Check the browser console for details.'));
                                console.error('Upload response:', data);
                            }
                        })
                        .catch(function(err) {
                            $placeholder.remove();
                            console.error('Image upload error:', err);
                            alert('Image upload failed: ' + err.message + '\n\nCheck that:\n1. You are logged in\n2. The route /admin/news/upload-image is registered\n3. PHP has write permission to storage/app/public/news-images/');
                        });
                    }
                }
            });
        }
    });
    </script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-newspaper me-2"></i>Add News</h2>
        <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">
                        Content
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle me-1"></i>
                            Rich text editor — supports bold, italic, headings, lists, links, quotes, and images.
                        </small>
                    </label>
                    <textarea name="content" class="form-control" rows="6">{{ old('content') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cover Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">Optional — shown as a thumbnail on news cards</small>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Active</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                            <label class="form-check-label">Show on website</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Show Until</label>
                        <input type="datetime-local" name="show_until" class="form-control" value="{{ old('show_until') }}">
                        <small class="text-muted">Leave empty to show indefinitely</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Priority</label>
                        <input type="number" name="priority" class="form-control" value="{{ old('priority', 0) }}" min="0">
                        <small class="text-muted">Higher = shown first</small>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save News</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
