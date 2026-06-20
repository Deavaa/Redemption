@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@php
    $userRole = Auth::user()?->role ?? '';
    $isAdminOrGM = in_array($userRole, ['admin', 'super_admin', 'general_manager']);
    $isBranchPrincipal = $userRole === 'branch_principal';
    $isTeacher = $userRole === 'teacher';
@endphp

<div class="dash-welcome">
    <h2><i class="fas fa-th-large" style="color:var(--primary);"></i> Welcome, {{ Auth::user()?->name ?? 'Guest' }}</h2>
    <p>
        @if($currentYear) {{ $currentYear->name }} &middot; @endif
        @if($branchName) {{ $branchName }} @else All Branches @endif
        &middot; {{ now()->format('l, F j, Y') }}
    </p>
</div>

<div class="dash-stats">
    @if(!$isBranchScoped)
    <a href="{{ route('admin.branches.index') }}" class="dash-stat-card" style="text-decoration:none;color:inherit;">
        <div class="dash-stat-icon blue"><i class="fas fa-building"></i></div>
        <div class="dash-stat-info"><h3>{{ $totalBranches }}</h3><p>Branches</p></div>
    </a>
    @endif
    <a href="{{ route('admin.students.index') }}" class="dash-stat-card" style="text-decoration:none;color:inherit;">
        <div class="dash-stat-icon gold"><i class="fas fa-user-graduate"></i></div>
        <div class="dash-stat-info"><h3>{{ $totalStudents }}</h3><p>Students</p></div>
    </a>
    <a href="{{ route('admin.teachers.index') }}" class="dash-stat-card" style="text-decoration:none;color:inherit;">
        <div class="dash-stat-icon green"><i class="fas fa-chalkboard-teacher"></i></div>
        <div class="dash-stat-info"><h3>{{ $totalTeachers }}</h3><p>Teachers</p></div>
    </a>
    @if($isAdminOrGM)
    <a href="{{ route('admin.staff.index') }}" class="dash-stat-card" style="text-decoration:none;color:inherit;">
        <div class="dash-stat-icon info"><i class="fas fa-id-badge"></i></div>
        <div class="dash-stat-info"><h3>{{ $totalStaff }}</h3><p>Staff</p></div>
    </a>
    @endif
    <a href="{{ route('admin.classrooms.index') }}" class="dash-stat-card" style="text-decoration:none;color:inherit;">
        <div class="dash-stat-icon blue"><i class="fas fa-chalkboard"></i></div>
        <div class="dash-stat-info"><h3>{{ $totalClasses }}</h3><p>Classes</p></div>
    </a>
    <a href="{{ route('admin.subjects.index') }}" class="dash-stat-card" style="text-decoration:none;color:inherit;">
        <div class="dash-stat-icon green"><i class="fas fa-book"></i></div>
        <div class="dash-stat-info"><h3>{{ $totalSubjects }}</h3><p>Subjects</p></div>
    </a>
    @if(in_array($userRole, ['admin','super_admin','general_manager','finance','cashier']))
    <a href="{{ route('admin.fee-payments.index') }}" class="dash-stat-card" style="text-decoration:none;color:inherit;">
        <div class="dash-stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
        <div class="dash-stat-info"><h3 style="font-size:20px;">{{ number_format($totalFeeCollected, 0) }}</h3><p>Fee Collected</p></div>
    </a>
    <a href="{{ route('admin.fees.index') }}" class="dash-stat-card" style="text-decoration:none;color:inherit;">
        <div class="dash-stat-icon red"><i class="fas fa-exclamation-circle"></i></div>
        <div class="dash-stat-info"><h3 style="font-size:20px;">{{ number_format($pendingFees, 0) }}</h3><p>Pending Fees</p></div>
    </a>
    @endif
    @if($isAdminOrGM)
    <a href="{{ route('admin.chat.index') }}" class="dash-stat-card" style="text-decoration:none;color:inherit;">
        <div class="dash-stat-icon gold"><i class="fas fa-envelope"></i></div>
        <div class="dash-stat-info"><h3>{{ $unreadMessages }}</h3><p>Unread Messages</p></div>
    </a>
    @endif
</div>

<div class="dash-grid">
    {{-- Left column --}}
    <div>
        {{-- Quick Actions --}}
        <div class="dash-card" style="margin-bottom:18px;">
            <div class="dash-card-header">
                <h5><i class="fas fa-bolt" style="color:var(--warning);"></i> Quick Actions</h5>
            </div>
            <div class="dash-card-body">
                <div class="dash-quick-actions">
                    @if($isAdminOrGM)
                    <a href="{{ route('admin.branches.create') }}" class="dash-quick-action"><i class="fas fa-plus"></i> Add Branch</a>
                    @endif
                    @if($isAdminOrGM || $isBranchPrincipal || $isTeacher)
                    <a href="{{ route('admin.teachers.create') }}" class="dash-quick-action"><i class="fas fa-plus"></i> Add Teacher</a>
                    <a href="{{ route('admin.students.create') }}" class="dash-quick-action"><i class="fas fa-plus"></i> Add Student</a>
                    @endif
                    @if($isAdminOrGM || $isBranchPrincipal)
                    <a href="{{ route('admin.classrooms.create') }}" class="dash-quick-action"><i class="fas fa-plus"></i> Add Class</a>
                    @endif
                    @if($isAdminOrGM)
                    <a href="{{ route('admin.subjects.create') }}" class="dash-quick-action"><i class="fas fa-plus"></i> Add Subject</a>
                    <a href="{{ route('admin.academic-years.create') }}" class="dash-quick-action"><i class="fas fa-plus"></i> New Academic Year</a>
                    <a href="{{ route('admin.terms.create') }}" class="dash-quick-action"><i class="fas fa-plus"></i> New Term</a>
                    @endif
                    <a href="{{ route('admin.mark-entries.index') }}" class="dash-quick-action"><i class="fas fa-pen"></i> Mark Entry</a>
                    <a href="{{ route('admin.attendance.index') }}" class="dash-quick-action"><i class="fas fa-clipboard-check"></i> Attendance</a>
                </div>
            </div>
        </div>

        {{-- Management Overview --}}
        <div class="dash-card">
            <div class="dash-card-header">
                <h5><i class="fas fa-th-large" style="color:var(--primary);"></i> Management Overview</h5>
            </div>
            <div class="dash-card-body" style="padding:0;">
                <table class="table table-hover mb-0" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);">Module</th>
                            <th style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);">Total</th>
                            <th style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);">Status</th>
                            <th style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:var(--text-muted);">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$isBranchScoped)
                        <tr>
                            <td><i class="fas fa-building me-2" style="color:var(--primary);"></i>Branches</td>
                            <td><strong>{{ $totalBranches }}</strong></td>
                            <td>@if($totalBranches > 0)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Empty</span>@endif</td>
                            <td><a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                        @endif
                        <tr>
                            <td><i class="fas fa-chalkboard-teacher me-2" style="color:var(--success);"></i>Teachers</td>
                            <td><strong>{{ $totalTeachers }}</strong></td>
                            <td>@if($totalTeachers > 0)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Empty</span>@endif</td>
                            <td><a href="{{ route('admin.teachers.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-user-graduate me-2" style="color:var(--warning);"></i>Students</td>
                            <td><strong>{{ $totalStudents }}</strong></td>
                            <td>@if($totalStudents > 0)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Empty</span>@endif</td>
                            <td><a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-chalkboard me-2" style="color:var(--info);"></i>Classes</td>
                            <td><strong>{{ $totalClasses }}</strong></td>
                            <td>@if($totalClasses > 0)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Empty</span>@endif</td>
                            <td><a href="{{ route('admin.classrooms.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-book me-2" style="color:var(--primary);"></i>Subjects</td>
                            <td><strong>{{ $totalSubjects }}</strong></td>
                            <td>@if($totalSubjects > 0)<span class="badge bg-success">Active</span>@else<span class="badge bg-secondary">Empty</span>@endif</td>
                            <td><a href="{{ route('admin.subjects.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-calendar-alt me-2" style="color:var(--success);"></i>Academic Years</td>
                            <td><strong>{{ \App\Models\AcademicYear::count() }}</strong></td>
                            <td>@if($currentYear)<span class="badge bg-info">{{ $currentYear->name }}</span>@else<span class="badge bg-danger">None Set</span>@endif</td>
                            <td><a href="{{ route('admin.academic-years.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Right column --}}
    <div>
        {{-- Current Academic Year --}}
        @if($currentYear)
        <div class="dash-card" style="margin-bottom:18px;border-left:3px solid var(--success);">
            <div class="dash-card-body" style="text-align:center;padding:24px;">
                <i class="fas fa-calendar-check" style="font-size:28px;color:var(--success);margin-bottom:8px;"></i>
                <h3 style="color:var(--success);font-weight:800;margin-bottom:4px;">{{ $currentYear->name }}</h3>
                @if($currentYear->start_date)
                <p style="color:var(--text-muted);font-size:13px;margin-bottom:12px;">{{ $currentYear->start_date }} &mdash; {{ $currentYear->end_date }}</p>
                @endif
                <a href="{{ route('admin.academic-years.index') }}" class="btn btn-sm btn-outline-success"><i class="fas fa-eye me-1"></i> View All</a>
            </div>
        </div>
        @endif

        {{-- Recent Payments --}}
        <div class="dash-card" style="margin-bottom:18px;">
            <div class="dash-card-header">
                <h5><i class="fas fa-credit-card" style="color:var(--warning);"></i> Recent Payments</h5>
            </div>
            <div class="dash-card-body" style="padding:0;">
                @if($recentPayments->count() > 0)
                    @foreach($recentPayments as $payment)
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border-light);">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:8px;background:var(--success-50,#d1fae5);color:var(--success);display:flex;align-items:center;justify-content:center;font-size:12px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:var(--text-dark);">{{ $payment->student->full_name ?? 'Unknown' }}</div>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $payment->payment_date?->format('M d, Y') ?? '-' }} &middot; {{ $payment->payment_method ?? '-' }}</div>
                            </div>
                        </div>
                        <span style="font-size:13px;font-weight:700;color:var(--success);">{{ number_format($payment->amount_paid, 2) }}</span>
                    </div>
                    @endforeach
                @else
                    <div style="text-align:center;padding:32px;color:var(--text-muted);">
                        <i class="fas fa-inbox" style="font-size:32px;opacity:0.3;display:block;margin-bottom:8px;"></i>
                        <p style="font-size:13px;margin:0;">No recent payments</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- System Info --}}
        @if(!$isBranchScoped && $isAdminOrGM)
        <div class="dash-card">
            <div class="dash-card-header">
                <h5><i class="fas fa-info-circle" style="color:var(--info);"></i> System Info</h5>
            </div>
            <div class="dash-card-body" style="padding:0;">
                <table class="table table-sm mb-0" style="font-size:12px;">
                    <tr><td class="text-muted">School</td><td class="text-end fw-semibold">School of Redemption</td></tr>
                    <tr><td class="text-muted">Framework</td><td class="text-end fw-semibold">Laravel 12</td></tr>
                    <tr><td class="text-muted">Branches</td><td class="text-end fw-semibold">{{ $totalBranches }}</td></tr>
                    <tr><td class="text-muted">Students</td><td class="text-end fw-semibold">{{ $totalStudents }}</td></tr>
                    <tr><td class="text-muted border-0">Academic Year</td><td class="text-end fw-semibold border-0">{{ $currentYear ? $currentYear->name : 'Not Set' }}</td></tr>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
