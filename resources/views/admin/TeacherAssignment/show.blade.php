@extends('layouts.admin')
@section('title','TeacherAssignment Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">TeacherAssignment Details</h4>
<a href="{{route('admin.teacher-assignments.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Teacher Id</th><td>{!! $data->teacher_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Class Id</th><td>{!! $data->class_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Section Id</th><td>{!! $data->section_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Subject Id</th><td>{!! $data->subject_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Academic Year Id</th><td>{!! $data->academic_year_id ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.teacher-assignments.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
