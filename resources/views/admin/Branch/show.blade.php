@extends('layouts.admin')
@section('title','Branch Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Branch Details</h4>
<a href="{{route('admin.branches.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Name</th><td>{!! $data->name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Address</th><td>{!! $data->address ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Phone</th><td>{!! $data->phone ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Email</th><td>{!! $data->email ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Is Main</th><td>{!! $data->is_main ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.branches.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
