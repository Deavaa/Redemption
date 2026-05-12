@extends('layouts.admin')
@section('title','AcademicYear Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">AcademicYear Details</h4>
<a href="{{route('admin.academic-years.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Name</th><td>{!! $data->name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Start Date</th><td>{!! $data->start_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">End Date</th><td>{!! $data->end_date ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Is Current</th><td>{!! $data->is_current ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.academic-years.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
