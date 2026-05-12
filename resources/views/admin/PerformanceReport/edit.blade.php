@extends('layouts.admin')
@section('title','Edit PerformanceReport')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Edit PerformanceReport</h4>
<a href="{{route('admin.performance-reports.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.performance-reports.update',$data->id)}}">
@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Student Id</label><input type="text" name="student_id" class="form-control" value="{{$data->student_id ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Academic Year Id</label><input type="text" name="academic_year_id" class="form-control" value="{{$data->academic_year_id ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Term Id</label><input type="text" name="term_id" class="form-control" value="{{$data->term_id ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Class Id</label><input type="text" name="class_id" class="form-control" value="{{$data->class_id ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Attendance Rate</label><input type="text" name="attendance_rate" class="form-control" value="{{$data->attendance_rate ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Behavior Grade</label><input type="text" name="behavior_grade" class="form-control" value="{{$data->behavior_grade ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Remarks</label><input type="text" name="remarks" class="form-control" value="{{$data->remarks ?? ''}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
</form></div>
@endsection
