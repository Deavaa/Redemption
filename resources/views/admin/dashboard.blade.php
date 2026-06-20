@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">

    {{-- ── Page Header ──────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 mt-2">
        <div>
            <h1 class="mb-1" style="font-size:1.6rem;font-weight:700;color:var(--color-text-dark);">
                <i class="fas fa-th-large text-primary me-2"></i>
                {{ __('app.welcome') }}, {{ Auth::user()?->name ?? "Guest" }}
            </h1>
            <p class="text-muted mb-0" style="font-size:0.85rem;">
                @if(isset($currentYear) && $currentYear)
                    <span class="badge bg-success-subtle text-success me-1">{{ $currentYear->name }}</span>
                @endif
                @if(isset($branchName) && $branchName)
                    <span class="badge bg-primary-subtle text-primary">{{ $branchName }}</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary">All Branches</span>
                @endif
                <span class="ms-2"><i class="far fa-calendar me-1"></i>{{ now()->format('l, F j, Y') }}</span>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if(in_array(Auth::user()?->role ?? "", ['admin', 'super_admin', 'general_manager', 'branch_principal', 'teacher']))
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus me-1"></i> Add Student
            </a>
            @endif
            @if(in_array(Auth::user()?->role ?? "", ['admin', 'super_admin', 'general_manager', 'branch_principal', 'teacher']))
            <a href="{{ route('admin.mark-entries.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-pen me-1"></i> Mark Entry
            </a>
            @endif
            <a href="{{ route('admin.profile') }}" class="btn btn-outline-secondary btn-sm" title="My Profile">
                <i class="fas fa-user-cog"></i>
            </a>
        </div>
    </div>

    {{-- ── Stat Cards ───────────────────────────────────────────── --}}
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.students.index') }}" class="card text-decoration-none h-100" style="color:inherit;border-left:3px solid #4361ee;">
                <div class="card-body p-2 d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:8px;background:#e0e7ff;color:#4361ee;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Students</div>
                        <div style="font-size:20px;font-weight:800;color:#1a1a2e;line-height:1.1;">{{ $totalStudents }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.teachers.index') }}" class="card text-decoration-none h-100" style="color:inherit;border-left:3px solid #10b981;">
                <div class="card-body p-2 d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:8px;background:#d1fae5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Teachers</div>
                        <div style="font-size:20px;font-weight:800;color:#1a1a2e;line-height:1.1;">{{ $totalTeachers }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.classrooms.index') }}" class="card text-decoration-none h-100" style="color:inherit;border-left:3px solid #0d9488;">
                <div class="card-body p-2 d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:8px;background:#ccfbf1;color:#0d9488;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Classes</div>
                        <div style="font-size:20px;font-weight:800;color:#1a1a2e;line-height:1.1;">{{ $totalClasses }}</div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.subjects.index') }}" class="card text-decoration-none h-100" style="color:inherit;border-left:3px solid #6366f1;">
                <div class="card-body p-2 d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:8px;background:#e0e7ff;color:#6366f1;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Subjects</div>
                        <div style="font-size:20px;font-weight:800;color:#1a1a2e;line-height:1.1;">{{ $totalSubjects }}</div>
                    </div>
                </div>
            </a>
        </div>
        @if(!$isBranchScoped)
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.branches.index') }}" class="card text-decoration-none h-100" style="color:inherit;border-left:3px solid #f59e0b;">
                <div class="card-body p-2 d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:8px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Branches</div>
                        <div style="font-size:20px;font-weight:800;color:#1a1a2e;line-height:1.1;">{{ $totalBranches }}</div>
                    </div>
                </div>
            </a>
        </div>
        @endif
        @if(in_array(Auth::user()?->role ?? "", ['admin', 'super_admin', 'general_manager', 'finance', 'cashier']))
        <div class="col-6 col-md-4 col-lg-2">
            <a href="{{ route('admin.fee-payments.index') }}" class="card text-decoration-none h-100" style="color:inherit;border-left:3px solid #22c55e;">
                <div class="card-body p-2 d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:8px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;">Fee Collected</div>
                        <div style="font-size:16px;font-weight:800;color:#1a1a2e;line-height:1.1;">{{ number_format($totalFeeCollected, 0) }}</div>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>

    {{-- ── Quick Actions ────────────────────────────────────────── --}}
    @php
        $isAdminOrGM = in_array(Auth::user()?->role ?? "", ['admin', 'super_admin', 'general_manager']);
        $isBranchPrincipal = Auth::user()?->role ?? "" === 'branch_principal';
        $isTeacher = Auth::user()?->role ?? "" === 'teacher';
    @endphp
    <div class="card mb-3">
        <div class="card-header py-2" style="background:#f8f9fa;">
            <h6 class="mb-0"><i class="fas fa-bolt text-warning me-1"></i> Quick Actions</h6>
        </div>
        <div class="card-body py-2">
            <div class="d-flex flex-wrap gap-2">
                @if($isAdminOrGM)
                <a href="{{ route('admin.branches.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Branch
                </a>
                @endif
                @if($isAdminOrGM || $isBranchPrincipal || $isTeacher)
                <a href="{{ route('admin.teachers.create') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Teacher
                </a>
                @endif
                @if($isAdminOrGM || $isBranchPrincipal)
                <a href="{{ route('admin.classrooms.create') }}" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Class
                </a>
                @endif
                @if($isAdminOrGM)
                <a href="{{ route('admin.subjects.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Subject
                </a>
                <a href="{{ route('admin.academic-years.create') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-plus me-1"></i> New Academic Year
                </a>
                <a href="{{ route('admin.terms.create') }}" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-plus me-1"></i> New Term
                </a>
                @endif
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-clipboard-check me-1"></i> Take Attendance
                </a>
            </div>
        </div>
    </div>

    {{-- ── Management Overview + Sidebar ─────────────────────────── --}}
    <div class="row g-3">
            {{-- Management Overview --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header py-2" style="background:#f8f9fa;">
                        <h6 class="mb-0"><i class="fas fa-th-large text-primary me-1"></i> Management Overview</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Module</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(!$isBranchScoped)
                                    <tr>
                                        <td><i class="fas fa-building text-primary me-2"></i>Branches</td>
                                        <td><strong>{{ $totalBranches }}</strong></td>
                                        <td>
                                            @if($totalBranches > 0)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Empty</span>
                                            @endif
                                        </td>
                                        <td><a href="{{ route('admin.branches.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td><i class="fas fa-chalkboard-teacher text-success me-2"></i>Teachers</td>
                                        <td><strong>{{ $totalTeachers }}</strong></td>
                                        <td>
                                            @if($totalTeachers > 0)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Empty</span>
                                            @endif
                                        </td>
                                        <td><a href="{{ route('admin.teachers.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-user-graduate text-warning me-2"></i>Students</td>
                                        <td><strong>{{ $totalStudents }}</strong></td>
                                        <td>
                                            @if($totalStudents > 0)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Empty</span>
                                            @endif
                                        </td>
                                        <td><a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-chalkboard text-info me-2"></i>Classes</td>
                                        <td><strong>{{ $totalClasses }}</strong></td>
                                        <td>
                                            @if($totalClasses > 0)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Empty</span>
                                            @endif
                                        </td>
                                        <td><a href="{{ route('admin.classrooms.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-book text-primary me-2"></i>Subjects</td>
                                        <td><strong>{{ $totalSubjects }}</strong></td>
                                        <td>
                                            @if($totalSubjects > 0)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Empty</span>
                                            @endif
                                        </td>
                                        <td><a href="{{ route('admin.subjects.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                                    </tr>
                                    <tr>
                                        <td><i class="fas fa-calendar-alt text-success me-2"></i>Academic Years</td>
                                        <td><strong>{{ \App\Models\AcademicYear::count() }}</strong></td>
                                        <td>
                                            @if($currentYear)
                                                <span class="badge bg-info-subtle text-info">{{ $currentYear->name }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">None Set</span>
                                            @endif
                                        </td>
                                        <td><a href="{{ route('admin.academic-years.index') }}" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Current Academic Year --}}
                @if($currentYear)
                <div class="card mb-3" style="border-left:3px solid #10b981;">
                    <div class="card-body p-3 text-center">
                        <i class="fas fa-calendar-check text-success mb-1" style="font-size:1.5rem;"></i>
                        <h5 class="mb-1" style="color:#10b981;font-weight:700;">{{ $currentYear->name }}</h5>
                        @if($currentYear->start_date)
                            <p class="text-muted mb-2" style="font-size:0.8rem;">{{ $currentYear->start_date }} &mdash; {{ $currentYear->end_date }}</p>
                        @endif
                        <a href="{{ route('admin.academic-years.index') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-eye me-1"></i> View All
                        </a>
                    </div>
                </div>
                @endif

                {{-- Recent Payments --}}
                <div class="card mb-3">
                    <div class="card-header py-2" style="background:#f8f9fa;">
                        <h6 class="mb-0"><i class="fas fa-credit-card text-warning me-1"></i> Recent Payments</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($recentPayments->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($recentPayments as $payment)
                                <li class="list-group-item d-flex align-items-center justify-content-between py-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:28px;height:28px;border-radius:6px;background:#d1fae5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:11px;flex-shrink:0;">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div>
                                            <div style="font-size:0.82rem;font-weight:600;">{{ $payment->student->full_name ?? 'Unknown' }}</div>
                                            <div style="font-size:0.7rem;color:#6b7280;">{{ $payment->payment_date?->format('M d, Y') ?? '-' }} &middot; {{ $payment->payment_method ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <span style="font-size:0.82rem;font-weight:700;color:#059669;">{{ number_format($payment->amount_paid, 2) }}</span>
                                </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox mb-2" style="font-size:1.8rem;display:block;opacity:0.4;"></i>
                                <p class="mb-0" style="font-size:0.82rem;">No recent payments</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- System Info --}}
                @if(!$isBranchScoped && in_array(Auth::user()?->role ?? "", ['admin', 'super_admin', 'general_manager']))
                <div class="card">
                    <div class="card-header py-2" style="background:#f8f9fa;">
                        <h6 class="mb-0"><i class="fas fa-info-circle text-info me-1"></i> System Info</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted" style="font-size:0.8rem;">School</td><td style="font-size:0.8rem;font-weight:600;">School of Redemption</td></tr>
                            <tr><td class="text-muted" style="font-size:0.8rem;">Framework</td><td style="font-size:0.8rem;font-weight:600;">Laravel 12</td></tr>
                            <tr><td class="text-muted" style="font-size:0.8rem;">Branches</td><td style="font-size:0.8rem;font-weight:600;">{{ $totalBranches }}</td></tr>
                            <tr><td class="text-muted" style="font-size:0.8rem;">Students</td><td style="font-size:0.8rem;font-weight:600;">{{ $totalStudents }}</td></tr>
                            <tr><td class="text-muted border-0" style="font-size:0.8rem;">Academic Year</td><td class="border-0" style="font-size:0.8rem;font-weight:600;">{{ $currentYear ? $currentYear->name : 'Not Set' }}</td></tr>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Modern Page Layout */
            .modern-page {
                animation: fadeSlideIn 0.4s ease-out;
            }

            @keyframes fadeSlideIn {
                from { opacity: 0; transform: translateY(12px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Clickable Stats Grid */
            .dash-stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
                margin-bottom: 1.25rem;
            }

            .dash-stat-card {
                display: flex;
                flex-direction: column;
                padding: 14px 16px;
                background: var(--card-bg, #fff);
                border-radius: 14px;
                border: 1px solid var(--border, #e5e7eb);
                text-decoration: none;
                color: inherit;
                transition: all 0.2s ease;
                cursor: pointer;
                position: relative;
                overflow: hidden;
            }

            .dash-stat-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.1);
                border-color: transparent;
            }

            .dash-stat-top {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }

            .dash-stat-icon {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 15px;
            }

            .dash-stat-arrow {
                font-size: 11px;
                opacity: 0;
                transition: opacity 0.2s, transform 0.2s;
                transform: translateX(-4px);
            }

            .dash-stat-card:hover .dash-stat-arrow {
                opacity: 0.6;
                transform: translateX(0);
            }

            .dash-stat-value {
                font-size: 1.5rem;
                font-weight: 800;
                line-height: 1.2;
            }

            .dash-stat-label {
                font-size: 0.72rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-top: 2px;
            }

            /* Color variants */
            .dash-stat-blue .dash-stat-icon { background: rgba(99,102,241,0.12); color: #6366f1; }
            .dash-stat-blue .dash-stat-value { color: #6366f1; }
            .dash-stat-blue .dash-stat-label { color: #818cf8; }
            .dash-stat-blue:hover { background: linear-gradient(135deg, rgba(99,102,241,0.08), rgba(99,102,241,0.03)); }

            .dash-stat-gold .dash-stat-icon { background: rgba(245,158,11,0.12); color: #f59e0b; }
            .dash-stat-gold .dash-stat-value { color: #f59e0b; }
            .dash-stat-gold .dash-stat-label { color: #fbbf24; }
            .dash-stat-gold:hover { background: linear-gradient(135deg, rgba(245,158,11,0.08), rgba(245,158,11,0.03)); }

            .dash-stat-green .dash-stat-icon { background: rgba(16,185,129,0.12); color: #10b981; }
            .dash-stat-green .dash-stat-value { color: #10b981; }
            .dash-stat-green .dash-stat-label { color: #34d399; }
            .dash-stat-green:hover { background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(16,185,129,0.03)); }

            .dash-stat-purple .dash-stat-icon { background: rgba(139,92,246,0.12); color: #8b5cf6; }
            .dash-stat-purple .dash-stat-value { color: #8b5cf6; }
            .dash-stat-purple .dash-stat-label { color: #a78bfa; }
            .dash-stat-purple:hover { background: linear-gradient(135deg, rgba(139,92,246,0.08), rgba(139,92,246,0.03)); }

            .dash-stat-teal .dash-stat-icon { background: rgba(20,184,166,0.12); color: #14b8a6; }
            .dash-stat-teal .dash-stat-value { color: #14b8a6; }
            .dash-stat-teal .dash-stat-label { color: #2dd4bf; }
            .dash-stat-teal:hover { background: linear-gradient(135deg, rgba(20,184,166,0.08), rgba(20,184,166,0.03)); }

            .dash-stat-rose .dash-stat-icon { background: rgba(244,63,94,0.12); color: #f43f5e; }
            .dash-stat-rose .dash-stat-value { color: #f43f5e; }
            .dash-stat-rose .dash-stat-label { color: #fb7185; }
            .dash-stat-rose:hover { background: linear-gradient(135deg, rgba(244,63,94,0.08), rgba(244,63,94,0.03)); }

            /* Dashboard Grid */
            .modern-dashboard-grid {
                display: grid;
                grid-template-columns: 1fr 320px;
                gap: 1.25rem;
            }

            /* Quick Actions */
            .modern-quick-actions {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
                gap: 10px;
            }

            .modern-quick-action-card {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 6px;
                padding: 12px 8px;
                border-radius: 12px;
                border: 1px solid var(--border, #e5e7eb);
                background: var(--card-bg, #fff);
                text-decoration: none;
                color: inherit;
                transition: all 0.2s;
            }

            .modern-quick-action-card:hover {
                border-color: var(--primary, #6366f1);
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            }

            .modern-quick-action-icon {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
            }

            .modern-quick-action-label {
                font-size: 0.72rem;
                font-weight: 600;
                text-align: center;
                color: var(--text-dark, #1f2937);
            }

            /* Activity List */
            .modern-activity-list {
                padding: 0;
            }

            .modern-activity-item {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 14px;
                border-bottom: 1px solid var(--border, #e5e7eb);
            }

            .modern-activity-item:last-child {
                border-bottom: none;
            }

            .modern-activity-icon {
                flex-shrink: 0;
            }

            .modern-activity-info {
                flex: 1;
                min-width: 0;
            }

            .modern-activity-title {
                display: block;
                font-size: 0.82rem;
                font-weight: 600;
                color: var(--text-dark, #1f2937);
            }

            .modern-activity-desc {
                display: block;
                font-size: 0.72rem;
                color: var(--text-muted, #9ca3af);
            }

            .modern-activity-amount {
                font-size: 0.85rem;
                font-weight: 700;
                color: #10b981;
            }

            /* Info List */
            .modern-info-list {
                padding: 0;
            }

            .modern-info-row {
                display: flex;
                justify-content: space-between;
                padding: 8px 0;
                border-bottom: 1px solid var(--border, #e5e7eb);
            }

            .modern-info-row:last-child {
                border-bottom: none;
            }

            .modern-info-label {
                font-size: 0.82rem;
                color: var(--text-muted, #9ca3af);
            }

            .modern-info-value {
                font-size: 0.82rem;
                font-weight: 600;
                color: var(--text-dark, #1f2937);
            }

            /* Table Module */
            .modern-table-module {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .modern-table-module-icon {
                width: 30px;
                height: 30px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
            }

            /* Responsive */
            @media (max-width: 1024px) {
                .dash-stats-grid { grid-template-columns: repeat(3, 1fr); }
                .modern-dashboard-grid { grid-template-columns: 1fr; }
            }

            @media (max-width: 768px) {
                .dash-stats-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
                .dash-stat-card { padding: 10px 12px; }
                .dash-stat-icon { width: 30px; height: 30px; font-size: 12px; }
                .dash-stat-value { font-size: 1.15rem; }
                .dash-stat-label { font-size: 0.65rem; }
                .dash-stat-arrow { display: none; }
                .modern-quick-actions { grid-template-columns: repeat(3, 1fr); }
            }

            @media (max-width: 480px) {
                .dash-stats-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; }
                .dash-stat-card { padding: 8px 10px; }
                .dash-stat-icon { width: 26px; height: 26px; font-size: 11px; border-radius: 6px; }
                .dash-stat-value { font-size: 1rem; }
                .dash-stat-label { font-size: 0.6rem; }
                .dash-stat-top { margin-bottom: 4px; }
                .modern-quick-actions { grid-template-columns: repeat(2, 1fr); }
            }
        </style>
    @endpush
@endsection
