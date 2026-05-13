@extends('layouts.admin')
@section('title','FinanceStatement Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">FinanceStatement Details</h4>
<a href="{{route('admin.finance-statements.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Name</th><td>{!! $data->name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Type</th><td>{!! $data->type ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Period Start</th><td>{!! $data->period_start ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Period End</th><td>{!! $data->period_end ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Total Income</th><td>{!! $data->total_income ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Total Expense</th><td>{!! $data->total_expense ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Status</th><td>{!! $data->status ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.finance-statements.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
