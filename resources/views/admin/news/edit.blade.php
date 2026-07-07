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
                callbacks: {
                    onImageUpload: function(files) {
                        var editor = $(this);
                        var formData = new FormData();
                        formData.append('file', files[0]);
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                        var $placeholder = $('<div class="note-image-uploading"><i class="fas fa-spinner fa-spin"></i> Uploading...</div>');
                        editor.summernote('insertNode', $placeholder[0]);
                        fetch('{{ route("admin.news.upload-image") }}', {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.url) {
                                $placeholder.remove();
                                editor.summernote('insertImage', data.url);
                            } else {
                                $placeholder.remove();
                                alert('Image upload failed: ' + (data.error || 'Unknown error'));
                            }
                        })
                        .catch(function(err) {
                            $placeholder.remove();
                            alert('Image upload failed: ' + err.message);
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
                        </small>
                    </label>
                    <textarea name="content" class="form-control" rows="6">{{ old('content', $news->content) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Cover Image</label>
                    @if($news->image_path)<img src="{{ Storage::url($news->image_path) }}" alt="" style="max-height:80px" class="mb-2 d-block">@endif
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <small class="text-muted">Optional — shown as a thumbnail on news cards</small>
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
