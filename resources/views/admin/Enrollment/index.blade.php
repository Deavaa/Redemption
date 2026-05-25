@extends('layouts.admin')
@section('title', 'All Enrollments')

@section('content')
<div class="sl-page">
    {{-- Page Header --}}
    <div class="sl-header">
        <div class="sl-header-left">
            <nav aria-label="breadcrumb" class="sl-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Enrollment</a></li>
                    <li class="active">All Enrollments</li>
                </ol>
            </nav>
        </div>
        <div class="sl-header-right">
            <form method="POST" action="{{ route('admin.enrollments.sync') }}" style="display:inline;" onsubmit="return confirm('This will create enrollment records for all active students who do not have them in the current academic year. Continue?')">
                @csrf
                <button type="submit" class="sl-btn sl-btn-outline" style="background:#fef3c7;color:#92400e;border-color:#f59e0b;">
                    <i class="fas fa-sync-alt"></i> Sync Students
                </button>
            </form>
            <a href="{{ route('admin.enrollments.bulk-enroll') }}" class="sl-btn sl-btn-outline">
                <i class="fas fa-users"></i> Bulk Enroll
            </a>
            <a href="{{ route('admin.enrollments.create') }}" class="sl-btn sl-btn-primary">
                <i class="fas fa-plus"></i> Enroll Student
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="sl-stats">
        <div class="sl-stat">
            <div class="sl-stat-icon sl-stat-blue"><i class="fas fa-user-graduate"></i></div>
            <div class="sl-stat-body">
                <span class="sl-stat-val">{{ $totalEnrolled ?? 0 }}</span>
                <span class="sl-stat-lbl">Total Enrolled</span>
            </div>
        </div>
        <div class="sl-stat">
            <div class="sl-stat-icon sl-stat-green"><i class="fas fa-check-circle"></i></div>
            <div class="sl-stat-body">
                <span class="sl-stat-val">{{ $feePaid ?? 0 }}</span>
                <span class="sl-stat-lbl">Fee Paid</span>
            </div>
        </div>
        <div class="sl-stat">
            <div class="sl-stat-icon sl-stat-red"><i class="fas fa-times-circle"></i></div>
            <div class="sl-stat-body">
                <span class="sl-stat-val">{{ $feeUnpaid ?? 0 }}</span>
                <span class="sl-stat-lbl">Fee Unpaid</span>
            </div>
        </div>
        <div class="sl-stat">
            <div class="sl-stat-icon sl-stat-purple"><i class="fas fa-hand-holding-heart"></i></div>
            <div class="sl-stat-body">
                <span class="sl-stat-val">{{ $feeWaived ?? 0 }}</span>
                <span class="sl-stat-lbl">Fee Waived</span>
            </div>
        </div>
        <div class="sl-stat">
            <div class="sl-stat-icon sl-stat-gold"><i class="fas fa-coins"></i></div>
            <div class="sl-stat-body">
                <span class="sl-stat-val">{{ number_format($totalFeeCollected ?? 0, 0) }}</span>
                <span class="sl-stat-lbl">Fee Collected (ETB)</span>
            </div>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="sl-card" style="margin-bottom:0.75rem;">
        <div class="sl-card-head">
            <div class="sl-card-head-left">
                <h2 class="sl-card-title"><i class="fas fa-filter" style="margin-right:0.35rem;color:#4361ee;font-size:0.75rem;"></i> Filters</h2>
            </div>
        </div>
        <div class="sl-filter-body">
            <form method="GET" action="{{ route('admin.enrollments.index') }}" id="filterForm">
                <div class="sl-filter-grid">
                    <div class="sl-filter-group">
                        <label class="sl-filter-label">Academic Year</label>
                        <select name="academic_year_id" class="sl-filter-select">
                            <option value="">All Years</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ $academicYearId == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sl-filter-group">
                        <label class="sl-filter-label">Branch</label>
                        <select name="branch_id" class="sl-filter-select">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sl-filter-group">
                        <label class="sl-filter-label">Class</label>
                        <select name="class_id" class="sl-filter-select">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sl-filter-group">
                        <label class="sl-filter-label">Section</label>
                        <select name="section_id" class="sl-filter-select">
                            <option value="">All Sections</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ $sectionId == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sl-filter-group">
                        <label class="sl-filter-label">Status</label>
                        <select name="status" class="sl-filter-select">
                            <option value="">All Statuses</option>
                            <option value="enrolled" {{ $status === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="withdrawn" {{ $status === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                            <option value="graduated" {{ $status === 'graduated' ? 'selected' : '' }}>Graduated</option>
                            <option value="transferred" {{ $status === 'transferred' ? 'selected' : '' }}>Transferred</option>
                        </select>
                    </div>
                    <div class="sl-filter-group">
                        <label class="sl-filter-label">Fee Status</label>
                        <select name="fee_status" class="sl-filter-select">
                            <option value="">All Fee Statuses</option>
                            <option value="unpaid" {{ $feeStatus === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ $feeStatus === 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ $feeStatus === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="waived" {{ $feeStatus === 'waived' ? 'selected' : '' }}>Waived</option>
                        </select>
                    </div>
                    <div class="sl-filter-group">
                        <label class="sl-filter-label">Search</label>
                        <div class="sl-search">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search student...">
                        </div>
                    </div>
                    <div class="sl-filter-group" style="display:flex;align-items:flex-end;">
                        <button type="submit" class="sl-btn sl-btn-primary" style="width:100%;">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Enrollments Table Card --}}
    <div class="sl-card">
        <div class="sl-card-head">
            <div class="sl-card-head-left">
                <h2 class="sl-card-title">All Enrollments</h2>
                <span class="sl-count">{{ $enrollments->total() }}</span>
            </div>
        </div>

        @if(session('success'))
            <div class="sl-alert sl-alert-ok">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;margin-left:auto;opacity:0.6;color:inherit">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="sl-alert sl-alert-err">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;margin-left:auto;opacity:0.6;color:inherit">&times;</button>
            </div>
        @endif

        @if($enrollments->count() > 0)
        <div class="sl-table-wrap">
            <table class="sl-table" id="enrollmentTable">
                <thead>
                    <tr>
                        <th class="sl-th-narrow">#</th>
                        <th>Student</th>
                        <th>Branch</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Roll No</th>
                        <th>Enrollment Type</th>
                        <th class="sl-th-center">Reg. Fee Status</th>
                        <th class="sl-th-center">Status</th>
                        <th class="sl-th-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($enrollments as $enrollment)
                    <tr>
                        <td class="sl-td-narrow">
                            <span class="sl-num">{{ $loop->iteration + ($enrollments->currentPage() - 1) * $enrollments->perPage() }}</span>
                        </td>
                        <td>
                            <div class="sl-user">
                                @if($enrollment->student && $enrollment->student->photo)
                                    <img src="{{ asset('storage/' . $enrollment->student->photo) }}" alt="{{ $enrollment->student->full_name ?? '' }}" class="sl-avatar-img">
                                @else
                                    <div class="sl-avatar-char">{{ strtoupper(substr($enrollment->student->full_name ?? '?', 0, 1)) }}</div>
                                @endif
                                <div class="sl-user-info">
                                    <span class="sl-name">{{ $enrollment->student->full_name ?? '-' }}</span>
                                    <span class="sl-sub">{{ $enrollment->student->admission_number ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td><span class="sl-text">{{ $enrollment->branch->name ?? '-' }}</span></td>
                        <td><span class="sl-text">{{ $enrollment->classroom->name ?? '-' }}</span></td>
                        <td><span class="sl-text">{{ $enrollment->section->name ?? '-' }}</span></td>
                        <td><span class="sl-text">{{ $enrollment->roll_number ?? '-' }}</span></td>
                        <td>
                            @php
                                $etLabel = match($enrollment->enrollment_type ?? '') {
                                    'new' => 'New',
                                    'returning' => 'Returning',
                                    'transferred_in' => 'Transfer In',
                                    default => ucfirst($enrollment->enrollment_type ?? '-')
                                };
                            @endphp
                            <span class="sl-text">{{ $etLabel }}</span>
                        </td>
                        <td class="sl-td-center">
                            @php
                                $fsb = match($enrollment->registration_fee_status ?? '') {
                                    'paid' => 'sl-tag-green',
                                    'unpaid' => 'sl-tag-red',
                                    'partial' => 'sl-tag-yellow',
                                    'waived' => 'sl-tag-blue',
                                    default => 'sl-tag-gray'
                                };
                                $fsLabel = match($enrollment->registration_fee_status ?? '') {
                                    'waived' => 'Waived',
                                    default => ucfirst($enrollment->registration_fee_status ?? 'N/A')
                                };
                            @endphp
                            <span class="sl-tag {{ $fsb }}">{{ $fsLabel }}</span>
                        </td>
                        <td class="sl-td-center">
                            @php
                                $esb = match($enrollment->status ?? '') {
                                    'enrolled' => 'sl-tag-green',
                                    'pending' => 'sl-tag-yellow',
                                    'withdrawn' => 'sl-tag-red',
                                    'graduated' => 'sl-tag-blue',
                                    'transferred' => 'sl-tag-gray',
                                    default => 'sl-tag-gray'
                                };
                            @endphp
                            <span class="sl-tag {{ $esb }}">{{ ucfirst($enrollment->status ?? 'N/A') }}</span>
                        </td>
                        <td class="sl-td-right">
                            <div class="sl-actions">
                                <a href="{{ route('admin.enrollments.show', $enrollment->id) }}" class="sl-act sl-act-view" title="View"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.enrollments.edit', $enrollment->id) }}" class="sl-act sl-act-edit" title="Edit"><i class="fas fa-pen"></i></a>
                                @if(in_array($enrollment->registration_fee_status ?? '', ['unpaid', 'partial']))
                                <a href="{{ route('admin.enrollments.pay-registration-fee', $enrollment->id) }}" class="sl-act sl-act-pay" title="Pay Fee"><i class="fas fa-money-bill-wave"></i></a>
                                @endif
                                @if($enrollment->status === 'enrolled')
                                <a href="{{ route('admin.enrollments.withdraw', $enrollment->id) }}" class="sl-act sl-act-warn" title="Withdraw"><i class="fas fa-user-minus"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($enrollments->hasPages())
        <div class="sl-pag">
            <div class="sl-pag-nav">
                @if($enrollments->onFirstPage())
                    <span class="sl-pag-btn sl-pag-off">&lsaquo;</span>
                @else
                    <a href="{{ $enrollments->previousPageUrl() }}" class="sl-pag-btn">&lsaquo;</a>
                @endif

                @php
                    $cp = $enrollments->currentPage();
                    $lp = $enrollments->lastPage();
                    $s = max(1, $cp - 2);
                    $e = min($lp, $cp + 2);
                    if ($s > 1) { echo '<a href="' . $enrollments->url(1) . '" class="sl-pag-btn">1</a>'; if ($s > 2) echo '<span class="sl-pag-dots">...</span>'; }
                    for ($i = $s; $i <= $e; $i++) {
                        if ($i == $cp) echo '<span class="sl-pag-btn sl-pag-cur">' . $i . '</span>';
                        else echo '<a href="' . $enrollments->url($i) . '" class="sl-pag-btn">' . $i . '</a>';
                    }
                    if ($e < $lp) { if ($e < $lp - 1) echo '<span class="sl-pag-dots">...</span>'; echo '<a href="' . $enrollments->url($lp) . '" class="sl-pag-btn">' . $lp . '</a>'; }
                @endphp

                @if($enrollments->hasMorePages())
                    <a href="{{ $enrollments->nextPageUrl() }}" class="sl-pag-btn">&rsaquo;</a>
                @else
                    <span class="sl-pag-btn sl-pag-off">&rsaquo;</span>
                @endif
            </div>
            <span class="sl-pag-info">{{ $enrollments->firstItem() }}-{{ $enrollments->lastItem() }} of {{ $enrollments->total() }}</span>
        </div>
        @endif

        @else
        <div class="sl-empty">
            <div class="sl-empty-icon"><i class="fas fa-user-graduate"></i></div>
            <h3>No Enrollments Yet</h3>
            <p>Get started by syncing existing students or enrolling your first student.</p>
            <div style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">
                <form method="POST" action="{{ route('admin.enrollments.sync') }}" style="display:inline;" onsubmit="return confirm('This will create enrollment records for all active students who do not have them. Continue?')">
                    @csrf
                    <button type="submit" class="sl-btn sl-btn-outline" style="background:#fef3c7;color:#92400e;border-color:#f59e0b;">
                        <i class="fas fa-sync-alt"></i> Sync Existing Students
                    </button>
                </form>
                <a href="{{ route('admin.enrollments.create') }}" class="sl-btn sl-btn-primary"><i class="fas fa-plus"></i> Enroll Student</a>
                <a href="{{ route('admin.enrollments.bulk-enroll') }}" class="sl-btn sl-btn-outline"><i class="fas fa-users"></i> Bulk Enroll</a>
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
/* ========================================================
   ENROLLMENT LIST - sl-* namespace (matches Student index)
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
    grid-template-columns: repeat(5, 1fr);
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
.sl-stat-purple { background: #f3e8ff; color: #7c3aed; }
.sl-stat-gold { background: #fefce8; color: #d97706; }
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

/* --- Filter --- */
.sl-filter-body { padding: 0.75rem; }
.sl-filter-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.65rem;
    align-items: end;
}
.sl-filter-group { display: flex; flex-direction: column; }
.sl-filter-label {
    font-size: 0.68rem; font-weight: 600; color: #6b7280;
    margin-bottom: 0.2rem; text-transform: uppercase; letter-spacing: 0.3px;
}
.sl-filter-select,
.sl-filter-grid .sl-search input {
    border: 1px solid #e5e7eb; border-radius: 6px;
    padding: 0.35rem 0.55rem; font-size: 0.78rem;
    background: #f9fafb; color: #374151; outline: none;
    width: 100%;
}
.sl-filter-select:focus,
.sl-filter-grid .sl-search input:focus {
    border-color: #4361ee; background: #fff;
}

/* --- Search --- */
.sl-search { position: relative; display: flex; align-items: center; width: 100%; }
.sl-search i { position: absolute; left: 8px; color: #adb5bd; font-size: 0.75rem; }
.sl-search input {
    border: 1px solid #e5e7eb; border-radius: 6px;
    padding: 0.3rem 0.5rem 0.3rem 1.7rem; font-size: 0.78rem;
    width: 100%; background: #f9fafb; color: #374151; outline: none;
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
.sl-alert-err { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

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
.sl-user-info { display: flex; flex-direction: column; }
.sl-name { font-weight: 600; color: #1a1a2e; font-size: 0.78rem; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sl-sub { font-size: 0.65rem; color: #9ca3af; }
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
.sl-act-pay { background: #ecfdf5; color: #059669; }
.sl-act-pay:hover { background: #059669; color: #fff; }
.sl-act-warn { background: #fff7ed; color: #ea580c; }
.sl-act-warn:hover { background: #ea580c; color: #fff; }

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
@media (max-width: 1200px) {
    .sl-filter-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .sl-header { flex-direction: column; align-items: stretch; }
    .sl-stats { grid-template-columns: repeat(3, 1fr); gap: 0.35rem; }
    .sl-stat { padding: 0.4rem 0.5rem; gap: 0.3rem; }
    .sl-stat-icon { width: 28px; height: 28px; font-size: 0.75rem; }
    .sl-stat-val { font-size: 0.95rem; }
    .sl-stat-lbl { font-size: 0.6rem; }
    .sl-filter-grid { grid-template-columns: 1fr; }
    .sl-card-head { flex-direction: column; align-items: stretch; padding: 0.4rem 0.5rem; }
    .sl-table { font-size: 0.72rem; }
    .sl-table td { padding: 0.25rem 0.35rem; }
    .sl-text { max-width: 65px; }
    .sl-avatar-img, .sl-avatar-char { width: 22px; height: 22px; font-size: 0.6rem; }
    .sl-act { width: 22px; height: 22px; font-size: 0.6rem; }
    .sl-btn { padding: 0.25rem 0.5rem; font-size: 0.68rem; }
    .sl-name { max-width: 90px; }
}
</style>
@endpush

@push('scripts')
<script>
// Dynamic class/section filtering on enrollment page
document.addEventListener('DOMContentLoaded', function() {
    var branchSelect = document.querySelector('select[name="branch_id"]');
    var classSelect = document.querySelector('select[name="class_id"]');
    var sectionSelect = document.querySelector('select[name="section_id"]');
    var academicYearSelect = document.querySelector('select[name="academic_year_id"]');

    function loadClasses() {
        if (!classSelect) return;
        var branchId = branchSelect ? branchSelect.value : '';
        var ayId = academicYearSelect ? academicYearSelect.value : '';
        var currentClassId = '{{ $classId }}';

        var url = '{{ route("admin.enrollments.api.classes") }}?';
        if (branchId) url += 'branch_id=' + branchId + '&';
        if (ayId) url += 'academic_year_id=' + ayId;

        fetch(url)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                classSelect.innerHTML = '<option value="">All Classes</option>';
                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    if (c.id == currentClassId) opt.selected = true;
                    classSelect.appendChild(opt);
                });
                loadSections();
            })
            .catch(function(err) { console.error('Error loading classes:', err); });
    }

    function loadSections() {
        if (!sectionSelect) return;
        var classId = classSelect ? classSelect.value : '';
        if (!classId) {
            sectionSelect.innerHTML = '<option value="">All Sections</option>';
            return;
        }

        fetch('{{ route("admin.enrollments.api.sections") }}?class_id=' + classId)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                var currentSectionId = '{{ $sectionId }}';
                sectionSelect.innerHTML = '<option value="">All Sections</option>';
                data.forEach(function(s) {
                    var opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    if (s.id == currentSectionId) opt.selected = true;
                    sectionSelect.appendChild(opt);
                });
            })
            .catch(function(err) { console.error('Error loading sections:', err); });
    }

    if (branchSelect) branchSelect.addEventListener('change', loadClasses);
    if (academicYearSelect) academicYearSelect.addEventListener('change', loadClasses);
    if (classSelect) classSelect.addEventListener('change', loadSections);
});
</script>
@endpush
@endsection