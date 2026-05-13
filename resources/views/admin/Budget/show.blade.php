@extends('layouts.admin')
@section('title','Budget Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Budget Details</h4>
<a href="{{route('admin.budgets.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Name</th><td>{!! $data->name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Amount</th><td>{!! $data->amount ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Academic Year Id</th><td>{!! $data->academic_year_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Category</th><td>{!! $data->category ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Description</th><td>{!! $data->description ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Status</th><td>{!! $data->status ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.budgets.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
