@extends('layouts.admin')
@section('title','IdCard Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">IdCard Details</h4>
<a href="{{route('admin.id-cards.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Student Id</th><td>{!! $data->student_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Card Number</th><td>{!! $data->card_number ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Issue Date</th><td>{!! $data->issue_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Expiry Date</th><td>{!! $data->expiry_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Status</th><td>{!! $data->status ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.id-cards.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
