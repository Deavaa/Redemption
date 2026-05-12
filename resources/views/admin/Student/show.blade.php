@extends('layouts.admin')
@section('title','Student Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Student Details</h4>
<a href="{{route('admin.students.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">First Name</th><td>{!! $data->first_name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Last Name</th><td>{!! $data->last_name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Email</th><td>{!! $data->email ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Phone</th><td>{!! $data->phone ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Date Of Birth</th><td>{!! $data->date_of_birth ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Gender</th><td>{!! $data->gender ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Address</th><td>{!! $data->address ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Class Id</th><td>{!! $data->class_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Section Id</th><td>{!! $data->section_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Academic Year Id</th><td>{!! $data->academic_year_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Parent Id</th><td>{!! $data->parent_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Admission Date</th><td>{!! $data->admission_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Status</th><td>{!! $data->status ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.students.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
