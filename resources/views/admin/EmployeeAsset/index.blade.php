@extends('layouts.admin')
@section('title','EmployeeAsset')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">EmployeeAsset</h4>
<a href="{{route('admin.employee-assets.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add New</a>
</div>
<div class="sc">
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
{{session('success')}}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="table-responsive">
<table class="table table-hover table-bordered">
<thead class="table-dark"><tr><th>#</th><th>Name</th><th>Employee Id</th><th>Quantity</th><th>Condition</th><th>Purchase Date</th><th>Description</th><th>Actions</th></tr></thead>
<tbody>
@foreach($data as $item)
<tr><td>{{$loop->iteration}}</td><td>{!! $item->name ?? '-' !!}</td><td>{!! $item->employee_id ?? '-' !!}</td><td>{!! $item->quantity ?? '-' !!}</td><td>{!! $item->condition ?? '-' !!}</td><td>{!! $item->purchase_date ?? '-' !!}</td><td>{!! $item->description ?? '-' !!}</td>
<td class="nowrap">
<a href="{{route('admin.employee-assets.edit',$item->id)}}" class="btn btn-sm btn-warning me-1" title="Edit"><i class="fas fa-edit"></i></a>
<form method="POST" action="{{route('admin.employee-assets.destroy',$item->id)}}" style="display:inline" onsubmit="return confirm('Delete?')">
@csrf @method('DELETE')
<button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
</form></td></tr>
@endforeach
</tbody></table></div></div>
@endsection
