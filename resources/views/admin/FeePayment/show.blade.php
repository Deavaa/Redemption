@extends('layouts.admin')
@section('title','FeePayment Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">FeePayment Details</h4>
<a href="{{route('admin.fee-payments.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Student Id</th><td>{!! $data->student_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Fee Id</th><td>{!! $data->fee_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Amount</th><td>{!! $data->amount ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Payment Date</th><td>{!! $data->payment_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Payment Method</th><td>{!! $data->payment_method ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Reference</th><td>{!! $data->reference ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Status</th><td>{!! $data->status ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.fee-payments.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
