@extends('student.layout')

@section('title', 'My Profile')

@section('content')
<div class="dash-welcome">
    <h2><i class="fas fa-user-circle me-2" style="color: var(--primary);"></i>My Profile</h2>
    <p>View your personal and academic information.</p>
</div>

{{-- Profile Header --}}
<div class="profile-header">
    <div class="profile-avatar">
        @if($student->photo)
            <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->first_name }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
        @else
            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
        @endif
    </div>
    <div class="profile-info">
        <h3>{{ $student->first_name }} {{ $student->last_name }}</h3>
        <p><i class="fas fa-id-badge me-1"></i> Admission No: {{ $student->admission_number ?? 'N/A' }}
            &nbsp;&bull;&nbsp;
            <i class="fas fa-hashtag me-1"></i> Roll No: {{ $student->roll_number ?? 'N/A' }}
        </p>
        <p>
            <i class="fas fa-school me-1"></i> {{ $student->classroom ? $student->classroom->name : 'N/A' }}
            &nbsp;&bull;&nbsp;
            <i class="fas fa-layer-group me-1"></i> Section: {{ $student->section ? $student->section->name : 'N/A' }}
            &nbsp;&bull;&nbsp;
            @if($student->status)
                <span class="grade-badge {{ $student->status === 'active' ? 'grade-a' : 'grade-c' }}">{{ ucfirst($student->status) }}</span>
            @endif
        </p>
    </div>
</div>

{{-- Personal Information --}}
<div class="row g-3">
    <div class="col-lg-8">
        <div class="student-card">
            <div class="student-card-header">
                <h5><i class="fas fa-user me-2" style="color: var(--primary);"></i>Personal Information</h5>
            </div>
            <div class="student-card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>First Name</label>
                        <span>{{ $student->first_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Last Name</label>
                        <span>{{ $student->last_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <span>{{ $student->email ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Phone</label>
                        <span>{{ $student->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Date of Birth</label>
                        <span>{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Gender</label>
                        <span>{{ ucfirst($student->gender ?? 'N/A') }}</span>
                    </div>
                    <div class="info-item">
                        <label>Blood Group</label>
                        <span>{{ $student->blood_group ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Religion</label>
                        <span>{{ $student->religion ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Nationality</label>
                        <span>{{ $student->nationality ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Admission Date</label>
                        <span>{{ $student->admission_date ? $student->admission_date->format('M d, Y') : 'N/A' }}</span>
                    </div>
                    <div class="info-item" style="grid-column: span 2;">
                        <label>Address</label>
                        <span>{{ $student->address ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Academic Info --}}
        <div class="student-card">
            <div class="student-card-header">
                <h5><i class="fas fa-graduation-cap me-2" style="color: var(--accent);"></i>Academic Info</h5>
            </div>
            <div class="student-card-body">
                <div style="display:flex;flex-direction:column;gap:14px;">
                    <div class="info-item">
                        <label>Class</label>
                        <span>{{ $student->classroom ? $student->classroom->name : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Section</label>
                        <span>{{ $student->section ? $student->section->name : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Academic Year</label>
                        <span>{{ $student->academicYear ? $student->academicYear->name : 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Admission Number</label>
                        <span>{{ $student->admission_number ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label>Roll Number</label>
                        <span>{{ $student->roll_number ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Parent/Guardian Info --}}
        @if($student->parents->count() > 0)
        <div class="student-card">
            <div class="student-card-header">
                <h5><i class="fas fa-user-friends me-2" style="color: var(--success);"></i>Parent/Guardian</h5>
            </div>
            <div class="student-card-body">
                @foreach($student->parents as $parent)
                    <div style="margin-bottom: {{ !$loop->last ? '14px' : '0' }}; {{ !$loop->last ? 'padding-bottom: 14px; border-bottom: 1px solid var(--border);' : '' }}">
                        <div class="fw-semibold" style="color: var(--text-dark); font-size: 13px;">{{ $parent->father_name ?? $parent->mother_name ?? $parent->name ?? 'N/A' }}</div>
                        @if($parent->pivot && $parent->pivot->relation)
                            <span class="grade-badge grade-b" style="font-size:10px;">{{ ucfirst($parent->pivot->relation) }}</span>
                        @endif
                        @if(isset($parent->phone))
                            <div style="font-size:12px; color: var(--text-muted); margin-top:4px;">
                                <i class="fas fa-phone me-1"></i>{{ $parent->phone }}
                            </div>
                        @endif
                        @if(isset($parent->email))
                            <div style="font-size:12px; color: var(--text-muted); margin-top:2px;">
                                <i class="fas fa-envelope me-1"></i>{{ $parent->email }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
