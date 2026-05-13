@extends('layouts.admin')
@section('title','Subject Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Subject Details</h4>
<a href="{{route('admin.subjects.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Name</th><td>{!! $data->name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Code</th><td>{!! $data->code ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Type</th><td>{!! $data->type ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Description</th><td>{!! $data->description ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.subjects.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
