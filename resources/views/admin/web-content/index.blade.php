@extends('layouts.admin')
@section('title', __('app.web_content') ?? 'Web Content Management')

@section('content')
<style>
/* Toggle Switch Styles */
.program-toggle-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}
.program-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.program-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1;
    border-radius: 24px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.program-toggle-slider:before {
    content: "";
    position: absolute;
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.program-toggle-switch input:checked + .program-toggle-slider {
    background-color: var(--primary, #059669);
}
.program-toggle-switch input:checked + .program-toggle-slider:before {
    transform: translateX(20px);
}
.program-toggle-switch input:focus + .program-toggle-slider {
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
}

/* Program Card */
.program-manage-card {
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 12px;
    margin-bottom: 14px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: #fff;
}
.program-manage-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.program-manage-card.is-hidden {
    opacity: 0.55;
    border-style: dashed;
}
.program-manage-card.is-hidden .program-card-header {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.program-card-header {
    padding: 12px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
    border-bottom: 1px solid var(--border, #e2e8f0);
}
.program-card-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.program-card-number {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: var(--primary, #059669);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}
.program-card-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark, #1e293b);
}
.program-card-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}
.program-toggle-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: color 0.2s;
}
.program-toggle-label.is-on {
    color: var(--primary, #059669);
}
.program-toggle-label.is-off {
    color: #94a3b8;
}

.program-remove-btn {
    background: none;
    border: 1px solid #fecaca;
    color: #ef4444;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
}
.program-remove-btn:hover {
    background: #fef2f2;
    border-color: #fca5a5;
}
.program-remove-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.program-card-body {
    padding: 16px 18px;
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 14px;
}

/* Add Program Button */
.program-add-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px;
    border: 2px dashed var(--border, #e2e8f0);
    border-radius: 12px;
    background: transparent;
    color: var(--primary, #059669);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 4px;
}
.program-add-btn:hover {
    border-color: var(--primary, #059669);
    background: #f0fdf4;
}
</style>

<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.settings.index') }}">{{ __('app.settings') }}</a></li>
                    <li class="active">{{ __('app.web_content') ?? 'Web Content' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px 18px;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px 18px;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.web-content.update') }}">
        @csrf
        @method('PUT')

        @foreach($groupLabels as $groupKey => $groupLabel)
        @if($groupKey === 'programs')
            {{-- Enhanced Programs Section with Toggle, Add, Remove --}}
            <div class="modern-card" style="margin-bottom:16px;">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#059669,#10b981);display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-graduation-cap" style="color:#fff;font-size:16px;"></i>
                        </div>
                        <div>
                            <h3 style="font-size:15px;font-weight:700;color:var(--text-dark);margin:0;">{{ $groupLabel }}</h3>
                            <span style="font-size:11px;color:var(--text-light);">Manage programs displayed on the homepage</span>
                        </div>
                        <span style="background:var(--primary);color:#fff;font-size:11px;padding:2px 10px;border-radius:10px;font-weight:600;">{{ $programsCount }} {{ $programsCount === 1 ? 'program' : 'programs' }}</span>
                    </div>
                </div>
                <div style="padding:16px 20px;">
                    {{-- Section title & subtitle --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">
                        @foreach(($groups['programs'] ?? collect())->where('key', 'programs_section_title') as $setting)
                        <div>
                            <label style="font-size:12px;font-weight:600;color:var(--text-dark);display:block;margin-bottom:4px;">{{ $setting->description }}</label>
                            <input type="text" name="setting_{{ $setting->key }}" value="{{ old("setting_{$setting->key}", $setting->value) }}"
                                style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;">
                        </div>
                        @endforeach
                        @foreach(($groups['programs'] ?? collect())->where('key', 'programs_section_subtitle') as $setting)
                        <div>
                            <label style="font-size:12px;font-weight:600;color:var(--text-dark);display:block;margin-bottom:4px;">{{ $setting->description }}</label>
                            <textarea name="setting_{{ $setting->key }}" rows="1"
                                style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;resize:vertical;">{{ old("setting_{$setting->key}", $setting->value) }}</textarea>
                        </div>
                        @endforeach
                    </div>

                    {{-- Program cards with toggle switches and remove buttons --}}
                    @for ($i = 1; $i <= $programsCount; $i++)
                        @php
                            $isVisible = ($groups['programs']->where('key', "program_{$i}_visible")->first()?->value ?? '1') === '1';
                            $titleSetting = $groups['programs']->where('key', "program_{$i}_title")->first();
                            $programTitle = $titleSetting ? $titleSetting->value : "Program {$i}";
                        @endphp
                        <div class="program-manage-card {{ $isVisible ? '' : 'is-hidden' }}" data-program-index="{{ $i }}">
                            <div class="program-card-header">
                                <div class="program-card-header-left">
                                    <div class="program-card-number">{{ $i }}</div>
                                    <span class="program-card-title">{{ $programTitle ?: "Program {$i}" }}</span>
                                </div>
                                <div class="program-card-header-right">
                                    <span class="program-toggle-label {{ $isVisible ? 'is-on' : 'is-off' }}" id="toggleLabel_{{ $i }}">
                                        {{ $isVisible ? 'ON' : 'OFF' }}
                                    </span>
                                    <label class="program-toggle-switch">
                                        <input type="checkbox" name="setting_program_{{ $i }}_visible" value="1" {{ $isVisible ? 'checked' : '' }}
                                            data-program-index="{{ $i }}"
                                            onchange="handleToggleChange(this)">
                                        <span class="program-toggle-slider"></span>
                                    </label>
                                    @if(!$isVisible)
                                        <input type="hidden" name="setting_program_{{ $i }}_visible" value="0">
                                    @endif
                                    <button type="button" class="program-remove-btn" onclick="removeSpecificProgram({{ $i }})" {{ $programsCount <= 1 ? 'disabled title="Must have at least 1 program"' : '' }}>
                                        <i class="fas fa-trash-alt"></i> Remove
                                    </button>
                                </div>
                            </div>
                            <div class="program-card-body">
                                {{-- Image upload --}}
                                <div>
                                    @php $imgSetting = $groups['programs']->where('key', "program_{$i}_image")->first(); @endphp
                                    <label style="font-size:11px;font-weight:600;color:var(--text-light);display:block;margin-bottom:4px;">Image</label>
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
                                        @if($imgSetting && $imgSetting->value)
                                            <img src="{{ \App\Models\Setting::getFileUrl("program_{$i}_image") }}" alt="Program {{ $i }}" style="width:80px;height:60px;object-fit:cover;border:1px solid var(--border);border-radius:6px;">
                                        @else
                                            <div style="width:80px;height:60px;background:#f1f5f9;border:1px dashed var(--border);border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                                <i class="fas fa-image" style="color:#cbd5e1;font-size:18px;"></i>
                                            </div>
                                        @endif
                                        <input type="file" data-setting-key="program_{{ $i }}_image" class="web-content-file-upload" accept="image/*" style="font-size:11px;">
                                        <input type="hidden" name="setting_program_{{ $i }}_image" value="{{ $imgSetting->value ?? '' }}">
                                    </div>
                                </div>
                                {{-- Fields --}}
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                    @php $tagSetting = $groups['programs']->where('key', "program_{$i}_tag")->first(); @endphp
                                    <div>
                                        <label style="font-size:11px;font-weight:600;color:var(--text-light);display:block;margin-bottom:4px;">Tag / Label</label>
                                        <input type="text" name="setting_program_{{ $i }}_tag" value="{{ old("setting_program_{$i}_tag", $tagSetting->value ?? '') }}"
                                            style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:6px 8px;font-size:12px;" placeholder="e.g. Ages 3-5">
                                    </div>
                                    @php $titleSetting = $groups['programs']->where('key', "program_{$i}_title")->first(); @endphp
                                    <div>
                                        <label style="font-size:11px;font-weight:600;color:var(--text-light);display:block;margin-bottom:4px;">Title</label>
                                        <input type="text" name="setting_program_{{ $i }}_title" value="{{ old("setting_program_{$i}_title", $titleSetting->value ?? '') }}"
                                            style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:6px 8px;font-size:12px;" placeholder="e.g. Early Childhood Education">
                                    </div>
                                    @php $descSetting = $groups['programs']->where('key', "program_{$i}_description")->first(); @endphp
                                    <div style="grid-column:1/-1;">
                                        <label style="font-size:11px;font-weight:600;color:var(--text-light);display:block;margin-bottom:4px;">Description</label>
                                        <textarea name="setting_program_{{ $i }}_description" rows="2"
                                            style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:6px 8px;font-size:12px;resize:vertical;" placeholder="Brief description of this program...">{{ old("setting_program_{$i}_description", $descSetting->value ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor

                    {{-- Add Program Button --}}
                    <button type="button" class="program-add-btn" onclick="addProgram()">
                        <i class="fas fa-plus-circle" style="font-size:18px;"></i> Add New Program
                    </button>
                </div>
            </div>
        @else
        <div class="modern-card" style="margin-bottom:16px;">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;">
                @if($groupKey === 'general')
                    <i class="fas fa-school" style="color:var(--primary);font-size:16px;"></i>
                @elseif($groupKey === 'contact')
                    <i class="fas fa-phone" style="color:var(--primary);font-size:16px;"></i>
                @elseif($groupKey === 'academic')
                    <i class="fas fa-chart-bar" style="color:var(--primary);font-size:16px;"></i>
                @elseif($groupKey === 'about')
                    <i class="fas fa-info-circle" style="color:var(--primary);font-size:16px;"></i>
                @elseif($groupKey === 'website')
                    <i class="fas fa-globe" style="color:var(--primary);font-size:16px;"></i>
                @elseif($groupKey === 'social')
                    <i class="fas fa-share-alt" style="color:var(--primary);font-size:16px;"></i>
                @elseif($groupKey === 'why_choose_us')
                    <i class="fas fa-star" style="color:var(--primary);font-size:16px;"></i>
                @elseif($groupKey === 'appearance')
                    <i class="fas fa-palette" style="color:var(--primary);font-size:16px;"></i>
                @endif
                <h3 style="font-size:15px;font-weight:700;color:var(--text-dark);margin:0;">{{ $groupLabel }}</h3>
            </div>
            <div style="padding:16px 20px;">
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:14px;">
                    @foreach(($groups[$groupKey] ?? collect()) as $setting)
                        <div>
                            <label style="font-size:12px;font-weight:600;color:var(--text-dark);display:block;margin-bottom:4px;">
                                {{ $setting->description ?? $setting->key }}
                            </label>
                            @if($setting->type === 'textarea')
                                <textarea name="setting_{{ $setting->key }}" rows="3"
                                    style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;font-family:var(--font);resize:vertical;">{{ old("setting_{$setting->key}", $setting->value) }}</textarea>
                            @elseif($setting->type === 'boolean')
                                <select name="setting_{{ $setting->key }}"
                                    style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;">
                                    <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ $setting->value != '1' ? 'selected' : '' }}>No</option>
                                </select>
                            @elseif($setting->type === 'file')
                                <div style="display:flex;align-items:center;gap:8px;">
                                    @if($setting->value)
                                        <img src="{{ \App\Models\Setting::getFileUrl($setting->key) }}" alt="{{ $setting->key }}" style="width:40px;height:40px;object-fit:contain;border:1px solid var(--border);border-radius:4px;">
                                    @endif
                                    <input type="file" data-setting-key="{{ $setting->key }}" class="web-content-file-upload" accept="image/*"
                                        style="font-size:12px;">
                                    <input type="hidden" name="setting_{{ $setting->key }}" value="{{ $setting->value }}">
                                </div>
                            @else
                                <input type="text" name="setting_{{ $setting->key }}" value="{{ old("setting_{$setting->key}", $setting->value) }}"
                                    style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
        @endforeach

        <div class="modern-card">
            <div class="modern-form-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn-modern btn-modern-ghost">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-save"></i> {{ __('app.save') }}</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.querySelectorAll('.web-content-file-upload').forEach(input => {
    input.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const key = this.dataset.settingKey;
        const formData = new FormData();
        formData.append('file', file);
        formData.append('setting_key', key);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch('{{ route("admin.web-content.upload") }}', {
            method: 'POST',
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                this.parentElement.querySelector('input[type=hidden]').value = data.path;
                const container = this.parentElement;
                let img = container.querySelector('img');
                if (!img) {
                    // Replace placeholder div with actual image
                    const placeholder = container.querySelector('div');
                    if (placeholder) placeholder.remove();
                    img = document.createElement('img');
                    img.style.cssText = 'width:80px;height:60px;object-fit:cover;border:1px solid var(--border);border-radius:6px;';
                    container.prepend(img);
                }
                img.src = data.url;
            }
        })
        .catch(err => alert('Upload failed: ' + err.message));
    });
});

/**
 * Handle toggle switch change for program visibility
 */
function handleToggleChange(checkbox) {
    const index = checkbox.dataset.programIndex;
    const card = checkbox.closest('.program-manage-card');
    const label = document.getElementById('toggleLabel_' + index);

    if (checkbox.checked) {
        // Remove hidden input if it exists
        const existing = card.querySelector('input[type="hidden"][name="setting_program_' + index + '_visible"]');
        if (existing) existing.remove();
        card.classList.remove('is-hidden');
        if (label) {
            label.textContent = 'ON';
            label.classList.add('is-on');
            label.classList.remove('is-off');
        }
    } else {
        // Add hidden input with value 0
        const existing = card.querySelector('input[type="hidden"][name="setting_program_' + index + '_visible"]');
        if (!existing) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'setting_program_' + index + '_visible';
            hidden.value = '0';
            checkbox.parentElement.appendChild(hidden);
        }
        card.classList.add('is-hidden');
        if (label) {
            label.textContent = 'OFF';
            label.classList.remove('is-on');
            label.classList.add('is-off');
        }
    }
}

/**
 * Add a new program slot
 */
function addProgram() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.web-content.add-program") }}';
    form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
    document.body.appendChild(form);
    form.submit();
}

/**
 * Remove a specific program by index
 */
function removeSpecificProgram(index) {
    const card = document.querySelector('.program-manage-card[data-program-index="' + index + '"]');
    const titleEl = card ? card.querySelector('.program-card-title') : null;
    const title = titleEl ? titleEl.textContent.trim() : 'Program ' + index;

    if (!confirm('Are you sure you want to remove "' + title + '"? This will also re-order remaining programs.')) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.web-content.remove-specific-program", ["index" => "INDEX_PLACEHOLDER"]) }}'.replace('INDEX_PLACEHOLDER', index);
    form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection
