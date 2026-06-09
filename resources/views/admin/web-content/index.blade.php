@extends('layouts.admin')
@section('title', __('app.web_content') ?? 'Web Content Management')

@section('content')
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

    <form method="POST" action="{{ route('admin.web-content.update') }}">
        @csrf
        @method('PUT')

        @foreach($groupLabels as $groupKey => $groupLabel)
        @if($groupKey === 'programs')
            {{-- Special Programs Section with Add/Remove/Toggle --}}
            <div class="modern-card" style="margin-bottom:16px;">
                <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <i class="fas fa-graduation-cap" style="color:var(--primary);font-size:16px;"></i>
                        <h3 style="font-size:15px;font-weight:700;color:var(--text-dark);margin:0;">{{ $groupLabel }}</h3>
                        <span style="background:var(--primary);color:#fff;font-size:11px;padding:2px 8px;border-radius:10px;font-weight:600;">{{ $programsCount }} programs</span>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <button type="button" onclick="addProgram()" style="background:var(--primary);color:#fff;border:none;border-radius:6px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:4px;">
                            <i class="fas fa-plus"></i> Add Program
                        </button>
                        <button type="button" onclick="removeProgram()" style="background:#ef4444;color:#fff;border:none;border-radius:6px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:4px;" @if($programsCount <= 1) disabled title="Must have at least 1 program" @endif>
                            <i class="fas fa-trash"></i> Remove Last
                        </button>
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

                    {{-- Program cards --}}
                    @for ($i = 1; $i <= $programsCount; $i++)
                        @php
                            $isVisible = ($groups['programs']->where('key', "program_{$i}_visible")->first()?->value ?? '1') === '1';
                        @endphp
                        <div style="border:1px solid var(--border);border-radius:10px;margin-bottom:12px;overflow:hidden;{{ $isVisible ? '' : 'opacity:0.5;' }}">
                            <div style="background:var(--bg-secondary);padding:10px 16px;display:flex;align-items:center;justify-content:space-between;">
                                <span style="font-size:13px;font-weight:700;color:var(--text-dark);">
                                    <i class="fas fa-graduation-cap" style="color:var(--primary);margin-right:6px;"></i>
                                    Program {{ $i }}
                                </span>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <label style="font-size:11px;font-weight:600;color:var(--text-light);display:flex;align-items:center;gap:6px;margin:0;cursor:pointer;">
                                        <input type="checkbox" name="setting_program_{{ $i }}_visible" value="1" {{ $isVisible ? 'checked' : '' }}
                                            style="width:16px;height:16px;cursor:pointer;">
                                        Visible
                                    </label>
                                    @if(!$isVisible)
                                        <input type="hidden" name="setting_program_{{ $i }}_visible" value="0">
                                    @endif
                                </div>
                            </div>
                            <div style="padding:14px 16px;display:grid;grid-template-columns:auto 1fr;gap:12px;">
                                {{-- Image upload --}}
                                <div>
                                    @php $imgSetting = $groups['programs']->where('key', "program_{$i}_image")->first(); @endphp
                                    <label style="font-size:11px;font-weight:600;color:var(--text-light);display:block;margin-bottom:4px;">Image</label>
                                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
                                        @if($imgSetting && $imgSetting->value)
                                            <img src="{{ \App\Models\Setting::getFileUrl("program_{$i}_image") }}" alt="Program {{ $i }}" style="width:80px;height:60px;object-fit:cover;border:1px solid var(--border);border-radius:6px;">
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
                                            style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:6px 8px;font-size:12px;">
                                    </div>
                                    @php $titleSetting = $groups['programs']->where('key', "program_{$i}_title")->first(); @endphp
                                    <div>
                                        <label style="font-size:11px;font-weight:600;color:var(--text-light);display:block;margin-bottom:4px;">Title</label>
                                        <input type="text" name="setting_program_{{ $i }}_title" value="{{ old("setting_program_{$i}_title", $titleSetting->value ?? '') }}"
                                            style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:6px 8px;font-size:12px;">
                                    </div>
                                    @php $descSetting = $groups['programs']->where('key', "program_{$i}_description")->first(); @endphp
                                    <div style="grid-column:1/-1;">
                                        <label style="font-size:11px;font-weight:600;color:var(--text-light);display:block;margin-bottom:4px;">Description</label>
                                        <textarea name="setting_program_{{ $i }}_description" rows="2"
                                            style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:6px 8px;font-size:12px;resize:vertical;">{{ old("setting_program_{$i}_description", $descSetting->value ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
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

// Checkbox toggle: when unchecked, add hidden 0 value
document.querySelectorAll('input[type="checkbox"][name^="setting_program_"]').forEach(cb => {
    cb.addEventListener('change', function() {
        // Remove existing hidden sibling if any
        const existing = this.parentElement.querySelector('input[type="hidden"][name="' + this.name + '"]');
        if (this.checked) {
            if (existing) existing.remove();
        } else {
            if (!existing) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = this.name;
                hidden.value = '0';
                this.parentElement.appendChild(hidden);
            }
        }
        // Visual feedback on parent card
        const card = this.closest('div[style*="border:"]');
        if (card) card.style.opacity = this.checked ? '1' : '0.5';
    });
});

function addProgram() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.web-content.add-program") }}';
    form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
    document.body.appendChild(form);
    form.submit();
}

function removeProgram() {
    if (!confirm('Remove the last program? This cannot be undone.')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.web-content.remove-program") }}';
    form.innerHTML = '<input type="hidden" name="_token" value="' + document.querySelector('meta[name="csrf-token"]').content + '">';
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection
