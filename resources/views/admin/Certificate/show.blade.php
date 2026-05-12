@extends('layouts.admin')
@section('title','Certificate Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Certificate Details</h4>
<a href="{{route('admin.certificates.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Student Id</th><td>{!! $data->student_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Certificate Type</th><td>{!! $data->certificate_type ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Certificate Number</th><td>{!! $data->certificate_number ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Issue Date</th><td>{!! $data->issue_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Description</th><td>{!! $data->description ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.certificates.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
