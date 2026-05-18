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

    {{-- Selection Panel --}}
    <div class="modern-card" style="margin-bottom:12px;">
        <div class="certgen-toolbar">
            <span class="certgen-toolbar-label"><i class="fas fa-sliders-h" style="margin-right:4px;"></i> Select Class & Date</span>
        </div>
        <div style="padding:14px;">
            <form method="GET" action="{{ route('admin.attendance.create') }}" id="filterForm" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                <div style="display:flex;flex-direction:column;min-width:150px;">
                    <label style="font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase;">Date</label>
                    <input type="date" name="date" value="{{ $selectedDate }}" class="form-control form-control-sm" style="border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;">
                </div>
                <div style="display:flex;flex-direction:column;min-width:180px;">
                    <label style="font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase;">Class</label>
                    <select name="class_id" id="classSelect" class="form-select form-select-sm" style="border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;">
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
                <div style="display:flex;flex-direction:column;min-width:150px;">
                    <label style="font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase;">Section</label>
                    <select name="section_id" id="sectionSelect" class="form-select form-select-sm" style="border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;">
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
                <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.7rem;padding:5px 14px;"><i class="fas fa-search"></i> Load Students</button>
            </form>
        </div>
    </div>

    {{-- Homeroom / Delegation Info Banner --}}
    @if($isTeacher && isset($isHomeroomForClass))
    @if($isHomeroomForClass)
    <div style="padding:8px 14px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:8px;margin-bottom:10px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-check-circle" style="color:#10b981;"></i>
        <span style="font-size:0.72rem;font-weight:600;color:#065f46;">You are the homeroom teacher for this class. You can take attendance directly.</span>
    </div>
    @elseif($delegationInfo)
    <div style="padding:8px 14px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:8px;margin-bottom:10px;display:flex;align-items:center;gap:8px;">
        <i class="fas fa-exchange-alt" style="color:#f59e0b;"></i>
        <span style="font-size:0.72rem;font-weight:600;color:#92400e;">You are taking attendance via delegation for {{ $selectedDate }}. {{ $delegationInfo->reason ? 'Reason: ' . $delegationInfo->reason : '' }}</span>
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
            <div style="padding:10px 14px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Quick Mark:</span>
                <button type="button" onclick="markAll('present')" class="btn-modern" style="font-size:0.65rem;padding:3px 10px;background:rgba(16,185,129,0.12);color:#10b981;border:1px solid rgba(16,185,129,0.3);border-radius:6px;">
                    <i class="fas fa-check-circle"></i> All Present
                </button>
                <button type="button" onclick="markAll('absent')" class="btn-modern" style="font-size:0.65rem;padding:3px 10px;background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.3);border-radius:6px;">
                    <i class="fas fa-times-circle"></i> All Absent
                </button>
                <button type="button" onclick="markAll('late')" class="btn-modern" style="font-size:0.65rem;padding:3px 10px;background:rgba(245,158,11,0.12);color:#f59e0b;border:1px solid rgba(245,158,11,0.3);border-radius:6px;">
                    <i class="fas fa-clock"></i> All Late
                </button>
                <div style="flex:1;"></div>
                <span style="font-size:11px;color:var(--text-muted);">{{ $students->count() }} students</span>
                <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.7rem;padding:5px 16px;">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
            </div>
        </div>

        {{-- Student List --}}
        <div class="modern-card">
            <div style="padding:0;">
                <div class="table-responsive">
                    <table class="modern-table" style="width:100%;border-collapse:collapse;font-size:12px;">
                        <thead>
                            <tr style="background:var(--bg);border-bottom:2px solid var(--border);">
                                <th style="padding:8px 14px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);width:30px;">#</th>
                                <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Student Name</th>
                                <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Roll #</th>
                                <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Section</th>
                                <th style="padding:8px 10px;text-align:center;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);min-width:260px;">Status</th>
                                <th style="padding:8px 10px;text-align:left;font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:0.3px;color:var(--text-muted);">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                            @php
                                $existing = $existingAttendance->get($student->id);
                                $currentStatus = $existing ? $existing->status : 'present';
                                $currentRemarks = $existing ? $existing->remarks : '';
                            @endphp
                            <tr style="border-bottom:1px solid var(--border);transition:background 0.15s;" onmouseover="this.style.background='var(--primary-light)'" onmouseout="this.style.background='transparent'">
                                <td style="padding:8px 14px;color:var(--text-muted);font-weight:600;">{{ $index + 1 }}</td>
                                <td style="padding:8px 10px;font-weight:600;color:var(--text-dark);">
                                    {{ $student->full_name }}
                                    @if($existing)
                                    <i class="fas fa-check-circle" style="color:#10b981;font-size:9px;margin-left:4px;" title="Already recorded"></i>
                                    @endif
                                </td>
                                <td style="padding:8px 10px;color:var(--text-muted);">{{ $student->roll_number ?? '-' }}</td>
                                <td style="padding:8px 10px;color:var(--text-muted);">{{ $student->section?->name ?? '-' }}</td>
                                <td style="padding:8px 10px;">
                                    <input type="hidden" name="students[{{ $index }}][student_id]" value="{{ $student->id }}">
                                    <div style="display:flex;gap:4px;justify-content:center;">
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
                                            {{ ucfirst($status) }}
                                            <input type="radio" name="students[{{ $index }}][status]" value="{{ $status }}" {{ $isActive ? 'checked' : '' }}
                                                style="display:none;" class="att-status-radio" data-student="{{ $index }}">
                                        </label>
                                        @endforeach
                                    </div>
                                </td>
                                <td style="padding:8px 10px;">
                                    <input type="text" name="students[{{ $index }}][remarks]" value="{{ $currentRemarks }}"
                                        placeholder="Optional" class="form-control form-control-sm"
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
.att-status-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,0.08); }
.att-status-active { font-weight: 700 !important; }

/* Mobile responsive for attendance form */
@media (max-width: 768px) {
    .certgen-toolbar { font-size: 12px !important; }
    #filterForm {
        flex-direction: column !important;
        gap: 8px !important;
    }
    #filterForm > div {
        min-width: 100% !important;
    }
    .modern-table thead th:nth-child(3),
    .modern-table thead th:nth-child(4),
    .modern-table thead th:nth-child(6),
    .modern-table tbody td:nth-child(3),
    .modern-table tbody td:nth-child(4),
    .modern-table tbody td:nth-child(6) {
        display: none !important;
    }
    .att-status-btn {
        font-size: 9px !important;
        padding: 2px 5px !important;
    }
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
        document.querySelectorAll(`.att-status-btn[data-student="${studentIdx}"]`).forEach(function(btn) {
            btn.classList.remove('att-status-active');
            btn.style.fontWeight = '600';
            const status = btn.dataset.status;
            const colors = {
                present: '#10b981', absent: '#ef4444', late: '#f59e0b', excused: '#3b82f6'
            };
            const bgs = {
                present: 'rgba(16,185,129,0.12)', absent: 'rgba(239,68,68,0.12)', late: 'rgba(245,158,11,0.12)', excused: 'rgba(59,130,246,0.12)'
            };
            btn.style.borderColor = 'var(--border)';
            btn.style.background = 'var(--card-bg)';
            btn.style.color = 'var(--text-muted)';
        });
        // Activate selected
        const selectedBtn = document.querySelector(`.att-status-btn[data-student="${studentIdx}"][data-status="${this.value}"]`);
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
    document.querySelectorAll(`.att-status-radio[value="${status}"]`).forEach(function(radio) {
        radio.checked = true;
        radio.dispatchEvent(new Event('change'));
    });
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
            // Get unique sections from students
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
