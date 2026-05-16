@extends('parent.layout')

@section('title', 'Profile - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="page-header">
    <div>
        <h4><i class="fas fa-id-card me-2" style="color: var(--primary);"></i> Student Profile</h4>
        <div class="page-header-sub">
            {{ $student->first_name }} {{ $student->last_name }}
        </div>
    </div>
    <a href="{{ route('parent.dashboard') }}" class="btn-modern btn-modern-outline">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

{{-- Student Info Header Card --}}
<div class="info-card" style="margin-bottom: 20px;">
    <div class="info-card-body" style="padding: 24px;">
        <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
            <div style="width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg, var(--primary), var(--accent)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:700; flex-shrink:0;">
                {{ strtoupper(substr($student->first_name, 0, 1)) }}
            </div>
            <div style="flex:1; min-width:200px;">
                <h3 style="font-size:20px; font-weight:700; color:var(--text-dark); margin-bottom:4px;">
                    {{ $student->first_name }} {{ $student->last_name }}
                </h3>
                <div style="display:flex; gap:12px; flex-wrap:wrap; font-size:13px; color:var(--text-muted);">
                    <span><i class="fas fa-building me-1"></i>{{ $student->classroom->name ?? 'No Class' }}</span>
                    @if($student->section)
                        <span><i class="fas fa-layer-group me-1"></i>{{ $student->section->name }}</span>
                    @endif
                    <span><i class="fas fa-hashtag me-1"></i>Roll: {{ $student->roll_number ?? 'N/A' }}</span>
                    <span><i class="fas fa-id-badge me-1"></i>Adm: {{ $student->admission_number ?? 'N/A' }}</span>
                </div>
            </div>
            <div>
                @if($student->status === 'active')
                    <span class="modern-badge modern-badge-green" style="font-size:13px; padding:5px 12px;"><i class="fas fa-check-circle me-1"></i>Active</span>
                @else
                    <span class="modern-badge modern-badge-red" style="font-size:13px; padding:5px 12px;"><i class="fas fa-times-circle me-1"></i>{{ ucfirst($student->status) }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
{{-- Personal Information --}}
<div class="col-md-6">
    <div class="info-card">
        <div class="info-card-header">
            <h5><i class="fas fa-user me-2" style="color: var(--primary);"></i> Personal Information</h5>
        </div>
        <div class="info-card-body">
            <div class="profile-detail">
                <div class="profile-item">
                    <div class="profile-item-label">First Name</div>
                    <div class="profile-item-value">{{ $student->first_name ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Last Name</div>
                    <div class="profile-item-value">{{ $student->last_name ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Gender</div>
                    <div class="profile-item-value">{{ ucfirst($student->gender ?? '—') }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Date of Birth</div>
                    <div class="profile-item-value">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Blood Group</div>
                    <div class="profile-item-value">{{ $student->blood_group ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Religion</div>
                    <div class="profile-item-value">{{ $student->religion ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Nationality</div>
                    <div class="profile-item-value">{{ $student->nationality ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Email</div>
                    <div class="profile-item-value">{{ $student->email ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Phone</div>
                    <div class="profile-item-value">{{ $student->phone ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Address</div>
                    <div class="profile-item-value">{{ $student->address ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Academic Information --}}
<div class="col-md-6">
    <div class="info-card" style="margin-bottom:20px;">
        <div class="info-card-header">
            <h5><i class="fas fa-graduation-cap me-2" style="color: var(--primary);"></i> Academic Information</h5>
        </div>
        <div class="info-card-body">
            <div class="profile-detail">
                <div class="profile-item">
                    <div class="profile-item-label">Class</div>
                    <div class="profile-item-value">{{ $student->classroom->name ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Section</div>
                    <div class="profile-item-value">{{ $student->section->name ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Academic Year</div>
                    <div class="profile-item-value">{{ $student->academicYear->name ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Roll Number</div>
                    <div class="profile-item-value">{{ $student->roll_number ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Admission Number</div>
                    <div class="profile-item-value">{{ $student->admission_number ?? '—' }}</div>
                </div>
                <div class="profile-item">
                    <div class="profile-item-label">Admission Date</div>
                    <div class="profile-item-value">{{ $student->admission_date ? $student->admission_date->format('M d, Y') : '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Parent/Guardian Information --}}
    <div class="info-card">
        <div class="info-card-header">
            <h5><i class="fas fa-user-friends me-2" style="color: var(--primary);"></i> Parent / Guardian Info</h5>
        </div>
        <div class="info-card-body">
            @foreach($student->parents as $sp)
            <div style="padding:12px; background:var(--body-bg); border-radius:var(--radius-sm); margin-bottom:10px;">
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    <span class="modern-badge modern-badge-orange">{{ ucfirst($sp->pivot->relation ?? 'parent') }}</span>
                    <strong style="font-size:14px; color:var(--text-dark);">
                        {{ $sp->pivot->relation === 'father' ? $sp->father_name : ($sp->pivot->relation === 'mother' ? $sp->mother_name : $sp->guardian_name) }}
                    </strong>
                </div>
                <div style="font-size:12px; color:var(--text-muted); display:flex; gap:16px; flex-wrap:wrap;">
                    @if($sp->father_phone)
                        <span><i class="fas fa-phone me-1"></i>{{ $sp->father_phone }}</span>
                    @endif
                    @if($sp->mother_phone)
                        <span><i class="fas fa-phone me-1"></i>{{ $sp->mother_phone }}</span>
                    @endif
                    @if($sp->guardian_phone)
                        <span><i class="fas fa-phone me-1"></i>{{ $sp->guardian_phone }}</span>
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Also show from the parent model directly --}}
            <div style="border-top:1px dashed var(--border); padding-top:12px; margin-top:4px;">
                <div style="font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">All Contacts</div>
                @if($parent->father_name)
                <div style="display:flex; align-items:center; gap:10px; padding:6px 0; font-size:13px;">
                    <span class="modern-badge modern-badge-amber" style="min-width:60px; justify-content:center;">Father</span>
                    <span style="color:var(--text-dark); font-weight:500;">{{ $parent->father_name }}</span>
                    @if($parent->father_phone)
                        <span style="color:var(--text-muted);"><i class="fas fa-phone me-1"></i>{{ $parent->father_phone }}</span>
                    @endif
                </div>
                @endif
                @if($parent->mother_name)
                <div style="display:flex; align-items:center; gap:10px; padding:6px 0; font-size:13px;">
                    <span class="modern-badge modern-badge-green" style="min-width:60px; justify-content:center;">Mother</span>
                    <span style="color:var(--text-dark); font-weight:500;">{{ $parent->mother_name }}</span>
                    @if($parent->mother_phone)
                        <span style="color:var(--text-muted);"><i class="fas fa-phone me-1"></i>{{ $parent->mother_phone }}</span>
                    @endif
                </div>
                @endif
                @if($parent->guardian_name)
                <div style="display:flex; align-items:center; gap:10px; padding:6px 0; font-size:13px;">
                    <span class="modern-badge modern-badge-blue" style="min-width:60px; justify-content:center;">Guardian</span>
                    <span style="color:var(--text-dark); font-weight:500;">{{ $parent->guardian_name }}</span>
                    @if($parent->guardian_relation)
                        <span style="color:var(--text-muted);">({{ $parent->guardian_relation }})</span>
                    @endif
                    @if($parent->guardian_phone)
                        <span style="color:var(--text-muted);"><i class="fas fa-phone me-1"></i>{{ $parent->guardian_phone }}</span>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection
