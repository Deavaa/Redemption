@extends('layouts.admin')
@section('title','Leave Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Leave Details</h4>
<a href="{{route('admin.leaves.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Employee Id</th><td>{!! $data->employee_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Leave Type</th><td>{!! $data->leave_type ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Start Date</th><td>{!! $data->start_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">End Date</th><td>{!! $data->end_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Reason</th><td>{!! $data->reason ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Status</th><td>{!! $data->status ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.leaves.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
