@extends('layouts.admin')
@section('title', 'Teacher Assignment Details')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li>Academics</li>
                    <li><a href="{{ route('admin.teacher-assignments.index') }}">Teacher Assignments</a></li>
                    <li class="active">Details</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teacher-assignments.index') }}" class="btn-modern btn-modern-ghost">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="modern-detail-grid">
        <div class="modern-detail-main">
            <div class="modern-detail-hero">
                <div class="modern-detail-hero-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <h2 class="modern-detail-hero-title">
                        @if($item->teacher)
                            {{ $item->teacher->name ?? 'Unknown Teacher' }}
                        @else
                            Teacher #{{ $item->teacher_id }}
                        @endif
                    </h2>
                    <div class="modern-detail-hero-badges">
                        @if($item->subject)
                            <span class="modern-badge modern-badge-info">{{ $item->subject->name ?? 'Subject' }}</span>
                        @else
                            <span class="modern-badge modern-badge-light">Subject #{{ $item->subject_id }}</span>
                        @endif
                        @if($item->academicYear)
                            <span class="modern-badge modern-badge-gold">{{ $item->academicYear->name ?? 'Year' }}</span>
                        @else
                            <span class="modern-badge modern-badge-light">Year #{{ $item->academic_year_id }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modern-detail-body">
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-user-tie"></i> Teacher
                    </div>
                    <div class="modern-detail-value">
                        @if($item->teacher)
                            <a href="#" class="modern-link">{{ $item->teacher->name ?? '-' }}</a>
                        @else
                            <span class="modern-muted">ID: {{ $item->teacher_id }}</span>
                        @endif
                    </div>
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-school"></i> Class
                    </div>
                    <div class="modern-detail-value">
                        @if($item->classRoom)
                            <span class="modern-cell-text">{{ $item->classRoom->name ?? '-' }}</span>
                        @else
                            <span class="modern-muted">ID: {{ $item->class_id }}</span>
                        @endif
                    </div>
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-layer-group"></i> Section
                    </div>
                    <div class="modern-detail-value">
                        @if($item->section)
                            <span class="modern-cell-text">{{ $item->section->name ?? '-' }}</span>
                        @else
                            <span class="modern-muted">ID: {{ $item->section_id }}</span>
                        @endif
                    </div>
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-book"></i> Subject
                    </div>
                    <div class="modern-detail-value">
                        @if($item->subject)
                            <span class="modern-cell-text">{{ $item->subject->name ?? '-' }}</span>
                        @else
                            <span class="modern-muted">ID: {{ $item->subject_id }}</span>
                        @endif
                    </div>
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label">
                        <i class="fas fa-calendar-alt"></i> Academic Year
                    </div>
                    <div class="modern-detail-value">
                        @if($item->academicYear)
                            <span class="modern-badge modern-badge-info">{{ $item->academicYear->name ?? '-' }}</span>
                        @else
                            <span class="modern-muted">ID: {{ $item->academic_year_id }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="modern-detail-sidebar">
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <h3>Quick Actions</h3>
                </div>
                <div class="modern-card-body" style="padding:1rem">
                    <div class="modern-quick-actions">
                        <a href="{{ route('admin.teacher-assignments.edit', $item->id) }}" class="modern-quick-action">
                            <i class="fas fa-pen"></i>
                            <span>Edit Assignment</span>
                        </a>
                        <a href="{{ route('admin.teacher-assignments.index') }}" class="modern-quick-action">
                            <i class="fas fa-list"></i>
                            <span>All Assignments</span>
                        </a>
                        <form method="POST" action="{{ route('admin.teacher-assignments.destroy', $item->id) }}" onsubmit="return confirm('Are you sure you want to delete this assignment?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="modern-quick-action modern-quick-action-danger">
                                <i class="fas fa-trash"></i>
                                <span>Delete Assignment</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <h3>Timestamps</h3>
                </div>
                <div class="modern-card-body" style="padding:1rem">
                    <div class="modern-timestamps">
                        <div class="modern-timestamp">
                            <span class="modern-detail-label">Created</span>
                            <span class="modern-detail-value">{{ $item->created_at ? $item->created_at->format('M d, Y h:i A') : '-' }}</span>
                        </div>
                        <div class="modern-timestamp">
                            <span class="modern-detail-label">Updated</span>
                            <span class="modern-detail-value">{{ $item->updated_at ? $item->updated_at->format('M d, Y h:i A') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.modern-page{animation:fadeSlideIn .4s ease-out;padding:1.5rem}
.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;gap:1rem}
.modern-page-header-left{flex:1}
.modern-page-header-right{display:flex;align-items:center;gap:.75rem;flex-shrink:0}
.modern-breadcrumb{margin-bottom:.5rem}
.modern-breadcrumb ol{display:flex;align-items:center;list-style:none;padding:0;margin:0;gap:.25rem;font-size:.8rem}
.modern-breadcrumb li{color:#94a3b8}
.modern-breadcrumb li:not(:last-child)::after{content:'/';margin-left:.25rem;color:#cbd5e1}
.modern-breadcrumb li a{color:#64748b;text-decoration:none;transition:color .2s}
.modern-breadcrumb li a:hover{color:#4361ee}
.modern-breadcrumb li.active{color:#4361ee;font-weight:600}
.modern-detail-grid{display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start}
.modern-detail-main{background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden}
.modern-detail-hero{display:flex;align-items:center;gap:1.25rem;padding:1.5rem;border-bottom:1px solid #f1f5f9;background:linear-gradient(135deg,#f8fafc,#fff)}
.modern-detail-hero-icon{width:56px;height:56px;border-radius:14px;background:rgba(139,92,246,.1);color:#8b5cf6;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0}
.modern-detail-hero-title{font-size:1.35rem;font-weight:700;color:#1e293b;margin:0}
.modern-detail-hero-badges{display:flex;gap:.5rem;margin-top:.5rem;flex-wrap:wrap}
.modern-detail-body{padding:1.5rem}
.modern-detail-row{display:flex;justify-content:space-between;align-items:flex-start;padding:.875rem 0;border-bottom:1px solid #f8fafc;gap:1rem}
.modern-detail-row:last-child{border-bottom:none}
.modern-detail-label{font-size:.85rem;font-weight:500;color:#64748b;display:flex;align-items:center;gap:.5rem;flex-shrink:0;min-width:140px}
.modern-detail-label i{width:16px;text-align:center;color:#94a3b8}
.modern-detail-value{font-size:.9rem;color:#1e293b;text-align:right;word-break:break-word}
.modern-link{color:#4361ee;text-decoration:none;font-weight:500;transition:color .2s}
.modern-link:hover{color:#3a0ca3;text-decoration:underline}
.modern-muted{color:#94a3b8;font-style:italic}
.modern-cell-text{color:#334155}
.modern-badge{display:inline-block;padding:.25rem .75rem;border-radius:9999px;font-size:.75rem;font-weight:500;line-height:1.4}
.modern-badge-light{background:#f1f5f9;color:#64748b}
.modern-badge-success{background:#ecfdf5;color:#059669}
.modern-badge-danger{background:#fef2f2;color:#dc2626}
.modern-badge-gold{background:#fffbeb;color:#b45309}
.modern-badge-warning{background:#fffbeb;color:#d97706}
.modern-badge-info{background:#eff6ff;color:#2563eb}
.modern-detail-sidebar{display:flex;flex-direction:column;gap:1rem}
.modern-card{background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden}
.modern-card-header-simple{padding:1rem 1.25rem;border-bottom:1px solid #f1f5f9;font-size:.95rem;font-weight:600;color:#1e293b}
.modern-card-header-simple h3{margin:0;font-size:.95rem;font-weight:600}
.modern-card-body{padding:0}
.modern-quick-actions{display:flex;flex-direction:column;gap:.5rem}
.modern-quick-action{display:flex;align-items:center;gap:.75rem;padding:.65rem .875rem;border-radius:8px;font-size:.875rem;color:#475569;text-decoration:none;transition:all .2s;cursor:pointer;border:none;background:none;width:100%;text-align:left}
.modern-quick-action:hover{background:#f1f5f9;color:#1e293b}
.modern-quick-action i{width:18px;text-align:center;font-size:.85rem;color:#4361ee}
.modern-quick-action-danger{color:#ef4444}
.modern-quick-action-danger i{color:#ef4444}
.modern-quick-action-danger:hover{background:#fef2f2;color:#dc2626}
.modern-timestamps{display:flex;flex-direction:column;gap:.75rem}
.modern-timestamp{display:flex;flex-direction:column;gap:.125rem}
.modern-timestamp .modern-detail-label{font-size:.8rem;min-width:unset}
.modern-timestamp .modern-detail-value{font-size:.8rem;text-align:left}
.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:8px;font-size:.875rem;font-weight:500;cursor:pointer;transition:all .2s;border:none;text-decoration:none;line-height:1.4}
.btn-modern-primary{background:#4361ee;color:#fff;box-shadow:0 1px 3px rgba(67,97,238,.3)}
.btn-modern-primary:hover{background:#3a0ca3;box-shadow:0 4px 12px rgba(67,97,238,.4)}
.btn-modern-ghost{background:transparent;color:#64748b}
.btn-modern-ghost:hover{background:#f1f5f9;color:#1e293b}
@media(max-width:768px){
.modern-page{padding:1rem}
.modern-page-header{flex-direction:column;gap:.75rem}
.modern-page-header-right{width:100%;justify-content:flex-start}
.modern-detail-grid{grid-template-columns:1fr}
.modern-detail-row{flex-direction:column;gap:.25rem}
.modern-detail-label{min-width:unset}
.modern-detail-value{text-align:left}
}
@media(max-width:480px){
.modern-detail-hero{flex-direction:column;text-align:center}
.modern-detail-hero-badges{justify-content:center}
.btn-modern{padding:.5rem 1rem;font-size:.8rem}
}
</style>
@endpush
@endsection
