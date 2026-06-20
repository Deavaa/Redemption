@extends('layouts.admin')
@section('title', 'Dashboard')

@push('styles')
<style>
/* Dashboard-specific styles — scoped to .dash-* to avoid conflicts with admin.css */
.dash-wrap { padding: 0 !important; max-width: 100% !important; }
.dash-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
.dash-header h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #111827; }
.dash-header h4 i { color: #047857; margin-right: 8px; }
.dash-meta { margin: 4px 0 0; font-size: 12px; color: #6b7280; }
.dash-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; margin-right: 4px; }
.dash-badge-ay { background: #d1fae5; color: #065f46; }
.dash-badge-br { background: #dbeafe; color: #1e40af; }
.dash-badge-all { background: #f3f4f6; color: #6b7280; }
.dash-actions { display: flex; gap: 6px; flex-wrap: wrap; }

/* Stat cards grid — 3 per row on desktop, 2 on tablet, 1 on mobile */
.dash-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 16px; }
@media (max-width: 991px) { .dash-stats { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 575px) { .dash-stats { grid-template-columns: 1fr; } }

.dash-stat { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #fff; border: 1px solid #e5e7eb; border-left-width: 3px; border-radius: 10px; text-decoration: none; color: inherit; transition: box-shadow 0.2s, transform 0.2s; }
.dash-stat:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); text-decoration: none; }
.dash-stat-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
.dash-stat-lbl { font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
.dash-stat-val { font-size: 20px; font-weight: 800; color: #111827; line-height: 1.1; }

/* Quick Actions */
.dash-quick { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px 14px; margin-bottom: 16px; }
.dash-quick-title { font-size: 13px; font-weight: 700; color: #111827; margin-bottom: 8px; }
.dash-quick-title i { color: #f59e0b; margin-right: 4px; }
.dash-quick-actions { display: flex; flex-wrap: wrap; gap: 6px; }
.dash-btn { display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; border: 1px solid; transition: all 0.15s; cursor: pointer; }
.dash-btn:hover { opacity: 0.85; text-decoration: none; }

/* Two-column layout */
.dash-cols { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; }
@media (max-width: 991px) { .dash-cols { grid-template-columns: 1fr; } }

/* Cards */
.dash-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; margin-bottom: 12px; }
.dash-card-header { padding: 8px 14px; background: #f9fafb; border-bottom: 1px solid #e5e7eb; font-size: 13px; font-weight: 700; color: #111827; }
.dash-card-header i { margin-right: 4px; }

/* Table */
.dash-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.dash-table th { padding: 8px 12px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
.dash-table td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; color: #1f2937; }
.dash-table tbody tr:hover { background: #f9fafb; }
.dash-table tbody tr:last-child td { border-bottom: none; }

/* Status badges */
.dash-status { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; }
.dash-status-active { background: #d1fae5; color: #065f46; }
.dash-status-empty { background: #f3f4f6; color: #6b7280; }
.dash-status-info { background: #dbeafe; color: #1e40af; }
.dash-status-danger { background: #fee2e2; color: #991b1b; }

/* Recent payments list */
.dash-payment { display: flex; align-items: center; justify-content: space-between; padding: 8px 14px; border-bottom: 1px solid #f3f4f6; }
.dash-payment:last-child { border-bottom: none; }
.dash-payment-left { display: flex; align-items: center; gap: 8px; }
.dash-payment-icon { width: 28px; height: 28px; border-radius: 6px; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0; }
.dash-payment-name { font-size: 13px; font-weight: 600; color: #111827; }
.dash-payment-date { font-size: 11px; color: #6b7280; }
.dash-payment-amount { font-size: 13px; font-weight: 700; color: #059669; }

/* System info */
.dash-info-row { display: flex; justify-content: space-between; padding: 6px 14px; font-size: 12px; border-bottom: 1px solid #f3f4f6; }
.dash-info-row:last-child { border-bottom: none; }
.dash-info-label { color: #6b7280; }
.dash-info-value { font-weight: 600; color: #111827; }

/* Current AY card */
.dash-ay-card { text-align: center; padding: 14px; }
.dash-ay-card i { font-size: 1.4rem; color: #10b981; margin-bottom: 4px; }
.dash-ay-card .name { font-size: 1.1rem; font-weight: 800; color: #10b981; }
.dash-ay-card .dates { font-size: 12px; color: #6b7280; margin: 2px 0 8px; }
</style>
@endpush

@section('content')
@php
    $userRole = Auth::user()?->role ?? '';
    $isAdminOrGM = in_array($userRole, ['admin', 'super_admin', 'general_manager']);
    $isBranchPrincipal = $userRole === 'branch_principal';
    $isTeacher = $userRole === 'teacher';
@endphp

<div class="dash-wrap">

    {{-- ── Page Header ──────────────────────────────────────────── --}}
    <div class="dash-header">
        <div>
            <h4><i class="fas fa-th-large"></i> Welcome, {{ Auth::user()?->name ?? "Guest" }}</h4>
            <p class="dash-meta">
                @if(isset($currentYear) && $currentYear)
                    <span class="dash-badge dash-badge-ay">{{ $currentYear->name }}</span>
                @endif
                @if(isset($branchName) && $branchName)
                    <span class="dash-badge dash-badge-br">{{ $branchName }}</span>
                @else
                    <span class="dash-badge dash-badge-all">All Branches</span>
                @endif
                <i class="far fa-calendar" style="margin:0 4px 0 8px;"></i>{{ now()->format('M j, Y') }}
            </p>
        </div>
        <div class="dash-actions">
            @if($isAdminOrGM || $isBranchPrincipal || $isTeacher)
            <a href="{{ route('admin.students.create') }}" class="dash-btn" style="background:#047857;color:#fff;border-color:#047857;">
                <i class="fas fa-user-plus"></i> Add Student
            </a>
            <a href="{{ route('admin.mark-entries.index') }}" class="dash-btn" style="background:#fff;color:#047857;border-color:#047857;">
                <i class="fas fa-pen"></i> Mark Entry
            </a>
            @endif
            <a href="{{ route('admin.profile') }}" class="dash-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;">
                <i class="fas fa-user-cog"></i>
            </a>
        </div>
    </div>

    {{-- ── Stat Cards ───────────────────────────────────────────── --}}
    <div class="dash-stats">
        <a href="{{ route('admin.students.index') }}" class="dash-stat" style="border-left-color:#4361ee;">
            <div class="dash-stat-icon" style="background:#e0e7ff;color:#4361ee;"><i class="fas fa-user-graduate"></i></div>
            <div><div class="dash-stat-lbl">Students</div><div class="dash-stat-val">{{ $totalStudents }}</div></div>
        </a>
        <a href="{{ route('admin.teachers.index') }}" class="dash-stat" style="border-left-color:#10b981;">
            <div class="dash-stat-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-chalkboard-teacher"></i></div>
            <div><div class="dash-stat-lbl">Teachers</div><div class="dash-stat-val">{{ $totalTeachers }}</div></div>
        </a>
        <a href="{{ route('admin.classrooms.index') }}" class="dash-stat" style="border-left-color:#0d9488;">
            <div class="dash-stat-icon" style="background:#ccfbf1;color:#0d9488;"><i class="fas fa-chalkboard"></i></div>
            <div><div class="dash-stat-lbl">Classes</div><div class="dash-stat-val">{{ $totalClasses }}</div></div>
        </a>
        <a href="{{ route('admin.subjects.index') }}" class="dash-stat" style="border-left-color:#8b5cf6;">
            <div class="dash-stat-icon" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-book"></i></div>
            <div><div class="dash-stat-lbl">Subjects</div><div class="dash-stat-val">{{ $totalSubjects }}</div></div>
        </a>
        @if(!$isBranchScoped)
        <a href="{{ route('admin.branches.index') }}" class="dash-stat" style="border-left-color:#f59e0b;">
            <div class="dash-stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-building"></i></div>
            <div><div class="dash-stat-lbl">Branches</div><div class="dash-stat-val">{{ $totalBranches }}</div></div>
        </a>
        @endif
        @if(in_array($userRole, ['admin', 'super_admin', 'general_manager', 'finance', 'cashier']))
        <a href="{{ route('admin.fee-payments.index') }}" class="dash-stat" style="border-left-color:#22c55e;">
            <div class="dash-stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-money-bill-wave"></i></div>
            <div><div class="dash-stat-lbl">Fee Collected</div><div class="dash-stat-val" style="font-size:16px;">{{ number_format($totalFeeCollected, 0) }}</div></div>
        </a>
        @endif
    </div>

    {{-- ── Quick Actions ────────────────────────────────────────── --}}
    <div class="dash-quick">
        <div class="dash-quick-title"><i class="fas fa-bolt"></i> Quick Actions</div>
        <div class="dash-quick-actions">
            @if($isAdminOrGM)
            <a href="{{ route('admin.branches.create') }}" class="dash-btn" style="background:#fff;color:#4361ee;border-color:#4361ee;">+ Branch</a>
            @endif
            @if($isAdminOrGM || $isBranchPrincipal || $isTeacher)
            <a href="{{ route('admin.teachers.create') }}" class="dash-btn" style="background:#fff;color:#059669;border-color:#059669;">+ Teacher</a>
            @endif
            @if($isAdminOrGM || $isBranchPrincipal)
            <a href="{{ route('admin.classrooms.create') }}" class="dash-btn" style="background:#fff;color:#0d9488;border-color:#0d9488;">+ Class</a>
            @endif
            @if($isAdminOrGM)
            <a href="{{ route('admin.subjects.create') }}" class="dash-btn" style="background:#fff;color:#7c3aed;border-color:#7c3aed;">+ Subject</a>
            <a href="{{ route('admin.academic-years.create') }}" class="dash-btn" style="background:#fff;color:#059669;border-color:#059669;">+ AY</a>
            <a href="{{ route('admin.terms.create') }}" class="dash-btn" style="background:#fff;color:#d97706;border-color:#d97706;">+ Term</a>
            @endif
            <a href="{{ route('admin.attendance.index') }}" class="dash-btn" style="background:#fff;color:#dc2626;border-color:#dc2626;"><i class="fas fa-clipboard-check"></i> Attendance</a>
        </div>
    </div>

    {{-- ── Two-column layout: Overview + Sidebar ─────────────────── --}}
    <div class="dash-cols">
        {{-- Left: Management Overview --}}
        <div>
            <div class="dash-card">
                <div class="dash-card-header"><i class="fas fa-th-large" style="color:#047857;"></i> Management Overview</div>
                <table class="dash-table">
                    <thead>
                        <tr><th>Module</th><th>Total</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @if(!$isBranchScoped)
                        <tr>
                            <td><i class="fas fa-building" style="color:#4361ee;margin-right:6px;"></i>Branches</td>
                            <td><strong>{{ $totalBranches }}</strong></td>
                            <td>@if($totalBranches > 0)<span class="dash-status dash-status-active">Active</span>@else<span class="dash-status dash-status-empty">Empty</span>@endif</td>
                            <td><a href="{{ route('admin.branches.index') }}" class="dash-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;">Manage</a></td>
                        </tr>
                        @endif
                        <tr>
                            <td><i class="fas fa-chalkboard-teacher" style="color:#10b981;margin-right:6px;"></i>Teachers</td>
                            <td><strong>{{ $totalTeachers }}</strong></td>
                            <td>@if($totalTeachers > 0)<span class="dash-status dash-status-active">Active</span>@else<span class="dash-status dash-status-empty">Empty</span>@endif</td>
                            <td><a href="{{ route('admin.teachers.index') }}" class="dash-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-user-graduate" style="color:#f59e0b;margin-right:6px;"></i>Students</td>
                            <td><strong>{{ $totalStudents }}</strong></td>
                            <td>@if($totalStudents > 0)<span class="dash-status dash-status-active">Active</span>@else<span class="dash-status dash-status-empty">Empty</span>@endif</td>
                            <td><a href="{{ route('admin.students.index') }}" class="dash-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-chalkboard" style="color:#0d9488;margin-right:6px;"></i>Classes</td>
                            <td><strong>{{ $totalClasses }}</strong></td>
                            <td>@if($totalClasses > 0)<span class="dash-status dash-status-active">Active</span>@else<span class="dash-status dash-status-empty">Empty</span>@endif</td>
                            <td><a href="{{ route('admin.classrooms.index') }}" class="dash-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-book" style="color:#8b5cf6;margin-right:6px;"></i>Subjects</td>
                            <td><strong>{{ $totalSubjects }}</strong></td>
                            <td>@if($totalSubjects > 0)<span class="dash-status dash-status-active">Active</span>@else<span class="dash-status dash-status-empty">Empty</span>@endif</td>
                            <td><a href="{{ route('admin.subjects.index') }}" class="dash-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-calendar-alt" style="color:#10b981;margin-right:6px;"></i>Academic Years</td>
                            <td><strong>{{ \App\Models\AcademicYear::count() }}</strong></td>
                            <td>@if($currentYear)<span class="dash-status dash-status-info">{{ $currentYear->name }}</span>@else<span class="dash-status dash-status-danger">None Set</span>@endif</td>
                            <td><a href="{{ route('admin.academic-years.index') }}" class="dash-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;">Manage</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right: Sidebar --}}
        <div>
            @if($currentYear)
            <div class="dash-card" style="border-left:3px solid #10b981;">
                <div class="dash-ay-card">
                    <i class="fas fa-calendar-check"></i>
                    <div class="name">{{ $currentYear->name }}</div>
                    @if($currentYear->start_date)
                        <div class="dates">{{ $currentYear->start_date }} — {{ $currentYear->end_date }}</div>
                    @endif
                    <a href="{{ route('admin.academic-years.index') }}" class="dash-btn" style="background:#fff;color:#059669;border-color:#059669;"><i class="fas fa-eye"></i> View All</a>
                </div>
            </div>
            @endif

            <div class="dash-card">
                <div class="dash-card-header"><i class="fas fa-credit-card" style="color:#f59e0b;"></i> Recent Payments</div>
                @if($recentPayments->count() > 0)
                    @foreach($recentPayments as $payment)
                    <div class="dash-payment">
                        <div class="dash-payment-left">
                            <div class="dash-payment-icon"><i class="fas fa-check"></i></div>
                            <div>
                                <div class="dash-payment-name">{{ $payment->student->full_name ?? 'Unknown' }}</div>
                                <div class="dash-payment-date">{{ $payment->payment_date?->format('M d, Y') ?? '-' }}</div>
                            </div>
                        </div>
                        <span class="dash-payment-amount">{{ number_format($payment->amount_paid, 2) }}</span>
                    </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:24px;color:#9ca3af;">
                        <i class="fas fa-inbox" style="font-size:1.5rem;opacity:0.3;display:block;margin-bottom:6px;"></i>
                        <span style="font-size:12px;">No recent payments</span>
                    </div>
                @endif
            </div>

            @if(!$isBranchScoped && $isAdminOrGM)
            <div class="dash-card">
                <div class="dash-card-header"><i class="fas fa-info-circle" style="color:#0d9488;"></i> System Info</div>
                <div class="dash-info-row"><span class="dash-info-label">School</span><span class="dash-info-value">School of Redemption</span></div>
                <div class="dash-info-row"><span class="dash-info-label">Framework</span><span class="dash-info-value">Laravel 12</span></div>
                <div class="dash-info-row"><span class="dash-info-label">Branches</span><span class="dash-info-value">{{ $totalBranches }}</span></div>
                <div class="dash-info-row"><span class="dash-info-label">Students</span><span class="dash-info-value">{{ $totalStudents }}</span></div>
                <div class="dash-info-row"><span class="dash-info-label">Academic Year</span><span class="dash-info-value">{{ $currentYear ? $currentYear->name : 'Not Set' }}</span></div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
