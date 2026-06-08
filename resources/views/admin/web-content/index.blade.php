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
                @elseif($groupKey === 'programs')
                    <i class="fas fa-graduation-cap" style="color:var(--primary);font-size:16px;"></i>
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
                // Show preview
                const container = this.parentElement;
                let img = container.querySelector('img');
                if (!img) {
                    img = document.createElement('img');
                    img.style.cssText = 'width:40px;height:40px;object-fit:contain;border:1px solid var(--border);border-radius:4px;';
                    container.prepend(img);
                }
                img.src = data.url;
            }
        })
        .catch(err => alert('Upload failed: ' + err.message));
    });
});
</script>
@endpush
@endsection
