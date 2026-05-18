@extends('layouts.portal')

@section('home_route', route('portal.dashboard'))

@section('title', 'My Profile')

@section('topbar_title', 'My Profile')

@section('sidebar_menu')
    <a href="{{ route('portal.dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="{{ route('portal.marks') }}"><i class="fas fa-chart-bar"></i> My Marks</a>
    <a href="{{ route('portal.fees') }}"><i class="fas fa-wallet"></i> Fee Progress</a>
    <a href="{{ route('portal.profile') }}" class="active"><i class="fas fa-user"></i> My Profile</a>
@endsection

@section('content')
@php
    $studentName = $student->full_name ?? 'Student';
    $photo = $student->photo ?? null;
@endphp

{{-- Profile Header --}}
<div class="portal-card">
    <div class="portal-card-body">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <div style="width:72px; height:72px; border-radius:50%; overflow:hidden; background:#e5e7eb; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                @if($photo)
                    <img src="{{ asset('storage/'.$photo) }}" alt="{{ $studentName }}"
                         style="width:100%; height:100%; object-fit:cover;">
                @else
                    <span style="font-size:1.6rem; font-weight:800; color:#4361ee;">
                        {{ strtoupper(substr($studentName, 0, 1)) }}
                    </span>
                @endif
            </div>
            <div>
                <h4 class="mb-1" style="font-weight:800; font-size:1.25rem;">{{ $studentName }}</h4>
                <p class="mb-0" style="color:#6b7280; font-size:0.88rem;">
                    @if($student->admission_number)
                        Admission #{{ $student->admission_number }}
                    @endif
                    @if($student->classroom)
                        &bull; {{ $student->classroom->name ?? '' }}
                    @endif
                    @if($student->section)
                        &bull; {{ $student->section->name ?? '' }}
                    @endif
                </p>
                @if($student->status)
                    <span class="badge {{ $student->status === 'active' ? 'bg-success' : 'bg-secondary' }}" style="font-size:0.75rem;">
                        {{ ucfirst($student->status) }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Personal Information --}}
    <div class="col-lg-6">
        <div class="portal-card">
            <div class="portal-card-header">
                <i class="fas fa-id-card" style="color:#4361ee;"></i>
                Personal Information
            </div>
            <div class="portal-card-body">
                <div class="row g-0">
                    @include('portal.student._profile_field', ['label' => 'Admission Number', 'value' => $student->admission_number])
                    @include('portal.student._profile_field', ['label' => 'Roll Number', 'value' => $student->roll_number])
                    @include('portal.student._profile_field', ['label' => 'Date of Birth', 'value' => $student->date_of_birth ? $student->date_of_birth->format('d M, Y') : null])
                    @include('portal.student._profile_field', ['label' => 'Gender', 'value' => $student->gender ? ucfirst($student->gender) : null])
                    @include('portal.student._profile_field', ['label' => 'Blood Group', 'value' => $student->blood_group])
                    @include('portal.student._profile_field', ['label' => 'Religion', 'value' => $student->religion])
                    @include('portal.student._profile_field', ['label' => 'Nationality', 'value' => $student->nationality])
                    @include('portal.student._profile_field', ['label' => 'Ethnicity', 'value' => $student->ethnicity])
                    @include('portal.student._profile_field', ['label' => 'Place of Birth', 'value' => $student->place_of_birth])
                    @include('portal.student._profile_field', ['label' => 'Email', 'value' => $student->email])
                    @include('portal.student._profile_field', ['label' => 'Phone', 'value' => $student->phone])
                    @include('portal.student._profile_field', ['label' => 'Address', 'value' => $student->address])
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        {{-- Academic Information --}}
        <div class="portal-card">
            <div class="portal-card-header">
                <i class="fas fa-graduation-cap" style="color:#8b5cf6;"></i>
                Academic Information
            </div>
            <div class="portal-card-body">
                <div class="row g-0">
                    @include('portal.student._profile_field', ['label' => 'Class', 'value' => $student->classroom->name ?? null])
                    @include('portal.student._profile_field', ['label' => 'Section', 'value' => $student->section->name ?? null])
                    @include('portal.student._profile_field', ['label' => 'Academic Year', 'value' => $student->academicYear->name ?? $student->academicYear->year ?? null])
                    @include('portal.student._profile_field', ['label' => 'Branch', 'value' => $student->branch->name ?? null])
                    @include('portal.student._profile_field', ['label' => 'Admission Date', 'value' => $student->admission_date ? $student->admission_date->format('d M, Y') : null])
                    @include('portal.student._profile_field', ['label' => 'Previous School', 'value' => $student->previous_school])
                </div>
            </div>
        </div>

        {{-- Medical Information --}}
        @if($student->medical_conditions || $student->allergies)
            <div class="portal-card">
                <div class="portal-card-header">
                    <i class="fas fa-heartbeat" style="color:#ef4444;"></i>
                    Medical Information
                </div>
                <div class="portal-card-body">
                    <div class="row g-0">
                        @include('portal.student._profile_field', ['label' => 'Medical Conditions', 'value' => $student->medical_conditions])
                        @include('portal.student._profile_field', ['label' => 'Allergies', 'value' => $student->allergies])
                    </div>
                </div>
            </div>
        @endif

        {{-- Parent / Guardian Information --}}
        @if($student->parents->count() > 0)
            @foreach($student->parents as $parent)
                <div class="portal-card">
                    <div class="portal-card-header">
                        <i class="fas fa-users" style="color:#f59e0b;"></i>
                        Parent / Guardian
                        @if($parent->pivot->relation)
                            <span class="badge bg-light text-dark ms-2" style="font-size:0.75rem;">
                                {{ $parent->pivot->relation }}
                            </span>
                        @endif
                    </div>
                    <div class="portal-card-body">
                        <div class="row g-0">
                            @include('portal.student._profile_field', ['label' => "Father's Name", 'value' => $parent->father_name])
                            @include('portal.student._profile_field', ['label' => "Father's Occupation", 'value' => $parent->father_occupation])
                            @include('portal.student._profile_field', ['label' => "Father's Phone", 'value' => $parent->father_phone])
                            @include('portal.student._profile_field', ['label' => "Mother's Name", 'value' => $parent->mother_name])
                            @include('portal.student._profile_field', ['label' => "Mother's Occupation", 'value' => $parent->mother_occupation])
                            @include('portal.student._profile_field', ['label' => "Mother's Phone", 'value' => $parent->mother_phone])
                            @include('portal.student._profile_field', ['label' => 'Guardian Name', 'value' => $parent->guardian_name])
                            @include('portal.student._profile_field', ['label' => 'Guardian Relation', 'value' => $parent->guardian_relation])
                            @include('portal.student._profile_field', ['label' => 'Guardian Phone', 'value' => $parent->guardian_phone])
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="portal-card">
                <div class="portal-card-header">
                    <i class="fas fa-users" style="color:#f59e0b;"></i>
                    Parent / Guardian
                </div>
                <div class="portal-card-body text-center py-3">
                    <p class="mb-0" style="color:#9ca3af; font-size:0.88rem;">
                        No parent or guardian information linked to this profile.
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
