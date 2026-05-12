@extends('layouts.admin')
@section('title','Add PerformanceReport')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Add PerformanceReport</h4>
<a href="{{route('admin.performance-reports.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.performance-reports.store')}}">
@csrf
<div class="mb-3"><label class="form-label">Student Id</label><input type="text" name="student_id" class="form-control" value="{{old('student_id')}}" required></div>
<div class="mb-3"><label class="form-label">Academic Year Id</label><input type="text" name="academic_year_id" class="form-control" value="{{old('academic_year_id')}}" required></div>
<div class="mb-3"><label class="form-label">Term Id</label><input type="text" name="term_id" class="form-control" value="{{old('term_id')}}" required></div>
<div class="mb-3"><label class="form-label">Class Id</label><input type="text" name="class_id" class="form-control" value="{{old('class_id')}}" required></div>
<div class="mb-3"><label class="form-label">Attendance Rate</label><input type="text" name="attendance_rate" class="form-control" value="{{old('attendance_rate')}}" required></div>
<div class="mb-3"><label class="form-label">Behavior Grade</label><input type="text" name="behavior_grade" class="form-control" value="{{old('behavior_grade')}}" required></div>
<div class="mb-3"><label class="form-label">Remarks</label><input type="text" name="remarks" class="form-control" value="{{old('remarks')}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
</form></div>
@endsection
