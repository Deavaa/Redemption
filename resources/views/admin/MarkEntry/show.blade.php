@extends('layouts.admin')
@section('title','MarkEntry Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">MarkEntry Details</h4>
<a href="{{route('admin.mark-entries.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Student Id</th><td>{!! $data->student_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Exam Id</th><td>{!! $data->exam_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Subject Id</th><td>{!! $data->subject_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Class Id</th><td>{!! $data->class_id ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Marks</th><td>{!! $data->marks ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Total Marks</th><td>{!! $data->total_marks ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Grade</th><td>{!! $data->grade ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Remarks</th><td>{!! $data->remarks ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.mark-entries.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
