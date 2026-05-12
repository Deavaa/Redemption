@extends('layouts.admin')
@section('title','ParentModel Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">ParentModel Details</h4>
<a href="{{route('admin.parents.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">First Name</th><td>{!! $data->first_name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Last Name</th><td>{!! $data->last_name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Email</th><td>{!! $data->email ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Phone</th><td>{!! $data->phone ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Occupation</th><td>{!! $data->occupation ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Address</th><td>{!! $data->address ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.parents.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
