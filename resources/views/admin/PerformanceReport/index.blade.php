@extends('layouts.admin')
@section('title','PerformanceReport')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">PerformanceReport</h4>
<a href="{{route('admin.performance-reports.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add New</a>
</div>
<div class="sc">
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
{{session('success')}}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="table-responsive">
<table class="table table-hover table-bordered">
<thead class="table-dark"><tr><th>#</th><th>Student Id</th><th>Academic Year Id</th><th>Term Id</th><th>Class Id</th><th>Attendance Rate</th><th>Behavior Grade</th><th>Remarks</th><th>Actions</th></tr></thead>
<tbody>
@foreach($data as $item)
<tr><td>{{$loop->iteration}}</td><td>{!! $item->student_id ?? '-' !!}</td><td>{!! $item->academic_year_id ?? '-' !!}</td><td>{!! $item->term_id ?? '-' !!}</td><td>{!! $item->class_id ?? '-' !!}</td><td>{!! $item->attendance_rate ?? '-' !!}</td><td>{!! $item->behavior_grade ?? '-' !!}</td><td>{!! $item->remarks ?? '-' !!}</td>
<td class="nowrap">
<a href="{{route('admin.performance-reports.edit',$item->id)}}" class="btn btn-sm btn-warning me-1" title="Edit"><i class="fas fa-edit"></i></a>
<form method="POST" action="{{route('admin.performance-reports.destroy',$item->id)}}" style="display:inline" onsubmit="return confirm('Delete?')">
@csrf @method('DELETE')
<button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
</form></td></tr>
@endforeach
</tbody></table></div></div>
@endsection
