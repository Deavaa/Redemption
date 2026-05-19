@extends('layouts.admin')
@section('title', 'Students')

@section('content')
<div class="sl-page">
    {{-- Page Header --}}
    <div class="sl-header">
        <div class="sl-header-left">
            <nav aria-label="breadcrumb" class="sl-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Students</a></li>
                    <li class="active">All Students</li>
                </ol>
            </nav>
        </div>
        <div class="sl-header-right">
            <a href="{{ route('admin.students.generateIds') }}" class="sl-btn sl-btn-outline" title="Generate Student ID Numbers">
                <i class="fas fa-id-badge"></i> Generate IDs
            </a>
            <a href="{{ route('admin.id-card-generate.index') }}" class="sl-btn sl-btn-outline" style="color:#7c3aed;border-color:#7c3aed;" title="Generate & Print ID Cards">
                <i class="fas fa-id-card"></i> ID Cards
            </a>
            <a href="{{ route('admin.certificate-generate.index') }}" class="sl-btn sl-btn-outline" style="color:#059669;border-color:#059669;" title="Generate Certificates">
                <i class="fas fa-certificate"></i> Certificates
            </a>
            <a href="{{ route('admin.chat.index') }}" class="sl-btn sl-btn-outline" style="color:#ea580c;border-color:#ea580c;" title="Send Message">
                <i class="fas fa-paper-plane"></i> Message
            </a>
            <a href="{{ route('admin.students.create') }}" class="sl-btn sl-btn-primary">
                <i class="fas fa-plus"></i> Add Student
            </a>
        </div>
    </div>

    {{-- Compact Stats Row --}}
    <div class="sl-stats">
        <div class="sl-stat">
            <div class="sl-stat-icon sl-stat-blue"><i class="fas fa-user-graduate"></i></div>
            <div class="sl-stat-body">
                <span class="sl-stat-val">{{ $totalStudents ?? 0 }}</span>
                <span class="sl-stat-lbl">Total</span>
            </div>
        </div>
        <div class="sl-stat">
            <div class="sl-stat-icon sl-stat-green"><i class="fas fa-check-circle"></i></div>
            <div class="sl-stat-body">
                <span class="sl-stat-val">{{ $activeStudents ?? 0 }}</span>
                <span class="sl-stat-lbl">Active</span>
            </div>
        </div>
        <div class="sl-stat">
            <div class="sl-stat-icon sl-stat-red"><i class="fas fa-times-circle"></i></div>
            <div class="sl-stat-body">
                <span class="sl-stat-val">{{ $inactiveStudents ?? 0 }}</span>
                <span class="sl-stat-lbl">Inactive</span>
            </div>
        </div>
    </div>

    {{-- Students Table Card --}}
    <div class="sl-card">
        <div class="sl-card-head">
            <div class="sl-card-head-left">
                <h2 class="sl-card-title">All Students</h2>
                <span class="sl-count">{{ $students->total() }}</span>
            </div>
            <div class="sl-card-head-right">
                <div class="sl-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="studentSearch" placeholder="Search..." onkeyup="filterTable()">
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="sl-alert sl-alert-ok">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;margin-left:auto;opacity:0.6;color:inherit">&times;</button>
            </div>
        @endif

        @if($students->count() > 0)
        <div class="sl-table-wrap">
            <table class="sl-table" id="studentTable">
                <thead>
                    <tr>
                        <th class="sl-th-narrow">#</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Roll No</th>
                        <th class="sl-th-center">Status</th>
                        <th class="sl-th-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td class="sl-td-narrow">
                            <span class="sl-num">{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</span>
                        </td>
                        <td>
                            <div class="sl-user">
                                @if($student->photo)
                                    <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->full_name }}" class="sl-avatar-img">
                                @else
                                    <div class="sl-avatar-char">{{ strtoupper(substr($student->full_name, 0, 1)) }}</div>
                                @endif
                                <span class="sl-name">{{ $student->full_name }}</span>
                            </div>
                        </td>
                        <td><span class="sl-text">{{ $student->classroom?->name ?? '-' }}</span></td>
                        <td><span class="sl-text">{{ $student->section?->name ?? '-' }}</span></td>
                        <td><span class="sl-text">{{ $student->roll_number ?? '-' }}</span></td>
                        <td class="sl-td-center">
                            @php
                                $sb = match($student->status ?? '') {
                                    'active' => 'sl-tag-green',
                                    'inactive' => 'sl-tag-red',
                                    'graduated' => 'sl-tag-blue',
                                    'transferred' => 'sl-tag-yellow',
                                    default => 'sl-tag-gray'
                                };
                            @endphp
                            <span class="sl-tag {{ $sb }}">{{ ucfirst($student->status ?? 'N/A') }}</span>
                        </td>
                        <td class="sl-td-right">
                            <div class="sl-actions">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="sl-act sl-act-view" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.students.edit', ['student' => $student->id, 'page' => $students->currentPage()]) }}" class="sl-act sl-act-edit" title="Edit"><i class="fas fa-pen"></i></a>
                                <a href="{{ route('admin.chat.index') }}?recipient_id={{ $student->user_id ?? '' }}&recipient_type=student" class="sl-act sl-act-msg" title="Send Message"><i class="fas fa-paper-plane"></i></a>
                                <a href="{{ route('admin.id-card-generate.index') }}?student_id={{ $student->id }}" class="sl-act sl-act-id" title="Generate ID Card"><i class="fas fa-id-card"></i></a>
                                <a href="{{ route('admin.certificate-generate.index') }}?student_id={{ $student->id }}" class="sl-act sl-act-cert" title="Generate Certificate"><i class="fas fa-certificate"></i></a>
                                <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" style="display:inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="sl-act sl-act-del" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- INLINE PAGINATION --}}
        @if($students->hasPages())
        <div class="sl-pag">
            <div class="sl-pag-nav">
                @if($students->onFirstPage())
                    <span class="sl-pag-btn sl-pag-off">&lsaquo;</span>
                @else
                    <a href="{{ $students->previousPageUrl() }}" class="sl-pag-btn">&lsaquo;</a>
                @endif

                @php
                    $cp = $students->currentPage();
                    $lp = $students->lastPage();
                    $s = max(1, $cp - 2);
                    $e = min($lp, $cp + 2);
                    if ($s > 1) { echo '<a href="' . $students->url(1) . '" class="sl-pag-btn">1</a>'; if ($s > 2) echo '<span class="sl-pag-dots">...</span>'; }
                    for ($i = $s; $i <= $e; $i++) {
                        if ($i == $cp) echo '<span class="sl-pag-btn sl-pag-cur">' . $i . '</span>';
                        else echo '<a href="' . $students->url($i) . '" class="sl-pag-btn">' . $i . '</a>';
                    }
                    if ($e < $lp) { if ($e < $lp - 1) echo '<span class="sl-pag-dots">...</span>'; echo '<a href="' . $students->url($lp) . '" class="sl-pag-btn">' . $lp . '</a>'; }
                @endphp

                @if($students->hasMorePages())
                    <a href="{{ $students->nextPageUrl() }}" class="sl-pag-btn">&rsaquo;</a>
                @else
                    <span class="sl-pag-btn sl-pag-off">&rsaquo;</span>
                @endif
            </div>
            <span class="sl-pag-info">{{ $students->firstItem() }}-{{ $students->lastItem() }} of {{ $students->total() }}</span>
        </div>
        @endif

        @else
        <div class="sl-empty">
            <div class="sl-empty-icon"><i class="fas fa-user-graduate"></i></div>
            <h3>No Students Yet</h3>
            <p>Get started by enrolling your first student.</p>
            <a href="{{ route('admin.students.create') }}" class="sl-btn sl-btn-primary"><i class="fas fa-plus"></i> Add Student</a>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
/* ========================================================
   STUDENT LIST - Completely isolated sl-* namespace
   No conflicts with Bootstrap, admin.css, or modern-components.css
   ======================================================== */

/* --- Page --- */
.sl-page { animation: slIn 0.3s ease-out; }
@keyframes slIn { from { opacity: 0; } to { opacity: 1; } }

/* --- Header --- */
.sl-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.sl-header-left { flex: 1; }
.sl-header-right { display: flex; gap: 0.4rem; flex-wrap: wrap; }

/* --- Breadcrumb --- */
.sl-breadcrumb ol {
    display: flex; list-style: none; padding: 0; margin: 0;
    gap: 0.3rem; font-size: 0.72rem; align-items: center;
}
.sl-breadcrumb li { color: #adb5bd; }
.sl-breadcrumb li a { color: #6c757d; text-decoration: none; }
.sl-breadcrumb li a:hover { color: #4361ee; }
.sl-breadcrumb li + li::before { content: '/'; margin-right: 0.3rem; color: #dee2e6; }
.sl-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* --- Stats --- */
.sl-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}
.sl-stat {
    background: #fff; border-radius: 8px; padding: 0.5rem 0.7rem;
    display: flex; align-items: center; gap: 0.5rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04); border: 1px solid #f0f0f0;
}
.sl-stat-icon {
    width: 32px; height: 32px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; flex-shrink: 0;
}
.sl-stat-blue { background: #eef2ff; color: #4361ee; }
.sl-stat-green { background: #ecfdf5; color: #10b981; }
.sl-stat-red { background: #fef2f2; color: #ef4444; }
.sl-stat-body { display: flex; flex-direction: column; }
.sl-stat-val { font-size: 1.1rem; font-weight: 800; color: #1a1a2e; line-height: 1.1; }
.sl-stat-lbl { font-size: 0.65rem; color: #6c757d; font-weight: 500; }

/* --- Card --- */
.sl-card {
    background: #fff; border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #f0f0f0;
    overflow: hidden; margin-bottom: 0.75rem;
}
.sl-card-head {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.5rem 0.75rem; border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap; gap: 0.5rem;
}
.sl-card-head-left { display: flex; align-items: center; gap: 0.4rem; }
.sl-card-head-right { display: flex; align-items: center; }
.sl-card-title { font-size: 0.9rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.sl-count {
    display: inline-block; padding: 1px 7px; border-radius: 20px;
    font-size: 0.68rem; font-weight: 600; background: #f3f4f6; color: #6b7280;
}

/* --- Search --- */
.sl-search { position: relative; display: flex; align-items: center; }
.sl-search i { position: absolute; left: 8px; color: #adb5bd; font-size: 0.75rem; }
.sl-search input {
    border: 1px solid #e5e7eb; border-radius: 6px;
    padding: 0.3rem 0.5rem 0.3rem 1.7rem; font-size: 0.78rem;
    width: 150px; background: #f9fafb; color: #374151; outline: none;
}
.sl-search input:focus { border-color: #4361ee; background: #fff; }
.sl-search input::placeholder { color: #9ca3af; }

/* --- Buttons --- */
.sl-btn {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.3rem 0.65rem; border-radius: 6px; font-weight: 600;
    font-size: 0.73rem; text-decoration: none; border: none; cursor: pointer;
    transition: all 0.2s; white-space: nowrap;
}
.sl-btn-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff; box-shadow: 0 1px 4px rgba(67,97,238,0.3);
}
.sl-btn-primary:hover { color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.4); }
.sl-btn-outline { background: #fff; color: #4361ee; border: 1px solid #4361ee; }
.sl-btn-outline:hover { background: #4361ee; color: #fff; }

/* --- Alert --- */
.sl-alert {
    display: flex; align-items: center; gap: 0.4rem;
    padding: 0.4rem 0.7rem; margin: 0.4rem 0.75rem; border-radius: 6px;
    font-size: 0.78rem; font-weight: 500;
}
.sl-alert-ok { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }

/* --- Table --- */
.sl-table-wrap { overflow-x: auto; }
.sl-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; }
.sl-table thead th {
    background: #f9fafb; padding: 0.4rem 0.55rem; text-align: left;
    font-weight: 600; font-size: 0.65rem; text-transform: uppercase;
    letter-spacing: 0.3px; color: #6b7280; border-bottom: 2px solid #e5e7eb;
    white-space: nowrap;
}
.sl-table tbody tr { border-bottom: 1px solid #f3f4f6; }
.sl-table tbody tr:hover { background: #f8f9ff; }
.sl-table td { padding: 0.35rem 0.55rem; vertical-align: middle; color: #374151; }
.sl-th-narrow { width: 36px; }
.sl-th-center { text-align: center; }
.sl-th-right { text-align: right; }
.sl-td-narrow { width: 36px; }
.sl-td-center { text-align: center; }
.sl-td-right { text-align: right; }

/* Row number */
.sl-num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px; border-radius: 5px;
    background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 0.68rem;
}

/* User cell */
.sl-user { display: flex; align-items: center; gap: 0.35rem; }
.sl-avatar-img {
    width: 26px; height: 26px; border-radius: 6px; object-fit: cover; flex-shrink: 0;
}
.sl-avatar-char {
    width: 26px; height: 26px; border-radius: 6px;
    background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.7rem; flex-shrink: 0;
}
.sl-name { font-weight: 600; color: #1a1a2e; font-size: 0.78rem; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sl-text { color: #4b5563; font-size: 0.75rem; max-width: 90px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; }

/* Status tags */
.sl-tag {
    display: inline-block; padding: 1px 7px; border-radius: 20px;
    font-size: 0.66rem; font-weight: 600; line-height: 1.5;
}
.sl-tag-green { background: #ecfdf5; color: #059669; }
.sl-tag-red { background: #fef2f2; color: #dc2626; }
.sl-tag-blue { background: #eff6ff; color: #2563eb; }
.sl-tag-yellow { background: #fefce8; color: #b45309; }
.sl-tag-gray { background: #f3f4f6; color: #6b7280; }

/* Action buttons */
.sl-actions { display: inline-flex; gap: 2px; }
.sl-act {
    width: 24px; height: 24px; border-radius: 5px; border: none;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 0.65rem; text-decoration: none;
    transition: all 0.15s; padding: 0; line-height: 1;
}
.sl-act-view { background: #eef2ff; color: #4361ee; }
.sl-act-view:hover { background: #4361ee; color: #fff; }
.sl-act-edit { background: #fefce8; color: #d97706; }
.sl-act-edit:hover { background: #d97706; color: #fff; }
.sl-act-del { background: #fef2f2; color: #dc2626; }
.sl-act-del:hover { background: #dc2626; color: #fff; }
.sl-act-msg { background: #fff7ed; color: #ea580c; }
.sl-act-msg:hover { background: #ea580c; color: #fff; }
.sl-act-id { background: #f3e8ff; color: #7c3aed; }
.sl-act-id:hover { background: #7c3aed; color: #fff; }
.sl-act-cert { background: #ecfdf5; color: #059669; }
.sl-act-cert:hover { background: #059669; color: #fff; }

/* --- Pagination --- */
.sl-pag {
    padding: 6px 10px; border-top: 1px solid #f0f0f0;
    display: flex; justify-content: center; align-items: center; gap: 6px;
    flex-wrap: wrap;
}
.sl-pag-nav { display: flex; align-items: center; gap: 3px; }
.sl-pag-btn {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 26px; height: 26px; padding: 0 5px; border-radius: 5px;
    font-size: 0.7rem; font-weight: 600; color: #4b5563;
    background: #f3f4f6; border: 1px solid #e5e7eb;
    text-decoration: none; cursor: pointer; transition: all 0.15s;
    line-height: 1;
}
.sl-pag-btn:hover { background: #4361ee; color: #fff; border-color: #4361ee; }
.sl-pag-cur { background: #4361ee; color: #fff; border-color: #4361ee; }
.sl-pag-off { color: #d1d5db; background: #f9fafb; cursor: not-allowed; }
.sl-pag-dots { display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 26px; font-size: 0.7rem; color: #9ca3af; }
.sl-pag-info { font-size: 0.65rem; color: #9ca3af; }

/* --- Empty state --- */
.sl-empty { text-align: center; padding: 2.5rem 1.5rem; }
.sl-empty-icon { width: 56px; height: 56px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #d1d5db; margin-bottom: 0.75rem; }
.sl-empty h3 { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.3rem; }
.sl-empty p { color: #9ca3af; font-size: 0.82rem; margin: 0 0 1rem; }

/* --- Mobile responsive --- */
@media (max-width: 768px) {
    .sl-header { flex-direction: column; align-items: stretch; }
    .sl-stats { grid-template-columns: 1fr 1fr 1fr; gap: 0.35rem; }
    .sl-stat { padding: 0.4rem 0.5rem; gap: 0.3rem; }
    .sl-stat-icon { width: 28px; height: 28px; font-size: 0.75rem; }
    .sl-stat-val { font-size: 0.95rem; }
    .sl-stat-lbl { font-size: 0.6rem; }
    .sl-card-head { flex-direction: column; align-items: stretch; padding: 0.4rem 0.5rem; }
    .sl-search input { width: 100%; }
    .sl-table { font-size: 0.72rem; }
    .sl-table td { padding: 0.25rem 0.35rem; }
    .sl-text { max-width: 65px; }
    .sl-avatar-img, .sl-avatar-char { width: 22px; height: 22px; font-size: 0.6rem; }
    .sl-act { width: 22px; height: 22px; font-size: 0.6rem; }
    .sl-btn { padding: 0.25rem 0.5rem; font-size: 0.68rem; }
    .sl-pag-btn { min-width: 24px; height: 24px; font-size: 0.65rem; }
    .sl-pag-info { font-size: 0.6rem; }
    .sl-name { max-width: 90px; }
}
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const input = document.getElementById('studentSearch');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('studentTable');
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
