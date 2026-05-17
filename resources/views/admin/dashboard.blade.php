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
        <div class="modern-page-header-right">
            <a href="{{ route('admin.settings.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-building"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalBranches }}</span>
                <span class="modern-stat-label">Branches</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalTeachers }}</span>
                <span class="modern-stat-label">Teachers</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalStudents }}</span>
                <span class="modern-stat-label">Students</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-purple">
                <i class="fas fa-chalkboard"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalClasses }}</span>
                <span class="modern-stat-label">Classes</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-book"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalSubjects }}</span>
                <span class="modern-stat-label">Subjects</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $unreadMessages }}</span>
                <span class="modern-stat-label">Unread Messages</span>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="modern-card" style="margin-bottom: 1.5rem;">
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
                <div class="modern-card-body" style="text-align:center;padding:1.5rem;">
                    <h4 style="color:#10b981;font-weight:800;margin:0 0 0.35rem;font-size:1.35rem;">{{ $currentYear->name }}</h4>
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
                            <span class="modern-info-value">Laravel 11</span>
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
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }
.modern-page-header-right { display: flex; gap: 0.75rem; flex-wrap: wrap; }

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

/* Stats Row */
.modern-stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.75rem;
}

.modern-stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    transition: transform 0.2s, box-shadow 0.2s;
}

.modern-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.modern-stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.modern-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-stat-icon-green { background: #ecfdf5; color: #10b981; }
.modern-stat-icon-gold { background: #fefce8; color: #d97706; }
.modern-stat-icon-purple { background: #f5f3ff; color: #7c3aed; }

.modern-stat-info { display: flex; flex-direction: column; }

.modern-stat-value {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.2;
}

.modern-stat-label {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
}

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
    gap: 1.5rem;
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

/* Responsive */
@media (max-width: 1024px) {
    .modern-dashboard-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-page-title { font-size: 1.35rem; }
    .modern-stats-row { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
    .modern-stat-card { padding: 1rem; gap: 0.75rem; }
    .modern-stat-icon { width: 42px; height: 42px; font-size: 1.1rem; }
    .modern-stat-value { font-size: 1.3rem; }
    .modern-quick-actions { flex-wrap: wrap; gap: 0.5rem; }
    .modern-quick-action-card { padding: 0.6rem 0.8rem; }
    .modern-quick-action-label { font-size: 0.78rem; }
    .modern-table thead th, .modern-table tbody td { padding: 0.65rem 0.75rem; font-size: 0.82rem; }
    .modern-activity-item { padding: 0.6rem 1rem; }
    .modern-card-body { padding: 1rem; }
    .btn-modern { min-height: 44px; }
    .modern-page-header-right { width: 100%; }
    .modern-page-header-right .btn-modern { flex: 1; justify-content: center; }
}

@media (max-width: 480px) {
    .modern-stats-row { grid-template-columns: 1fr 1fr; gap: 0.5rem; }
    .modern-stat-card { padding: 0.85rem; gap: 0.6rem; }
    .modern-stat-icon { width: 38px; height: 38px; font-size: 1rem; border-radius: 10px; }
    .modern-stat-value { font-size: 1.15rem; }
    .modern-stat-label { font-size: 0.72rem; }
    .modern-quick-action-label { display: none; }
    .modern-quick-action-card { padding: 0.5rem; justify-content: center; }
    .modern-card-header { padding: 0.85rem 1rem; }
    .modern-card-body { padding: 0.85rem; }
}
</style>
@endpush
@endsection