@extends('layouts.admin')
@section('title', 'Student Access Management')

@push('styles')
<style>
.ua-page{animation:uaIn .4s ease-out}
@keyframes uaIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.ua-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.ua-header-left{flex:1}
.ua-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.ua-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.ua-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.ua-breadcrumb li{color:#adb5bd}
.ua-breadcrumb li a{color:#6c757d;text-decoration:none;transition:color .2s}
.ua-breadcrumb li a:hover{color:#4361ee}
.ua-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}
.ua-breadcrumb li.active{color:#4361ee;font-weight:500}

.ua-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.25rem}
.ua-card-head{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.ua-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.ua-card-icon.blue{background:#eef2ff;color:#4361ee}
.ua-card-icon.green{background:#ecfdf5;color:#10b981}
.ua-card-icon.gold{background:#fefce8;color:#d97706}
.ua-card-icon.purple{background:#f5f3ff;color:#7c3aed}
.ua-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.ua-card-body{padding:1.25rem 1.5rem}

.ua-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.55rem 1.15rem;border-radius:10px;font-weight:600;font-size:.85rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3);text-decoration:none}
.ua-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}
.ua-btn-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none}
.ua-btn-outline:hover{border-color:#4361ee;color:#4361ee;background:#f8f9ff;transform:none;box-shadow:none}
.ua-btn-sm{font-size:.78rem;padding:.4rem .85rem}
.ua-btn-success{background:linear-gradient(135deg,#10b981,#059669);box-shadow:0 2px 8px rgba(16,185,129,.3)}
.ua-btn-success:hover{box-shadow:0 4px 16px rgba(16,185,129,.4)}
.ua-btn-warning{background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 2px 8px rgba(245,158,11,.3)}
.ua-btn-warning:hover{box-shadow:0 4px 16px rgba(245,158,11,.4)}

.ua-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem}
.ua-stat{background:#fff;border-radius:12px;padding:1.15rem 1.25rem;display:flex;align-items:center;gap:1rem;border:1px solid #f0f0f0;box-shadow:0 1px 3px rgba(0,0,0,.04);transition:transform .2s}
.ua-stat:hover{transform:translateY(-2px)}
.ua-stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.ua-stat-val{font-size:1.5rem;font-weight:800;color:#1a1a2e}
.ua-stat-lbl{font-size:.78rem;color:#6c757d;font-weight:500}

.ua-table{width:100%;border-collapse:collapse;font-size:.85rem}
.ua-table th{padding:.75rem 1rem;text-align:left;font-weight:700;color:#1a1a2e;border-bottom:2px solid #e5e7eb;background:#f8fafc;white-space:nowrap}
.ua-table td{padding:.7rem 1rem;border-bottom:1px solid #f0f0f0;vertical-align:middle}
.ua-table tbody tr:hover{background:#f8f9ff}
.ua-table tbody tr:last-child td{border-bottom:none}

.ua-badge{display:inline-flex;align-items:center;gap:.35rem;font-size:.72rem;padding:.25rem .6rem;border-radius:6px;font-weight:600}
.ua-badge-success{background:#ecfdf5;color:#059669}
.ua-badge-warning{background:#fefce8;color:#d97706}
.ua-badge-info{background:#eef2ff;color:#4361ee}

.ua-avatar{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:#fff;flex-shrink:0}

.ua-filter-bar{display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;margin-bottom:1.25rem}
.ua-filter-bar select{padding:.45rem .75rem;border:1.5px solid #e5e7eb;border-radius:10px;font-size:.85rem;color:#374151;background:#fff;cursor:pointer;transition:border-color .2s}
.ua-filter-bar select:focus{border-color:#4361ee;outline:none}
</style>
@endpush

@section('content')
<div class="ua-page">
    <div class="ua-header">
        <div class="ua-header-left">
            <nav aria-label="breadcrumb" class="ua-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li>System</li><li class="active">Student Access</li></ol></nav>
            <h1 class="ua-title">Student Access Management</h1>
            <p class="ua-subtitle">Create user accounts for students and manage bulk access</p>
        </div>
        <div style="display:flex;gap:.5rem">
            <form method="POST" action="{{ route('admin.user-access.students.bulk') }}" id="bulkForm">
                @csrf
                <input type="hidden" name="class_id" id="bulkClassId" value="">
                <button type="submit" class="ua-btn ua-btn-warning" onclick="return confirm('Create accounts for all students without accounts?')"><i class="fas fa-users"></i> Bulk Create All</button>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    @php
        use App\Models\Student;
        $totalStudents = Student::count();
        $withAccount = Student::whereNotNull('user_id')->count();
        $withoutAccount = $totalStudents - $withAccount;
    @endphp
    <div class="ua-stats">
        <div class="ua-stat">
            <div class="ua-stat-icon" style="background:#eef2ff;color:#4361ee"><i class="fas fa-user-graduate"></i></div>
            <div><div class="ua-stat-val">{{ $totalStudents }}</div><div class="ua-stat-lbl">Total Students</div></div>
        </div>
        <div class="ua-stat">
            <div class="ua-stat-icon" style="background:#ecfdf5;color:#10b981"><i class="fas fa-user-check"></i></div>
            <div><div class="ua-stat-val">{{ $withAccount }}</div><div class="ua-stat-lbl">With Account</div></div>
        </div>
        <div class="ua-stat">
            <div class="ua-stat-icon" style="background:#fefce8;color:#d97706"><i class="fas fa-user-plus"></i></div>
            <div><div class="ua-stat-val">{{ $withoutAccount }}</div><div class="ua-stat-lbl">Need Account</div></div>
        </div>
        <div class="ua-stat">
            <div class="ua-stat-icon" style="background:#f5f3ff;color:#7c3aed"><i class="fas fa-percentage"></i></div>
            <div><div class="ua-stat-val">{{ $totalStudents > 0 ? round(($withAccount/$totalStudents)*100) : 0 }}%</div><div class="ua-stat-lbl">Coverage</div></div>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
    <div style="display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;margin-bottom:1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;background:#ecfdf5;color:#059669;border:1px solid #a7f3d0">
        <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div style="display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;margin-bottom:1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5">
        <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Students Table --}}
    <div class="ua-card">
        <div class="ua-card-head">
            <div style="display:flex;align-items:center;gap:.75rem">
                <div class="ua-card-icon green"><i class="fas fa-user-graduate"></i></div>
                <h3 class="ua-card-title">Students</h3>
            </div>
            @if($studentRole)
            <span class="ua-badge ua-badge-info"><i class="fas fa-shield-alt"></i> Role: {{ $studentRole->display_name }}</span>
            @endif
        </div>
        <div class="ua-card-body" style="padding:0">
            <table class="ua-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Admission No.</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Account</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    @php
                        $colors = ['#4361ee','#7c3aed','#10b981','#f59e0b','#ef4444','#06b6d4','#8b5cf6','#ec4899'];
                        $color = $colors[$student->id % count($colors)];
                        $initials = strtoupper(substr($student->full_name ?? 'S', 0, 1));
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.75rem">
                                <div class="ua-avatar" style="background:{{ $color }}">{{ $initials }}</div>
                                <div>
                                    <div style="font-weight:600;color:#1a1a2e">{{ $student->full_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="color:#6b7280;font-family:monospace;font-size:.82rem">{{ $student->admission_number ?? '—' }}</td>
                        <td style="color:#6b7280">{{ $student->classroom->name ?? '—' }}</td>
                        <td style="color:#6b7280">{{ $student->section->name ?? '—' }}</td>
                        <td>
                            @if($student->user_id)
                            <span class="ua-badge ua-badge-success"><i class="fas fa-check"></i> Linked</span>
                            @else
                            <span class="ua-badge ua-badge-warning"><i class="fas fa-times"></i> None</span>
                            @endif
                        </td>
                        <td style="text-align:right">
                            @if(!$student->user_id)
                            <form method="POST" action="{{ route('admin.user-access.students.create') }}" style="display:inline">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <button type="submit" class="ua-btn ua-btn-success ua-btn-sm"><i class="fas fa-user-plus"></i> Create Account</button>
                            </form>
                            @else
                            <span class="ua-badge ua-badge-info"><i class="fas fa-check-circle"></i> Active</span>
                            <form method="POST" action="{{ route('admin.user-access.students.reset-password') }}" style="display:inline" onsubmit="return confirm('Reset password to default (123456)?')">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $student->user->id }}">
                                <button type="submit" class="ua-btn ua-btn-warning ua-btn-sm"><i class="fas fa-redo"></i> Reset Password</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div style="margin-top:1rem">
        {{ $students->withQueryString()->links() }}
    </div>
</div>
@endsection
