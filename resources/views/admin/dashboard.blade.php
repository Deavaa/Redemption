@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
@php
    $userRole = Auth::user()?->role ?? '';
    $isAdminOrGM = in_array($userRole, ['admin', 'super_admin', 'general_manager']);
    $isBranchPrincipal = $userRole === 'branch_principal';
    $isTeacher = $userRole === 'teacher';
@endphp

{{-- ── Page Header ──────────────────────────────────────────────── --}}
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h4 class="mb-0" style="font-weight:700;color:#111827;">
            <i class="fas fa-th-large me-2" style="color:#047857;"></i>
            Welcome, {{ Auth::user()?->name ?? "Guest" }}
        </h4>
        <p class="text-muted mb-0 mt-1" style="font-size:13px;">
            @if(isset($currentYear) && $currentYear)
                <span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">{{ $currentYear->name }}</span>
            @endif
            @if(isset($branchName) && $branchName)
                <span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">{{ $branchName }}</span>
            @else
                <span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">All Branches</span>
            @endif
            <span class="ms-1"><i class="far fa-calendar me-1"></i>{{ now()->format('M j, Y') }}</span>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($isAdminOrGM || $isBranchPrincipal || $isTeacher)
        <a href="{{ route('admin.students.create') }}" class="btn btn-sm text-white" style="background:#047857;border-color:#047857;">
            <i class="fas fa-user-plus me-1"></i> Add Student
        </a>
        <a href="{{ route('admin.mark-entries.index') }}" class="btn btn-sm" style="border-color:#047857;color:#047857;" onmouseover="this.style.background='#ecfdf5'" onmouseout="this.style.background=''">
            <i class="fas fa-pen me-1"></i> Mark Entry
        </a>
        @endif
        <a href="{{ route('admin.profile') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-user-cog"></i>
        </a>
    </div>
</div>

{{-- ── Stat Cards — 3 per row on desktop, 2 on mobile ───────────── --}}
<div class="row g-2 mb-3">
    <div class="col-6 col-md-4">
        <a href="{{ route('admin.students.index') }}" class="text-decoration-none">
            <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-white border" style="border-left:3px solid #4361ee !important;">
                <div style="width:36px;height:36px;border-radius:8px;background:#e0e7ff;color:#4361ee;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;">Students</div>
                    <div style="font-size:20px;font-weight:800;color:#111827;line-height:1;">{{ $totalStudents }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('admin.teachers.index') }}" class="text-decoration-none">
            <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-white border" style="border-left:3px solid #10b981 !important;">
                <div style="width:36px;height:36px;border-radius:8px;background:#d1fae5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;">Teachers</div>
                    <div style="font-size:20px;font-weight:800;color:#111827;line-height:1;">{{ $totalTeachers }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('admin.classrooms.index') }}" class="text-decoration-none">
            <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-white border" style="border-left:3px solid #0d9488 !important;">
                <div style="width:36px;height:36px;border-radius:8px;background:#ccfbf1;color:#0d9488;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                    <i class="fas fa-chalkboard"></i>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;">Classes</div>
                    <div style="font-size:20px;font-weight:800;color:#111827;line-height:1;">{{ $totalClasses }}</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md-4">
        <a href="{{ route('admin.subjects.index') }}" class="text-decoration-none">
            <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-white border" style="border-left:3px solid #8b5cf6 !important;">
                <div style="width:36px;height:36px;border-radius:8px;background:#ede9fe;color:#7c3aed;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;">Subjects</div>
                    <div style="font-size:20px;font-weight:800;color:#111827;line-height:1;">{{ $totalSubjects }}</div>
                </div>
            </div>
        </a>
    </div>
    @if(!$isBranchScoped)
    <div class="col-6 col-md-4">
        <a href="{{ route('admin.branches.index') }}" class="text-decoration-none">
            <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-white border" style="border-left:3px solid #f59e0b !important;">
                <div style="width:36px;height:36px;border-radius:8px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;">Branches</div>
                    <div style="font-size:20px;font-weight:800;color:#111827;line-height:1;">{{ $totalBranches }}</div>
                </div>
            </div>
        </a>
    </div>
    @endif
    @if(in_array($userRole, ['admin', 'super_admin', 'general_manager', 'finance', 'cashier']))
    <div class="col-6 col-md-4">
        <a href="{{ route('admin.fee-payments.index') }}" class="text-decoration-none">
            <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-white border" style="border-left:3px solid #22c55e !important;">
                <div style="width:36px;height:36px;border-radius:8px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div>
                    <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;">Fee Collected</div>
                    <div style="font-size:16px;font-weight:800;color:#111827;line-height:1;">{{ number_format($totalFeeCollected, 0) }}</div>
                </div>
            </div>
        </a>
    </div>
    @endif
</div>

{{-- ── Quick Actions ────────────────────────────────────────────── --}}
<div class="bg-white rounded-3 border p-2 mb-3">
    <div class="d-flex align-items-center gap-2 mb-2">
        <i class="fas fa-bolt" style="color:#f59e0b;"></i>
        <span style="font-size:13px;font-weight:700;color:#111827;">Quick Actions</span>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if($isAdminOrGM)
        <a href="{{ route('admin.branches.create') }}" class="btn btn-sm" style="border-color:#4361ee;color:#4361ee;">+ Branch</a>
        @endif
        @if($isAdminOrGM || $isBranchPrincipal || $isTeacher)
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-sm" style="border-color:#10b981;color:#059669;">+ Teacher</a>
        @endif
        @if($isAdminOrGM || $isBranchPrincipal)
        <a href="{{ route('admin.classrooms.create') }}" class="btn btn-sm" style="border-color:#0d9488;color:#0d9488;">+ Class</a>
        @endif
        @if($isAdminOrGM)
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-sm" style="border-color:#8b5cf6;color:#7c3aed;">+ Subject</a>
        <a href="{{ route('admin.academic-years.create') }}" class="btn btn-sm" style="border-color:#10b981;color:#059669;">+ AY</a>
        <a href="{{ route('admin.terms.create') }}" class="btn btn-sm" style="border-color:#f59e0b;color:#d97706;">+ Term</a>
        @endif
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm" style="border-color:#ef4444;color:#dc2626;"><i class="fas fa-clipboard-check me-1"></i>Attendance</a>
    </div>
</div>

{{-- ── Management Overview + Sidebar ────────────────────────────── --}}
<div class="row g-3">
    {{-- Management Overview --}}
    <div class="col-12 col-lg-8">
        <div class="bg-white rounded-3 border">
            <div class="px-3 py-2 border-bottom" style="background:#f9fafb;border-radius:0.75rem 0.75rem 0 0 !important;">
                <span style="font-size:13px;font-weight:700;color:#111827;"><i class="fas fa-th-large me-1" style="color:#047857;"></i> Management Overview</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead>
                        <tr style="background:#f9fafb;">
                            <th style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#6b7280;">Module</th>
                            <th style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#6b7280;">Total</th>
                            <th style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#6b7280;">Status</th>
                            <th style="font-size:11px;text-transform:uppercase;letter-spacing:0.5px;color:#6b7280;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$isBranchScoped)
                        <tr>
                            <td><i class="fas fa-building me-2" style="color:#4361ee;"></i>Branches</td>
                            <td><strong>{{ $totalBranches }}</strong></td>
                            <td>
                                @if($totalBranches > 0)
                                    <span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Active</span>
                                @else
                                    <span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Empty</span>
                                @endif
                            </td>
                            <td><a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                        @endif
                        <tr>
                            <td><i class="fas fa-chalkboard-teacher me-2" style="color:#10b981;"></i>Teachers</td>
                            <td><strong>{{ $totalTeachers }}</strong></td>
                            <td>
                                @if($totalTeachers > 0)
                                    <span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Active</span>
                                @else
                                    <span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Empty</span>
                                @endif
                            </td>
                            <td><a href="{{ route('admin.teachers.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-user-graduate me-2" style="color:#f59e0b;"></i>Students</td>
                            <td><strong>{{ $totalStudents }}</strong></td>
                            <td>
                                @if($totalStudents > 0)
                                    <span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Active</span>
                                @else
                                    <span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Empty</span>
                                @endif
                            </td>
                            <td><a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-chalkboard me-2" style="color:#0d9488;"></i>Classes</td>
                            <td><strong>{{ $totalClasses }}</strong></td>
                            <td>
                                @if($totalClasses > 0)
                                    <span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Active</span>
                                @else
                                    <span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Empty</span>
                                @endif
                            </td>
                            <td><a href="{{ route('admin.classrooms.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-book me-2" style="color:#8b5cf6;"></i>Subjects</td>
                            <td><strong>{{ $totalSubjects }}</strong></td>
                            <td>
                                @if($totalSubjects > 0)
                                    <span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Active</span>
                                @else
                                    <span style="background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">Empty</span>
                                @endif
                            </td>
                            <td><a href="{{ route('admin.subjects.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-calendar-alt me-2" style="color:#10b981;"></i>Academic Years</td>
                            <td><strong>{{ \App\Models\AcademicYear::count() }}</strong></td>
                            <td>
                                @if($currentYear)
                                    <span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">{{ $currentYear->name }}</span>
                                @else
                                    <span style="background:#fee2e2;color:#991b1b;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;">None Set</span>
                                @endif
                            </td>
                            <td><a href="{{ route('admin.academic-years.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-12 col-lg-4">
        {{-- Current Academic Year --}}
        @if($currentYear)
        <div class="bg-white rounded-3 border p-3 mb-2 text-center" style="border-left:3px solid #10b981 !important;">
            <i class="fas fa-calendar-check mb-1" style="color:#10b981;font-size:1.4rem;"></i>
            <div style="color:#10b981;font-weight:800;font-size:1.1rem;">{{ $currentYear->name }}</div>
            @if($currentYear->start_date)
                <div class="text-muted" style="font-size:0.78rem;">{{ $currentYear->start_date }} — {{ $currentYear->end_date }}</div>
            @endif
            <a href="{{ route('admin.academic-years.index') }}" class="btn btn-sm btn-outline-success mt-2">
                <i class="fas fa-eye me-1"></i> View All
            </a>
        </div>
        @endif

        {{-- Recent Payments --}}
        <div class="bg-white rounded-3 border mb-2">
            <div class="px-3 py-2 border-bottom" style="background:#f9fafb;">
                <span style="font-size:13px;font-weight:700;color:#111827;"><i class="fas fa-credit-card me-1" style="color:#f59e0b;"></i> Recent Payments</span>
            </div>
            <div>
                @if($recentPayments->count() > 0)
                    @foreach($recentPayments as $payment)
                    <div class="d-flex align-items-center justify-content-between px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:28px;height:28px;border-radius:6px;background:#d1fae5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <div style="font-size:0.8rem;font-weight:600;color:#111827;">{{ $payment->student->full_name ?? 'Unknown' }}</div>
                                <div style="font-size:0.7rem;color:#6b7280;">{{ $payment->payment_date?->format('M d, Y') ?? '-' }}</div>
                            </div>
                        </div>
                        <span style="font-size:0.8rem;font-weight:700;color:#059669;">{{ number_format($payment->amount_paid, 2) }}</span>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-inbox mb-1" style="font-size:1.5rem;opacity:0.3;display:block;"></i>
                        <span style="font-size:0.8rem;">No recent payments</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- System Info --}}
        @if(!$isBranchScoped && $isAdminOrGM)
        <div class="bg-white rounded-3 border">
            <div class="px-3 py-2 border-bottom" style="background:#f9fafb;">
                <span style="font-size:13px;font-weight:700;color:#111827;"><i class="fas fa-info-circle me-1" style="color:#0d9488;"></i> System Info</span>
            </div>
            <div class="p-2">
                <div class="d-flex justify-content-between py-1" style="font-size:0.78rem;">
                    <span class="text-muted">School</span><span style="font-weight:600;color:#111827;">School of Redemption</span>
                </div>
                <div class="d-flex justify-content-between py-1" style="font-size:0.78rem;">
                    <span class="text-muted">Framework</span><span style="font-weight:600;color:#111827;">Laravel 12</span>
                </div>
                <div class="d-flex justify-content-between py-1" style="font-size:0.78rem;">
                    <span class="text-muted">Branches</span><span style="font-weight:600;color:#111827;">{{ $totalBranches }}</span>
                </div>
                <div class="d-flex justify-content-between py-1" style="font-size:0.78rem;">
                    <span class="text-muted">Students</span><span style="font-weight:600;color:#111827;">{{ $totalStudents }}</span>
                </div>
                <div class="d-flex justify-content-between py-1" style="font-size:0.78rem;">
                    <span class="text-muted">Academic Year</span><span style="font-weight:600;color:#111827;">{{ $currentYear ? $currentYear->name : 'Not Set' }}</span>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
