@extends('layouts.admin')
@section('title','Audit')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Audit</h4>
<a href="{{route('admin.audits.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add New</a>
</div>
<div class="sc">
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
{{session('success')}}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="table-responsive">
<table class="table table-hover table-bordered">
<thead class="table-dark"><tr><th>#</th><th>Name</th><th>Type</th><th>Auditor</th><th>Audit Date</th><th>Findings</th><th>Recommendations</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
@foreach($data as $item)
<tr><td>{{$loop->iteration}}</td><td>{!! $item->name ?? '-' !!}</td><td>{!! $item->type ?? '-' !!}</td><td>{!! $item->auditor ?? '-' !!}</td><td>{!! $item->audit_date ?? '-' !!}</td><td>{!! $item->findings ?? '-' !!}</td><td>{!! $item->recommendations ?? '-' !!}</td><td>{!! $item->status ?? '-' !!}</td>
<td class="nowrap">
<a href="{{route('admin.audits.edit',$item->id)}}" class="btn btn-sm btn-warning me-1" title="Edit"><i class="fas fa-edit"></i></a>
<form method="POST" action="{{route('admin.audits.destroy',$item->id)}}" style="display:inline" onsubmit="return confirm('Delete?')">
@csrf @method('DELETE')
<button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
</form></td></tr>
@endforeach
</tbody></table></div></div>
@endsection
