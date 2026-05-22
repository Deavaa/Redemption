@extends('layouts.admin')
@section('title', 'System Modules')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="#">Admin</a></li><li class="active">System Modules</li></ol>
            </nav>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="modern-info-banner">
        <i class="fas fa-cogs"></i>
        <span>Control which features and modules are active in the system. <strong>Disabled modules</strong> will be hidden from all users but their data is preserved.</span>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success" style="margin-bottom:1rem;">
        <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
        <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.system-modules.update-all') }}">
        @csrf

        @foreach($groupedModules as $groupKey => $modules)
        <div class="modern-card" style="margin-bottom:1.5rem;">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <div class="modern-form-section-icon modern-form-section-icon-blue" style="width:36px;height:36px;font-size:.9rem;">
                        <i class="fas fa-{{ $groupKey === 'academic' ? 'graduation-cap' : ($groupKey === 'assessment' ? 'clipboard-check' : ($groupKey === 'communication' ? 'comments' : ($groupKey === 'finance' ? 'wallet' : ($groupKey === 'website' ? 'globe' : ($groupKey === 'documents' ? 'file-alt' : ($groupKey === 'library' ? 'book' : 'cog')))))) }}"></i>
                    </div>
                    <h2 class="modern-card-title">{{ $groups[$groupKey] ?? ucfirst($groupKey) }}</h2>
                    <span class="modern-badge modern-badge-light">{{ $modules->count() }} modules</span>
                </div>
            </div>
            <div style="padding:0;">
                @foreach($modules as $module)
                <div class="module-row" style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid #f3f4f6;@if(!$module->is_enabled)background:#f9fafb;opacity:.7;@endif">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;background:{{ $module->is_enabled ? '#eef2ff' : '#f3f4f6' }};color:{{ $module->is_enabled ? '#4361ee' : '#9ca3af' }};">
                            <i class="fas fa-{{ $module->is_enabled ? 'check-circle' : 'times-circle' }}"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;color:#1a1a2e;font-size:.92rem;">{{ $module->name }}</div>
                            @if($module->description)
                            <div style="font-size:.8rem;color:#9ca3af;margin-top:2px;">{{ $module->description }}</div>
                            @endif
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <span class="modern-badge {{ $module->is_enabled ? 'modern-badge-success' : 'modern-badge-danger' }}">
                            {{ $module->is_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                        <label class="toggle-switch">
                            <input type="checkbox" name="modules[{{ $module->id }}][is_enabled]" value="1" {{ $module->is_enabled ? 'checked' : '' }} onchange="this.form.submit()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </form>
</div>

@push('styles')
<style>
.modern-page{animation:fadeSlideIn .4s ease-out}@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem}.modern-page-header-left{flex:1}.modern-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}.modern-breadcrumb li{color:#adb5bd}.modern-breadcrumb li a{color:#6c757d;text-decoration:none}.modern-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.modern-breadcrumb li.active{color:#4361ee;font-weight:500}.modern-info-banner{display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;margin-bottom:1.75rem;font-size:.88rem;color:#1e40af}.modern-info-banner i{color:#3b82f6}.modern-info-banner strong{color:#1e3a8a}.modern-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden}.modern-card-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #f0f0f0}.modern-card-header-left{display:flex;align-items:center;gap:.75rem}.modern-card-title{font-size:1.1rem;font-weight:700;color:#1a1a2e;margin:0}.modern-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:50px;font-size:.75rem;font-weight:600}.modern-badge-light{background:#f3f4f6;color:#6b7280}.modern-badge-success{background:#ecfdf5;color:#059669}.modern-badge-danger{background:#fef2f2;color:#dc2626}.modern-alert{display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;border-radius:10px;font-size:.88rem;font-weight:500}.modern-alert-success{background:#ecfdf5;color:#059669;border:1px solid #a7f3d0}.modern-alert-close{margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;opacity:.6}.modern-alert-close:hover{opacity:1}.modern-form-section-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}.modern-form-section-icon-blue{background:#eef2ff;color:#4361ee}.module-row:hover{background:#f8f9ff!important}.toggle-switch{position:relative;display:inline-block;width:48px;height:26px}.toggle-switch input{opacity:0;width:0;height:0}.toggle-slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#d1d5db;transition:.3s;border-radius:26px}.toggle-slider:before{position:absolute;content:"";height:20px;width:20px;left:3px;bottom:3px;background-color:#fff;transition:.3s;border-radius:50%}.toggle-switch input:checked+.toggle-slider{background-color:#4361ee}.toggle-switch input:checked+.toggle-slider:before{transform:translateX(22px)}.toggle-switch input:focus+.toggle-slider{box-shadow:0 0 0 3px rgba(67,97,238,.2)}
</style>
@endpush
@endsection
