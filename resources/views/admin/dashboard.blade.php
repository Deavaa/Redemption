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
            </div>
            <div class="modern-page-header-right">
                <a href="{{ route('admin.settings.index') }}" class="btn-modern btn-modern-outline" title="Settings">
                    <i class="fas fa-cog"></i>
                </a>
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
                        <div class="modern-form-section-icon modern-form-section-icon-blue"
                            style="width:36px;height:36px;border-radius:10px;font-size:0.95rem;">
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
                            <div class="modern-form-section-icon modern-form-section-icon-gold"
                                style="width:36px;height:36px;border-radius:10px;font-size:0.95rem;">
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
                                        <div class="modern-activity-icon modern-stat-icon-green"
                                            style="width:34px;height:34px;border-radius:8px;font-size:0.8rem;">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="modern-activity-info">
                                            <span class="modern-activity-title">{{ $payment->student->full_name ?? 'Unknown' }}</span>
                                            <span class="modern-activity-desc">{{ $payment->payment_date?->format('M d, Y') ?? '-' }}
                                                &middot; {{ $payment->payment_method ?? '-' }}</span>
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
                            <div class="modern-form-section-icon modern-form-section-icon-purple"
                                style="width:36px;height:36px;border-radius:10px;font-size:0.95rem;">
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
