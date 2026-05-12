@extends('layouts.admin')
@section('title','Teacher Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Teacher Details</h4>
<a href="{{route('admin.teachers.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">First Name</th><td>{!! $data->first_name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Last Name</th><td>{!! $data->last_name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Email</th><td>{!! $data->email ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Phone</th><td>{!! $data->phone ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Qualification</th><td>{!! $data->qualification ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Department</th><td>{!! $data->department ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Hire Date</th><td>{!! $data->hire_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Salary</th><td>{!! $data->salary ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Status</th><td>{!! $data->status ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.teachers.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
