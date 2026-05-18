@extends('layouts.admin')
@section('title', 'Student Management')

@push('styles')
<style>
.stu-page { animation: stuFadeIn 0.4s ease-out; }
@keyframes stuFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.stu-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.stu-header-left { flex: 1; }
.stu-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark, #1a1a2e); margin: 0; letter-spacing: -0.5px; }
.stu-subtitle { font-size: 0.88rem; color: var(--text-muted, #6c757d); margin: 0.25rem 0 0; }
.stu-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.stu-breadcrumb li { color: #adb5bd; }
.stu-breadcrumb li a { color: var(--text-muted); text-decoration: none; }
.stu-breadcrumb li a:hover { color: #4361ee; }
.stu-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.stu-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.stu-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
.stu-avatar { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; color: #fff; flex-shrink: 0; }
.stu-name-cell { display: flex; align-items: center; gap: 8px; }
.stu-filters { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.25rem; align-items: center; }
.stu-filter-select { border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.5rem 2rem 0.5rem 0.75rem; font-size: 0.85rem; background: #fff; appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.6rem center; background-repeat: no-repeat; background-size: 1rem; }
.stu-filter-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.stu-search { position: relative; }
.stu-search input { border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.5rem 0.75rem 0.5rem 2.25rem; font-size: 0.85rem; width: 240px; }
.stu-search input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.stu-search i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #adb5bd; font-size: 0.85rem; }
</style>
@endpush

@section('content')
<div class="stu-page">
    <div class="stu-header">
        <div class="stu-header-left">
            <nav class="stu-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li class="active">Students</li>
            </ol></nav>
            <h1 class="stu-title">Student Management</h1>
            <p class="stu-subtitle">Manage student admissions, readmissions, and records</p>
        </div>
        <div class="stu-actions">
            <a href="{{ route('admin.students.create') }}" class="btn-modern btn-modern-primary" style="font-size:0.82rem;padding:0.5rem 1.1rem;">
                <i class="fas fa-plus"></i> New Admission
            </a>
            <a href="{{ route('admin.students.inactive') }}" class="btn-modern btn-modern-outline" style="font-size:0.82rem;padding:0.5rem 1.1rem;">
                <i class="fas fa-user-clock"></i> Inactive Students
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue"><i class="fas fa-users"></i></div>
            <div class="modern-stat-info">
                <div class="modern-stat-value">{{ $statusCounts['total'] ?? 0 }}</div>
                <div class="modern-stat-label">Total Students</div>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green"><i class="fas fa-user-check"></i></div>
            <div class="modern-stat-info">
                <div class="modern-stat-value">{{ $statusCounts['active'] ?? 0 }}</div>
                <div class="modern-stat-label">Active</div>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-red"><i class="fas fa-user-times"></i></div>
            <div class="modern-stat-info">
                <div class="modern-stat-value">{{ $statusCounts['inactive'] ?? 0 }}</div>
                <div class="modern-stat-label">Inactive</div>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-yellow"><i class="fas fa-exchange-alt"></i></div>
            <div class="modern-stat-info">
                <div class="modern-stat-value">{{ $statusCounts['transferred'] ?? 0 }}</div>
                <div class="modern-stat-label">Transferred</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.students.index') }}" class="stu-filters">
        <div class="stu-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name or admission #...">
        </div>
        <select name="status" class="stu-filter-select">
            <option value="">All Status</option>
            <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="transferred" {{ $statusFilter === 'transferred' ? 'selected' : '' }}>Transferred</option>
            <option value="graduated" {{ $statusFilter === 'graduated' ? 'selected' : '' }}>Graduated</option>
        </select>
        <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.82rem;padding:0.45rem 0.9rem;">
            <i class="fas fa-filter"></i> Filter
        </button>
        <a href="{{ route('admin.students.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.82rem;padding:0.45rem 0.9rem;">
            <i class="fas fa-times"></i> Clear
        </a>
    </form>

    {{-- Students Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <div class="modern-form-section-icon modern-form-section-icon-blue" style="width:36px;height:36px;border-radius:10px;font-size:0.95rem;">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <h3 class="modern-card-title">Students</h3>
            </div>
            <div style="font-size:0.78rem;color:var(--text-muted);">
                @if($students->count() > 0)
                    Showing {{ $students->firstItem() }} - {{ $students->lastItem() }} of {{ $students->total() }}
                @endif
            </div>
        </div>
        <div class="modern-card-body" style="padding:0;overflow-x:auto;">
            @if($students->count() > 0)
            <table class="promo-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Admission #</th>
                        <th>Roll #</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $index => $student)
                    @php
                        $avatarColors = ['#6366f1','#8b5cf6','#ec4899','#ef4444','#f59e0b','#10b981','#06b6d4','#3b82f6'];
                        $colorIndex = ($student->id % count($avatarColors));
                        $statusBadge = match($student->status) {
                            'active' => 'modern-badge-success',
                            'inactive' => 'modern-badge-danger',
                            'transferred' => 'modern-badge-warning',
                            'graduated' => 'modern-badge-info',
                            default => 'modern-badge-light',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight:600;color:var(--text-muted);">{{ $students->firstItem() + $index }}</td>
                        <td>
                            <div class="stu-name-cell">
                                <div class="stu-avatar" style="background:{{ $avatarColors[$colorIndex] }};">
                                    {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}{{ strtoupper(substr($student->last_name ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;color:var(--text-dark);">{{ $student->first_name }} {{ $student->last_name }}</div>
                                    <div style="font-size:0.72rem;color:var(--text-muted);">{{ $student->gender ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-weight:600;">{{ $student->admission_number }}</td>
                        <td>{{ $student->roll_number ?? '-' }}</td>
                        <td>{{ $student->classroom->name ?? '-' }}</td>
                        <td>{{ $student->section->name ?? '-' }}</td>
                        <td><span class="modern-badge {{ $statusBadge }}">{{ ucfirst($student->status) }}</span></td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="promo-action-btn" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.students.edit', $student->id) }}" class="promo-action-btn" title="Edit"><i class="fas fa-edit"></i></a>
                                @if($student->status === 'active')
                                <button type="button" class="promo-action-btn promo-action-override" title="Mark as Left" data-id="{{ $student->id }}" data-name="{{ $student->first_name }} {{ $student->last_name }}" onclick="openLeaveModal(this)"><i class="fas fa-sign-out-alt"></i></button>
                                @endif
                                @if($student->canBeReadmitted())
                                <a href="{{ route('admin.students.readmit', $student->id) }}" class="promo-action-btn" title="Readmit" style="border-color:#10b981;color:#10b981;"><i class="fas fa-redo"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="text-align:center;padding:3rem 1.5rem;">
                <i class="fas fa-user-graduate" style="font-size:3rem;color:#d1d5db;margin-bottom:1rem;display:block;"></i>
                <p style="color:var(--text-muted);font-size:0.95rem;">No students found.</p>
            </div>
            @endif
        </div>
    </div>

    @if($students->count() > 0)
    <div style="display:flex;justify-content:center;margin-top:1rem;">
        {{ $students->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Mark as Left Modal --}}
<div class="modal fade" id="leaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#ef4444,#dc2626);border:none;padding:1rem 1.5rem;">
                <h5 class="modal-title" style="color:#fff;font-weight:700;font-size:1rem;"><i class="fas fa-sign-out-alt me-2"></i>Mark Student as Left</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="leaveForm">
                @csrf
                <div class="modal-body" style="padding:1.5rem;">
                    <p style="font-size:0.88rem;color:var(--text-dark);margin-bottom:1rem;">
                        Mark <strong id="leaveStudentName">-</strong> as having left the school?
                    </p>
                    <div>
                        <label style="font-weight:600;font-size:0.85rem;color:#374151;margin-bottom:0.4rem;display:block;">Reason for Leaving</label>
                        <textarea name="leave_reason" rows="3" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:0.6rem 0.8rem;font-size:0.88rem;resize:vertical;" placeholder="e.g., Family relocation, Transfer to another school..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);padding:1rem 1.5rem;">
                    <button type="button" class="btn-modern btn-modern-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-modern btn-modern-primary" style="background:linear-gradient(135deg,#ef4444,#dc2626);border:none;">
                        <i class="fas fa-check"></i> Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openLeaveModal(btn) {
    document.getElementById('leaveForm').action = '{{ route('admin.students.mark-as-left', '__ID__') }}'.replace('__ID__', btn.dataset.id);
    document.getElementById('leaveStudentName').textContent = btn.dataset.name;
    new bootstrap.Modal(document.getElementById('leaveModal')).show();
}
</script>
@endpush
