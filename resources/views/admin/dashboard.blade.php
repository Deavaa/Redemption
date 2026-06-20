@extends('layouts.admin')
@section('title', 'Dashboard')

@push('styles')
<style>
.dash-wrap { padding:0 !important; max-width:100% !important; width:100% !important; overflow:visible !important; }
.dash-header { display:flex !important; justify-content:space-between !important; align-items:flex-start !important; flex-wrap:wrap !important; gap:10px !important; margin-bottom:16px !important; }
.dash-header h4 { margin:0 !important; font-size:1.4rem !important; font-weight:700 !important; color:#111827 !important; }
.dash-header h4 i { color:#047857 !important; margin-right:8px !important; }
.dash-meta { margin:4px 0 0 !important; font-size:12px !important; color:#6b7280 !important; }
.dash-badge { display:inline-block !important; padding:2px 8px !important; border-radius:10px !important; font-size:11px !important; font-weight:600 !important; margin-right:4px !important; }
.dash-badge-ay { background:#d1fae5 !important; color:#065f46 !important; }
.dash-badge-br { background:#dbeafe !important; color:#1e40af !important; }
.dash-badge-all { background:#f3f4f6 !important; color:#6b7280 !important; }
.dash-actions { display:flex !important; gap:6px !important; flex-wrap:wrap !important; }
.dash-stats { display:grid !important; grid-template-columns:repeat(3,1fr) !important; gap:8px !important; margin-bottom:16px !important; }
.dash-stat { display:flex !important; align-items:center !important; gap:10px !important; padding:10px 14px !important; background:#fff !important; border:1px solid #e5e7eb !important; border-left-width:3px !important; border-radius:10px !important; text-decoration:none !important; color:inherit !important; transition:box-shadow 0.2s,transform 0.2s !important; }
.dash-stat:hover { box-shadow:0 4px 12px rgba(0,0,0,0.08) !important; transform:translateY(-1px) !important; text-decoration:none !important; }
.dash-stat-icon { width:36px !important; height:36px !important; border-radius:8px !important; display:flex !important; align-items:center !important; justify-content:center !important; font-size:15px !important; flex-shrink:0 !important; }
.dash-stat-lbl { font-size:10px !important; font-weight:700 !important; color:#6b7280 !important; text-transform:uppercase !important; letter-spacing:0.5px !important; }
.dash-stat-val { font-size:20px !important; font-weight:800 !important; color:#111827 !important; line-height:1.1 !important; }
.dash-quick { background:#fff !important; border:1px solid #e5e7eb !important; border-radius:10px !important; padding:10px 14px !important; margin-bottom:16px !important; }
.dash-quick-title { font-size:13px !important; font-weight:700 !important; color:#111827 !important; margin-bottom:8px !important; }
.dash-quick-title i { color:#f59e0b !important; margin-right:4px !important; }
.dash-quick-actions { display:flex !important; flex-wrap:wrap !important; gap:6px !important; }
.dash-btn { display:inline-flex !important; align-items:center !important; gap:4px !important; padding:5px 12px !important; border-radius:6px !important; font-size:12px !important; font-weight:600 !important; text-decoration:none !important; border:1px solid !important; transition:all 0.15s !important; cursor:pointer !important; }
.dash-btn:hover { opacity:0.85 !important; text-decoration:none !important; }
.dash-cols { display:grid !important; grid-template-columns:2fr 1fr !important; gap:16px !important; }
.dash-card { background:#fff !important; border:1px solid #e5e7eb !important; border-radius:10px !important; overflow:hidden !important; margin-bottom:12px !important; }
.dash-card-header { padding:8px 14px !important; background:#f9fafb !important; border-bottom:1px solid #e5e7eb !important; font-size:13px !important; font-weight:700 !important; color:#111827 !important; }
.dash-card-header i { margin-right:4px !important; }
.dash-table { width:100% !important; border-collapse:collapse !important; font-size:13px !important; }
.dash-table th { padding:8px 12px !important; text-align:left !important; font-size:11px !important; font-weight:700 !important; text-transform:uppercase !important; color:#6b7280 !important; background:#f9fafb !important; border-bottom:1px solid #e5e7eb !important; }
.dash-table td { padding:8px 12px !important; border-bottom:1px solid #f3f4f6 !important; color:#1f2937 !important; }
.dash-table tbody tr:hover { background:#f9fafb !important; }
.dash-table tbody tr:last-child td { border-bottom:none !important; }
.dash-status { display:inline-block !important; padding:2px 8px !important; border-radius:10px !important; font-size:11px !important; font-weight:600 !important; }
.dash-status-active { background:#d1fae5 !important; color:#065f46 !important; }
.dash-status-empty { background:#f3f4f6 !important; color:#6b7280 !important; }
.dash-status-info { background:#dbeafe !important; color:#1e40af !important; }
.dash-status-danger { background:#fee2e2 !important; color:#991b1b !important; }
.dash-payment { display:flex !important; align-items:center !important; justify-content:space-between !important; padding:8px 14px !important; border-bottom:1px solid #f3f4f6 !important; }
.dash-payment:last-child { border-bottom:none !important; }
.dash-payment-left { display:flex !important; align-items:center !important; gap:8px !important; }
.dash-payment-icon { width:28px !important; height:28px !important; border-radius:6px !important; background:#d1fae5 !important; color:#059669 !important; display:flex !important; align-items:center !important; justify-content:center !important; font-size:11px !important; flex-shrink:0 !important; }
.dash-payment-name { font-size:13px !important; font-weight:600 !important; color:#111827 !important; }
.dash-payment-date { font-size:11px !important; color:#6b7280 !important; }
.dash-payment-amount { font-size:13px !important; font-weight:700 !important; color:#059669 !important; }
.dash-info-row { display:flex !important; justify-content:space-between !important; padding:6px 14px !important; font-size:12px !important; border-bottom:1px solid #f3f4f6 !important; }
.dash-info-row:last-child { border-bottom:none !important; }
.dash-info-label { color:#6b7280 !important; }
.dash-info-value { font-weight:600 !important; color:#111827 !important; }
.dash-ay-card { text-align:center !important; padding:14px !important; }
.dash-ay-card i { font-size:1.4rem !important; color:#10b981 !important; margin-bottom:4px !important; }
.dash-ay-card .name { font-size:1.1rem !important; font-weight:800 !important; color:#10b981 !important; }
.dash-ay-card .dates { font-size:12px !important; color:#6b7280 !important; margin:2px 0 8px !important; }
@media (max-width:991px) { .dash-stats { grid-template-columns:repeat(2,1fr) !important; } .dash-cols { grid-template-columns:1fr !important; } }
@media (max-width:575px) { .dash-stats { grid-template-columns:1fr !important; } }
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

    <div class="dash-cols">
        <div>
            <div class="dash-card">
                <div class="dash-card-header"><i class="fas fa-th-large" style="color:#047857;"></i> Management Overview</div>
                <table class="dash-table">
                    <thead><tr><th>Module</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
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
        <div>
            @if($currentYear)
            <div class="dash-card" style="border-left:3px solid #10b981;">
                <div class="dash-ay-card">
                    <i class="fas fa-calendar-check"></i>
                    <div class="name">{{ $currentYear->name }}</div>
                    @if($currentYear->start_date)<div class="dates">{{ $currentYear->start_date }} — {{ $currentYear->end_date }}</div>@endif
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
                            <div><div class="dash-payment-name">{{ $payment->student->full_name ?? 'Unknown' }}</div><div class="dash-payment-date">{{ $payment->payment_date?->format('M d, Y') ?? '-' }}</div></div>
                        </div>
                        <span class="dash-payment-amount">{{ number_format($payment->amount_paid, 2) }}</span>
                    </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:24px;color:#9ca3af;"><i class="fas fa-inbox" style="font-size:1.5rem;opacity:0.3;display:block;margin-bottom:6px;"></i><span style="font-size:12px;">No recent payments</span></div>
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
