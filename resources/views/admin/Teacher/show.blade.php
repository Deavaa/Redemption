@extends('layouts.admin')
@section('title', 'Teacher Details')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.teachers.index') }}">Teachers</a></li>
                    <li class="active">{{ $data->full_name }}</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teachers.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
            <a href="{{ route('admin.teachers.edit', $data->id) }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-pen"></i>
                <span>Edit</span>
            </a>
        </div>
    </div>

    <div class="modern-detail-grid">
        {{-- Main Info Card --}}
        <div class="modern-card modern-detail-main">
            {{-- Hero Section --}}
            <div class="modern-detail-hero">
                <div class="modern-detail-hero-avatar">
                    {{ strtoupper(substr($data->full_name ?? 'NA', 0, 1)) }}{{ strtoupper(substr(preg_replace('/^[^ ]+\\s*/', '', $data->full_name ?? 'A'), 0, 1)) }}
                </div>
                <div class="modern-detail-hero-info">
                    <h2 class="modern-detail-hero-title">{{ $data->full_name }}</h2>
                    <div class="modern-detail-hero-badges">
                        @if($data->status === 'active')
                            <span class="modern-badge modern-badge-success"><i class="fas fa-check-circle"></i> Active</span>
                        @elseif($data->status === 'on_leave')
                            <span class="modern-badge modern-badge-warning"><i class="fas fa-clock"></i> On Leave</span>
                        @else
                            <span class="modern-badge modern-badge-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                        @if($data->department)
                            <span class="modern-badge modern-badge-department"><i class="fas fa-building"></i> {{ $data->department }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Personal Info --}}
            <div class="modern-detail-section">
                <div class="modern-detail-section-title">
                    <i class="fas fa-user"></i> Personal Information
                </div>
                <div class="modern-detail-body">
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-id-card"></i> Full Name
                        </div>
                        <div class="modern-detail-value">{{ $data->full_name }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-envelope"></i> Email
                        </div>
                        <div class="modern-detail-value">
                            @if($data->email)
                                <a href="mailto:{{ $data->email }}" class="modern-link">{{ $data->email }}</a>
                            @else
                                <span class="modern-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-phone"></i> Phone
                        </div>
                        <div class="modern-detail-value">
                            @if($data->phone)
                                <a href="tel:{{ $data->phone }}" class="modern-link">{{ $data->phone }}</a>
                            @else
                                <span class="modern-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-map-marker-alt"></i> Branch
                        </div>
                        <div class="modern-detail-value">
                            @if($data->branch)
                                <span style="background:#eff6ff;color:#2563eb;padding:0.15rem 0.6rem;border-radius:50px;font-size:0.78rem;font-weight:600;">{{ $data->branch->name }}</span>
                            @else
                                <span class="modern-muted">Not assigned</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Professional Info --}}
            <div class="modern-detail-section">
                <div class="modern-detail-section-title">
                    <i class="fas fa-graduation-cap"></i> Professional Information
                </div>
                <div class="modern-detail-body">
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-award"></i> Qualification
                        </div>
                        <div class="modern-detail-value">{{ $data->qualification ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-building"></i> Department
                        </div>
                        <div class="modern-detail-value">{{ $data->department ?? '-' }}</div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-calendar-alt"></i> Hire Date
                        </div>
                        <div class="modern-detail-value">
                            @if($data->hire_date)
                                {{ \Carbon\Carbon::parse($data->hire_date)->format('M d, Y') }}
                            @else
                                <span class="modern-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-dollar-sign"></i> Salary
                        </div>
                        <div class="modern-detail-value">
                            @if($data->salary)
                                <span class="modern-salary">{{ number_format($data->salary, 2) }}</span>
                            @else
                                <span class="modern-muted">-</span>
                            @endif
                        </div>
                    </div>
                    <div class="modern-detail-row">
                        <div class="modern-detail-label">
                            <i class="fas fa-toggle-on"></i> Status
                        </div>
                        <div class="modern-detail-value">
                            @if($data->status === 'active')
                                <span class="modern-badge modern-badge-success">Active</span>
                            @elseif($data->status === 'on_leave')
                                <span class="modern-badge modern-badge-warning">On Leave</span>
                            @else
                                <span class="modern-badge modern-badge-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar Card --}}
        <div class="modern-detail-sidebar">
            {{-- Quick Actions Card --}}
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <i class="fas fa-bolt"></i> Quick Actions
                </div>
                <div class="modern-quick-actions">
                    <a href="{{ route('admin.teachers.edit', $data->id) }}" class="modern-quick-action">
                        <i class="fas fa-pen"></i>
                        <span>Edit Teacher</span>
                    </a>
                    @if($data->phone)
                    <a href="tel:{{ $data->phone }}" class="modern-quick-action">
                        <i class="fas fa-phone"></i>
                        <span>Call Teacher</span>
                    </a>
                    @endif
                    @if($data->email)
                    <a href="mailto:{{ $data->email }}" class="modern-quick-action">
                        <i class="fas fa-envelope"></i>
                        <span>Send Email</span>
                    </a>
                    @endif
                    <form method="POST" action="{{ route('admin.teachers.destroy', $data->id) }}" onsubmit="return confirm('Are you sure you want to delete this teacher? This action cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="modern-quick-action modern-quick-action-danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>Delete Teacher</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Timestamps Card --}}
            <div class="modern-card">
                <div class="modern-card-header-simple">
                    <i class="fas fa-clock"></i> Timestamps
                </div>
                <div class="modern-timestamps">
                    <div class="modern-timestamp">
                        <span class="modern-timestamp-label">Created</span>
                        <span class="modern-timestamp-value">{{ $data->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="modern-timestamp">
                        <span class="modern-timestamp-label">Updated</span>
                        <span class="modern-timestamp-value">{{ $data->updated_at->format('M d, Y H:i') }}</span>
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

.modern-page-header-right {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
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

/* Detail Grid */
.modern-detail-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 1.25rem;
    align-items: start;
}

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Detail Hero */
.modern-detail-hero {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.75rem 2rem;
    background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
    border-bottom: 1px solid #e5e8ff;
}

.modern-detail-hero-avatar {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.modern-detail-hero-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 0.5rem;
}

.modern-detail-hero-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

/* Badges */
.modern-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.65rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}

.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-warning { background: #fffbeb; color: #d97706; }
.modern-badge-department { background: #f3e8ff; color: #7c3aed; }

/* Detail Section */
.modern-detail-section {
    border-bottom: 1px solid #f0f0f0;
}

.modern-detail-section:last-child {
    border-bottom: none;
}

.modern-detail-section-title {
    padding: 1rem 2rem 0.5rem;
    font-weight: 700;
    color: #1a1a2e;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-detail-section-title i {
    color: #4361ee;
    font-size: 0.85rem;
}

/* Detail Body */
.modern-detail-body { padding: 0.5rem 0; }

.modern-detail-row {
    display: flex;
    padding: 0.9rem 2rem;
    border-bottom: 1px solid #f8f9fa;
    transition: background 0.15s;
}

.modern-detail-row:last-child { border-bottom: none; }
.modern-detail-row:hover { background: #fafbfc; }

.modern-detail-label {
    width: 180px;
    flex-shrink: 0;
    font-weight: 600;
    color: #6b7280;
    font-size: 0.88rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-detail-label i { color: #9ca3af; font-size: 0.82rem; width: 16px; text-align: center; }

.modern-detail-value {
    color: #1a1a2e;
    font-size: 0.9rem;
}

.modern-link { color: #4361ee; text-decoration: none; font-weight: 500; }
.modern-link:hover { text-decoration: underline; }

.modern-muted { color: #d1d5db; }

.modern-salary {
    font-weight: 700;
    color: #059669;
    font-size: 0.95rem;
}

/* Sidebar */
.modern-detail-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.modern-card-header-simple {
    padding: 1rem 1.25rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modern-card-header-simple i { color: #4361ee; font-size: 0.85rem; }

/* Quick Actions */
.modern-quick-actions {
    padding: 0.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
}

.modern-quick-action {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.7rem 0.85rem;
    border-radius: 10px;
    color: #374151;
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 500;
    transition: all 0.15s;
    border: none;
    background: none;
    width: 100%;
    cursor: pointer;
    text-align: left;
}

.modern-quick-action i { color: #6b7280; width: 18px; text-align: center; }

.modern-quick-action:hover {
    background: #f3f4f6;
    color: #1a1a2e;
}

.modern-quick-action:hover i { color: #4361ee; }

.modern-quick-action-danger { color: #dc2626; }
.modern-quick-action-danger i { color: #f87171; }
.modern-quick-action-danger:hover { background: #fef2f2; color: #b91c1c; }
.modern-quick-action-danger:hover i { color: #dc2626; }

/* Timestamps */
.modern-timestamps { padding: 0.85rem 1.25rem; }

.modern-timestamp {
    display: flex;
    justify-content: space-between;
    padding: 0.45rem 0;
}

.modern-timestamp + .modern-timestamp { border-top: 1px solid #f3f4f6; }

.modern-timestamp-label { color: #9ca3af; font-size: 0.82rem; }
.modern-timestamp-value { color: #374151; font-size: 0.82rem; font-weight: 500; }

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
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
}

/* Responsive */
@media (max-width: 992px) {
    .modern-detail-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-detail-hero { padding: 1.25rem; flex-direction: column; text-align: center; }
    .modern-detail-hero-badges { justify-content: center; }
    .modern-detail-row { flex-direction: column; gap: 0.25rem; padding: 0.75rem 1.25rem; }
    .modern-detail-label { width: auto; }
    .modern-detail-section-title { padding: 1rem 1.25rem 0.5rem; }
}
</style>
@endpush
@endsection
