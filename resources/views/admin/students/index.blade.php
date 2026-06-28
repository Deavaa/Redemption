@extends('layouts.admin')
@section('title', 'Student Management')

@push('styles')
<style>
/* ===== STUDENT LIST - COMPACT DESIGN ===== */
.stu-page { animation: stuFadeIn 0.4s ease-out; }
@keyframes stuFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.stu-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem; }
.stu-header-left { flex: 1; }
.stu-title { font-size: 1.35rem; font-weight: 800; color: var(--text-dark, #1a1a2e); margin: 0; letter-spacing: -0.5px; }
.stu-subtitle { font-size: 0.82rem; color: var(--text-muted, #6c757d); margin: 0.15rem 0 0; }
.stu-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.35rem; gap: 0.5rem; font-size: 0.75rem; align-items: center; }
.stu-breadcrumb li { color: #adb5bd; }
.stu-breadcrumb li a { color: var(--text-muted); text-decoration: none; }
.stu-breadcrumb li a:hover { color: #4361ee; }
.stu-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.stu-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.stu-actions { display: flex; gap: 0.4rem; flex-wrap: wrap; align-items: center; }

/* Compact Stats — 3 columns on desktop, 2 on tablet, 1 on mobile */
.stu-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.6rem; margin-bottom: 0.9rem; }
.stu-stat { background: #fff; border-radius: 10px; padding: 0.6rem 0.75rem; border: 1px solid #f0f0f0; border-left: 3px solid #4361ee; box-shadow: 0 1px 2px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 0.6rem; transition: box-shadow 0.2s, transform 0.2s; }
.stu-stat:hover { box-shadow: 0 4px 10px rgba(0,0,0,0.08); transform: translateY(-1px); }
.stu-stat-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; }
.stu-stat-icon.si-blue { background: #e0e7ff; color: #4361ee; }
.stu-stat-icon.si-green { background: #d1fae5; color: #059669; }
.stu-stat-icon.si-red { background: #fee2e2; color: #dc2626; }
.stu-stat-icon.si-yellow { background: #fef3c7; color: #d97706; }
.stu-stat-val { font-size: 1.2rem; font-weight: 800; color: #111827; line-height: 1.1; }
.stu-stat-lbl { font-size: 0.65rem; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }

/* Compact Filters */
.stu-filters { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.9rem; align-items: center; }
.stu-search { position: relative; }
.stu-search input { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 0.4rem 0.7rem 0.4rem 2rem; font-size: 0.82rem; width: 200px; transition: all 0.2s; }
.stu-search input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,0.1); }
.stu-search i { position: absolute; left: 8px; top: 50%; transform: translateY(-50%); color: #adb5bd; font-size: 0.78rem; }
.stu-filter-select { border: 1.5px solid #e5e7eb; border-radius: 8px; padding: 0.4rem 1.8rem 0.4rem 0.6rem; font-size: 0.82rem; background: #fff; appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 0.9rem; }
.stu-filter-select:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 2px rgba(67,97,238,0.1); }

/* Compact Table */
.stu-table-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
.stu-table-header { display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 1rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; }
.stu-table-header-left { display: flex; align-items: center; gap: 0.5rem; }
.stu-table-icon { width: 30px; height: 30px; border-radius: 8px; background: #eef2ff; color: #4361ee; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; }
.stu-table-title { font-size: 0.88rem; font-weight: 700; color: #1a1a2e; }
.stu-table-count { font-size: 0.72rem; color: #9ca3af; }

.stu-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.stu-table thead th { padding: 7px 10px; text-align: left; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.3px; color: #6b7280; background: #f9fafb; border-bottom: 2px solid #e5e7eb; white-space: nowrap; }
.stu-table tbody td { padding: 6px 10px; border-bottom: 1px solid #f0f0f0; color: #1f2937; vertical-align: middle; }
.stu-table tbody tr { transition: background 0.15s; }
.stu-table tbody tr:hover { background: rgba(67,97,238,0.03); }

/* Student Name Cell */
.stu-name-cell { display: flex; align-items: center; gap: 6px; }
.stu-avatar { width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 700; color: #fff; flex-shrink: 0; }
.stu-name-text { font-weight: 600; color: #1a1a2e; white-space: nowrap; }
.stu-name-sub { font-size: 0.65rem; color: #9ca3af; }

/* Action Buttons - Small */
.stu-action-btn { width: 26px; height: 26px; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; color: #6b7280; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.68rem; text-decoration: none; }
.stu-action-btn:hover { border-color: #4361ee; color: #4361ee; background: #eef2ff; }
.stu-action-btn.stu-action-danger:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
.stu-action-btn.stu-action-green:hover { border-color: #10b981; color: #10b981; background: #ecfdf5; }
.stu-action-btn.stu-action-msg:hover { border-color: #ea580c; color: #ea580c; background: #fff7ed; }
.stu-action-btn.stu-action-id:hover { border-color: #7c3aed; color: #7c3aed; background: #f3e8ff; }
.stu-action-btn.stu-action-cert:hover { border-color: #059669; color: #059669; background: #ecfdf5; }

/* Status Badge - Compact */
.stu-status { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 5px; font-size: 0.68rem; font-weight: 600; text-transform: capitalize; gap: 3px; }
.stu-status-active { background: rgba(16,185,129,0.1); color: #059669; }
.stu-status-inactive { background: rgba(239,68,68,0.1); color: #dc2626; }
.stu-status-transferred { background: rgba(245,158,11,0.1); color: #d97706; }
.stu-status-graduated { background: rgba(99,102,241,0.1); color: #4361ee; }

/* Compact Pagination */
.stu-pagination { display: flex; justify-content: center; align-items: center; gap: 3px; margin-top: 0.75rem; flex-wrap: wrap; }
.stu-pagination .page-link { border-radius: 6px !important; font-size: 0.72rem; padding: 4px 8px; border: 1px solid #e5e7eb; color: #6b7280; min-width: 28px; text-align: center; transition: all 0.2s; background: #fff; }
.stu-pagination .page-link:hover { border-color: #4361ee; color: #4361ee; background: #eef2ff; }
.stu-pagination .page-item.active .page-link { background: #4361ee; border-color: #4361ee; color: #fff; font-weight: 700; }
.stu-pagination .page-item.disabled .page-link { color: #d1d5db; background: #f9fafb; border-color: #f0f0f0; }

/* Empty State */
.stu-empty { text-align: center; padding: 2.5rem 1.5rem; }
.stu-empty i { font-size: 2.5rem; color: #d1d5db; margin-bottom: 0.75rem; display: block; }
.stu-empty p { color: #9ca3af; font-size: 0.88rem; margin: 0; }

/* Responsive */
@media (max-width: 768px) {
    .stu-stats { grid-template-columns: repeat(2, 1fr); }
    .stu-header { flex-direction: column; align-items: stretch; }
    .stu-search input { width: 100%; }
    .stu-filters { flex-direction: column; align-items: stretch; }
    .stu-filter-select { width: 100%; }
}
@media (max-width: 480px) {
    .stu-stats { grid-template-columns: 1fr; gap: 0.4rem; }
    .stu-stat { padding: 0.5rem 0.6rem; }
    .stu-stat-val { font-size: 1rem; }
}
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
            <p class="stu-subtitle">Manage student admissions, records, and status</p>
        </div>
        <div class="stu-actions">
            <a href="{{ route('admin.students.create') }}" class="btn-modern btn-modern-primary" style="font-size:0.78rem;padding:0.4rem 0.9rem;">
                <i class="fas fa-plus"></i> New Admission
            </a>
            <a href="{{ route('admin.students.inactive') }}" class="btn-modern btn-modern-outline" style="font-size:0.78rem;padding:0.4rem 0.9rem;">
                <i class="fas fa-user-clock"></i> Inactive
            </a>
            {{-- Export buttons — uses /api/export/students endpoint. Pass
                 current filter params so the export matches what's on screen. --}}
            @php
                $exportQuery = http_build_query(array_filter([
                    'branch_id' => request('branch_id'),
                    'class_id' => request('class_id'),
                    'section_id' => request('section_id'),
                    'search' => request('search'),
                    'format' => '',
                ]));
            @endphp
            <div class="dropdown d-inline-block">
                <button class="btn-modern btn-modern-outline dropdown-toggle" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false"
                        style="font-size:0.78rem;padding:0.4rem 0.9rem;">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="min-width:180px;">
                    <li><h6 class="dropdown-header"><i class="fas fa-file-pdf me-1"></i>PDF</h6></li>
                    <li><a class="dropdown-item" href="{{ url('/api/export/students') }}?{{ str_replace('format=', 'format=pdf', $exportQuery) }}" target="_blank">
                        <i class="fas fa-print me-2"></i>Print / Save as PDF
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header"><i class="fas fa-file-excel me-1"></i>Excel / CSV</h6></li>
                    <li><a class="dropdown-item" href="{{ url('/api/export/students') }}?{{ str_replace('format=', 'format=csv', $exportQuery) }}">
                        <i class="fas fa-file-csv me-2"></i>Download CSV
                    </a></li>
                </ul>
            </div>
            @if(in_array(auth()->user()->role, ['admin', 'super_admin', 'general_manager', 'branch_principal']))
            <button type="button" class="btn-modern btn-modern-outline" onclick="document.getElementById('studentImportFile').click()"
                    style="font-size:0.78rem;padding:0.4rem 0.9rem;color:#d97706;border-color:#d97706;">
                <i class="fas fa-file-import"></i> Import CSV
            </button>
            <input type="file" id="studentImportFile" accept=".csv,.txt" style="display:none;"
                   onchange="document.getElementById('studentImportForm').submit()" />
            <form id="studentImportForm" action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" style="display:none;">
                @csrf
                <input type="file" name="file" />
            </form>
            @endif
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="stu-stats">
        <div class="stu-stat">
            <div class="stu-stat-icon si-blue"><i class="fas fa-users"></i></div>
            <div><div class="stu-stat-val">{{ $statusCounts['total'] ?? 0 }}</div><div class="stu-stat-lbl">Total</div></div>
        </div>
        <div class="stu-stat">
            <div class="stu-stat-icon si-green"><i class="fas fa-user-check"></i></div>
            <div><div class="stu-stat-val">{{ $statusCounts['active'] ?? 0 }}</div><div class="stu-stat-lbl">Active</div></div>
        </div>
        <div class="stu-stat">
            <div class="stu-stat-icon si-red"><i class="fas fa-user-times"></i></div>
            <div><div class="stu-stat-val">{{ $statusCounts['inactive'] ?? 0 }}</div><div class="stu-stat-lbl">Inactive</div></div>
        </div>
        <div class="stu-stat">
            <div class="stu-stat-icon si-yellow"><i class="fas fa-exchange-alt"></i></div>
            <div><div class="stu-stat-val">{{ $statusCounts['transferred'] ?? 0 }}</div><div class="stu-stat-lbl">Transferred</div></div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.students.index') }}" class="stu-filters">
        <div class="stu-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name, admission #, roll #...">
        </div>
        <select name="status" class="stu-filter-select">
            <option value="">All Status</option>
            <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="transferred" {{ $statusFilter === 'transferred' ? 'selected' : '' }}>Transferred</option>
            <option value="graduated" {{ $statusFilter === 'graduated' ? 'selected' : '' }}>Graduated</option>
        </select>
        <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.78rem;padding:0.35rem 0.75rem;">
            <i class="fas fa-filter"></i> Filter
        </button>
        <a href="{{ route('admin.students.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.78rem;padding:0.35rem 0.75rem;">
            <i class="fas fa-times"></i> Clear
        </a>
    </form>

    {{-- Students Table --}}
    <div class="stu-table-card">
        <div class="stu-table-header">
            <div class="stu-table-header-left">
                <div class="stu-table-icon"><i class="fas fa-user-graduate"></i></div>
                <span class="stu-table-title">Students</span>
            </div>
            <span class="stu-table-count">
                @if($students->count() > 0)
                    {{ $students->firstItem() }}-{{ $students->lastItem() }} of {{ $students->total() }}
                @endif
            </span>
        </div>
        <div style="overflow-x:auto;">
            @if($students->count() > 0)
            <table class="stu-table">
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
                        $statusClass = match($student->status) {
                            'active' => 'stu-status-active',
                            'inactive' => 'stu-status-inactive',
                            'transferred' => 'stu-status-transferred',
                            'graduated' => 'stu-status-graduated',
                            default => 'stu-status-active',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight:600;color:#9ca3af;font-size:0.75rem;">{{ $students->firstItem() + $index }}</td>
                        <td>
                            <div class="stu-name-cell">
                                <div class="stu-avatar" style="background:{{ $avatarColors[$colorIndex] }};">
                                    {{ strtoupper(substr($student->full_name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="stu-name-text">{{ $student->full_name }}</div>
                                    <div class="stu-name-sub">{{ ucfirst($student->gender ?? '-') }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-weight:600;font-size:0.8rem;">{{ $student->admission_number }}</td>
                        <td style="font-size:0.8rem;">{{ $student->roll_number ?? '-' }}</td>
                        <td style="font-size:0.8rem;">{{ $student->classroom->name ?? '-' }}</td>
                        <td style="font-size:0.8rem;">{{ $student->section->name ?? '-' }}</td>
                        <td><span class="stu-status {{ $statusClass }}">{{ ucfirst($student->status) }}</span></td>
                        <td>
                            <div style="display:flex;gap:3px;align-items:center;">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="stu-action-btn" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.students.edit', $student->id) }}" class="stu-action-btn" title="Edit"><i class="fas fa-edit"></i></a>
                                @php
                                    $chatRecipientId = $student->user_id;
                                    $chatRecipientType = 'student';
                                    if (!$chatRecipientId) {
                                        $chatParent = $student->parents()->first();
                                        if ($chatParent && $chatParent->user_id) {
                                            $chatRecipientId = $chatParent->user_id;
                                            $chatRecipientType = 'parent';
                                        } else {
                                            $chatRecipientId = $student->id;
                                        }
                                    }
                                @endphp
                                <a href="{{ route('admin.chat.index') }}?recipient_id={{ $chatRecipientId }}&recipient_type={{ $chatRecipientType }}" class="stu-action-btn stu-action-msg" title="Send Message"><i class="fas fa-paper-plane"></i></a>
                                <a href="{{ route('admin.id-card-generate.index') }}?student_id={{ $student->id }}" class="stu-action-btn stu-action-id" title="Generate ID Card"><i class="fas fa-id-card"></i></a>
                                <a href="{{ route('admin.certificate-generate.index') }}?student_id={{ $student->id }}" class="stu-action-btn stu-action-cert" title="Generate Certificate"><i class="fas fa-certificate"></i></a>
                                @if($student->status === 'active')
                                <button type="button" class="stu-action-btn stu-action-danger" title="Mark as Left" data-id="{{ $student->id }}" data-name="{{ $student->full_name }}" onclick="openLeaveModal(this)"><i class="fas fa-sign-out-alt"></i></button>
                                @endif
                                @if($student->canBeReadmitted())
                                <a href="{{ route('admin.students.readmit', $student->id) }}" class="stu-action-btn stu-action-green" title="Readmit"><i class="fas fa-redo"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="stu-empty">
                <i class="fas fa-user-graduate"></i>
                <p>No students found.</p>
            </div>
            @endif
        </div>
    </div>

    @if($students->count() > 0)
    <div class="stu-pagination">
        {{ $students->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Mark as Left Modal --}}
<div class="modal fade" id="leaveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;border:none;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#ef4444,#dc2626);border:none;padding:0.85rem 1.25rem;">
                <h5 class="modal-title" style="color:#fff;font-weight:700;font-size:0.92rem;"><i class="fas fa-sign-out-alt me-2"></i>Mark Student as Left</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="leaveForm">
                @csrf
                <div class="modal-body" style="padding:1.25rem;">
                    <p style="font-size:0.85rem;color:var(--text-dark);margin-bottom:0.9rem;">
                        Mark <strong id="leaveStudentName">-</strong> as having left the school?
                    </p>
                    <div>
                        <label style="font-weight:600;font-size:0.82rem;color:#374151;margin-bottom:0.35rem;display:block;">Reason for Leaving</label>
                        <textarea name="leave_reason" rows="3" style="width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:0.5rem 0.7rem;font-size:0.85rem;resize:vertical;" placeholder="e.g., Family relocation, Transfer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--border);padding:0.75rem 1.25rem;">
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
