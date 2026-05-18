@extends('layouts.admin')
@section('title', 'Promotion Settings')

@push('styles')
<style>
.stu-page { animation: stuFadeIn 0.4s ease-out; }
@keyframes stuFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.stu-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin: 0; }
.stu-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.stu-breadcrumb li a { color: var(--text-muted); text-decoration: none; }
.stu-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.stu-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.stu-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.stu-form-group { display: flex; flex-direction: column; }
.stu-form-label { font-weight: 600; color: #374151; margin-bottom: 0.4rem; font-size: 0.85rem; }
.stu-form-label .required { color: #ef4444; }
.stu-form-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.6rem 0.8rem; font-size: 0.88rem; color: #1a1a2e; transition: all 0.2s; }
.stu-form-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.stu-toggle { position: relative; display: inline-block; width: 42px; height: 22px; }
.stu-toggle input { opacity: 0; width: 0; height: 0; }
.stu-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #d1d5db; border-radius: 22px; transition: 0.3s; }
.stu-toggle-slider::before { content: ''; position: absolute; height: 16px; width: 16px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: 0.3s; }
.stu-toggle input:checked + .stu-toggle-slider { background: #4361ee; }
.stu-toggle input:checked + .stu-toggle-slider::before { transform: translateX(20px); }
@media (max-width: 768px) { .stu-form-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="stu-page">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <nav class="stu-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.promotion.index') }}">Promotion</a></li>
                <li class="active">Settings</li>
            </ol></nav>
            <h1 class="stu-title">Promotion Settings</h1>
        </div>
        <a href="{{ route('admin.promotion.grade-scales.index') }}" class="btn-modern btn-modern-outline" style="font-size:0.82rem;padding:0.45rem 1rem;">
            <i class="fas fa-layer-group"></i> Grade Scales
        </a>
    </div>

    <form method="POST" action="{{ route('admin.promotion.settings.store') }}">
        @csrf

        {{-- Academic Criteria --}}
        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="width:36px;height:36px;border-radius:10px;background:#eef2ff;color:#4361ee;display:flex;align-items:center;justify-content:center;"><i class="fas fa-chart-bar"></i></div>
                    <h3 class="modern-card-title">Academic Criteria</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">Setting Name</label>
                        <input type="text" name="name" class="stu-form-input" value="{{ old('name', $settings->name ?? 'Default Promotion Policy') }}" placeholder="e.g., Standard Policy">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Academic Year</label>
                        <select name="academic_year_id" class="stu-form-input">
                            <option value="">All Academic Years</option>
                            @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ old('academic_year_id', $settings->academic_year_id ?? '') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Minimum Average for Promotion (%) <span class="required">*</span></label>
                        <input type="number" name="minimum_average_for_promotion" class="stu-form-input" value="{{ old('minimum_average_for_promotion', $settings->minimum_average_for_promotion ?? 50) }}" min="0" max="100" step="0.1">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Maximum Subjects to Fail</label>
                        <input type="number" name="maximum_subjects_to_fail" class="stu-form-input" value="{{ old('maximum_subjects_to_fail', $settings->maximum_subjects_to_fail ?? 0) }}" min="0">
                        <small style="color:var(--text-muted);font-size:0.75rem;">0 = must pass all subjects</small>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Minimum Subject Pass Mark (%)</label>
                        <input type="number" name="minimum_subject_pass_mark" class="stu-form-input" value="{{ old('minimum_subject_pass_mark', $settings->minimum_subject_pass_mark ?? 50) }}" min="0" max="100" step="0.1">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Description</label>
                        <input type="text" name="description" class="stu-form-input" value="{{ old('description', $settings->description ?? '') }}" placeholder="Brief description of this policy">
                    </div>
                </div>
            </div>
        </div>

        {{-- Attendance & Behavior --}}
        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="width:36px;height:36px;border-radius:10px;background:#fefce8;color:#f59e0b;display:flex;align-items:center;justify-content:center;"><i class="fas fa-clipboard-check"></i></div>
                    <h3 class="modern-card-title">Attendance & Behavior</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">Consider Attendance</label>
                        <label class="stu-toggle">
                            <input type="checkbox" name="consider_attendance" value="1" {{ old('consider_attendance', $settings->consider_attendance ?? true) ? 'checked' : '' }}>
                            <span class="stu-toggle-slider"></span>
                        </label>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Minimum Attendance (%)</label>
                        <input type="number" name="minimum_attendance_percentage" class="stu-form-input" value="{{ old('minimum_attendance_percentage', $settings->minimum_attendance_percentage ?? 75) }}" min="0" max="100" step="0.1">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Consider Behavior</label>
                        <label class="stu-toggle">
                            <input type="checkbox" name="consider_behavior" value="1" {{ old('consider_behavior', $settings->consider_behavior ?? false) ? 'checked' : '' }}>
                            <span class="stu-toggle-slider"></span>
                        </label>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Consider Conduct</label>
                        <label class="stu-toggle">
                            <input type="checkbox" name="consider_conduct" value="1" {{ old('consider_conduct', $settings->consider_conduct ?? false) ? 'checked' : '' }}>
                            <span class="stu-toggle-slider"></span>
                        </label>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Minimum Conduct Score</label>
                        <input type="number" name="minimum_conduct_score" class="stu-form-input" value="{{ old('minimum_conduct_score', $settings->minimum_conduct_score ?? 3) }}" min="0" max="5" step="0.1">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Auto-promote if Pass All</label>
                        <label class="stu-toggle">
                            <input type="checkbox" name="auto_promote_if_pass_all" value="1" {{ old('auto_promote_if_pass_all', $settings->auto_promote_if_pass_all ?? true) ? 'checked' : '' }}>
                            <span class="stu-toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Conditional Promotion --}}
        <div class="modern-card" style="margin-bottom:1.25rem;">
            <div class="modern-card-header">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="width:36px;height:36px;border-radius:10px;background:#fef2f2;color:#ef4444;display:flex;align-items:center;justify-content:center;"><i class="fas fa-exclamation-triangle"></i></div>
                    <h3 class="modern-card-title">Conditional Promotion</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">Allow Conditional Promotion</label>
                        <label class="stu-toggle">
                            <input type="checkbox" name="allow_conditional_promotion" value="1" {{ old('allow_conditional_promotion', $settings->allow_conditional_promotion ?? true) ? 'checked' : '' }}>
                            <span class="stu-toggle-slider"></span>
                        </label>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Conditional Min Average (%)</label>
                        <input type="number" name="conditional_promotion_min_average" class="stu-form-input" value="{{ old('conditional_promotion_min_average', $settings->conditional_promotion_min_average ?? 40) }}" min="0" max="100" step="0.1">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Conditional Max Failures</label>
                        <input type="number" name="conditional_promotion_max_failures" class="stu-form-input" value="{{ old('conditional_promotion_max_failures', $settings->conditional_promotion_max_failures ?? 2) }}" min="0">
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
            <a href="{{ route('admin.promotion.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.85rem;padding:0.55rem 1.2rem;">Cancel</a>
            <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.85rem;padding:0.55rem 1.5rem;">
                <i class="fas fa-check"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
