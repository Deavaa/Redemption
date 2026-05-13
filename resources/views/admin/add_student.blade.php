@extends('layouts.admin')
@section('page-title', 'Add Student')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold text-warning mb-0"><i class="bi bi-mortarboard-fill me-2"></i>Add Student</h5>
        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>

    <ul class="nav nav-tabs" id="studentTab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="personal-info-tab" data-bs-toggle="tab" href="#personal-info" role="tab">Personal Information</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="academic-info-tab" data-bs-toggle="tab" href="#academic-info" role="tab">Academic Information</a>
        </li>
    </ul>
</div>
@endsection
