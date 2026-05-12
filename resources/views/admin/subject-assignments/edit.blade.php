@extends('layouts.admin')
@section('title', 'Edit Assignment')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Edit Assignment</h4><p class="text-muted mb-0">@if(is_null($assignment->section_id))Core - all sections @else Elective - section-specific @endif</p></div><a href="{{ route('admin.subject-assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="row justify-content-center"><div class="col-lg-8"><div class="card"><div class="card-body">
        <form method="POST" action="{{ route('admin.subject-assignments.update', $assignment) }}">@csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Academic Year</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $assignment->academic_year_id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Subject</label><select name="subject_id" class="form-select" required><option value="">-- Select --</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" {{ $assignment->subject_id == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Class</label><select name="class_id" class="form-select" required><option value="">-- Select --</option>@foreach($classes as $class)<option value="{{ $class->id }}" {{ $assignment->class_id == $class->id ? 'selected' : '' }}>{{ $class->name }} ({{ $class->branch->name }})</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Section @if(is_null($assignment->section_id))<span class="badge bg-primary ms-1">Core = All</span>@endif</label><select name="section_id" class="form-select"><option value="">-- All Sections (Core) --</option>@foreach($sections as $sec)<option value="{{ $sec->id }}" {{ $assignment->section_id == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>@endforeach</select><div class="form-text"><i class="bi bi-info-circle me-1"></i>Empty = Core (all). Selected = Elective (specific).</div></div>
                <div class="col-12"><label class="form-label fw-semibold">Teacher</label><select name="teacher_id" class="form-select"><option value="">-- No Teacher --</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" {{ $assignment->teacher_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->full_name }}</option>@endforeach</select></div>
            </div>
            <div class="d-flex gap-2 mt-4"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button><a href="{{ route('admin.subject-assignments.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div></div></div>
</div>
@endsection