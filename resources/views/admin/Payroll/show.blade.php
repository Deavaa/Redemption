@extends('layouts.admin')
@section('title','Payroll Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Payroll Details</h4>
<a href="{{route('admin.payrolls.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Employee Id</th><td>{!! $data->employee_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Basic Salary</th><td>{!! $data->basic_salary ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Allowances</th><td>{!! $data->allowances ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Deductions</th><td>{!! $data->deductions ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Net Salary</th><td>{!! $data->net_salary ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Pay Date</th><td>{!! $data->pay_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Status</th><td>{!! $data->status ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.payrolls.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
