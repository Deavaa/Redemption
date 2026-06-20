@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@php
    $userRole = Auth::user()?->role ?? '';
    $isAdminOrGM = in_array($userRole, ['admin', 'super_admin', 'general_manager']);
    $isBranchPrincipal = $userRole === 'branch_principal';
    $isTeacher = $userRole === 'teacher';
@endphp

<style>
/* ALL dashboard CSS is inline here. No external dependency. */
/* Class prefix: rw- (Redemption Workspace) — exists nowhere else. */

.rw-wrap{padding:0;margin:0;width:100%;box-sizing:border-box;}
.rw-header{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:20px;}
.rw-header h2{margin:0;font-size:22px;font-weight:700;color:#111827;}
.rw-header h2 i{color:#047857;margin-right:8px;}
.rw-header p{margin:4px 0 0;font-size:13px;color:#6b7280;}
.rw-actions{display:flex;gap:6px;flex-wrap:wrap;}
.rw-btn{display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid;cursor:pointer;transition:opacity .15s;}
.rw-btn:hover{opacity:.85;text-decoration:none;}

.rw-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;}
@media(max-width:1200px){.rw-stats{grid-template-columns:repeat(3,1fr);}}
@media(max-width:768px){.rw-stats{grid-template-columns:repeat(2,1fr);}}
@media(max-width:480px){.rw-stats{grid-template-columns:1fr;}}

.rw-stat{display:flex;align-items:center;gap:14px;padding:16px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;text-decoration:none;color:inherit;transition:box-shadow .2s,transform .2s;position:relative;overflow:hidden;}
.rw-stat:hover{box-shadow:0 4px 12px rgba(0,0,0,.1);transform:translateY(-2px);text-decoration:none;}
.rw-stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.rw-stat-icon.blue{background:#e0e7ff;color:#4361ee;}
.rw-stat-icon.green{background:#d1fae5;color:#059669;}
.rw-stat-icon.gold{background:#fef3c7;color:#d97706;}
.rw-stat-icon.red{background:#fee2e2;color:#dc2626;}
.rw-stat-icon.info{background:#e0f2fe;color:#0284c7;}
.rw-stat-icon.purple{background:#ede9fe;color:#7c3aed;}
.rw-stat-val{font-size:24px;font-weight:800;color:#111827;line-height:1.2;}
.rw-stat-lbl{font-size:12px;color:#6b7280;font-weight:500;margin-top:2px;}

.rw-grid{display:grid;grid-template-columns:2fr 1fr;gap:16px;}
@media(max-width:991px){.rw-grid{grid-template-columns:1fr;}}

.rw-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:16px;}
.rw-card-head{padding:12px 20px;border-bottom:1px solid #e5e7eb;background:#f9fafb;font-size:14px;font-weight:700;color:#111827;}
.rw-card-head i{margin-right:6px;}
.rw-card-body{padding:16px 20px;}

.rw-quick{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
@media(max-width:480px){.rw-quick{grid-template-columns:1fr;}}
.rw-quick a{display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:12px;font-weight:500;color:#374151;text-decoration:none;transition:all .15s;}
.rw-quick a:hover{border-color:#047857;color:#047857;background:#ecfdf5;text-decoration:none;}
.rw-quick a i{font-size:13px;opacity:.7;}

.rw-table{width:100%;border-collapse:collapse;font-size:13px;}
.rw-table th{padding:8px 12px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;background:#f9fafb;border-bottom:1px solid #e5e7eb;}
.rw-table td{padding:10px 12px;border-bottom:1px solid #f3f4f6;color:#1f2937;}
.rw-table tbody tr:hover{background:#f9fafb;}
.rw-table tbody tr:last-child td{border-bottom:none;}
.rw-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;}
.rw-badge-ok{background:#d1fae5;color:#065f46;}
.rw-badge-no{background:#f3f4f6;color:#6b7280;}
.rw-badge-info{background:#dbeafe;color:#1e40af;}
.rw-badge-danger{background:#fee2e2;color:#991b1b;}

.rw-pay{display:flex;align-items:center;justify-content:space-between;padding:10px 20px;border-bottom:1px solid #f3f4f6;}
.rw-pay:last-child{border-bottom:none;}
.rw-pay-l{display:flex;align-items:center;gap:10px;}
.rw-pay-ic{width:30px;height:30px;border-radius:6px;background:#d1fae5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;}
.rw-pay-n{font-size:13px;font-weight:600;color:#111827;}
.rw-pay-d{font-size:11px;color:#6b7280;}
.rw-pay-a{font-size:13px;font-weight:700;color:#059669;}

.rw-ay{text-align:center;padding:20px;}
.rw-ay i{font-size:28px;color:#059669;margin-bottom:6px;}
.rw-ay h3{color:#059669;font-weight:800;margin:0 0 4px;}
.rw-ay p{color:#6b7280;font-size:12px;margin:0 0 10px;}

.rw-info{display:flex;justify-content:space-between;padding:6px 20px;font-size:12px;border-bottom:1px solid #f3f4f6;}
.rw-info:last-child{border-bottom:none;}
.rw-info-k{color:#6b7280;}
.rw-info-v{font-weight:600;color:#111827;}

.rw-empty{text-align:center;padding:32px;color:#9ca3af;}
.rw-empty i{font-size:28px;opacity:.3;display:block;margin-bottom:6px;}
</style>

<div class="rw-wrap">
    <div class="rw-header">
        <div>
            <h2><i class="fas fa-th-large"></i>Welcome, {{ Auth::user()?->name ?? 'Guest' }}</h2>
            <p>@if($currentYear){{ $currentYear->name }} · @endif @if($branchName){{ $branchName }} @else All Branches @endif · {{ now()->format('M j, Y') }}</p>
        </div>
        <div class="rw-actions">
            @if($isAdminOrGM || $isBranchPrincipal || $isTeacher)
            <a href="{{ route('admin.students.create') }}" class="rw-btn" style="background:#047857;color:#fff;border-color:#047857;"><i class="fas fa-user-plus"></i> Add Student</a>
            <a href="{{ route('admin.mark-entries.index') }}" class="rw-btn" style="background:#fff;color:#047857;border-color:#047857;"><i class="fas fa-pen"></i> Mark Entry</a>
            @endif
            <a href="{{ route('admin.profile') }}" class="rw-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;"><i class="fas fa-user-cog"></i></a>
        </div>
    </div>

    <div class="rw-stats">
        @if(!$isBranchScoped)
        <a href="{{ route('admin.branches.index') }}" class="rw-stat"><div class="rw-stat-icon blue"><i class="fas fa-building"></i></div><div><div class="rw-stat-val">{{ $totalBranches }}</div><div class="rw-stat-lbl">Branches</div></div></a>
        @endif
        <a href="{{ route('admin.students.index') }}" class="rw-stat"><div class="rw-stat-icon gold"><i class="fas fa-user-graduate"></i></div><div><div class="rw-stat-val">{{ $totalStudents }}</div><div class="rw-stat-lbl">Students</div></div></a>
        <a href="{{ route('admin.teachers.index') }}" class="rw-stat"><div class="rw-stat-icon green"><i class="fas fa-chalkboard-teacher"></i></div><div><div class="rw-stat-val">{{ $totalTeachers }}</div><div class="rw-stat-lbl">Teachers</div></div></a>
        @if($isAdminOrGM)
        <a href="{{ route('admin.staff.index') }}" class="rw-stat"><div class="rw-stat-icon info"><i class="fas fa-id-badge"></i></div><div><div class="rw-stat-val">{{ $totalStaff }}</div><div class="rw-stat-lbl">Staff</div></div></a>
        @endif
        <a href="{{ route('admin.classrooms.index') }}" class="rw-stat"><div class="rw-stat-icon blue"><i class="fas fa-chalkboard"></i></div><div><div class="rw-stat-val">{{ $totalClasses }}</div><div class="rw-stat-lbl">Classes</div></div></a>
        <a href="{{ route('admin.subjects.index') }}" class="rw-stat"><div class="rw-stat-icon purple"><i class="fas fa-book"></i></div><div><div class="rw-stat-val">{{ $totalSubjects }}</div><div class="rw-stat-lbl">Subjects</div></div></a>
        @if(in_array($userRole, ['admin','super_admin','general_manager','finance','cashier']))
        <a href="{{ route('admin.fee-payments.index') }}" class="rw-stat"><div class="rw-stat-icon green"><i class="fas fa-money-bill-wave"></i></div><div><div class="rw-stat-val" style="font-size:18px;">{{ number_format($totalFeeCollected, 0) }}</div><div class="rw-stat-lbl">Fee Collected</div></div></a>
        <a href="{{ route('admin.fees.index') }}" class="rw-stat"><div class="rw-stat-icon red"><i class="fas fa-exclamation-circle"></i></div><div><div class="rw-stat-val" style="font-size:18px;">{{ number_format($pendingFees, 0) }}</div><div class="rw-stat-lbl">Pending Fees</div></div></a>
        @endif
        @if($isAdminOrGM)
        <a href="{{ route('admin.chat.index') }}" class="rw-stat"><div class="rw-stat-icon gold"><i class="fas fa-envelope"></i></div><div><div class="rw-stat-val">{{ $unreadMessages }}</div><div class="rw-stat-lbl">Messages</div></div></a>
        @endif
    </div>

    <div class="rw-grid">
        <div>
            <div class="rw-card">
                <div class="rw-card-head"><i class="fas fa-bolt" style="color:#f59e0b;"></i>Quick Actions</div>
                <div class="rw-card-body">
                    <div class="rw-quick">
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

            <div class="rw-card">
                <div class="rw-card-head"><i class="fas fa-th-large" style="color:#047857;"></i>Management Overview</div>
                <table class="rw-table">
                    <thead><tr><th>Module</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @if(!$isBranchScoped)
                        <tr><td><i class="fas fa-building" style="color:#4361ee;margin-right:6px;"></i>Branches</td><td><b>{{ $totalBranches }}</b></td><td>@if($totalBranches>0)<span class="rw-badge rw-badge-ok">Active</span>@else<span class="rw-badge rw-badge-no">Empty</span>@endif</td><td><a href="{{ route('admin.branches.index') }}" class="rw-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;padding:3px 10px;">Manage</a></td></tr>
                        @endif
                        <tr><td><i class="fas fa-chalkboard-teacher" style="color:#059669;margin-right:6px;"></i>Teachers</td><td><b>{{ $totalTeachers }}</b></td><td>@if($totalTeachers>0)<span class="rw-badge rw-badge-ok">Active</span>@else<span class="rw-badge rw-badge-no">Empty</span>@endif</td><td><a href="{{ route('admin.teachers.index') }}" class="rw-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;padding:3px 10px;">Manage</a></td></tr>
                        <tr><td><i class="fas fa-user-graduate" style="color:#d97706;margin-right:6px;"></i>Students</td><td><b>{{ $totalStudents }}</b></td><td>@if($totalStudents>0)<span class="rw-badge rw-badge-ok">Active</span>@else<span class="rw-badge rw-badge-no">Empty</span>@endif</td><td><a href="{{ route('admin.students.index') }}" class="rw-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;padding:3px 10px;">Manage</a></td></tr>
                        <tr><td><i class="fas fa-chalkboard" style="color:#0284c7;margin-right:6px;"></i>Classes</td><td><b>{{ $totalClasses }}</b></td><td>@if($totalClasses>0)<span class="rw-badge rw-badge-ok">Active</span>@else<span class="rw-badge rw-badge-no">Empty</span>@endif</td><td><a href="{{ route('admin.classrooms.index') }}" class="rw-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;padding:3px 10px;">Manage</a></td></tr>
                        <tr><td><i class="fas fa-book" style="color:#7c3aed;margin-right:6px;"></i>Subjects</td><td><b>{{ $totalSubjects }}</b></td><td>@if($totalSubjects>0)<span class="rw-badge rw-badge-ok">Active</span>@else<span class="rw-badge rw-badge-no">Empty</span>@endif</td><td><a href="{{ route('admin.subjects.index') }}" class="rw-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;padding:3px 10px;">Manage</a></td></tr>
                        <tr><td><i class="fas fa-calendar-alt" style="color:#059669;margin-right:6px;"></i>Academic Years</td><td><b>{{ \App\Models\AcademicYear::count() }}</b></td><td>@if($currentYear)<span class="rw-badge rw-badge-info">{{ $currentYear->name }}</span>@else<span class="rw-badge rw-badge-danger">None Set</span>@endif</td><td><a href="{{ route('admin.academic-years.index') }}" class="rw-btn" style="background:#fff;color:#6b7280;border-color:#d1d5db;padding:3px 10px;">Manage</a></td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            @if($currentYear)
            <div class="rw-card" style="border-left:3px solid #059669;">
                <div class="rw-ay">
                    <i class="fas fa-calendar-check"></i>
                    <h3>{{ $currentYear->name }}</h3>
                    @if($currentYear->start_date)<p>{{ $currentYear->start_date }} — {{ $currentYear->end_date }}</p>@endif
                    <a href="{{ route('admin.academic-years.index') }}" class="rw-btn" style="background:#fff;color:#059669;border-color:#059669;"><i class="fas fa-eye"></i> View All</a>
                </div>
            </div>
            @endif

            <div class="rw-card">
                <div class="rw-card-head"><i class="fas fa-credit-card" style="color:#f59e0b;"></i>Recent Payments</div>
                @if($recentPayments->count() > 0)
                    @foreach($recentPayments as $payment)
                    <div class="rw-pay">
                        <div class="rw-pay-l">
                            <div class="rw-pay-ic"><i class="fas fa-check"></i></div>
                            <div><div class="rw-pay-n">{{ $payment->student->full_name ?? 'Unknown' }}</div><div class="rw-pay-d">{{ $payment->payment_date?->format('M d, Y') ?? '-' }}</div></div>
                        </div>
                        <span class="rw-pay-a">{{ number_format($payment->amount_paid, 2) }}</span>
                    </div>
                    @endforeach
                @else
                    <div class="rw-empty"><i class="fas fa-inbox"></i>No recent payments</div>
                @endif
            </div>

            @if(!$isBranchScoped && $isAdminOrGM)
            <div class="rw-card">
                <div class="rw-card-head"><i class="fas fa-info-circle" style="color:#0284c7;"></i>System Info</div>
                <div class="rw-info"><span class="rw-info-k">School</span><span class="rw-info-v">School of Redemption</span></div>
                <div class="rw-info"><span class="rw-info-k">Framework</span><span class="rw-info-v">Laravel 12</span></div>
                <div class="rw-info"><span class="rw-info-k">Branches</span><span class="rw-info-v">{{ $totalBranches }}</span></div>
                <div class="rw-info"><span class="rw-info-k">Students</span><span class="rw-info-v">{{ $totalStudents }}</span></div>
                <div class="rw-info"><span class="rw-info-k">Academic Year</span><span class="rw-info-v">{{ $currentYear ? $currentYear->name : 'Not Set' }}</span></div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
