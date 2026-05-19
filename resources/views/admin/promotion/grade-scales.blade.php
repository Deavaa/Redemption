@extends('layouts.admin')
@section('title', 'Grade Scales')

@push('styles')
<style>
.stu-page { animation: stuFadeIn 0.4s ease-out; }
@keyframes stuFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.stu-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin: 0; }
.stu-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.stu-breadcrumb li a { color: var(--text-muted); text-decoration: none; }
.stu-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.stu-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.stu-form-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; }
.stu-form-group { display: flex; flex-direction: column; }
.stu-form-label { font-weight: 600; color: #374151; margin-bottom: 0.3rem; font-size: 0.82rem; }
.stu-form-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.5rem 0.7rem; font-size: 0.85rem; color: #1a1a2e; transition: all 0.2s; }
.stu-form-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
@media (max-width: 768px) { .stu-form-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
@endpush

@section('content')
<div class="stu-page">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.25rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <nav class="stu-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.promotion.index') }}">Promotion</a></li>
                <li class="active">Grade Scales</li>
            </ol></nav>
            <h1 class="stu-title">Grade Scales</h1>
        </div>
        <a href="{{ route('admin.promotion.settings.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.82rem;padding:0.45rem 1rem;">
            <i class="fas fa-arrow-left"></i> Back to Settings
        </a>
    </div>

    {{-- Existing Grade Scales --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#eef2ff;color:#4361ee;display:flex;align-items:center;justify-content:center;"><i class="fas fa-layer-group"></i></div>
                <h3 class="modern-card-title">Grade Scale Table</h3>
            </div>
        </div>
        <div class="modern-card-body" style="padding:0;overflow-x:auto;">
            @if($scales->count() > 0)
            <table class="promo-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Min Score</th>
                        <th>Max Score</th>
                        <th>Grade</th>
                        <th>Grade Point</th>
                        <th>Description</th>
                        <th>Passing</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scales as $i => $scale)
                    <tr>
                        <td style="font-weight:600;color:var(--text-muted);">{{ $i + 1 }}</td>
                        <td>{{ $scale->min_score }}</td>
                        <td>{{ $scale->max_score }}</td>
                        <td><span style="font-weight:700;">{{ $scale->grade }}</span></td>
                        <td>{{ $scale->grade_point }}</td>
                        <td>{{ $scale->description }}</td>
                        <td>
                            @if($scale->is_passing)
                            <span class="modern-badge modern-badge-success"><i class="fas fa-check"></i> Pass</span>
                            @else
                            <span class="modern-badge modern-badge-danger"><i class="fas fa-times"></i> Fail</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="text-align:center;padding:2rem;">
                <p style="color:var(--text-muted);">No grade scales defined yet. Add scales below or seed defaults.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Add New Grade Scale --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;color:#10b981;display:flex;align-items:center;justify-content:center;"><i class="fas fa-plus"></i></div>
                <h3 class="modern-card-title">Add Grade Scale</h3>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
            <form method="POST" action="{{ route('admin.promotion.grade-scales.store') }}">
                @csrf
                <div class="stu-form-grid">
                    <div class="stu-form-group">
                        <label class="stu-form-label">Min Score <span style="color:#ef4444;">*</span></label>
                        <input type="number" name="min_score" class="stu-form-input" step="0.01" min="0" max="100" required>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Max Score <span style="color:#ef4444;">*</span></label>
                        <input type="number" name="max_score" class="stu-form-input" step="0.01" min="0" max="100" required>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Grade <span style="color:#ef4444;">*</span></label>
                        <input type="text" name="grade" class="stu-form-input" placeholder="e.g., A+" required>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Grade Point</label>
                        <input type="number" name="grade_point" class="stu-form-input" step="0.01" min="0" max="4" value="0">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Description</label>
                        <input type="text" name="description" class="stu-form-input" placeholder="e.g., Outstanding">
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Is Passing</label>
                        <select name="is_passing" class="stu-form-input">
                            <option value="1">Yes - Passing</option>
                            <option value="0">No - Failing</option>
                        </select>
                    </div>
                    <div class="stu-form-group">
                        <label class="stu-form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="stu-form-input" min="0" value="{{ $scales->count() + 1 }}">
                    </div>
                    <div class="stu-form-group" style="justify-content:flex-end;">
                        <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.82rem;padding:0.5rem 1rem;">
                            <i class="fas fa-plus"></i> Add Scale
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
