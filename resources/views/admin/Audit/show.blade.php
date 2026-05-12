@extends('layouts.admin')
@section('title','Audit Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Audit Details</h4>
<a href="{{route('admin.audits.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Name</th><td>{!! $data->name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Type</th><td>{!! $data->type ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Auditor</th><td>{!! $data->auditor ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Audit Date</th><td>{!! $data->audit_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Findings</th><td>{!! $data->findings ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Recommendations</th><td>{!! $data->recommendations ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Status</th><td>{!! $data->status ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.audits.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
