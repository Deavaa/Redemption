@extends('layouts.admin')
@section('title', 'Record Attendance')

@section('content')
<div class="modern-page">
    {{-- Header --}}
    <div class="modern-page-header" style="margin-bottom:0.75rem;">
        <div class="modern-page-header-left" style="display:flex;align-items:center;gap:10px;">
            <nav aria-label="breadcrumb" class="modern-breadcrumb" style="margin:0;">
                <ol style="margin:0;">
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
                    <li class="active">Record</li>
                </ol>
            </nav>
            <span style="color:var(--border);font-size:0.65rem;">|</span>
            <h1 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0;">Record Attendance</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.attendance.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:4px 10px;"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    {{-- Compact Filter Summary (shown when students are loaded) --}}
    @if($students->isNotEmpty())
    <div class="att-filter-summary visible" id="attFilterSummary">
        <i class="fas fa-check-circle" style="color:#10b981;"></i>
        <span id="attFilterSummaryText">
            @if($selectedClass)
                <span class="att-filter-chip"><i class="fas fa-chalkboard"></i> {{ $classes->where('id', $selectedClass)->first()?->name ?? 'Class' }}</span>
            @endif
            <span class="att-filter-chip"><i class="fas fa-calendar-alt"></i> {{ $selectedDate }}</span>
            @if($selectedSection)
                <span class="att-filter-chip"><i class="fas fa-layer-group"></i> {{ $sections->where('id', $selectedSection)->first()?->name ?? 'Section' }}</span>
            @endif
        </span>
        <button type="button" class="att-filter-change-btn" onclick="showAttFilterPanel()">
            <i class="fas fa-filter"></i> Change
        </button>
    </div>
    @endif

    {{-- Selection Panel --}}
    <div class="modern-card att-filter-card {{ $students->isNotEmpty() ? 'att-filter-collapsed' : '' }}" id="attFilterPanel" style="margin-bottom:12px;">
        <div class="certgen-toolbar">
            <span class="certgen-toolbar-label"><i class="fas fa-sliders-h" style="margin-right:4px;"></i> Select Class & Date</span>
        </div>
        <div class="att-filter-body" style="padding:14px;">
            <form method="GET" action="{{ route('admin.attendance.create') }}" id="filterForm" class="att-filter-form">
                <div class="att-filter-group">
                    <label class="att-filter-label">Date</label>
                    <input type="date" name="date" value="{{ $selectedDate }}" class="att-filter-input">
                </div>
                <div class="att-filter-group">
                    <label class="att-filter-label">Class</label>
                    <select name="class_id" id="classSelect" class="att-filter-input">
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $selectedClass == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                            @if($c->teacher)
                            ({{ $c->teacher->full_name }})
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="att-filter-group">
                    <label class="att-filter-label">Section</label>
                    <select name="section_id" id="sectionSelect" class="att-filter-input">
                        <option value="">All Sections</option>
                        @foreach($sections as $s)
                        <option value="{{ $s->id }}" {{ $selectedSection == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                            @if($s->teacher)
                            ({{ $s->teacher->full_name }})
                            @endif
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-modern btn-modern-primary att-filter-btn"><i class="fas fa-search"></i> Load Students</button>
            </form>
        </div>
    </div>

    {{-- Homeroom / Delegation Info Banner --}}
    @if($isTeacher && isset($isHomeroomForClass))
    @if($isHomeroomForClass)
    <div class="att-info-banner att-info-success">
        <i class="fas fa-check-circle"></i>
        <span>You are the homeroom teacher for this class. You can take attendance directly.</span>
    </div>
    @elseif($delegationInfo)
    <div class="att-info-banner att-info-warning">
        <i class="fas fa-exchange-alt"></i>
        <span>You are taking attendance via delegation for {{ $selectedDate }}. {{ $delegationInfo->reason ? 'Reason: ' . $delegationInfo->reason : '' }}</span>
    </div>
    @endif
    @endif

    {{-- Student Attendance Form --}}
    @if($students->isNotEmpty())
    <form method="POST" action="{{ route('admin.attendance.store') }}" id="attendanceForm">
        @csrf
        @method('POST')
        <input type="hidden" name="date" value="{{ $selectedDate }}">
        <input type="hidden" name="class_id" value="{{ $selectedClass }}">
        <input type="hidden" name="section_id" value="{{ $selectedSection }}">
        <input type="hidden" name="term_id" value="{{ $terms->first()?->id }}">

        {{-- Quick Actions --}}
        <div class="modern-card" style="margin-bottom:8px;">
            <div class="att-quick-actions">
                <span class="att-quick-label">Quick Mark:</span>
                <button type="button" onclick="markAll('present')" class="att-quick-btn att-quick-present">
                    <i class="fas fa-check-circle"></i> <span class="att-quick-text">All Present</span>
                </button>
                <button type="button" onclick="markAll('absent')" class="att-quick-btn att-quick-absent">
                    <i class="fas fa-times-circle"></i> <span class="att-quick-text">All Absent</span>
                </button>
                <button type="button" onclick="markAll('late')" class="att-quick-btn att-quick-late">
                    <i class="fas fa-clock"></i> <span class="att-quick-text">All Late</span>
                </button>
                <div style="flex:1;"></div>
                <span class="att-student-count">{{ $students->count() }} students</span>
                <button type="submit" class="btn-modern btn-modern-primary att-save-btn">
                    <i class="fas fa-save"></i> <span class="att-quick-text">Save Attendance</span>
                </button>
            </div>
        </div>

        {{-- Student List --}}
        <div class="modern-card">
            <div style="padding:0;">
                <div class="table-responsive">
                    <table class="modern-table att-table">
                        <thead>
                            <tr>
                                <th class="att-th att-th-left" style="width:30px;">#</th>
                                <th class="att-th att-th-left">Student Name</th>
                                <th class="att-th att-hide-mobile">Roll #</th>
                                <th class="att-th att-hide-mobile">Section</th>
                                <th class="att-th att-status-col">Status</th>
                                <th class="att-th att-hide-mobile">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                            @php
                                $existing = $existingAttendance->get($student->id);
                                $currentStatus = $existing ? $existing->status : 'present';
                                $currentRemarks = $existing ? $existing->remarks : '';
                            @endphp
                            <tr>
                                <td class="att-td att-td-muted">{{ $index + 1 }}</td>
                                <td class="att-td att-td-left att-td-bold">
                                    {{ $student->full_name }}
                                    @if($existing)
                                    <i class="fas fa-check-circle" style="color:#10b981;font-size:9px;margin-left:4px;" title="Already recorded"></i>
                                    @endif
                                    <span class="att-mobile-meta">
                                        @if($student->roll_number) #{{ $student->roll_number }}@endif
                                        {{ $student->section?->name ?? '' }}
                                    </span>
                                </td>
                                <td class="att-td att-hide-mobile att-td-muted">{{ $student->roll_number ?? '-' }}</td>
                                <td class="att-td att-hide-mobile att-td-muted">{{ $student->section?->name ?? '-' }}</td>
                                <td class="att-td">
                                    <input type="hidden" name="students[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    <div class="att-status-group">
                                        @foreach(['present','absent','late','excused'] as $status)
                                        @php
                                            $statusConfig = [
                                                'present' => ['color' => '#10b981', 'bg' => 'rgba(16,185,129,0.12)', 'icon' => 'fa-check-circle'],
                                                'absent'  => ['color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.12)', 'icon' => 'fa-times-circle'],
                                                'late'    => ['color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.12)', 'icon' => 'fa-clock'],
                                                'excused' => ['color' => '#3b82f6', 'bg' => 'rgba(59,130,246,0.12)', 'icon' => 'fa-info-circle'],
                                            ];
                                            $cfg = $statusConfig[$status];
                                            $isActive = $currentStatus === $status;
                                        @endphp
                                        <label class="att-status-btn {{ $isActive ? 'att-status-active' : '' }}" data-student="{{ $index }}" data-status="{{ $status }}"
                                            style="display:flex;align-items:center;gap:3px;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;cursor:pointer;
                                            border:1.5px solid {{ $isActive ? $cfg['color'] : 'var(--border)' }};
                                            background:{{ $isActive ? $cfg['bg'] : 'var(--card-bg)' }};
                                            color:{{ $isActive ? $cfg['color'] : 'var(--text-muted)' }};
                                            transition:all 0.15s;">
                                            <i class="fas {{ $cfg['icon'] }}"></i>
                                            <span class="att-status-label">{{ ucfirst($status) }}</span>
                                            <input type="radio" name="students[{{ $index }}][status]" value="{{ $status }}" {{ $isActive ? 'checked' : '' }}
                                                style="display:none;" class="att-status-radio" data-student="{{ $index }}">
                                        </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="att-td att-hide-mobile">
                                    <input type="text" name="students[{{ $index }}][remarks]" value="{{ $currentRemarks }}"
                                        placeholder="Optional" class="att-remarks-input"
                                        style="border:1.5px solid var(--border);border-radius:6px;padding:3px 8px;font-size:11px;min-width:120px;">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
    @elseif($selectedClass)
    <div class="modern-card" style="text-align:center;padding:3rem;">
        <i class="fas fa-users-slash" style="font-size:2rem;color:var(--text-muted);opacity:0.4;display:block;margin-bottom:8px;"></i>
        <p style="color:var(--text-muted);font-size:13px;">No active students found in this class/section.</p>
    </div>
    @else
    <div class="modern-card" style="text-align:center;padding:3rem;">
        <i class="fas fa-hand-pointer" style="font-size:2rem;color:var(--text-muted);opacity:0.4;display:block;margin-bottom:8px;"></i>
        <p style="color:var(--text-muted);font-size:13px;">Select a class and date above to load students.</p>
    </div>
    @endif
</div>

@push('styles')
<style>
/* ===== Attendance Record Styles ===== */
.att-status-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
.att-status-active { font-weight: 700 !important; }

/* Filter collapse */
.att-filter-card.att-filter-collapsed .att-filter-body { display: none; }
.att-filter-card.att-filter-collapsed .certgen-toolbar { border-bottom: none; }

/* Filter summary */
.att-filter-summary { display:none;align-items:center;gap:0.5rem;padding:0.6rem 1rem;background:#f0fdf4;border:1.5px solid #a7f3d0;border-radius:10px;margin-bottom:0.75rem;font-size:0.82rem;font-weight:600;color:#065f46;flex-wrap:wrap;animation:fadeIn 0.3s ease-out; }
.att-filter-summary.visible { display:flex; }
.att-filter-chip { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;background:#fff;border:1px solid #d1fae5;border-radius:6px;font-size:0.78rem;color:#1a1a2e; }
.att-filter-chip i { font-size:0.7rem;color:#10b981; }
.att-filter-change-btn { margin-left:auto;padding:4px 12px;border-radius:6px;border:1px solid #a7f3d0;background:#fff;color:#059669;font-size:0.78rem;font-weight:600;cursor:pointer;transition:all 0.2s;white-space:nowrap; }
.att-filter-change-btn:hover { background:#ecfdf5;border-color:#10b981; }

/* Filter form */
.att-filter-form { display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap; }
.att-filter-group { display:flex;flex-direction:column;min-width:150px; }
.att-filter-label { font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase; }
.att-filter-input { border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;font-family:var(--font);color:var(--text-dark);background:var(--card-bg); }
.att-filter-btn { font-size:0.7rem;padding:5px 14px; }

/* Info banners */
.att-info-banner { padding:8px 14px;border-radius:8px;margin-bottom:10px;display:flex;align-items:center;gap:8px;font-size:0.72rem;font-weight:600; }
.att-info-success { background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:#065f46; }
.att-info-warning { background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);color:#92400e; }

/* Quick actions */
.att-quick-actions { padding:10px 14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap; }
.att-quick-label { font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase; }
.att-quick-btn { font-size:0.65rem;padding:3px 10px;border-radius:6px;cursor:pointer;transition:all 0.15s;border:1px solid; }
.att-quick-present { background:rgba(16,185,129,0.12);color:#10b981;border-color:rgba(16,185,129,0.3); }
.att-quick-absent { background:rgba(239,68,68,0.12);color:#ef4444;border-color:rgba(239,68,68,0.3); }
.att-quick-late { background:rgba(245,158,11,0.12);color:#f59e0b;border-color:rgba(245,158,11,0.3); }
.att-student-count { font-size:11px;color:var(--text-muted); }
.att-save-btn { font-size:0.7rem;padding:5px 16px; }

/* Table */
.att-table { width:100%;border-collapse:collapse;font-size:12px; }
.att-th { padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);background:var(--bg);border-bottom:2px solid var(--border); }
.att-th-left { text-align:left;padding-left:14px; }
.att-status-col { min-width:260px; }
.att-td { padding:8px 10px;text-align:center;border-bottom:1px solid var(--border); }
.att-td-left { text-align:left;padding-left:14px; }
.att-td-bold { font-weight:600;color:var(--text-dark); }
.att-td-muted { color:var(--text-muted); }
.att-status-group { display:flex;gap:4px;justify-content:center;flex-wrap:wrap; }
.att-remarks-input { border:1.5px solid var(--border);border-radius:6px;padding:3px 8px;font-size:11px;min-width:120px; }

/* Mobile meta */
.att-mobile-meta { display:none;font-size:9px;color:var(--text-muted); }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .att-filter-form { flex-direction:column;gap:8px; }
    .att-filter-group { min-width:100%; }
    .att-filter-input { min-width:100%; }
    .att-filter-btn { width:100%;text-align:center; }
    .att-hide-mobile { display:none !important; }
    .att-mobile-meta { display:block !important; }
    .att-status-col { min-width:200px; }
    .att-status-btn { font-size:9px !important;padding:2px 5px !important; }
    .att-status-label { display:none; }
    .att-status-group { gap:2px; }
    .att-quick-actions { gap:4px;padding:8px 10px; }
    .att-quick-label { display:none; }
    .att-quick-text { font-size:0.6rem; }
    .att-save-btn { width:100%;text-align:center;margin-top:4px; }
}
@media (max-width: 480px) {
    .att-filter-summary { font-size:0.75rem;gap:0.35rem;padding:0.5rem 0.75rem; }
    .att-filter-chip { font-size:0.72rem;padding:2px 7px; }
    .att-status-btn { font-size:8px !important;padding:2px 4px !important; }
    .att-status-group { gap:1px; }
    .att-quick-btn { padding:2px 6px;font-size:0.58rem; }
    .att-quick-text { font-size:0.55rem; }
    .att-student-count { font-size:9px; }
}
</style>
@endpush

@push('scripts')
<script>
// Status button toggle
document.querySelectorAll('.att-status-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const studentIdx = this.dataset.student;
        // Remove active from siblings
        document.querySelectorAll('.att-status-btn[data-student="' + studentIdx + '"]').forEach(function(btn) {
            btn.classList.remove('att-status-active');
            btn.style.fontWeight = '600';
            btn.style.borderColor = 'var(--border)';
            btn.style.background = 'var(--card-bg)';
            btn.style.color = 'var(--text-muted)';
        });
        // Activate selected
        const selectedBtn = document.querySelector('.att-status-btn[data-student="' + studentIdx + '"][data-status="' + this.value + '"]');
        if (selectedBtn) {
            selectedBtn.classList.add('att-status-active');
            const colors = { present: '#10b981', absent: '#ef4444', late: '#f59e0b', excused: '#3b82f6' };
            const bgs = { present: 'rgba(16,185,129,0.12)', absent: 'rgba(239,68,68,0.12)', late: 'rgba(245,158,11,0.12)', excused: 'rgba(59,130,246,0.12)' };
            selectedBtn.style.borderColor = colors[this.value];
            selectedBtn.style.background = bgs[this.value];
            selectedBtn.style.color = colors[this.value];
            selectedBtn.style.fontWeight = '700';
        }
    });
});

// Mark all students with a status
function markAll(status) {
    document.querySelectorAll('.att-status-radio[value="' + status + '"]').forEach(function(radio) {
        radio.checked = true;
        radio.dispatchEvent(new Event('change'));
    });
}

// Show filter panel
function showAttFilterPanel() {
    var panel = document.getElementById('attFilterPanel');
    var summary = document.getElementById('attFilterSummary');
    panel.classList.remove('att-filter-collapsed');
    summary.classList.remove('visible');
    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Dynamic section loading
document.getElementById('classSelect').addEventListener('change', function() {
    const classId = this.value;
    const sectionSelect = document.getElementById('sectionSelect');
    sectionSelect.innerHTML = '<option value="">All Sections</option>';

    if (!classId) return;

    fetch('{{ route("admin.attendance.api.students") }}?class_id=' + classId)
        .then(r => r.json())
        .then(data => {
            const sections = [...new Set(data.map(s => s.section_name).filter(Boolean))];
            sections.forEach(name => {
                const opt = document.createElement('option');
                opt.value = name;
                opt.textContent = name;
                sectionSelect.appendChild(opt);
            });
        })
        .catch(() => {});
});
</script>
@endpush
@endsection
