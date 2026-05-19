@extends('layouts.admin')
@section('title', 'Student Details')

@push('styles')
<style>
.stu-page { animation: stuFadeIn 0.4s ease-out; }
@keyframes stuFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.stu-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.stu-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin: 0; }
.stu-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.stu-breadcrumb li a { color: var(--text-muted); text-decoration: none; }
.stu-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.stu-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.stu-profile-header { display: flex; align-items: center; gap: 1.25rem; padding: 1.5rem; }
.stu-avatar-lg { width: 80px; height: 80px; border-radius: 16px; background: linear-gradient(135deg,#6366f1,#818cf8); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 800; flex-shrink: 0; }
.stu-info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem 1.5rem; }
.stu-info-item { display: flex; flex-direction: column; }
.stu-info-label { font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 0.15rem; }
.stu-info-value { font-size: 0.92rem; font-weight: 600; color: var(--text-dark); }
@media (max-width: 768px) { .stu-info-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
@endpush

@section('content')
<div class="stu-page">
    <div class="stu-header">
        <div>
            <nav class="stu-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                <li class="active">{{ $student->full_name }}</li>
            </ol></nav>
        </div>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <a href="{{ route('admin.students.edit', $student->id) }}" class="btn-modern btn-modern-primary" style="font-size:0.82rem;padding:0.45rem 1rem;"><i class="fas fa-edit"></i> Edit</a>
            @if($student->status === 'active')
            <button type="button" class="btn-modern btn-modern-outline" style="font-size:0.82rem;padding:0.45rem 1rem;color:#ef4444;border-color:#ef4444;" onclick="if(confirm('Mark this student as left?')){ document.getElementById('leaveForm').submit(); }"><i class="fas fa-sign-out-alt"></i> Mark as Left</button>
            <form id="leaveForm" method="POST" action="{{ route('admin.students.mark-as-left', $student->id) }}" style="display:none;">@csrf <textarea name="leave_reason" style="display:none;">Student left school</textarea></form>
            @endif
            @if($student->canBeReadmitted())
            <a href="{{ route('admin.students.readmit', $student->id) }}" class="btn-modern btn-modern-outline" style="font-size:0.82rem;padding:0.45rem 1rem;color:#10b981;border-color:#10b981;"><i class="fas fa-redo"></i> Readmit</a>
            @endif
        </div>
    </div>

    {{-- Profile Header --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="stu-profile-header">
            <div class="stu-avatar-lg">{{ strtoupper(substr($student->full_name ?? 'S', 0, 1)) }}</div>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                    <h2 style="font-size:1.35rem;font-weight:800;color:var(--text-dark);margin:0;">{{ $student->full_name }}</h2>
                    @php $statusBadge = match($student->status) { 'active' => 'modern-badge-success', 'inactive' => 'modern-badge-danger', 'transferred' => 'modern-badge-warning', 'graduated' => 'modern-badge-info', default => 'modern-badge-light' }; @endphp
                    <span class="modern-badge {{ $statusBadge }}">{{ ucfirst($student->status) }}</span>
                    @if($student->is_readmitted)
                    <span class="modern-badge modern-badge-info"><i class="fas fa-redo"></i> Readmitted</span>
                    @endif
                </div>
                <div style="display:flex;gap:1.5rem;margin-top:0.5rem;font-size:0.85rem;color:var(--text-muted);flex-wrap:wrap;">
                    <span><i class="fas fa-id-badge"></i> {{ $student->admission_number }}</span>
                    <span><i class="fas fa-building"></i> {{ $student->classroom->name ?? '-' }} - {{ $student->section->name ?? '-' }}</span>
                    <span><i class="fas fa-calendar"></i> Admitted: {{ $student->admission_date ? $student->admission_date->format('M d, Y') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Personal Info --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#eef2ff;color:#4361ee;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user"></i></div>
                <h3 class="modern-card-title">Personal Information</h3>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
            <div class="stu-info-grid">
                <div class="stu-info-item"><div class="stu-info-label">Full Name</div><div class="stu-info-value">{{ $student->full_name ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Gender</div><div class="stu-info-value">{{ $student->gender ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Date of Birth</div><div class="stu-info-value">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Blood Group</div><div class="stu-info-value">{{ $student->blood_group ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Religion</div><div class="stu-info-value">{{ $student->religion ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Nationality</div><div class="stu-info-value">{{ $student->nationality ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Email</div><div class="stu-info-value">{{ $student->email ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Phone</div><div class="stu-info-value">{{ $student->phone ?? '-' }}</div></div>
                <div class="stu-info-item" style="grid-column:1/-1;"><div class="stu-info-label">Address</div><div class="stu-info-value">{{ $student->address ?? '-' }}</div></div>
            </div>
        </div>
    </div>

    {{-- Academic Info --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;color:#10b981;display:flex;align-items:center;justify-content:center;"><i class="fas fa-graduation-cap"></i></div>
                <h3 class="modern-card-title">Academic Information</h3>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
            <div class="stu-info-grid">
                <div class="stu-info-item"><div class="stu-info-label">Admission Number</div><div class="stu-info-value">{{ $student->admission_number }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Roll Number</div><div class="stu-info-value">{{ $student->roll_number ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Class</div><div class="stu-info-value">{{ $student->classroom->name ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Section</div><div class="stu-info-value">{{ $student->section->name ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Academic Year</div><div class="stu-info-value">{{ $student->academicYear->name ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Branch</div><div class="stu-info-value">{{ $student->branch->name ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Admission Date</div><div class="stu-info-value">{{ $student->admission_date ? $student->admission_date->format('M d, Y') : '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Previous School</div><div class="stu-info-value">{{ $student->previous_school ?? '-' }}</div></div>
                @if($student->is_readmitted)
                <div class="stu-info-item"><div class="stu-info-label">Readmission Count</div><div class="stu-info-value">{{ $student->readmission_count ?? 0 }}</div></div>
                @endif
            </div>
        </div>
    </div>

    {{-- Parent Info --}}
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#fefce8;color:#f59e0b;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-friends"></i></div>
                <h3 class="modern-card-title">Parent/Guardian</h3>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
            <div class="stu-info-grid">
                <div class="stu-info-item"><div class="stu-info-label">Guardian Name</div><div class="stu-info-value">{{ $student->guardian_name ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Guardian Phone</div><div class="stu-info-value">{{ $student->guardian_phone ?? '-' }}</div></div>
            </div>
            @if($student->parents && $student->parents->count() > 0)
            <div style="margin-top:1rem;">
                @foreach($student->parents as $parent)
                <div style="padding:0.75rem;background:#f9fafb;border-radius:10px;margin-bottom:0.5rem;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-weight:600;">{{ $parent->father_name ?? $parent->mother_name ?? '-' }}</div>
                        <div style="font-size:0.8rem;color:var(--text-muted);">{{ $parent->pivot->relation ?? 'Parent' }}</div>
                    </div>
                    <div style="font-size:0.85rem;">{{ $parent->father_phone ?? $parent->mother_phone ?? '-' }}</div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Leave Info (if student left) --}}
    @if($student->status !== 'active' && $student->leave_date)
    <div class="modern-card" style="margin-bottom:1.25rem;">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#fef2f2;color:#ef4444;display:flex;align-items:center;justify-content:center;"><i class="fas fa-sign-out-alt"></i></div>
                <h3 class="modern-card-title">Leave Information</h3>
            </div>
        </div>
        <div class="modern-card-body" style="padding:1.25rem 1.5rem;">
            <div class="stu-info-grid">
                <div class="stu-info-item"><div class="stu-info-label">Leave Date</div><div class="stu-info-value">{{ $student->leave_date ? $student->leave_date->format('M d, Y') : '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Leave Reason</div><div class="stu-info-value">{{ $student->leave_reason ?? '-' }}</div></div>
                <div class="stu-info-item"><div class="stu-info-label">Previous Class</div><div class="stu-info-value">{{ $student->previousClassroom->name ?? '-' }}</div></div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
