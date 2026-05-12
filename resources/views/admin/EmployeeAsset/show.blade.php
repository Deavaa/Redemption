@extends('layouts.admin')
@section('title','EmployeeAsset Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">EmployeeAsset Details</h4>
<a href="{{route('admin.employee-assets.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Name</th><td>{!! $data->name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Employee Id</th><td>{!! $data->employee_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Quantity</th><td>{!! $data->quantity ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Condition</th><td>{!! $data->condition ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Purchase Date</th><td>{!! $data->purchase_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Description</th><td>{!! $data->description ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.employee-assets.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
