@extends('layouts.admin')
@section('title', 'School Settings')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">System</a></li>
                    <li class="active">Settings</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">School Settings</h1>
            <p class="modern-page-subtitle">Configure and manage system preferences</p>
        </div>
        <div class="modern-page-header-right">
            <button type="button" class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                <i class="fas fa-plus"></i>
                <span>Add Setting</span>
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-cog"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $settings->sum(fn($g) => $g->count()) }}</span>
                <span class="modern-stat-label">Total Settings</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $settings->count() }}</span>
                <span class="modern-stat-label">Groups</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-check-double"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $settings->sum(fn($g) => $g->where('value', '!=', '')->where('value', '!=', null)->count()) }}</span>
                <span class="modern-stat-label">Configured</span>
            </div>
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    {{-- Logo Upload Section --}}
    <div class="modern-card" style="margin-bottom: 1.25rem;">
        <div class="modern-form-section">
            <div class="modern-form-section-header">
                <div class="modern-form-section-icon modern-form-section-icon-purple">
                    <i class="fas fa-image"></i>
                </div>
                <div>
                    <h3 class="modern-form-section-title">School Logo</h3>
                    <p class="modern-form-section-desc">Upload your school logo for the website and admin panel</p>
                </div>
            </div>
            <div class="modern-form-section-body">
                <form method="POST" action="{{ route('admin.settings.uploadLogo') }}" enctype="multipart/form-data" id="logoForm">
                    @csrf
                    <div class="logo-upload-area">
                        <div class="logo-preview" id="logoPreview">
                            @php $logoPath = \App\Models\Setting::get('school_logo'); @endphp
                            @if($logoPath && file_exists(public_path('storage/' . $logoPath)))
                                <img src="{{ asset('storage/' . $logoPath) }}?t={{ time() }}" alt="School Logo" id="logoImg">
                            @else
                                <div class="logo-placeholder" id="logoPlaceholder">
                                    <i class="fas fa-school"></i>
                                    <span>No logo uploaded</span>
                                </div>
                            @endif
                        </div>
                        <div class="logo-upload-controls">
                            <label class="btn-modern btn-modern-outline" for="logoFile">
                                <i class="fas fa-upload"></i>
                                <span>Choose Logo</span>
                            </label>
                            <input type="file" name="logo" id="logoFile" accept="image/png,image/jpeg,image/svg+xml,image/webp" style="display:none;" onchange="previewLogo(this)">
                            <span class="logo-hint">PNG, JPG, SVG or WebP. Max 2MB. Recommended: 200x200px</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Settings Form --}}
    <form method="POST" action="{{ route('admin.settings.updateAll') }}">
        @csrf

        @foreach($settings as $group => $items)
        <div class="modern-card" style="margin-bottom: 1.25rem;">
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    @php
                        $groupIcons = [
                            'general' => ['icon' => 'fas fa-sliders-h', 'color' => 'blue'],
                            'academic' => ['icon' => 'fas fa-graduation-cap', 'color' => 'green'],
                            'contact' => ['icon' => 'fas fa-address-book', 'color' => 'gold'],
                            'social' => ['icon' => 'fas fa-share-alt', 'color' => 'purple'],
                            'about' => ['icon' => 'fas fa-info-circle', 'color' => 'blue'],
                            'appearance' => ['icon' => 'fas fa-palette', 'color' => 'purple'],
                            'email' => ['icon' => 'fas fa-envelope', 'color' => 'green'],
                            'fees' => ['icon' => 'fas fa-money-bill-wave', 'color' => 'gold'],
                            'website' => ['icon' => 'fas fa-globe', 'color' => 'blue'],
                        ];
                        $groupConfig = $groupIcons[$group] ?? ['icon' => 'fas fa-folder', 'color' => 'blue'];
                    @endphp
                    <div class="modern-form-section-icon modern-form-section-icon-{{ $groupConfig['color'] }}">
                        <i class="{{ $groupConfig['icon'] }}"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">{{ $groupLabels[$group] ?? ucfirst($group) }}</h3>
                        <p class="modern-form-section-desc">{{ $items->count() }} setting{{ $items->count() > 1 ? 's' : '' }} in this group</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        @foreach($items as $item)
                            @if($item->type === 'boolean')
                            <div class="modern-form-group">
                                <div class="modern-toggle-wrapper">
                                    <label class="modern-toggle">
                                        <input type="hidden" name="settings[{{ $item->group }}__{{ $item->key }}]" value="0" disabled class="toggle-hidden">
                                        <input type="checkbox" name="settings[{{ $item->group }}__{{ $item->key }}]" value="1" {{ $item->value ? 'checked' : '' }}>
                                        <span class="modern-toggle-slider"></span>
                                    </label>
                                    <div class="modern-toggle-info">
                                        <span class="modern-toggle-title">{{ ucfirst(str_replace('_', ' ', $item->key)) }}</span>
                                        @if($item->description)
                                        <span class="modern-toggle-desc">{{ $item->description }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @elseif($item->type === 'file')
                            <div class="modern-form-group">
                                <label class="modern-form-label" for="setting_{{ $item->id }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->key)) }}
                                </label>
                                @if($item->value)
                                <div class="current-file-preview">
                                    <img src="{{ asset('storage/' . $item->value) }}?t={{ time() }}" alt="{{ $item->key }}" class="file-preview-img">
                                </div>
                                @endif
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-file-image modern-input-icon"></i>
                                    <input type="file"
                                        name="file_{{ $item->group }}__{{ $item->key }}"
                                        id="setting_{{ $item->id }}"
                                        class="modern-input modern-input-file"
                                        accept="image/*"
                                        data-setting-key="{{ $item->group }}__{{ $item->key }}">
                                </div>
                                @if($item->description)
                                <span class="modern-form-hint">{{ $item->description }}</span>
                                @endif
                            </div>
                            @else
                            <div class="modern-form-group">
                                <label class="modern-form-label" for="setting_{{ $item->id }}">
                                    {{ ucfirst(str_replace('_', ' ', $item->key)) }}
                                </label>
                                @if($item->type === 'number')
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-hashtag modern-input-icon"></i>
                                    <input type="number"
                                        name="settings[{{ $item->group }}__{{ $item->key }}]"
                                        id="setting_{{ $item->id }}"
                                        class="modern-input"
                                        value="{{ $item->value }}"
                                        placeholder="Enter value...">
                                </div>
                                @elseif($item->type === 'textarea')
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-align-left modern-input-icon" style="top: 1rem; transform: none;"></i>
                                    <textarea name="settings[{{ $item->group }}__{{ $item->key }}]"
                                        id="setting_{{ $item->id }}"
                                        class="modern-textarea"
                                        rows="3"
                                        placeholder="Enter value...">{{ $item->value }}</textarea>
                                </div>
                                @else
                                <div class="modern-input-wrapper">
                                    <i class="fas fa-font modern-input-icon"></i>
                                    <input type="text"
                                        name="settings[{{ $item->group }}__{{ $item->key }}]"
                                        id="setting_{{ $item->id }}"
                                        class="modern-input"
                                        value="{{ $item->value }}"
                                        placeholder="Enter value...">
                                </div>
                                @endif
                                @if($item->description)
                                <span class="modern-form-hint">{{ $item->description }}</span>
                                @endif
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Form Actions --}}
        <div class="modern-form-actions" style="border-radius: 14px;">
            <a href="{{ route('admin.dashboard') }}" class="btn-modern btn-modern-ghost">
                Cancel
            </a>
            <button type="submit" class="btn-modern btn-modern-primary">
                <i class="fas fa-save"></i>
                <span>Save All Settings</span>
            </button>
        </div>
    </form>
</div>

{{-- Add Setting Modal --}}
<div class="modal fade" id="addSettingModal" tabindex="-1" aria-labelledby="addSettingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 14px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title" id="addSettingModalLabel" style="font-weight: 700; color: #1a1a2e;">Add New Setting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.settings.store') }}">
                @csrf
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.88rem; color: #374151;">Group</label>
                        <select name="group" class="form-select" style="border-radius: 10px; padding: 0.65rem 0.9rem; border: 1.5px solid #e5e7eb;" required>
                            <option value="general">General</option>
                            <option value="contact">Contact</option>
                            <option value="academic">Academic</option>
                            <option value="social">Social Media</option>
                            <option value="about">About Page</option>
                            <option value="appearance">Appearance</option>
                            <option value="email">Email Settings</option>
                            <option value="fees">Fee Settings</option>
                            <option value="website">Website</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.88rem; color: #374151;">Key (e.g., school_name)</label>
                        <input type="text" name="key" class="form-control" style="border-radius: 10px; padding: 0.65rem 0.9rem; border: 1.5px solid #e5e7eb;" required placeholder="e.g., school_name" pattern="[a-z0-9_]+">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.88rem; color: #374151;">Value</label>
                        <input type="text" name="value" class="form-control" style="border-radius: 10px; padding: 0.65rem 0.9rem; border: 1.5px solid #e5e7eb;" placeholder="Enter value...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.88rem; color: #374151;">Type</label>
                        <select name="type" class="form-select" style="border-radius: 10px; padding: 0.65rem 0.9rem; border: 1.5px solid #e5e7eb;">
                            <option value="text">Text</option>
                            <option value="number">Number</option>
                            <option value="boolean">Boolean (Toggle)</option>
                            <option value="textarea">Long Text</option>
                            <option value="file">File/Image</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" style="font-size: 0.88rem; color: #374151;">Description (optional)</label>
                        <input type="text" name="description" class="form-control" style="border-radius: 10px; padding: 0.65rem 0.9rem; border: 1.5px solid #e5e7eb;" placeholder="Brief description of this setting">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #f0f0f0; padding: 1rem 1.5rem;">
                    <button type="button" class="btn-modern btn-modern-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern btn-modern-primary">
                        <i class="fas fa-plus"></i>
                        <span>Add Setting</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }
.modern-page-header-right { display: flex; gap: 0.75rem; flex-wrap: wrap; }

.modern-page-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0;
    letter-spacing: -0.5px;
}

.modern-page-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0.25rem 0 0;
}

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem;
    gap: 0.5rem;
    font-size: 0.8rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Stats Row */
.modern-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.modern-stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    transition: transform 0.2s, box-shadow 0.2s;
}

.modern-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.modern-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.modern-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-stat-icon-green { background: #ecfdf5; color: #10b981; }
.modern-stat-icon-gold { background: #fefce8; color: #d97706; }

.modern-stat-info { display: flex; flex-direction: column; }

.modern-stat-value {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.2;
}

.modern-stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
}

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Logo Upload */
.logo-upload-area {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.logo-preview {
    width: 120px;
    height: 120px;
    border-radius: 14px;
    border: 2px dashed #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #fafbfc;
    flex-shrink: 0;
}

.logo-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 8px;
}

.logo-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: #9ca3af;
    font-size: 0.75rem;
}

.logo-placeholder i {
    font-size: 2rem;
    color: #d1d5db;
}

.logo-upload-controls {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.logo-hint {
    font-size: 0.78rem;
    color: #9ca3af;
}

.btn-modern-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.55rem 1.25rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    border: 1.5px solid #4361ee;
    color: #4361ee;
    background: #fff;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-outline:hover {
    background: #eef2ff;
    color: #3a0ca3;
}

.current-file-preview {
    margin-bottom: 0.5rem;
}

.file-preview-img {
    max-width: 100px;
    max-height: 80px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.modern-input-file {
    padding-left: 2.5rem !important;
}

/* Alert */
.modern-alert {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.85rem 1.25rem;
    margin-bottom: 1.25rem;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 500;
    animation: fadeSlideIn 0.3s ease;
}

.modern-alert-success {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.modern-alert-close {
    margin-left: auto;
    background: none;
    border: none;
    cursor: pointer;
    color: inherit;
    opacity: 0.6;
    transition: opacity 0.2s;
}
.modern-alert-close:hover { opacity: 1; }

/* Form Section */
.modern-form-section { border-bottom: none; }

.modern-form-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 2rem 0.75rem;
}

.modern-form-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }
.modern-form-section-icon-gold { background: #fefce8; color: #d97706; }
.modern-form-section-icon-purple { background: #f5f3ff; color: #7c3aed; }

.modern-form-section-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-form-section-desc {
    font-size: 0.82rem;
    color: #9ca3af;
    margin: 0.15rem 0 0;
}

.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }

/* Form Grid */
.modern-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.modern-form-span-2 { grid-column: span 2; }

/* Form Group */
.modern-form-group { display: flex; flex-direction: column; }

.modern-form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.45rem;
    font-size: 0.88rem;
}

.modern-form-hint {
    display: block;
    color: #9ca3af;
    font-size: 0.78rem;
    margin-top: 0.3rem;
}

/* Input */
.modern-input-wrapper { position: relative; }

.modern-input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.85rem;
    pointer-events: none;
    z-index: 1;
}

.modern-input {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.7rem 0.9rem 0.7rem 2.5rem;
    font-size: 0.9rem;
    color: #1a1a2e;
    background: #fff;
    transition: all 0.2s;
}

.modern-input:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modern-input::placeholder { color: #c5c9d2; }

.modern-textarea {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.7rem 0.9rem 0.7rem 2.5rem;
    font-size: 0.9rem;
    color: #1a1a2e;
    background: #fff;
    transition: all 0.2s;
    resize: vertical;
    font-family: inherit;
}

.modern-textarea:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modern-textarea::placeholder { color: #c5c9d2; }

/* Toggle */
.modern-toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding-top: 0.5rem;
}

.modern-toggle {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}

.modern-toggle input { opacity: 0; width: 0; height: 0; }

.modern-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background: #d1d5db;
    border-radius: 50px;
    transition: 0.3s;
}

.modern-toggle-slider::before {
    content: '';
    position: absolute;
    height: 20px; width: 20px;
    left: 3px; bottom: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}

.modern-toggle input:checked + .modern-toggle-slider { background: #4361ee; }
.modern-toggle input:checked + .modern-toggle-slider::before { transform: translateX(22px); }

.modern-toggle-info { display: flex; flex-direction: column; }
.modern-toggle-title { font-weight: 600; color: #374151; font-size: 0.88rem; }
.modern-toggle-desc { font-size: 0.78rem; color: #9ca3af; }

/* Form Actions */
.modern-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem 2rem;
    background: #fafbfc;
    border: 1px solid #f0f0f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
}

/* Modern Button */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-ghost {
    background: transparent;
    color: #6b7280;
    padding: 0.65rem 1rem;
}

.btn-modern-ghost:hover {
    color: #1a1a2e;
    background: #f3f4f6;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-page-title { font-size: 1.35rem; }
    .modern-stats-row { grid-template-columns: 1fr; }
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-span-2 { grid-column: span 1; }
    .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; }
    .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; }
    .modern-form-actions { padding: 1rem 1.25rem; flex-direction: column; }
    .btn-modern { justify-content: center; width: 100%; }
    .logo-upload-area { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush

@push('scripts')
<script>
// Fix checkbox: when checkbox is checked, remove the disabled hidden input so value "1" is sent
document.querySelectorAll('.modern-toggle input[type="checkbox"]').forEach(cb => {
    cb.addEventListener('change', function() {
        const hidden = this.parentElement.querySelector('.toggle-hidden');
        if (hidden) {
            hidden.disabled = this.checked;
        }
    });
    // Initialize on load
    const hidden = cb.parentElement.querySelector('.toggle-hidden');
    if (hidden) {
        hidden.disabled = cb.checked;
    }
});

// Logo preview
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            const placeholder = document.getElementById('logoPlaceholder');
            const img = document.getElementById('logoImg');
            if (img) {
                img.src = e.target.result;
            } else if (placeholder) {
                placeholder.innerHTML = '';
                const newImg = document.createElement('img');
                newImg.id = 'logoImg';
                newImg.src = e.target.result;
                newImg.alt = 'School Logo';
                placeholder.parentElement.appendChild(newImg);
                placeholder.remove();
            }
        };
        reader.readAsDataURL(file);
        // Auto-submit logo form
        document.getElementById('logoForm').submit();
    }
}
</script>
@endpush
@endsection