@extends('layouts.admin')
@section('title', 'Teacher Efficiency Assessment')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Assessment</a></li>
                    <li class="active">Teacher Efficiency</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teacher-efficiency.summary') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-chart-bar"></i><span>Summary</span>
            </a>
            <a href="{{ route('admin.teacher-efficiency.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i><span>New Assessment</span>
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue"><i class="fas fa-clipboard-list"></i></div>
            <div class="modern-stat-info"><span class="modern-stat-value">{{ $stats['total'] }}</span><span class="modern-stat-label">Total Assessments</span></div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green"><i class="fas fa-trophy"></i></div>
            <div class="modern-stat-info"><span class="modern-stat-value">{{ $stats['excellent'] }}</span><span class="modern-stat-label">Excellent</span></div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-teal"><i class="fas fa-thumbs-up"></i></div>
            <div class="modern-stat-info"><span class="modern-stat-value">{{ $stats['good'] }}</span><span class="modern-stat-label">Good</span></div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold"><i class="fas fa-star"></i></div>
            <div class="modern-stat-info"><span class="modern-stat-value">{{ $stats['avg_score'] }}</span><span class="modern-stat-label">Avg Score</span></div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-orange"><i class="fas fa-check-circle"></i></div>
            <div class="modern-stat-info"><span class="modern-stat-value">{{ $stats['satisfactory'] }}</span><span class="modern-stat-label">Satisfactory</span></div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="modern-stat-info"><span class="modern-stat-value">{{ $stats['needs_improvement'] + $stats['unsatisfactory'] }}</span><span class="modern-stat-label">Need Improvement</span></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.5rem;">
        <form method="GET" action="{{ route('admin.teacher-efficiency.index') }}" style="padding:1rem 1.5rem;display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;">
            <select name="term_id" class="modern-filter-select">
                <option value="">All Terms</option>
                @foreach($allTerms as $t)
                <option value="{{ $t->id }}" {{ request('term_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <select name="branch_id" class="modern-filter-select">
                <option value="">All Branches</option>
                @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                @endforeach
            </select>
            <select name="teacher_id" class="modern-filter-select">
                <option value="">All Teachers</option>
                @foreach($teachers as $t)
                <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                @endforeach
            </select>
            <select name="grade" class="modern-filter-select">
                <option value="">All Grades</option>
                <option value="excellent" {{ request('grade') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                <option value="good" {{ request('grade') == 'good' ? 'selected' : '' }}>Good</option>
                <option value="satisfactory" {{ request('grade') == 'satisfactory' ? 'selected' : '' }}>Satisfactory</option>
                <option value="needs_improvement" {{ request('grade') == 'needs_improvement' ? 'selected' : '' }}>Needs Improvement</option>
                <option value="unsatisfactory" {{ request('grade') == 'unsatisfactory' ? 'selected' : '' }}>Unsatisfactory</option>
            </select>
            <select name="status" class="modern-filter-select">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="acknowledged" {{ request('status') == 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
            </select>
            <button type="submit" class="btn-modern btn-modern-primary" style="padding:.5rem 1rem;font-size:.85rem;"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.teacher-efficiency.index') }}" class="btn-modern btn-modern-ghost" style="padding:.5rem .75rem;font-size:.85rem;"><i class="fas fa-times"></i> Clear</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">Efficiency Assessments</h2>
                <span class="modern-badge modern-badge-light">{{ $assessments->total() }} records</span>
            </div>
        </div>
        <div class="modern-card-body">
            @if(session('success'))
            <div class="modern-alert modern-alert-success"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span><button type="button" class="modern-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button></div>
            @endif

            @if($errors->any())
            <div class="modern-alert modern-alert-error"><i class="fas fa-exclamation-circle"></i><span>{{ $errors->first() }}</span></div>
            @endif

            @if($assessments->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Teacher</th>
                            <th>Term</th>
                            <th class="th-center">Score</th>
                            <th class="th-center">Grade</th>
                            <th>Assessor</th>
                            <th>Date</th>
                            <th class="th-center">Status</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assessments as $i => $assessment)
                        <tr>
                            <td>{{ $assessments->firstItem() + $i }}</td>
                            <td><div class="modern-cell-title">{{ $assessment->teacher->full_name ?? '-' }}</div></td>
                            <td>{{ $assessment->term->name ?? '-' }}</td>
                            <td class="td-center"><span class="modern-cell-marks">{{ $assessment->overall_score }}</span></td>
                            <td class="td-center"><span class="modern-badge {{ $assessment->grade_badge_class }}">{{ $assessment->grade_label }}</span></td>
                            <td>{{ $assessment->assessor->name ?? '-' }}</td>
                            <td>{{ $assessment->created_at->format('M d, Y') }}</td>
                            <td class="td-center">
                                @if($assessment->status === 'draft')
                                <span class="modern-badge modern-badge-light">Draft</span>
                                @elseif($assessment->status === 'completed')
                                <span class="modern-badge modern-badge-success">Completed</span>
                                @elseif($assessment->status === 'acknowledged')
                                <span class="modern-badge modern-badge-info">Acknowledged</span>
                                @endif
                                @if($assessment->is_locked)
                                <i class="fas fa-lock text-muted" style="font-size:.7rem;margin-left:2px;" title="Locked"></i>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    <a href="{{ route('admin.teacher-efficiency.show', $assessment->id) }}" class="modern-btn-icon modern-btn-view" title="View"><i class="fas fa-eye"></i></a>
                                    @if($assessment->status === 'draft' && !$assessment->is_locked)
                                    <a href="{{ route('admin.teacher-efficiency.edit', $assessment->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit"><i class="fas fa-pen"></i></a>
                                    <form method="POST" action="{{ route('admin.teacher-efficiency.destroy', $assessment->id) }}" style="display:inline" onsubmit="return confirm('Delete this assessment?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="modern-btn-icon modern-btn-delete" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($assessments->hasPages())
            <div class="modern-pagination-wrapper">{{ $assessments->withQueryString()->links() }}</div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon"><i class="fas fa-clipboard-check"></i></div>
                <h3>No Assessments</h3>
                <p>Start by assessing a teacher's efficiency for a term.</p>
                <a href="{{ route('admin.teacher-efficiency.create') }}" class="btn-modern btn-modern-primary"><i class="fas fa-plus"></i> New Assessment</a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page{animation:fadeSlideIn .4s ease-out}@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem}.modern-page-header-left{flex:1}.modern-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}.modern-breadcrumb li{color:#adb5bd}.modern-breadcrumb li a{color:#6c757d;text-decoration:none}.modern-breadcrumb li a:hover{color:#4361ee}.modern-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.modern-breadcrumb li.active{color:#4361ee;font-weight:500}.modern-stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.75rem}.modern-stat-card{background:#fff;border-radius:14px;padding:1.25rem;display:flex;align-items:center;gap:1rem;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0}.modern-stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}.modern-stat-icon-blue{background:#eef2ff;color:#4361ee}.modern-stat-icon-green{background:#ecfdf5;color:#10b981}.modern-stat-icon-gold{background:#fefce8;color:#d97706}.modern-stat-icon-red{background:#fef2f2;color:#dc2626}.modern-stat-icon-teal{background:#f0fdfa;color:#14b8a6}.modern-stat-icon-orange{background:#fff7ed;color:#ea580c}.modern-stat-info{display:flex;flex-direction:column}.modern-stat-value{font-size:1.5rem;font-weight:800;color:#1a1a2e;line-height:1.2}.modern-stat-label{font-size:.8rem;color:#6c757d;font-weight:500}.modern-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.5rem}.modern-card-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #f0f0f0}.modern-card-header-left{display:flex;align-items:center;gap:.75rem}.modern-card-title{font-size:1.1rem;font-weight:700;color:#1a1a2e;margin:0}.modern-card-body{padding:0}.modern-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:50px;font-size:.75rem;font-weight:600}.modern-badge-light{background:#f3f4f6;color:#6b7280}.modern-badge-success{background:#ecfdf5;color:#059669}.modern-badge-danger{background:#fef2f2;color:#dc2626}.modern-badge-warning{background:#fffbeb;color:#d97706}.modern-badge-info{background:#eff6ff;color:#2563eb}.modern-filter-select{border:1.5px solid #e5e7eb;border-radius:10px;padding:.5rem .75rem;font-size:.85rem;background:#f9fafb;color:#374151;appearance:none;cursor:pointer;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .5rem center;background-repeat:no-repeat;background-size:1.1rem;padding-right:2rem}.modern-table-wrapper{overflow-x:auto}.modern-table{width:100%;border-collapse:collapse;font-size:.9rem}.modern-table thead th{background:#f9fafb;padding:.85rem 1rem;text-align:left;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:2px solid #e5e7eb}.modern-table tbody tr{border-bottom:1px solid #f3f4f6}.modern-table tbody tr:hover{background:#f8f9ff}.modern-table td{padding:.9rem 1rem;vertical-align:middle}.th-center,.td-center{text-align:center!important}.th-actions,.td-actions{text-align:right!important}.modern-cell-title{font-weight:600;color:#1a1a2e}.modern-cell-marks{font-weight:700;color:#4361ee;font-size:.95rem}.modern-action-group{display:inline-flex;gap:.35rem}.modern-btn-icon{width:34px;height:34px;border-radius:9px;border:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;font-size:.82rem;text-decoration:none}.modern-btn-view{background:#eef2ff;color:#4361ee}.modern-btn-view:hover{background:#4361ee;color:#fff}.modern-btn-edit{background:#fefce8;color:#d97706}.modern-btn-edit:hover{background:#d97706;color:#fff}.modern-btn-delete{background:#fef2f2;color:#dc2626}.modern-btn-delete:hover{background:#dc2626;color:#fff}.modern-alert{display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;margin:1rem 1.5rem;border-radius:10px;font-size:.88rem;font-weight:500}.modern-alert-success{background:#ecfdf5;color:#059669;border:1px solid #a7f3d0}.modern-alert-error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}.modern-alert-close{margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;opacity:.6}.modern-alert-close:hover{opacity:1}.modern-empty-state{text-align:center;padding:4rem 2rem}.modern-empty-icon{width:80px;height:80px;border-radius:50%;background:#f3f4f6;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;color:#d1d5db;margin-bottom:1.25rem}.modern-empty-state h3{font-size:1.2rem;font-weight:700;color:#1a1a2e;margin:0 0 .5rem}.modern-empty-state p{color:#9ca3af;margin:0 0 1.5rem}.modern-pagination-wrapper{padding:1rem 1.5rem;border-top:1px solid #f0f0f0;display:flex;justify-content:center}.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.35rem;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;border:none;cursor:pointer;transition:all .25s}.btn-modern-primary{background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;box-shadow:0 2px 8px rgba(67,97,238,.3)}.btn-modern-primary:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}.btn-modern-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb}.btn-modern-outline:hover{border-color:#4361ee;color:#4361ee}.btn-modern-ghost{background:transparent;color:#6b7280;padding:.65rem 1rem}.btn-modern-ghost:hover{color:#1a1a2e;background:#f3f4f6}
</style>
@endpush
@endsection
