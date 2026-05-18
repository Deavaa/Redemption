@extends('layouts.admin')
@section('title', 'Attendance Delegation')

@section('content')
<div class="modern-page">
    {{-- Header --}}
    <div class="modern-page-header" style="margin-bottom:0.75rem;">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.attendance.index') }}">Attendance</a></li>
                    <li class="active">Delegation</li>
                </ol>
            </nav>
            <h1 style="font-size:0.85rem;font-weight:700;color:var(--text-dark);margin:0;">Attendance Delegation</h1>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.attendance.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.7rem;padding:4px 10px;"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>

    {{-- Info Banner --}}
    <div class="modern-card" style="margin-bottom:12px;">
        <div style="padding:12px 16px;display:flex;align-items:flex-start;gap:10px;background:rgba(59,130,246,0.06);border:1px solid rgba(59,130,246,0.15);border-radius:10px;">
            <i class="fas fa-info-circle" style="color:#3b82f6;font-size:1rem;margin-top:2px;"></i>
            <div>
                <strong style="font-size:0.8rem;color:#1e40af;">How Attendance Delegation Works</strong>
                <p style="font-size:0.72rem;color:#3b82f6;margin:4px 0 0;line-height:1.5;">
                    Only <strong>homeroom teachers</strong> can take attendance for their assigned classes.
                    If a homeroom teacher is unavailable for the day, the <strong>branch principal</strong> or the <strong>homeroom teacher</strong>
                    themselves can delegate attendance-taking authority to another teacher for that specific date.
                    A class may have multiple homeroom teachers (one per class + one per section).
                </p>
            </div>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="modern-card" style="margin-bottom:12px;">
        <div style="padding:12px 16px;">
            <form method="GET" action="{{ route('admin.attendance-delegation.index') }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                <div style="display:flex;flex-direction:column;min-width:150px;">
                    <label style="font-size:9px;font-weight:600;color:var(--text-muted);margin-bottom:2px;text-transform:uppercase;">Date</label>
                    <input type="date" name="date" value="{{ $date }}" class="form-control form-control-sm" style="border:1.5px solid var(--border);border-radius:8px;padding:5px 10px;font-size:12px;">
                </div>
                <button type="submit" class="btn-modern btn-modern-primary" style="font-size:0.7rem;padding:5px 14px;"><i class="fas fa-search"></i> Filter</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        {{-- Create Delegation Form --}}
        <div class="col-md-5">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h2 class="modern-card-title" style="font-size:0.85rem;">
                        <i class="fas fa-user-plus" style="margin-right:6px;color:var(--primary);"></i>
                        Delegate Attendance
                    </h2>
                </div>
                <div style="padding:16px;">
                    @if(session('success'))
                    <div class="modern-alert modern-alert-success" style="margin-bottom:12px;">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    @endif

                    @if($errors->any())
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-bottom:12px;color:#991b1b;font-size:0.78rem;">
                        @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                        @endforeach
                    </div>
                    @endif

                    <form method="POST" action="{{ route('admin.attendance-delegation.store') }}">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date }}">

                        {{-- Class Selection --}}
                        <div style="margin-bottom:12px;">
                            <label style="font-size:0.7rem;font-weight:600;color:var(--text-dark);display:block;margin-bottom:4px;">Class <span style="color:var(--danger);">*</span></label>
                            <select name="class_id" id="delegClassSelect" class="form-select form-select-sm" required
                                style="border:1.5px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;width:100%;">
                                <option value="">-- Select Class --</option>
                                @foreach($classes as $c)
                                <option value="{{ $c->id }}" data-has-teacher="{{ $c->teacher_id ? '1' : '0' }}">
                                    {{ $c->name }}
                                    @if($c->teacher)
                                    (Homeroom: {{ trim($c->teacher->first_name . ' ' . $c->teacher->last_name) }})
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Section Selection (optional) --}}
                        <div style="margin-bottom:12px;">
                            <label style="font-size:0.7rem;font-weight:600;color:var(--text-dark);display:block;margin-bottom:4px;">Section <small style="font-weight:400;color:var(--text-muted);">(optional — leave empty for entire class)</small></label>
                            <select name="section_id" id="delegSectionSelect" class="form-select form-select-sm"
                                style="border:1.5px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;width:100%;">
                                <option value="">All Sections</option>
                            </select>
                        </div>

                        {{-- Teacher Selection --}}
                        <div style="margin-bottom:12px;">
                            <label style="font-size:0.7rem;font-weight:600;color:var(--text-dark);display:block;margin-bottom:4px;">Delegate To <span style="color:var(--danger);">*</span></label>
                            <select name="delegated_to_teacher_id" class="form-select form-select-sm" required
                                style="border:1.5px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;width:100%;">
                                <option value="">-- Select Teacher --</option>
                                @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ trim($t->first_name . ' ' . $t->last_name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Reason --}}
                        <div style="margin-bottom:14px;">
                            <label style="font-size:0.7rem;font-weight:600;color:var(--text-dark);display:block;margin-bottom:4px;">Reason <small style="font-weight:400;color:var(--text-muted);">(optional)</small></label>
                            <textarea name="reason" rows="2" placeholder="e.g., Homeroom teacher on sick leave"
                                style="border:1.5px solid var(--border);border-radius:8px;padding:6px 10px;font-size:12px;width:100%;resize:vertical;"></textarea>
                        </div>

                        <button type="submit" class="btn-modern btn-modern-primary" style="width:100%;justify-content:center;">
                            <i class="fas fa-check"></i> Create Delegation
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Active Delegations List --}}
        <div class="col-md-7">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h2 class="modern-card-title" style="font-size:0.85rem;">
                        <i class="fas fa-list" style="margin-right:6px;color:var(--primary);"></i>
                        Active Delegations for {{ $date }}
                    </h2>
                    <span class="modern-badge modern-badge-blue">{{ $delegations->count() }}</span>
                </div>
                <div style="padding:0;">
                    @if($delegations->count() > 0)
                    <div style="overflow-x:auto;">
                        <table class="modern-table" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Delegated To</th>
                                    <th>Reason</th>
                                    <th>Delegated By</th>
                                    <th style="text-align:center;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($delegations as $d)
                                <tr>
                                    <td style="font-weight:600;">{{ $d->classRoom?->name ?? '-' }}</td>
                                    <td>{{ $d->section?->name ?? 'All' }}</td>
                                    <td>
                                        <span style="font-weight:600;">{{ trim($d->delegatedTeacher?->first_name . ' ' . $d->delegatedTeacher?->last_name) }}</span>
                                    </td>
                                    <td style="font-size:0.72rem;color:var(--text-muted);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $d->reason ?? '-' }}
                                    </td>
                                    <td style="font-size:0.72rem;color:var(--text-muted);">{{ $d->delegatedBy?->name ?? '-' }}</td>
                                    <td style="text-align:center;">
                                        @if($d->is_active)
                                        <form method="POST" action="{{ route('admin.attendance-delegation.revoke', $d->id) }}" style="display:inline"
                                            onsubmit="return confirm('Revoke this delegation? The teacher will no longer be able to take attendance.')">
                                            @csrf @method('POST')
                                            <button type="submit" class="btn-modern btn-modern-danger btn-modern-sm" style="font-size:0.65rem;padding:3px 8px;">
                                                <i class="fas fa-times"></i> Revoke
                                            </button>
                                        </form>
                                        @else
                                        <span class="modern-badge modern-badge-light" style="font-size:0.6rem;">Revoked</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div style="text-align:center;padding:3rem;color:var(--text-muted);">
                        <i class="fas fa-clipboard-check" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:8px;"></i>
                        <p style="font-size:0.85rem;">No delegations for {{ $date }}</p>
                        <p style="font-size:0.72rem;">Create a delegation using the form on the left if a homeroom teacher is unavailable.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Dynamic section loading for delegation form
document.getElementById('delegClassSelect').addEventListener('change', function() {
    const classId = this.value;
    const sectionSelect = document.getElementById('delegSectionSelect');
    sectionSelect.innerHTML = '<option value="">All Sections</option>';

    if (!classId) return;

    fetch('{{ route("admin.attendance-delegation.api.sections", ["class" => "__CLASSID__"]) }}'.replace('__CLASSID__', classId))
        .then(r => r.json())
        .then(data => {
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.textContent = s.name + (s.teacher_name ? ' (Homeroom: ' + s.teacher_name + ')' : '');
                sectionSelect.appendChild(opt);
            });
        })
        .catch(() => {});
});
</script>
@endpush
@endsection
