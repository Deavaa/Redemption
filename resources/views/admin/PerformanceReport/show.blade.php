@extends('layouts.admin')
@section('title','PerformanceReport Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">PerformanceReport Details</h4>
<a href="{{route('admin.performance-reports.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Student Id</th><td>{!! $data->student_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Academic Year Id</th><td>{!! $data->academic_year_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Term Id</th><td>{!! $data->term_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Class Id</th><td>{!! $data->class_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Attendance Rate</th><td>{!! $data->attendance_rate ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Behavior Grade</th><td>{!! $data->behavior_grade ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Remarks</th><td>{!! $data->remarks ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.performance-reports.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
