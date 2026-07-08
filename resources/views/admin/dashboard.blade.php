@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@php
    $userRole = Auth::user()?->role ?? '';
    $isAdminOrGM = in_array($userRole, ['admin', 'super_admin', 'general_manager']);
    $isBranchPrincipal = $userRole === 'branch_principal';
    $isTeacher = $userRole === 'teacher';
    $userName = Auth::user()?->name ?? 'Guest';
    $firstName = explode(' ', $userName)[0];
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

<style>
/* ====================================================================
   MODERN DASHBOARD — Redemption School Management System
   Class prefix: md- (Modern Dashboard)
   ==================================================================== */

.md-wrap{padding:0;margin:0;width:100%;box-sizing:border-box;font-family:'Inter',sans-serif;}

/* ── Hero Header ── */
.md-hero{
    background:linear-gradient(135deg,#047857 0%,#0d9488 50%,#06b6d4 100%);
    border-radius:20px;padding:28px 32px;margin-bottom:24px;color:#fff;
    position:relative;overflow:hidden;
    box-shadow:0 10px 30px rgba(4,120,87,0.25);
}
.md-hero::before{
    content:'';position:absolute;top:-50%;right:-10%;width:400px;height:400px;
    background:radial-gradient(circle,rgba(255,255,255,0.12) 0%,transparent 70%);
    border-radius:50%;pointer-events:none;
}
.md-hero::after{
    content:'';position:absolute;bottom:-60%;left:30%;width:350px;height:350px;
    background:radial-gradient(circle,rgba(252,211,77,0.10) 0%,transparent 70%);
    border-radius:50%;pointer-events:none;
}
.md-hero-content{position:relative;z-index:1;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;}
.md-hero h1{margin:0;font-size:1.75rem;font-weight:800;color:#fff;letter-spacing:-0.5px;}
.md-hero h1 i{margin-right:10px;font-size:1.4rem;}
.md-hero p{margin:6px 0 0;font-size:0.85rem;color:rgba(255,255,255,0.85);font-weight:500;}
.md-hero-meta{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;}
.md-hero-chip{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:rgba(255,255,255,0.18);border:1px solid rgba(255,255,255,0.25);border-radius:50px;font-size:0.72rem;font-weight:600;color:#fff;backdrop-filter:blur(8px);}
.md-hero-chip i{font-size:0.7rem;}
.md-hero-actions{display:flex;gap:8px;flex-wrap:wrap;}
.md-btn-hero{display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:12px;font-size:0.82rem;font-weight:700;text-decoration:none;border:none;cursor:pointer;transition:all 0.25s ease;backdrop-filter:blur(8px);}
.md-btn-hero-primary{background:#fff;color:#047857;box-shadow:0 4px 14px rgba(0,0,0,0.15);}
.md-btn-hero-primary:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(0,0,0,0.20);color:#047857;text-decoration:none;}
.md-btn-hero-ghost{background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.30);}
.md-btn-hero-ghost:hover{background:rgba(255,255,255,0.28);transform:translateY(-2px);text-decoration:none;color:#fff;}

/* ── Stats Grid ── */
.md-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;}
@media(max-width:1200px){.md-stats{grid-template-columns:repeat(3,1fr);}}
@media(max-width:768px){.md-stats{grid-template-columns:repeat(2,1fr);}}
@media(max-width:480px){.md-stats{grid-template-columns:1fr;}}

.md-stat{
    position:relative;display:flex;align-items:center;gap:16px;padding:20px;
    background:#fff;border:1px solid #e5e7eb;border-radius:16px;text-decoration:none;color:inherit;
    transition:all 0.3s cubic-bezier(0.4,0,0.2,1);overflow:hidden;
}
.md-stat::before{
    content:'';position:absolute;top:0;left:0;width:4px;height:100%;
    background:var(--stat-color,#047857);opacity:0;transition:opacity 0.3s;
}
.md-stat:hover{
    box-shadow:0 12px 30px rgba(0,0,0,0.08);transform:translateY(-3px);
    border-color:var(--stat-color,#047857);text-decoration:none;
}
.md-stat:hover::before{opacity:1;}
.md-stat-icon{
    width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;
    font-size:1.35rem;flex-shrink:0;background:var(--stat-bg,#d1fae5);color:var(--stat-color,#059669);
    transition:transform 0.3s ease;
}
.md-stat:hover .md-stat-icon{transform:scale(1.08) rotate(-5deg);}
.md-stat-info{flex:1;min-width:0;}
.md-stat-val{font-size:1.65rem;font-weight:800;color:#0f172a;line-height:1.1;letter-spacing:-0.5px;}
.md-stat-lbl{font-size:0.78rem;color:#6b7280;font-weight:600;margin-top:3px;text-transform:uppercase;letter-spacing:0.3px;}

/* ── Main Grid ── */
.md-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px;}
@media(max-width:991px){.md-grid{grid-template-columns:1fr;}}

/* ── Cards ── */
.md-card{
    background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden;margin-bottom:20px;
    box-shadow:0 1px 3px rgba(0,0,0,0.04);transition:box-shadow 0.3s;
}
.md-card:hover{box-shadow:0 6px 20px rgba(0,0,0,0.06);}
.md-card-head{
    display:flex;align-items:center;justify-content:space-between;
    padding:16px 22px;border-bottom:1px solid #f1f5f9;background:linear-gradient(180deg,#fafbfc 0%,#fff 100%);
}
.md-card-head-l{display:flex;align-items:center;gap:10px;}
.md-card-head-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.95rem;flex-shrink:0;}
.md-card-head h3{margin:0;font-size:0.95rem;font-weight:700;color:#0f172a;letter-spacing:-0.2px;}
.md-card-head-sub{font-size:0.72rem;color:#94a3b8;margin-top:1px;}
.md-card-body{padding:20px 22px;}

/* ── Quick Actions ── */
.md-quick{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
@media(max-width:600px){.md-quick{grid-template-columns:repeat(2,1fr);}}
.md-quick a{
    display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px 10px;
    border:1.5px solid #e5e7eb;border-radius:14px;font-size:0.75rem;font-weight:600;color:#374151;
    text-decoration:none;transition:all 0.25s cubic-bezier(0.4,0,0.2,1);text-align:center;
}
.md-quick a:hover{border-color:#047857;color:#047857;background:#f0fdf4;transform:translateY(-2px);box-shadow:0 6px 16px rgba(4,120,87,0.12);text-decoration:none;}
.md-quick a i{font-size:1.3rem;color:#047857;transition:transform 0.25s;}
.md-quick a:hover i{transform:scale(1.15);}

/* ── Table ── */
.md-table{width:100%;border-collapse:collapse;font-size:0.85rem;}
.md-table th{padding:10px 16px;text-align:left;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;background:#f8fafc;border-bottom:2px solid #e2e8f0;}
.md-table td{padding:12px 16px;border-bottom:1px solid #f1f5f9;color:#1e293b;vertical-align:middle;}
.md-table tbody tr{transition:background 0.15s;}
.md-table tbody tr:hover{background:#f8fafc;}
.md-table tbody tr:last-child td{border-bottom:none;}
.md-table-icon{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:0.8rem;margin-right:8px;vertical-align:middle;}
.md-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:50px;font-size:0.7rem;font-weight:700;}
.md-badge-ok{background:#d1fae5;color:#065f46;}
.md-badge-no{background:#f1f5f9;color:#64748b;}
.md-badge-info{background:#dbeafe;color:#1e40af;}
.md-badge-danger{background:#fee2e2;color:#991b1b;}
.md-btn-sm{display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:8px;font-size:0.72rem;font-weight:600;text-decoration:none;border:1px solid #e2e8f0;background:#fff;color:#475569;transition:all 0.2s;}
.md-btn-sm:hover{background:#047857;color:#fff;border-color:#047857;text-decoration:none;}

/* ── Academic Year Card ── */
.md-ay-card{
    background:linear-gradient(135deg,#047857 0%,#0d9488 100%);
    border-radius:16px;padding:24px;color:#fff;text-align:center;margin-bottom:20px;
    position:relative;overflow:hidden;box-shadow:0 8px 24px rgba(4,120,87,0.25);
}
.md-ay-card::before{content:'';position:absolute;top:-40%;right:-20%;width:200px;height:200px;background:radial-gradient(circle,rgba(252,211,77,0.15) 0%,transparent 70%);border-radius:50%;}
.md-ay-icon{width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,0.20);border:1px solid rgba(255,255,255,0.30);display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin:0 auto 12px;position:relative;z-index:1;}
.md-ay-card h3{color:#fff;font-weight:800;margin:0 0 4px;font-size:1.3rem;position:relative;z-index:1;}
.md-ay-card p{color:rgba(255,255,255,0.85);font-size:0.78rem;margin:0 0 14px;position:relative;z-index:1;}
.md-ay-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;background:#fff;color:#047857;border-radius:50px;font-size:0.78rem;font-weight:700;text-decoration:none;transition:all 0.25s;position:relative;z-index:1;}
.md-ay-btn:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(0,0,0,0.15);color:#047857;text-decoration:none;}

/* ── Payments ── */
.md-pay{display:flex;align-items:center;justify-content:space-between;padding:12px 22px;border-bottom:1px solid #f1f5f9;transition:background 0.15s;}
.md-pay:hover{background:#f8fafc;}
.md-pay:last-child{border-bottom:none;}
.md-pay-l{display:flex;align-items:center;gap:12px;}
.md-pay-ic{width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#d1fae5 0%,#a7f3d0 100%);color:#059669;display:flex;align-items:center;justify-content:center;font-size:0.85rem;flex-shrink:0;}
.md-pay-n{font-size:0.85rem;font-weight:600;color:#0f172a;}
.md-pay-d{font-size:0.7rem;color:#94a3b8;margin-top:1px;}
.md-pay-a{font-size:0.9rem;font-weight:800;color:#059669;font-family:'Inter',sans-serif;}

/* ── System Info ── */
.md-info-row{display:flex;justify-content:space-between;align-items:center;padding:10px 22px;font-size:0.82rem;border-bottom:1px solid #f1f5f9;}
.md-info-row:last-child{border-bottom:none;}
.md-info-k{color:#64748b;font-weight:500;}
.md-info-v{font-weight:700;color:#0f172a;}
.md-info-v .md-badge{margin-left:6px;}

/* ── Empty State ── */
.md-empty{text-align:center;padding:36px 20px;color:#94a3b8;}
.md-empty i{font-size:2.2rem;opacity:0.3;display:block;margin-bottom:8px;}
.md-empty p{font-size:0.82rem;margin:0;}

/* ── Animations ── */
@keyframes mdFadeInUp{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}
.md-stat,.md-card,.md-ay-card{animation:mdFadeInUp 0.4s ease-out backwards;}
.md-stat:nth-child(1){animation-delay:0.05s;}
.md-stat:nth-child(2){animation-delay:0.10s;}
.md-stat:nth-child(3){animation-delay:0.15s;}
.md-stat:nth-child(4){animation-delay:0.20s;}
.md-stat:nth-child(5){animation-delay:0.25s;}
.md-stat:nth-child(6){animation-delay:0.30s;}
.md-stat:nth-child(7){animation-delay:0.35s;}
.md-stat:nth-child(8){animation-delay:0.40s;}
</style>

<div class="md-wrap">
    {{-- ═══════════════ Hero Header ═══════════════ --}}
    <div class="md-hero">
        <div class="md-hero-content">
            <div>
                <h1><i class="fas fa-graduation-cap"></i>{{ $greeting }}, {{ $firstName }}!</h1>
                <p>Welcome to your School of Redemption dashboard. Here's what's happening today.</p>
                <div class="md-hero-meta">
                    @if($currentYear)
                    <span class="md-hero-chip"><i class="fas fa-calendar-alt"></i>{{ $currentYear->name }}</span>
                    @endif
                    @if($branchName)
                    <span class="md-hero-chip"><i class="fas fa-map-marker-alt"></i>{{ $branchName }}</span>
                    @else
                    <span class="md-hero-chip"><i class="fas fa-globe"></i>All Branches</span>
                    @endif
                    <span class="md-hero-chip"><i class="fas fa-clock"></i>{{ now()->format('M j, Y') }}</span>
                </div>
            </div>
            <div class="md-hero-actions">
                @if($isAdminOrGM || $isBranchPrincipal || $isTeacher)
                <a href="{{ route('admin.students.create') }}" class="md-btn-hero md-btn-hero-primary"><i class="fas fa-user-plus"></i>Add Student</a>
                <a href="{{ route('admin.mark-entries.index') }}" class="md-btn-hero md-btn-hero-ghost"><i class="fas fa-pen"></i>Mark Entry</a>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════ Stats Grid ═══════════════ --}}
    <div class="md-stats">
        @if(!$isBranchScoped)
        <a href="{{ route('admin.branches.index') }}" class="md-stat" style="--stat-color:#4361ee;--stat-bg:#e0e7ff;">
            <div class="md-stat-icon"><i class="fas fa-building"></i></div>
            <div class="md-stat-info"><div class="md-stat-val">{{ $totalBranches }}</div><div class="md-stat-lbl">Branches</div></div>
        </a>
        @endif
        <a href="{{ route('admin.students.index') }}" class="md-stat" style="--stat-color:#d97706;--stat-bg:#fef3c7;">
            <div class="md-stat-icon"><i class="fas fa-user-graduate"></i></div>
            <div class="md-stat-info"><div class="md-stat-val">{{ $totalStudents }}</div><div class="md-stat-lbl">Students</div></div>
        </a>
        <a href="{{ route('admin.teachers.index') }}" class="md-stat" style="--stat-color:#059669;--stat-bg:#d1fae5;">
            <div class="md-stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <div class="md-stat-info"><div class="md-stat-val">{{ $totalTeachers }}</div><div class="md-stat-lbl">Teachers</div></div>
        </a>
        @if($isAdminOrGM)
        <a href="{{ route('admin.staff.index') }}" class="md-stat" style="--stat-color:#0284c7;--stat-bg:#e0f2fe;">
            <div class="md-stat-icon"><i class="fas fa-id-badge"></i></div>
            <div class="md-stat-info"><div class="md-stat-val">{{ $totalStaff }}</div><div class="md-stat-lbl">Staff</div></div>
        </a>
        @endif
        <a href="{{ route('admin.classrooms.index') }}" class="md-stat" style="--stat-color:#4361ee;--stat-bg:#e0e7ff;">
            <div class="md-stat-icon"><i class="fas fa-chalkboard"></i></div>
            <div class="md-stat-info"><div class="md-stat-val">{{ $totalClasses }}</div><div class="md-stat-lbl">Classes</div></div>
        </a>
        <a href="{{ route('admin.subjects.index') }}" class="md-stat" style="--stat-color:#7c3aed;--stat-bg:#ede9fe;">
            <div class="md-stat-icon"><i class="fas fa-book"></i></div>
            <div class="md-stat-info"><div class="md-stat-val">{{ $totalSubjects }}</div><div class="md-stat-lbl">Subjects</div></div>
        </a>
        @if(in_array($userRole, ['admin','super_admin','general_manager','finance','cashier']))
        <a href="{{ route('admin.fee-payments.index') }}" class="md-stat" style="--stat-color:#059669;--stat-bg:#d1fae5;">
            <div class="md-stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="md-stat-info"><div class="md-stat-val" style="font-size:1.2rem;">{{ number_format($totalFeeCollected, 0) }}</div><div class="md-stat-lbl">Fee Collected</div></div>
        </a>
        <a href="{{ route('admin.fees.index') }}" class="md-stat" style="--stat-color:#dc2626;--stat-bg:#fee2e2;">
            <div class="md-stat-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="md-stat-info"><div class="md-stat-val" style="font-size:1.2rem;">{{ number_format($pendingFees, 0) }}</div><div class="md-stat-lbl">Pending Fees</div></div>
        </a>
        @endif
        @if($isAdminOrGM)
        <a href="{{ route('admin.chat.index') }}" class="md-stat" style="--stat-color:#d97706;--stat-bg:#fef3c7;">
            <div class="md-stat-icon"><i class="fas fa-envelope"></i></div>
            <div class="md-stat-info"><div class="md-stat-val">{{ $unreadMessages }}</div><div class="md-stat-lbl">Messages</div></div>
        </a>
        @endif
    </div>

    {{-- ═══════════════ Main Grid ═══════════════ --}}
    <div class="md-grid">
        {{-- Left Column --}}
        <div>
            {{-- Quick Actions Card --}}
            <div class="md-card">
                <div class="md-card-head">
                    <div class="md-card-head-l">
                        <div class="md-card-head-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-bolt"></i></div>
                        <div>
                            <h3>Quick Actions</h3>
                            <div class="md-card-head-sub">Jump to common tasks</div>
                        </div>
                    </div>
                </div>
                <div class="md-card-body">
                    <div class="md-quick">
                        @if($isAdminOrGM)<a href="{{ route('admin.branches.create') }}"><i class="fas fa-plus"></i>Add Branch</a>@endif
                        @if($isAdminOrGM || $isBranchPrincipal || $isTeacher)<a href="{{ route('admin.teachers.create') }}"><i class="fas fa-plus"></i>Add Teacher</a>@endif
                        @if($isAdminOrGM || $isBranchPrincipal || $isTeacher)<a href="{{ route('admin.students.create') }}"><i class="fas fa-plus"></i>Add Student</a>@endif
                        @if($isAdminOrGM || $isBranchPrincipal)<a href="{{ route('admin.classrooms.create') }}"><i class="fas fa-plus"></i>Add Class</a>@endif
                        @if($isAdminOrGM)<a href="{{ route('admin.subjects.create') }}"><i class="fas fa-plus"></i>Add Subject</a>@endif
                        @if($isAdminOrGM)<a href="{{ route('admin.academic-years.create') }}"><i class="fas fa-plus"></i>New AY</a>@endif
                        @if($isAdminOrGM)<a href="{{ route('admin.terms.create') }}"><i class="fas fa-plus"></i>New Term</a>@endif
                        <a href="{{ route('admin.mark-entries.index') }}"><i class="fas fa-pen"></i>Mark Entry</a>
                        <a href="{{ route('admin.attendance.index') }}"><i class="fas fa-clipboard-check"></i>Attendance</a>
                    </div>
                </div>
            </div>

            {{-- Management Overview Card --}}
            <div class="md-card">
                <div class="md-card-head">
                    <div class="md-card-head-l">
                        <div class="md-card-head-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-th-large"></i></div>
                        <div>
                            <h3>Management Overview</h3>
                            <div class="md-card-head-sub">All modules at a glance</div>
                        </div>
                    </div>
                </div>
                <table class="md-table">
                    <thead>
                        <tr><th>Module</th><th>Total</th><th>Status</th><th style="text-align:right;">Action</th></tr>
                    </thead>
                    <tbody>
                        @if(!$isBranchScoped)
                        <tr>
                            <td><span class="md-table-icon" style="background:#e0e7ff;color:#4361ee;"><i class="fas fa-building"></i></span>Branches</td>
                            <td><b>{{ $totalBranches }}</b></td>
                            <td>@if($totalBranches>0)<span class="md-badge md-badge-ok"><i class="fas fa-check"></i>Active</span>@else<span class="md-badge md-badge-no">Empty</span>@endif</td>
                            <td style="text-align:right;"><a href="{{ route('admin.branches.index') }}" class="md-btn-sm"><i class="fas fa-cog"></i>Manage</a></td>
                        </tr>
                        @endif
                        <tr>
                            <td><span class="md-table-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-chalkboard-teacher"></i></span>Teachers</td>
                            <td><b>{{ $totalTeachers }}</b></td>
                            <td>@if($totalTeachers>0)<span class="md-badge md-badge-ok"><i class="fas fa-check"></i>Active</span>@else<span class="md-badge md-badge-no">Empty</span>@endif</td>
                            <td style="text-align:right;"><a href="{{ route('admin.teachers.index') }}" class="md-btn-sm"><i class="fas fa-cog"></i>Manage</a></td>
                        </tr>
                        <tr>
                            <td><span class="md-table-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-user-graduate"></i></span>Students</td>
                            <td><b>{{ $totalStudents }}</b></td>
                            <td>@if($totalStudents>0)<span class="md-badge md-badge-ok"><i class="fas fa-check"></i>Active</span>@else<span class="md-badge md-badge-no">Empty</span>@endif</td>
                            <td style="text-align:right;"><a href="{{ route('admin.students.index') }}" class="md-btn-sm"><i class="fas fa-cog"></i>Manage</a></td>
                        </tr>
                        <tr>
                            <td><span class="md-table-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-chalkboard"></i></span>Classes</td>
                            <td><b>{{ $totalClasses }}</b></td>
                            <td>@if($totalClasses>0)<span class="md-badge md-badge-ok"><i class="fas fa-check"></i>Active</span>@else<span class="md-badge md-badge-no">Empty</span>@endif</td>
                            <td style="text-align:right;"><a href="{{ route('admin.classrooms.index') }}" class="md-btn-sm"><i class="fas fa-cog"></i>Manage</a></td>
                        </tr>
                        <tr>
                            <td><span class="md-table-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-book"></i></span>Subjects</td>
                            <td><b>{{ $totalSubjects }}</b></td>
                            <td>@if($totalSubjects>0)<span class="md-badge md-badge-ok"><i class="fas fa-check"></i>Active</span>@else<span class="md-badge md-badge-no">Empty</span>@endif</td>
                            <td style="text-align:right;"><a href="{{ route('admin.subjects.index') }}" class="md-btn-sm"><i class="fas fa-cog"></i>Manage</a></td>
                        </tr>
                        <tr>
                            <td><span class="md-table-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-calendar-alt"></i></span>Academic Years</td>
                            <td><b>{{ \App\Models\AcademicYear::count() }}</b></td>
                            <td>@if($currentYear)<span class="md-badge md-badge-info">{{ $currentYear->name }}</span>@else<span class="md-badge md-badge-danger">None Set</span>@endif</td>
                            <td style="text-align:right;"><a href="{{ route('admin.academic-years.index') }}" class="md-btn-sm"><i class="fas fa-cog"></i>Manage</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right Column --}}
        <div>
            {{-- Academic Year Card --}}
            @if($currentYear)
            <div class="md-ay-card">
                <div class="md-ay-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>{{ $currentYear->name }}</h3>
                @if($currentYear->start_date)<p>{{ $currentYear->start_date->format('M j, Y') }} — {{ $currentYear->end_date?->format('M j, Y') }}</p>@else<p>Current academic year</p>@endif
                <a href="{{ route('admin.academic-years.index') }}" class="md-ay-btn"><i class="fas fa-eye"></i> View All Years</a>
            </div>
            @endif

            {{-- Recent Payments Card --}}
            <div class="md-card">
                <div class="md-card-head">
                    <div class="md-card-head-l">
                        <div class="md-card-head-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-credit-card"></i></div>
                        <div>
                            <h3>Recent Payments</h3>
                            <div class="md-card-head-sub">Latest fee transactions</div>
                        </div>
                    </div>
                </div>
                @if($recentPayments->count() > 0)
                    @foreach($recentPayments as $payment)
                    <div class="md-pay">
                        <div class="md-pay-l">
                            <div class="md-pay-ic"><i class="fas fa-check"></i></div>
                            <div>
                                <div class="md-pay-n">{{ $payment->student?->full_name ?? 'Unknown' }}</div>
                                <div class="md-pay-d">{{ $payment->payment_date?->format('M d, Y') ?? '-' }}</div>
                            </div>
                        </div>
                        <span class="md-pay-a">{{ number_format($payment->amount_paid, 2) }}</span>
                    </div>
                    @endforeach
                @else
                    <div class="md-empty">
                        <i class="fas fa-inbox"></i>
                        <p>No recent payments</p>
                    </div>
                @endif
            </div>

            {{-- System Info Card --}}
            @if(!$isBranchScoped && $isAdminOrGM)
            <div class="md-card">
                <div class="md-card-head">
                    <div class="md-card-head-l">
                        <div class="md-card-head-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-info-circle"></i></div>
                        <div>
                            <h3>System Info</h3>
                            <div class="md-card-head-sub">Platform overview</div>
                        </div>
                    </div>
                </div>
                <div class="md-info-row"><span class="md-info-k">School</span><span class="md-info-v">School of Redemption</span></div>
                <div class="md-info-row"><span class="md-info-k">Framework</span><span class="md-info-v">Laravel 12</span></div>
                <div class="md-info-row"><span class="md-info-k">Branches</span><span class="md-info-v">{{ $totalBranches }}</span></div>
                <div class="md-info-row"><span class="md-info-k">Students</span><span class="md-info-v">{{ $totalStudents }}</span></div>
                <div class="md-info-row"><span class="md-info-k">Academic Year</span><span class="md-info-v">{{ $currentYear ? $currentYear->name : 'Not Set' }}</span></div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
