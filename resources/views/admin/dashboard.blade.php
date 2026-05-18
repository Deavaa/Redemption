@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li class="active"><i class="fas fa-home"></i> Dashboard</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Dashboard</h1>
            <p class="modern-page-subtitle">Welcome back! Here's an overview of your school management system.</p>
        </div>
    </div>

    {{-- Compact Clickable Stats Grid --}}
    <div class="dash-stats-grid">
        <a href="{{ route('admin.branches.index') }}" class="dash-stat-card dash-stat-blue">
            <div class="dash-stat-top">
                <div class="dash-stat-icon"><i class="fas fa-building"></i></div>
                <div class="dash-stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="dash-stat-value">{{ $totalBranches }}</div>
            <div class="dash-stat-label">Branches</div>
        </a>
        <a href="{{ route('admin.students.index') }}" class="dash-stat-card dash-stat-gold">
            <div class="dash-stat-top">
                <div class="dash-stat-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="dash-stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="dash-stat-value">{{ $totalStudents }}</div>
            <div class="dash-stat-label">Students</div>
        </a>
        <a href="{{ route('admin.teachers.index') }}" class="dash-stat-card dash-stat-green">
            <div class="dash-stat-top">
                <div class="dash-stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="dash-stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="dash-stat-value">{{ $totalTeachers }}</div>
            <div class="dash-stat-label">Teachers</div>
        </a>
        <a href="{{ route('admin.staff.index') }}" class="dash-stat-card dash-stat-purple">
            <div class="dash-stat-top">
                <div class="dash-stat-icon"><i class="fas fa-id-badge"></i></div>
                <div class="dash-stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="dash-stat-value">{{ $totalStaff }}</div>
            <div class="dash-stat-label">Staff</div>
        </a>
        <a href="{{ route('admin.classrooms.index') }}" class="dash-stat-card dash-stat-teal">
            <div class="dash-stat-top">
                <div class="dash-stat-icon"><i class="fas fa-chalkboard"></i></div>
                <div class="dash-stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="dash-stat-value">{{ $totalClasses }}</div>
            <div class="dash-stat-label">Classes</div>
        </a>
        <a href="{{ route('admin.subjects.index') }}" class="dash-stat-card dash-stat-blue">
            <div class="dash-stat-top">
                <div class="dash-stat-icon"><i class="fas fa-book"></i></div>
                <div class="dash-stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="dash-stat-value">{{ $totalSubjects }}</div>
            <div class="dash-stat-label">Subjects</div>
        </a>
        <a href="{{ route('admin.fee-payments.index') }}" class="dash-stat-card dash-stat-green">
            <div class="dash-stat-top">
                <div class="dash-stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="dash-stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="dash-stat-value" style="font-size:1.15rem;">{{ number_format($totalFeeCollected, 0) }}</div>
            <div class="dash-stat-label">Fee Collected</div>
        </a>
        <a href="{{ route('admin.fees.index') }}" class="dash-stat-card dash-stat-rose">
            <div class="dash-stat-top">
                <div class="dash-stat-icon"><i class="fas fa-exclamation-circle"></i></div>
                <div class="dash-stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="dash-stat-value" style="font-size:1.15rem;">{{ number_format($pendingFees, 0) }}</div>
            <div class="dash-stat-label">Pending Fees</div>
        </a>
        <a href="{{ route('admin.chat.index') }}" class="dash-stat-card dash-stat-gold">
            <div class="dash-stat-top">
                <div class="dash-stat-icon"><i class="fas fa-envelope"></i></div>
                <div class="dash-stat-arrow"><i class="fas fa-arrow-right"></i></div>
            </div>
            <div class="dash-stat-value">{{ $unreadMessages }}</div>
            <div class="dash-stat-label">Unread Messages</div>
        </a>
    </div>

    {{-- Quick Actions --}}
    <div class="modern-card" style="margin-bottom: 1.25rem;">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <div class="modern-form-section-icon modern-form-section-icon-gold" style="width:36px;height:36px;border-radius:10px;font-size:0.95rem;">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="modern-card-title">Quick Actions</h3>
            </div>
        </div>
        <div class="modern-card-body">
            <div class="modern-quick-actions">
                <a href="{{ route('admin.branches.create') }}" class="modern-quick-action-card">
                    <div class="modern-quick-action-icon modern-stat-icon-blue"><i class="fas fa-plus"></i></div>
                    <span class="modern-quick-action-label">Add Branch</span>
                </a>
                <a href="{{ route('admin.teachers.create') }}" class="modern-quick-action-card">
                    <div class="modern-quick-action-icon modern-stat-icon-green"><i class="fas fa-plus"></i></div>
                    <span class="modern-quick-action-label">Add Teacher</span>
                </a>
                <a href="{{ route('admin.students.create') }}" class="modern-quick-action-card">
                    <div class="modern-quick-action-icon modern-stat-icon-gold"><i class="fas fa-plus"></i></div>
                    <span class="modern-quick-action-label">Add Student</span>
                </a>
                <a href="{{ route('admin.classrooms.create') }}" class="modern-quick-action-card">
                    <div class="modern-quick-action-icon modern-stat-icon-purple"><i class="fas fa-plus"></i></div>
                    <span class="modern-quick-action-label">Add Class</span>
                </a>
                <a href="{{ route('admin.subjects.create') }}" class="modern-quick-action-card">
                    <div class="modern-quick-action-icon modern-stat-icon-blue"><i class="fas fa-plus"></i></div>
                    <span class="modern-quick-action-label">Add Subject</span>
                </a>
                <a href="{{ route('admin.academic-years.create') }}" class="modern-quick-action-card">
                    <div class="modern-quick-action-icon modern-stat-icon-green"><i class="fas fa-plus"></i></div>
                    <span class="modern-quick-action-label">New Academic Year</span>
                </a>
                <a href="{{ route('admin.terms.create') }}" class="modern-quick-action-card">
                    <div class="modern-quick-action-icon modern-stat-icon-gold"><i class="fas fa-plus"></i></div>
                    <span class="modern-quick-action-label">New Term</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Main Content Row --}}
    <div class="modern-dashboard-grid">
        {{-- Management Overview --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <div class="modern-card-header-left">
                    <div class="modern-form-section-icon modern-form-section-icon-blue" style="width:36px;height:36px;border-radius:10px;font-size:0.95rem;">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <h3 class="modern-card-title">Management Overview</h3>
                </div>
            </div>
            <div class="modern-card-body" style="padding:0;">
                <div class="modern-table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="modern-table-module">
                                        <div class="modern-table-module-icon modern-stat-icon-blue"><i class="fas fa-building"></i></div>
                                        <span>Branches</span>
                                    </div>
                                </td>
                                <td><strong>{{ $totalBranches }}</strong></td>
                                <td>
                                    @if($totalBranches > 0)
                                        <span class="modern-badge modern-badge-success">Active</span>
                                    @else
                                        <span class="modern-badge modern-badge-light">Empty</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('admin.branches.index') }}" class="btn-modern btn-modern-ghost" style="padding:0.35rem 0.75rem;font-size:0.8rem;">Manage</a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="modern-table-module">
                                        <div class="modern-table-module-icon modern-stat-icon-green"><i class="fas fa-chalkboard-teacher"></i></div>
                                        <span>Teachers</span>
                                    </div>
                                </td>
                                <td><strong>{{ $totalTeachers }}</strong></td>
                                <td>
                                    @if($totalTeachers > 0)
                                        <span class="modern-badge modern-badge-success">Active</span>
                                    @else
                                        <span class="modern-badge modern-badge-light">Empty</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('admin.teachers.index') }}" class="btn-modern btn-modern-ghost" style="padding:0.35rem 0.75rem;font-size:0.8rem;">Manage</a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="modern-table-module">
                                        <div class="modern-table-module-icon modern-stat-icon-gold"><i class="fas fa-user-graduate"></i></div>
                                        <span>Students</span>
                                    </div>
                                </td>
                                <td><strong>{{ $totalStudents }}</strong></td>
                                <td>
                                    @if($totalStudents > 0)
                                        <span class="modern-badge modern-badge-success">Active</span>
                                    @else
                                        <span class="modern-badge modern-badge-light">Empty</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('admin.students.index') }}" class="btn-modern btn-modern-ghost" style="padding:0.35rem 0.75rem;font-size:0.8rem;">Manage</a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="modern-table-module">
                                        <div class="modern-table-module-icon modern-stat-icon-purple"><i class="fas fa-chalkboard"></i></div>
                                        <span>Classes</span>
                                    </div>
                                </td>
                                <td><strong>{{ $totalClasses }}</strong></td>
                                <td>
                                    @if($totalClasses > 0)
                                        <span class="modern-badge modern-badge-success">Active</span>
                                    @else
                                        <span class="modern-badge modern-badge-light">Empty</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('admin.classrooms.index') }}" class="btn-modern btn-modern-ghost" style="padding:0.35rem 0.75rem;font-size:0.8rem;">Manage</a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="modern-table-module">
                                        <div class="modern-table-module-icon modern-stat-icon-blue"><i class="fas fa-book"></i></div>
                                        <span>Subjects</span>
                                    </div>
                                </td>
                                <td><strong>{{ $totalSubjects }}</strong></td>
                                <td>
                                    @if($totalSubjects > 0)
                                        <span class="modern-badge modern-badge-success">Active</span>
                                    @else
                                        <span class="modern-badge modern-badge-light">Empty</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('admin.subjects.index') }}" class="btn-modern btn-modern-ghost" style="padding:0.35rem 0.75rem;font-size:0.8rem;">Manage</a></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="modern-table-module">
                                        <div class="modern-table-module-icon modern-stat-icon-green"><i class="fas fa-calendar-alt"></i></div>
                                        <span>Academic Years</span>
                                    </div>
                                </td>
                                <td><strong>{{ \App\Models\AcademicYear::count() }}</strong></td>
                                <td>
                                    @if($currentYear)
                                        <span class="modern-badge modern-badge-info">{{ $currentYear->name }}</span>
                                    @else
                                        <span class="modern-badge modern-badge-danger">None Set</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('admin.academic-years.index') }}" class="btn-modern btn-modern-ghost" style="padding:0.35rem 0.75rem;font-size:0.8rem;">Manage</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="modern-dashboard-sidebar">
            {{-- Current Academic Year --}}
            @if($currentYear)
            <div class="modern-card" style="margin-bottom: 1.25rem;">
                <div class="modern-card-header" style="background: linear-gradient(135deg, #10b981, #059669); border-radius: 14px 14px 0 0;">
                    <div class="modern-card-header-left">
                        <i class="fas fa-calendar-check" style="color:#fff;font-size:1.1rem;"></i>
                        <h3 class="modern-card-title" style="color:#fff;">Current Academic Year</h3>
                    </div>
                </div>
                <div class="modern-card-body" style="text-align:center;padding:1.25rem;">
                    <h4 style="color:#10b981;font-weight:800;margin:0 0 0.35rem;font-size:1.25rem;">{{ $currentYear->name }}</h4>
                    @if($currentYear->start_date)
                        <p style="color:#6c757d;margin:0 0 0.75rem;font-size:0.85rem;">{{ $currentYear->start_date }} &mdash; {{ $currentYear->end_date }}</p>
                    @endif
                    <a href="{{ route('admin.academic-years.index') }}" class="btn-modern btn-modern-outline" style="font-size:0.82rem;padding:0.4rem 1rem;">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
            </div>
            @endif

            {{-- Recent Payments --}}
            <div class="modern-card" style="margin-bottom: 1.25rem;">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <div class="modern-form-section-icon modern-form-section-icon-gold" style="width:36px;height:36px;border-radius:10px;font-size:0.95rem;">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3 class="modern-card-title">Recent Payments</h3>
                    </div>
                </div>
                <div class="modern-card-body" style="padding:0;">
                    @if($recentPayments->count() > 0)
                    <div class="modern-activity-list">
                        @foreach($recentPayments as $payment)
                        <div class="modern-activity-item">
                            <div class="modern-activity-icon modern-stat-icon-green" style="width:34px;height:34px;border-radius:8px;font-size:0.8rem;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="modern-activity-info">
                                <span class="modern-activity-title">{{ $payment->student->first_name ?? 'Unknown' }} {{ $payment->student->last_name ?? '' }}</span>
                                <span class="modern-activity-desc">{{ $payment->payment_date?->format('M d, Y') ?? '-' }} &middot; {{ $payment->payment_method ?? '-' }}</span>
                            </div>
                            <span class="modern-activity-amount">{{ number_format($payment->amount_paid, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div style="text-align:center;padding:2rem 1rem;color:#9ca3af;">
                        <i class="fas fa-inbox" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>
                        <p style="margin:0;font-size:0.88rem;">No recent payments</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- System Info --}}
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="modern-card-header-left">
                        <div class="modern-form-section-icon modern-form-section-icon-purple" style="width:36px;height:36px;border-radius:10px;font-size:0.95rem;">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h3 class="modern-card-title">System Info</h3>
                    </div>
                </div>
                <div class="modern-card-body">
                    <div class="modern-info-list">
                        <div class="modern-info-row">
                            <span class="modern-info-label">School</span>
                            <span class="modern-info-value">School of Redemption</span>
                        </div>
                        <div class="modern-info-row">
                            <span class="modern-info-label">Framework</span>
                            <span class="modern-info-value">Laravel 12</span>
                        </div>
                        <div class="modern-info-row">
                            <span class="modern-info-label">Branches</span>
                            <span class="modern-info-value">{{ $totalBranches }}</span>
                        </div>
                        <div class="modern-info-row">
                            <span class="modern-info-label">Students</span>
                            <span class="modern-info-value">{{ $totalStudents }}</span>
                        </div>
                        <div class="modern-info-row" style="border:none;">
                            <span class="modern-info-label">Academic Year</span>
                            <span class="modern-info-value">{{ $currentYear ? $currentYear->name : 'Not Set' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }

.modern-page-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0;
    letter-spacing: -0.5px;
}

.modern-page-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0.25rem 0 0;
}

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem;
    gap: 0.5rem;
    font-size: 0.8rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* ===== Compact Clickable Stat Cards ===== */
.dash-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.dash-stat-card {
    display: flex;
    flex-direction: column;
    padding: 0.9rem 1rem;
    border-radius: 12px;
    background: #fff;
    border: 1.5px solid #f0f0f0;
    text-decoration: none;
    transition: all 0.25s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.dash-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 14px 14px 0 0;
    opacity: 0;
    transition: opacity 0.25s;
}

.dash-stat-card:hover::before { opacity: 1; }

.dash-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    border-color: transparent;
}

.dash-stat-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.dash-stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
}

.dash-stat-arrow {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    opacity: 0;
    transform: translateX(-6px);
    transition: all 0.25s;
}

.dash-stat-card:hover .dash-stat-arrow {
    opacity: 1;
    transform: translateX(0);
}

.dash-stat-value {
    font-size: 1.4rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.2;
    margin-bottom: 0.1rem;
}

.dash-stat-label {
    font-size: 0.78rem;
    color: #6c757d;
    font-weight: 500;
}

/* Blue variant */
.dash-stat-blue .dash-stat-icon { background: #eef2ff; color: #4361ee; }
.dash-stat-blue .dash-stat-arrow { background: #eef2ff; color: #4361ee; }
.dash-stat-blue::before { background: linear-gradient(90deg, #4361ee, #6366f1); }
.dash-stat-blue:hover { border-color: rgba(67,97,238,0.15); }

/* Green variant */
.dash-stat-green .dash-stat-icon { background: #ecfdf5; color: #10b981; }
.dash-stat-green .dash-stat-arrow { background: #ecfdf5; color: #10b981; }
.dash-stat-green::before { background: linear-gradient(90deg, #10b981, #34d399); }
.dash-stat-green:hover { border-color: rgba(16,185,129,0.15); }

/* Gold variant */
.dash-stat-gold .dash-stat-icon { background: #fefce8; color: #d97706; }
.dash-stat-gold .dash-stat-arrow { background: #fefce8; color: #d97706; }
.dash-stat-gold::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.dash-stat-gold:hover { border-color: rgba(245,158,11,0.15); }

/* Purple variant */
.dash-stat-purple .dash-stat-icon { background: #f5f3ff; color: #7c3aed; }
.dash-stat-purple .dash-stat-arrow { background: #f5f3ff; color: #7c3aed; }
.dash-stat-purple::before { background: linear-gradient(90deg, #7c3aed, #a78bfa); }
.dash-stat-purple:hover { border-color: rgba(124,58,237,0.15); }

/* Teal variant */
.dash-stat-teal .dash-stat-icon { background: #f0fdfa; color: #14b8a6; }
.dash-stat-teal .dash-stat-arrow { background: #f0fdfa; color: #14b8a6; }
.dash-stat-teal::before { background: linear-gradient(90deg, #14b8a6, #5eead4); }
.dash-stat-teal:hover { border-color: rgba(20,184,166,0.15); }

/* Rose variant */
.dash-stat-rose .dash-stat-icon { background: #fff1f2; color: #f43f5e; }
.dash-stat-rose .dash-stat-arrow { background: #fff1f2; color: #f43f5e; }
.dash-stat-rose::before { background: linear-gradient(90deg, #f43f5e, #fb7185); }
.dash-stat-rose:hover { border-color: rgba(244,63,94,0.15); }

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

.modern-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
}

.modern-card-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.modern-card-header-right {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-card-body { padding: 1.5rem; }

/* Form Section Icon (reused for card headers) */
.modern-form-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }
.modern-form-section-icon-gold { background: #fefce8; color: #d97706; }
.modern-form-section-icon-purple { background: #f5f3ff; color: #7c3aed; }

/* Quick Actions */
.modern-quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.modern-quick-action-card {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.6rem 1.1rem;
    background: #f9fafb;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.25s;
    cursor: pointer;
}

.modern-quick-action-card:hover {
    border-color: #4361ee;
    background: #eef2ff;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(67,97,238,0.12);
}

.modern-quick-action-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.modern-quick-action-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
}

.modern-quick-action-card:hover .modern-quick-action-label {
    color: #4361ee;
}

/* Dashboard Grid */
.modern-dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 1.25rem;
}

.modern-dashboard-sidebar {
    display: flex;
    flex-direction: column;
}

/* Table */
.modern-table-wrapper { overflow-x: auto; }

.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead th {
    background: #f9fafb;
    padding: 0.75rem 1.25rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.modern-table tbody td {
    padding: 0.85rem 1.25rem;
    font-size: 0.88rem;
    color: #374151;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}

.modern-table tbody tr:last-child td { border-bottom: none; }
.modern-table tbody tr:hover { background: #f9fafb; }

.modern-table-module {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}

.modern-table-module-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
}

/* Badge */
.modern-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.4;
}

.modern-badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-gold { background: #fefce8; color: #d97706; }
.modern-badge-info { background: #eef2ff; color: #4361ee; }

/* Activity List */
.modern-activity-list { max-height: 280px; overflow-y: auto; }

.modern-activity-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.15s;
}

.modern-activity-item:last-child { border-bottom: none; }
.modern-activity-item:hover { background: #f9fafb; }

.modern-activity-info { flex: 1; min-width: 0; }

.modern-activity-title {
    display: block;
    font-weight: 600;
    color: #374151;
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.modern-activity-desc {
    display: block;
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 0.1rem;
}

.modern-activity-amount {
    font-weight: 700;
    color: #10b981;
    font-size: 0.88rem;
    flex-shrink: 0;
}

/* Info List */
.modern-info-list { display: flex; flex-direction: column; }

.modern-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.55rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.modern-info-row:last-child { border-bottom: none; }

.modern-info-label {
    font-size: 0.82rem;
    color: #9ca3af;
}

.modern-info-value {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
}

/* Modern Button */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-outline {
    background: #fff;
    color: #4361ee;
    border: 1.5px solid #4361ee;
}

.btn-modern-outline:hover {
    background: #4361ee;
    color: #fff;
    transform: translateY(-1px);
}

.btn-modern-ghost {
    background: transparent;
    color: #6b7280;
    padding: 0.65rem 1rem;
}

.btn-modern-ghost:hover {
    color: #4361ee;
    background: #eef2ff;
}

/* Stat Icon colors reused */
.modern-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-stat-icon-green { background: #ecfdf5; color: #10b981; }
.modern-stat-icon-gold { background: #fefce8; color: #d97706; }
.modern-stat-icon-purple { background: #f5f3ff; color: #7c3aed; }

/* ===== Responsive ===== */
@media (max-width: 1024px) {
    .modern-dashboard-grid {
        grid-template-columns: 1fr;
    }
    .dash-stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-page-title { font-size: 1.35rem; }
    .dash-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem;
    }
    .dash-stat-card { padding: 0.9rem 1rem; }
    .dash-stat-icon { width: 36px; height: 36px; font-size: 0.95rem; }
    .dash-stat-value { font-size: 1.35rem; }
    .dash-stat-label { font-size: 0.72rem; }
    .modern-quick-actions { flex-wrap: wrap; gap: 0.5rem; }
    .modern-quick-action-card { padding: 0.6rem 0.8rem; }
    .modern-quick-action-label { font-size: 0.78rem; }
    .modern-table thead th, .modern-table tbody td { padding: 0.65rem 0.75rem; font-size: 0.82rem; }
    .modern-activity-item { padding: 0.6rem 1rem; }
    .modern-card-body { padding: 1rem; }
    .btn-modern { min-height: 44px; }
}

@media (max-width: 480px) {
    .dash-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }
    .dash-stat-card { padding: 0.8rem; }
    .dash-stat-icon { width: 34px; height: 34px; border-radius: 9px; font-size: 0.9rem; }
    .dash-stat-value { font-size: 1.2rem; }
    .dash-stat-label { font-size: 0.7rem; }
    .dash-stat-arrow { display: none; }
    .modern-quick-action-label { display: none; }
    .modern-quick-action-card { padding: 0.5rem; justify-content: center; }
    .modern-card-header { padding: 0.85rem 1rem; }
    .modern-card-body { padding: 0.85rem; }
}
</style>
@endpush
@endsection