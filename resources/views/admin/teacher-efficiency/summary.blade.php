@extends('layouts.admin')
@section('title', 'Teacher Efficiency Summary')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.teacher-efficiency.index') }}">Teacher Efficiency</a></li>
                    <li class="active">Summary</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teacher-efficiency.index') }}" class="btn-modern btn-modern-outline"><i class="fas fa-arrow-left"></i><span>Back to List</span></a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.5rem;">
        <form method="GET" action="{{ route('admin.teacher-efficiency.summary') }}" style="padding:1rem 1.5rem;display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;">
            <select name="academic_year_id" class="modern-filter-select">
                <option value="">All Academic Years</option>
                @foreach($academicYears as $ay)
                <option value="{{ $ay->id }}" {{ request('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                @endforeach
            </select>
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
            <button type="submit" class="btn-modern btn-modern-primary" style="padding:.5rem 1rem;font-size:.85rem;"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.teacher-efficiency.summary') }}" class="btn-modern btn-modern-ghost" style="padding:.5rem .75rem;font-size:.85rem;"><i class="fas fa-times"></i> Clear</a>
        </form>
    </div>

    {{-- Stats Overview --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue"><i class="fas fa-users"></i></div>
            <div class="modern-stat-info"><span class="modern-stat-value">{{ $teacherRanking->count() }}</span><span class="modern-stat-label">Teachers Assessed</span></div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green"><i class="fas fa-clipboard-list"></i></div>
            <div class="modern-stat-info"><span class="modern-stat-value">{{ $assessments->count() }}</span><span class="modern-stat-label">Total Assessments</span></div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold"><i class="fas fa-star"></i></div>
            <div class="modern-stat-info"><span class="modern-stat-value">{{ $assessments->count() > 0 ? round($assessments->avg('overall_score'), 1) : 0 }}</span><span class="modern-stat-label">Avg Score</span></div>
        </div>
    </div>

    {{-- Grade Distribution --}}
    <div class="modern-card" style="margin-bottom:1.5rem;">
        <div class="modern-card-header"><h2 class="modern-card-title">Grade Distribution</h2></div>
        <div style="padding:1.25rem 1.5rem;">
            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                @foreach(['excellent' => ['color'=>'#059669','bg'=>'#ecfdf5','icon'=>'fa-trophy'], 'good' => ['color'=>'#2563eb','bg'=>'#eff6ff','icon'=>'fa-thumbs-up'], 'satisfactory' => ['color'=>'#6b7280','bg'=>'#f3f4f6','icon'=>'fa-check-circle'], 'needs_improvement' => ['color'=>'#d97706','bg'=>'#fffbeb','icon'=>'fa-exclamation-circle'], 'unsatisfactory' => ['color'=>'#dc2626','bg'=>'#fef2f2','icon'=>'fa-times-circle']] as $grade => $style)
                @php $count = $gradeDistribution[$grade] ?? 0; $pct = $assessments->count() > 0 ? round(($count / $assessments->count()) * 100) : 0; @endphp
                <div style="flex:1;min-width:140px;padding:1rem;border-radius:12px;background:{{ $style['bg'] }};text-align:center;">
                    <i class="fas {{ $style['icon'] }}" style="font-size:1.5rem;color:{{ $style['color'] }};margin-bottom:.5rem;"></i>
                    <div style="font-size:1.75rem;font-weight:800;color:{{ $style['color'] }};">{{ $count }}</div>
                    <div style="font-size:.8rem;font-weight:600;color:{{ $style['color'] }};text-transform:capitalize;">{{ str_replace('_', ' ', $grade) }}</div>
                    <div style="font-size:.75rem;color:#9ca3af;margin-top:.25rem;">{{ $pct }}%</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        {{-- Teacher Ranking --}}
        <div class="modern-card">
            <div class="modern-card-header"><h2 class="modern-card-title">Teacher Ranking</h2></div>
            <div class="modern-card-body">
                @if($teacherRanking->count() > 0)
                <div class="modern-table-wrapper" style="max-height:480px;overflow-y:auto;">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Teacher</th>
                                <th class="th-center">Avg Score</th>
                                <th class="th-center">Grade</th>
                                <th class="th-center">Assessments</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teacherRanking as $rank => $tr)
                            <tr>
                                <td>
                                    @if($rank < 3)
                                    <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:{{ $rank === 0 ? '#fefce8' : ($rank === 1 ? '#f5f5f4' : '#fff7ed') }};color:{{ $rank === 0 ? '#d97706' : ($rank === 1 ? '#78716c' : '#ea580c') }};font-size:.75rem;font-weight:700;">{{ $rank + 1 }}</span>
                                    @else
                                    {{ $rank + 1 }}
                                    @endif
                                </td>
                                <td><div class="modern-cell-title">{{ $tr['teacher_name'] }}</div></td>
                                <td class="td-center"><span class="modern-cell-marks">{{ $tr['avg_score'] }}</span></td>
                                <td class="td-center">
                                    @php
                                        $gradeBadge = match($tr['latest_grade']) {
                                            'Excellent' => 'modern-badge-success',
                                            'Good' => 'modern-badge-info',
                                            'Satisfactory' => 'modern-badge-light',
                                            'Needs Improvement' => 'modern-badge-warning',
                                            'Unsatisfactory' => 'modern-badge-danger',
                                            default => 'modern-badge-light',
                                        };
                                    @endphp
                                    <span class="modern-badge {{ $gradeBadge }}">{{ $tr['latest_grade'] }}</span>
                                </td>
                                <td class="td-center">{{ $tr['assessment_count'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="modern-empty-state" style="padding:2rem;">
                    <div class="modern-empty-icon" style="width:60px;height:60px;font-size:1.5rem;"><i class="fas fa-chart-bar"></i></div>
                    <h3>No Data</h3>
                    <p>No assessments found for the selected filters.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Per-Criteria Average --}}
        <div class="modern-card">
            <div class="modern-card-header"><h2 class="modern-card-title">Criteria Averages</h2></div>
            <div style="padding:1.25rem 1.5rem;">
                @if($assessments->count() > 0)
                @foreach($criteriaAverages as $field => $data)
                <div style="display:flex;align-items:center;gap:1rem;padding:.55rem 0;border-bottom:1px solid #f3f4f6;">
                    <span style="flex:1;font-size:.82rem;font-weight:500;color:#374151;">{{ $data['label'] }}</span>
                    <div style="width:100px;height:6px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                        <div style="width:{{ ($data['average'] / 5) * 100 }}%;height:100%;background:{{ $data['average'] >= 4 ? '#10b981' : ($data['average'] >= 3 ? '#d97706' : '#dc2626') }};border-radius:3px;"></div>
                    </div>
                    <span style="font-weight:700;font-size:.85rem;color:#1a1a2e;min-width:50px;text-align:right;">{{ round($data['average'], 2) }}/5</span>
                </div>
                @endforeach
                @else
                <div class="modern-empty-state" style="padding:2rem;">
                    <div class="modern-empty-icon" style="width:60px;height:60px;font-size:1.5rem;"><i class="fas fa-chart-bar"></i></div>
                    <h3>No Data</h3>
                    <p>No assessments found for the selected filters.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page{animation:fadeSlideIn .4s ease-out}@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem}.modern-page-header-left{flex:1}.modern-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}.modern-breadcrumb li{color:#adb5bd}.modern-breadcrumb li a{color:#6c757d;text-decoration:none}.modern-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.modern-breadcrumb li.active{color:#4361ee;font-weight:500}.modern-stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.75rem}.modern-stat-card{background:#fff;border-radius:14px;padding:1.25rem;display:flex;align-items:center;gap:1rem;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0}.modern-stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}.modern-stat-icon-blue{background:#eef2ff;color:#4361ee}.modern-stat-icon-green{background:#ecfdf5;color:#10b981}.modern-stat-icon-gold{background:#fefce8;color:#d97706}.modern-stat-info{display:flex;flex-direction:column}.modern-stat-value{font-size:1.5rem;font-weight:800;color:#1a1a2e;line-height:1.2}.modern-stat-label{font-size:.8rem;color:#6c757d;font-weight:500}.modern-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden}.modern-card-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #f0f0f0}.modern-card-title{font-size:1.1rem;font-weight:700;color:#1a1a2e;margin:0}.modern-card-body{padding:0}.modern-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:50px;font-size:.75rem;font-weight:600}.modern-badge-light{background:#f3f4f6;color:#6b7280}.modern-badge-success{background:#ecfdf5;color:#059669}.modern-badge-danger{background:#fef2f2;color:#dc2626}.modern-badge-warning{background:#fffbeb;color:#d97706}.modern-badge-info{background:#eff6ff;color:#2563eb}.modern-filter-select{border:1.5px solid #e5e7eb;border-radius:10px;padding:.5rem .75rem;font-size:.85rem;background:#f9fafb;color:#374151;appearance:none;cursor:pointer;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .5rem center;background-repeat:no-repeat;background-size:1.1rem;padding-right:2rem}.modern-table-wrapper{overflow-x:auto}.modern-table{width:100%;border-collapse:collapse;font-size:.9rem}.modern-table thead th{background:#f9fafb;padding:.85rem 1rem;text-align:left;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:2px solid #e5e7eb}.modern-table tbody tr{border-bottom:1px solid #f3f4f6}.modern-table tbody tr:hover{background:#f8f9ff}.modern-table td{padding:.9rem 1rem;vertical-align:middle}.th-center,.td-center{text-align:center!important}.modern-cell-title{font-weight:600;color:#1a1a2e}.modern-cell-marks{font-weight:700;color:#4361ee;font-size:.95rem}.modern-empty-state{text-align:center;padding:2rem}.modern-empty-icon{width:60px;height:60px;border-radius:50%;background:#f3f4f6;display:inline-flex;align-items:center;justify-content:center;font-size:1.5rem;color:#d1d5db;margin-bottom:.75rem}.modern-empty-state h3{font-size:1.1rem;font-weight:700;color:#1a1a2e;margin:0 0 .35rem}.modern-empty-state p{color:#9ca3af;margin:0;font-size:.88rem}.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.35rem;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;border:none;cursor:pointer;transition:all .25s}.btn-modern-primary{background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;box-shadow:0 2px 8px rgba(67,97,238,.3)}.btn-modern-primary:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}.btn-modern-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb}.btn-modern-outline:hover{border-color:#4361ee;color:#4361ee}.btn-modern-ghost{background:transparent;color:#6b7280;padding:.65rem 1rem}.btn-modern-ghost:hover{color:#1a1a2e;background:#f3f4f6}@media(max-width:768px){div[style*="grid-template-columns:1fr 1fr"]{grid-template-columns:1fr!important}.modern-form-grid{grid-template-columns:1fr}}
</style>
@endpush
@endsection
