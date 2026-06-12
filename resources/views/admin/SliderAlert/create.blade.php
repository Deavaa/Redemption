@extends('layouts.admin')
@section('title', 'Add Slider Alert')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.slider-alerts.index') }}">Slider Alerts</a></li>
                    <li class="active">Add New</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.slider-alerts.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.slider-alerts.store') }}" id="alertForm">
            @csrf

            {{-- Alert Message --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Alert Message</h3>
                        <p class="modern-form-section-desc">Enter the alert text shown on the slider</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="message">
                                Message <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-comment modern-input-icon"></i>
                                <input type="text"
                                    name="message"
                                    id="message"
                                    class="modern-input {{ $errors->has('message') ? 'is-invalid' : '' }}"
                                    value="{{ old('message') }}"
                                    placeholder="e.g. Registration is open for 2026/2027"
                                    oninput="updatePreview()"
                                    autofocus>
                            </div>
                            @error('message')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="icon">
                                Icon <small>(FontAwesome class)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-icons modern-input-icon"></i>
                                <input type="text"
                                    name="icon"
                                    id="icon"
                                    class="modern-input {{ $errors->has('icon') ? 'is-invalid' : '' }}"
                                    value="{{ old('icon', 'fa-bullhorn') }}"
                                    placeholder="e.g. fa-bullhorn"
                                    oninput="updatePreview()">
                            </div>
                            @error('icon')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="type">
                                Alert Type <span class="modern-required">*</span>
                            </label>
                            <select name="type" id="type" class="modern-input" style="padding-left:14px;" onchange="applyTypePreset()">
                                <option value="info" {{ old('type', 'info') === 'info' ? 'selected' : '' }}>Info (Green)</option>
                                <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Success</option>
                                <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Warning (Amber)</option>
                                <option value="danger" {{ old('type') === 'danger' ? 'selected' : '' }}>Danger (Red)</option>
                                <option value="primary" {{ old('type') === 'primary' ? 'selected' : '' }}>Primary (Blue)</option>
                            </select>
                            @error('type')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Appearance --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Appearance</h3>
                        <p class="modern-form-section-desc">Customize colors and display order</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="bg_color">
                                Background Color
                            </label>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <input type="color" name="bg_color" id="bg_color"
                                    value="{{ old('bg_color', '#059669') }}"
                                    style="width:44px;height:38px;border:1.5px solid #e5e7eb;border-radius:8px;cursor:pointer;padding:2px;"
                                    oninput="updatePreview()">
                                <div class="modern-input-wrapper" style="flex:1;">
                                    <i class="fas fa-fill-drip modern-input-icon"></i>
                                    <input type="text"
                                        id="bg_color_text"
                                        class="modern-input"
                                        value="{{ old('bg_color', '#059669') }}"
                                        oninput="syncColor('bg_color', this.value)"
                                        placeholder="#059669">
                                </div>
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="text_color">
                                Text Color
                            </label>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <input type="color" name="text_color" id="text_color"
                                    value="{{ old('text_color', '#ffffff') }}"
                                    style="width:44px;height:38px;border:1.5px solid #e5e7eb;border-radius:8px;cursor:pointer;padding:2px;"
                                    oninput="updatePreview()">
                                <div class="modern-input-wrapper" style="flex:1;">
                                    <i class="fas fa-font modern-input-icon"></i>
                                    <input type="text"
                                        id="text_color_text"
                                        class="modern-input"
                                        value="{{ old('text_color', '#ffffff') }}"
                                        oninput="syncColor('text_color', this.value)"
                                        placeholder="#ffffff">
                                </div>
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="sort_order">
                                Sort Order
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-sort-numeric-down modern-input-icon"></i>
                                <input type="number"
                                    name="sort_order"
                                    id="sort_order"
                                    class="modern-input {{ $errors->has('sort_order') ? 'is-invalid' : '' }}"
                                    value="{{ old('sort_order', 0) }}"
                                    placeholder="0"
                                    min="0">
                            </div>
                            @error('sort_order')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <div class="modern-toggle-wrapper">
                                <label class="modern-toggle">
                                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                    <span class="modern-toggle-slider"></span>
                                </label>
                                <div class="modern-toggle-info">
                                    <span class="modern-toggle-title">Active Status</span>
                                    <span class="modern-toggle-desc">Show this alert on the homepage slider</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Live Preview --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-gold">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Live Preview</h3>
                        <p class="modern-form-section-desc">See how your alert will look on the slider</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div style="background:linear-gradient(135deg,#1a1a2e,#0f0f23);border-radius:12px;padding:20px;display:flex;align-items:center;gap:16px;">
                        <div style="flex:1;overflow:hidden;">
                            <span id="previewItem" style="display:inline-flex;align-items:center;gap:8px;padding:6px 18px;border-radius:50px;font-size:0.82rem;font-weight:600;background:#059669;color:#ffffff;white-space:nowrap;">
                                <i id="previewIcon" class="fas fa-bullhorn" style="font-size:0.78rem;"></i>
                                <span id="previewText">Registration is open for 2026/2027</span>
                            </span>
                        </div>
                        <div style="background:rgba(0,0,0,0.25);padding:8px 14px;border-radius:50px;">
                            <span id="previewBadge" style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;border-radius:50px;font-size:0.82rem;font-weight:700;background:#059669;color:#ffffff;white-space:nowrap;">
                                <i id="previewBadgeIcon" class="fas fa-bullhorn" style="font-size:0.78rem;"></i>
                                <span id="previewBadgeText">Registration is open for 2026/2027</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.slider-alerts.index') }}" class="btn-modern btn-modern-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-check"></i>
                    <span>Create Alert</span>
                </button>
            </div>
        </form>
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

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Form Section */
.modern-form-section { border-bottom: 1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom: none; }

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
.modern-form-section-icon-gold { background: #fff7ed; color: #f97316; }

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

.modern-required { color: #ef4444; font-weight: 700; }

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

.modern-input.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.modern-form-error {
    display: block;
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.35rem;
    font-weight: 500;
}

/* Toggle */
.modern-toggle-wrapper {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding-top: 1.75rem;
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

.modern-toggle input:checked + .modern-toggle-slider { background: #059669; }
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
    border-top: 1px solid #f0f0f0;
    background: #fafbfc;
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

.btn-modern-outline {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
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
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-span-2 { grid-column: span 1; }
    .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; }
    .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; }
    .modern-form-actions { padding: 1rem 1.25rem; flex-direction: column; }
    .btn-modern { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
const typePresets = {
    info:    { bg: '#059669', text: '#ffffff', icon: 'fa-info-circle' },
    success: { bg: '#10b981', text: '#ffffff', icon: 'fa-check-circle' },
    warning: { bg: '#f59e0b', text: '#ffffff', icon: 'fa-exclamation-triangle' },
    danger:  { bg: '#ef4444', text: '#ffffff', icon: 'fa-exclamation-circle' },
    primary: { bg: '#4361ee', text: '#ffffff', icon: 'fa-bullhorn' },
};

function applyTypePreset() {
    const type = document.getElementById('type').value;
    const preset = typePresets[type];
    if (preset) {
        document.getElementById('bg_color').value = preset.bg;
        document.getElementById('bg_color_text').value = preset.bg;
        document.getElementById('text_color').value = preset.text;
        document.getElementById('text_color_text').value = preset.text;
        document.getElementById('icon').value = preset.icon;
        updatePreview();
    }
}

function syncColor(field, value) {
    if (/^#[0-9a-fA-F]{6}$/.test(value)) {
        document.getElementById(field).value = value;
        updatePreview();
    }
}

function updatePreview() {
    const message = document.getElementById('message').value || 'Your alert message...';
    const icon = document.getElementById('icon').value || 'fa-bullhorn';
    const bgColor = document.getElementById('bg_color').value;
    const textColor = document.getElementById('text_color').value;

    // Sync color text fields
    document.getElementById('bg_color_text').value = bgColor;
    document.getElementById('text_color_text').value = textColor;

    // Update preview items
    const previewItem = document.getElementById('previewItem');
    const previewBadge = document.getElementById('previewBadge');
    const previewIcon = document.getElementById('previewIcon');
    const previewBadgeIcon = document.getElementById('previewBadgeIcon');
    const previewText = document.getElementById('previewText');
    const previewBadgeText = document.getElementById('previewBadgeText');

    previewItem.style.background = bgColor;
    previewItem.style.color = textColor;
    previewBadge.style.background = bgColor;
    previewBadge.style.color = textColor;

    previewIcon.className = 'fas ' + icon;
    previewBadgeIcon.className = 'fas ' + icon;
    previewText.textContent = message;
    previewBadgeText.textContent = message;
}

// Initialize preview on load
document.addEventListener('DOMContentLoaded', updatePreview);
</script>
@endpush
@endsection
