@extends('layouts.admin')
@section('title', 'Add Team Member')

@push('scripts')
    <script src="{{ asset('js/client-compress.js') }}"></script>
@endpush

@section('content')
<style>
.tm-page{font-family:'Inter',sans-serif;padding:0 0 2rem;}
.tm-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;}
.tm-header h2{margin:0;font-size:1.5rem;font-weight:700;color:#0f172a;}
.tm-header h2 i{color:#047857;margin-right:8px;}
.tm-btn-back{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:1.5px solid #e2e8f0;border-radius:10px;text-decoration:none;color:#64748b;font-size:0.85rem;font-weight:600;transition:all 0.2s;}
.tm-btn-back:hover{border-color:#047857;color:#047857;background:#f0fdf4;text-decoration:none;}

.tm-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.04);}
.tm-section{border-bottom:1px solid #f1f5f9;}
.tm-section:last-child{border-bottom:none;}
.tm-section-head{display:flex;align-items:center;gap:12px;padding:20px 24px 12px;}
.tm-section-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.tm-section-icon.blue{background:#e0e7ff;color:#4361ee;}
.tm-section-icon.green{background:#d1fae5;color:#059669;}
.tm-section-icon.purple{background:#ede9fe;color:#7c3aed;}
.tm-section-icon.gold{background:#fef3c7;color:#d97706;}
.tm-section-title{font-size:1.05rem;font-weight:700;color:#0f172a;margin:0;}
.tm-section-desc{font-size:0.8rem;color:#94a3b8;margin:2px 0 0;}
.tm-section-body{padding:16px 24px 24px;}

.tm-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px;}
@media(max-width:768px){.tm-grid{grid-template-columns:1fr;}}
.tm-span2{grid-column:span 2;}
.tm-field{display:flex;flex-direction:column;}
.tm-label{font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:6px;display:flex;align-items:center;gap:4px;}
.tm-label .req{color:#ef4444;}
.tm-label small{font-weight:400;color:#9ca3af;font-size:0.75rem;}
.tm-input{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:0.7rem 0.9rem;font-size:0.9rem;color:#1f2937;background:#fff;transition:all 0.2s;font-family:'Inter',sans-serif;}
.tm-input:focus{outline:none;border-color:#047857;box-shadow:0 0 0 3px rgba(4,120,87,0.10);}
.tm-textarea{resize:vertical;min-height:80px;}

/* Photo upload zone */
.tm-photo-zone{border:2px dashed #cbd5e1;border-radius:14px;padding:24px;text-align:center;cursor:pointer;transition:all 0.3s;background:#f8fafc;min-height:200px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;position:relative;}
.tm-photo-zone:hover{border-color:#047857;background:#f0fdf4;transform:translateY(-2px);}
.tm-photo-zone.dragover{border-color:#047857;background:#ecfdf5;border-style:solid;}
.tm-photo-zone input[type="file"]{position:absolute;top:0;left:0;right:0;bottom:0;opacity:0;cursor:pointer;}
.tm-photo-icon{width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,#047857 0%,#0d9488 100%);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;box-shadow:0 6px 18px rgba(4,120,87,0.25);}
.tm-photo-title{font-weight:700;color:#0f172a;font-size:0.9rem;margin:4px 0 0;}
.tm-photo-hint{font-size:0.75rem;color:#64748b;margin:0;}
.tm-photo-preview{position:relative;margin-top:12px;border-radius:14px;overflow:hidden;border:2px solid #e2e8f0;display:none;background:#f8fafc;}
.tm-photo-preview.has-image{display:block;}
.tm-photo-preview img{width:100%;max-height:250px;object-fit:cover;display:block;}
.tm-photo-preview-actions{position:absolute;top:10px;right:10px;}
.tm-photo-remove{background:rgba(15,23,42,0.75);color:#fff;border:none;border-radius:8px;padding:6px 12px;font-size:0.75rem;font-weight:600;cursor:pointer;backdrop-filter:blur(8px);}
.tm-photo-remove:hover{background:#dc2626;}
.compress-status{font-size:0.75rem;color:#6b7280;margin-top:4px;display:none;}

/* Toggle */
.tm-toggle-wrap{display:flex;align-items:center;gap:10px;padding-top:4px;}
.tm-toggle{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;}
.tm-toggle input{opacity:0;width:0;height:0;}
.tm-toggle-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background:#d1d5db;transition:0.3s;border-radius:24px;}
.tm-toggle-slider::before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background:#fff;transition:0.3s;border-radius:50%;}
.tm-toggle input:checked+.tm-toggle-slider{background:linear-gradient(135deg,#047857,#0d9488);}
.tm-toggle input:checked+.tm-toggle-slider::before{transform:translateX(20px);}

.tm-actions{display:flex;justify-content:flex-end;gap:10px;padding:20px 24px;border-top:1px solid #f1f5f9;background:#fafbfc;}
.tm-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:10px;font-size:0.9rem;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:all 0.25s;}
.tm-btn-primary{background:linear-gradient(135deg,#047857 0%,#0d9488 100%);color:#fff;box-shadow:0 4px 14px rgba(4,120,87,0.30);}
.tm-btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(4,120,87,0.40);color:#fff;text-decoration:none;}
.tm-btn-ghost{background:transparent;color:#64748b;padding:10px 16px;}
.tm-btn-ghost:hover{color:#0f172a;background:#f3f4f6;text-decoration:none;}
</style>

<div class="tm-page">
    <div class="tm-header">
        <h2><i class="fas fa-user-plus"></i>Add Team Member</h2>
        <a href="{{ route('admin.team-members.index') }}" class="tm-btn-back"><i class="fas fa-arrow-left"></i>Back to List</a>
    </div>

    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:14px 18px;margin-bottom:16px;color:#991b1b;font-size:0.85rem;">
        <strong><i class="fas fa-exclamation-triangle"></i> Please fix these errors:</strong>
        <ul style="margin:6px 0 0 1.2rem;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="tm-card">
        <form method="POST" action="{{ route('admin.team-members.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- Photo Upload --}}
            <div class="tm-section">
                <div class="tm-section-head">
                    <div class="tm-section-icon gold"><i class="fas fa-camera"></i></div>
                    <div>
                        <h3 class="tm-section-title">Photo</h3>
                        <p class="tm-section-desc">Upload a professional photo — auto-compressed in your browser before upload</p>
                    </div>
                </div>
                <div class="tm-section-body">
                    <div class="tm-photo-zone" id="photoZone">
                        <div class="tm-photo-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                        <p class="tm-photo-title">Click to upload or drag & drop</p>
                        <p class="tm-photo-hint">JPEG, PNG, GIF, WEBP — auto-compressed to ~1MB (any size accepted)</p>
                        <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" data-compress="1200" data-maxsize="1500">
                    </div>
                    <div class="tm-photo-preview" id="photoPreview">
                        <img src="" alt="Preview" id="previewImg">
                        <div class="tm-photo-preview-actions">
                            <button type="button" class="tm-photo-remove" onclick="removePhoto()"><i class="fas fa-trash-alt"></i> Remove</button>
                        </div>
                    </div>
                    <div class="compress-status" id="compressStatus"></div>
                </div>
            </div>

            {{-- Personal Information --}}
            <div class="tm-section">
                <div class="tm-section-head">
                    <div class="tm-section-icon blue"><i class="fas fa-user"></i></div>
                    <div>
                        <h3 class="tm-section-title">Personal Information</h3>
                        <p class="tm-section-desc">Name, designation, and professional details</p>
                    </div>
                </div>
                <div class="tm-section-body">
                    <div class="tm-grid">
                        <div class="tm-field">
                            <label class="tm-label">Full Name <span class="req">*</span></label>
                            <input type="text" name="name" class="tm-input" value="{{ old('name') }}" placeholder="e.g. John Doe" required autofocus>
                        </div>
                        <div class="tm-field">
                            <label class="tm-label">Designation <span class="req">*</span></label>
                            <input type="text" name="designation" class="tm-input" value="{{ old('designation') }}" placeholder="e.g. Principal, Head of Department" required>
                        </div>
                        <div class="tm-field">
                            <label class="tm-label">Department <small>(optional)</small></label>
                            <input type="text" name="department" class="tm-input" value="{{ old('department') }}" placeholder="e.g. Science, Administration">
                        </div>
                        <div class="tm-field">
                            <label class="tm-label">Qualification <small>(optional)</small></label>
                            <input type="text" name="qualification" class="tm-input" value="{{ old('qualification') }}" placeholder="e.g. M.Ed, PhD">
                        </div>
                        <div class="tm-field">
                            <label class="tm-label">Experience <small>(optional)</small></label>
                            <input type="text" name="experience" class="tm-input" value="{{ old('experience') }}" placeholder="e.g. 10 years">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact --}}
            <div class="tm-section">
                <div class="tm-section-head">
                    <div class="tm-section-icon green"><i class="fas fa-address-book"></i></div>
                    <div>
                        <h3 class="tm-section-title">Contact Information</h3>
                        <p class="tm-section-desc">Optional contact details</p>
                    </div>
                </div>
                <div class="tm-section-body">
                    <div class="tm-grid">
                        <div class="tm-field">
                            <label class="tm-label">Email <small>(optional)</small></label>
                            <input type="email" name="email" class="tm-input" value="{{ old('email') }}" placeholder="e.g. john@school.com">
                        </div>
                        <div class="tm-field">
                            <label class="tm-label">Phone <small>(optional)</small></label>
                            <input type="text" name="phone" class="tm-input" value="{{ old('phone') }}" placeholder="e.g. +251 911 234 567">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bio & Settings --}}
            <div class="tm-section">
                <div class="tm-section-head">
                    <div class="tm-section-icon purple"><i class="fas fa-cog"></i></div>
                    <div>
                        <h3 class="tm-section-title">Bio & Display Settings</h3>
                        <p class="tm-section-desc">Bio text and website visibility</p>
                    </div>
                </div>
                <div class="tm-section-body">
                    <div class="tm-grid">
                        <div class="tm-field tm-span2">
                            <label class="tm-label">Bio <small>(optional)</small></label>
                            <textarea name="bio" class="tm-input tm-textarea" rows="4" placeholder="Brief biography or description...">{{ old('bio') }}</textarea>
                        </div>
                        <div class="tm-field">
                            <label class="tm-label">Sort Order <small>(optional)</small></label>
                            <input type="number" name="sort_order" class="tm-input" value="{{ old('sort_order', 0) }}" min="0" placeholder="0">
                        </div>
                        <div class="tm-field">
                            <label class="tm-label">Active Status</label>
                            <div class="tm-toggle-wrap">
                                <label class="tm-toggle">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <span class="tm-toggle-slider"></span>
                                </label>
                                <span style="font-size:0.85rem;color:#6b7280;">Show on website</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tm-actions">
                <a href="{{ route('admin.team-members.index') }}" class="tm-btn tm-btn-ghost">Cancel</a>
                <button type="submit" class="tm-btn tm-btn-primary"><i class="fas fa-check"></i>Create Member</button>
            </div>
        </form>
    </div>
</div>

<script>
// Photo preview + drag/drop
var photoZone = document.getElementById('photoZone');
var photoInput = document.getElementById('photo');
var photoPreview = document.getElementById('photoPreview');
var previewImg = document.getElementById('previewImg');

photoInput.addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        showPreview(e.target.files[0]);
    }
});

['dragenter','dragover'].forEach(function(evt) {
    photoZone.addEventListener(evt, function(e){e.preventDefault();e.stopPropagation();photoZone.classList.add('dragover');});
});
['dragleave','drop'].forEach(function(evt) {
    photoZone.addEventListener(evt, function(e){e.preventDefault();e.stopPropagation();photoZone.classList.remove('dragover');});
});
photoZone.addEventListener('drop', function(e) {
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
        var dt = new DataTransfer();
        dt.items.add(e.dataTransfer.files[0]);
        photoInput.files = dt.files;
        showPreview(e.dataTransfer.files[0]);
    }
});

function showPreview(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
        previewImg.src = e.target.result;
        photoPreview.classList.add('has-image');
        photoZone.style.display = 'none';
    };
    reader.readAsDataURL(file);
}

function removePhoto() {
    photoInput.value = '';
    previewImg.src = '';
    photoPreview.classList.remove('has-image');
    photoZone.style.display = 'flex';
}
</script>
@endsection
