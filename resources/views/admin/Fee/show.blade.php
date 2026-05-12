@extends('layouts.admin')
@section('title','Fee Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Fee Details</h4>
<a href="{{route('admin.fees.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Name</th><td>{!! $data->name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Amount</th><td>{!! $data->amount ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Academic Year Id</th><td>{!! $data->academic_year_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Term Id</th><td>{!! $data->term_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Class Id</th><td>{!! $data->class_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Due Date</th><td>{!! $data->due_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Status</th><td>{!! $data->status ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.fees.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
