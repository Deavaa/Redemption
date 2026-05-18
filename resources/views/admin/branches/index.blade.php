@extends('layouts.admin')
@section('title','Branches')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Branches Management</h4>
<a href="{{route('admin.branches.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Branch</a>
</div>
<div class="sc">
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead class="table-light">
<tr>
<th width="25%">Branch Name</th>
<th>Principal</th>
<th>Phone</th>
<th>Status</th>
<th width="15%">Actions</th>
</tr>
</thead>
<tbody>
@foreach($branches as $b)
<tr>
<td>
<div class="d-flex align-items-center">
<div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;">
<i class="fas fa-building text-danger"></i>
</div>
<div>
<strong>{{$b->name}}</strong>
@if($b->is_headquarters)
<br><span class="badge bg-warning text-dark" style="font-size:0.65rem"><i class="fas fa-star me-1"></i>Headquarters</span>
@endif
</div>
</div>
</td>
<td>
@if($b->principal)
<span class="d-flex align-items-center">
<div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width:28px;height:28px;">
<i class="fas fa-user-tie text-primary" style="font-size:0.7rem"></i>
</div>
{{$b->principal->full_name}}
</span>
@else
<span class="text-muted"><i class="fas fa-user-slash me-1"></i>Not assigned</span>
@endif
</td>
<td>{{$b->phone ?? 'N/A'}}</td>
<td>
<span class="badge {{$b->is_active ? 'bg-success' : 'bg-secondary'}}">
<i class="fas fa-circle me-1" style="font-size:0.5rem"></i>{{$b->is_active ? 'Active' : 'Inactive'}}
</span>
</td>
<td>
<div class="btn-group btn-group-sm">
<a href="{{route('admin.branches.edit',$b->id)}}" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
<form method="POST" action="{{route('admin.branches.destroy',$b->id)}}" onsubmit="return confirm('Delete this branch?')">
@csrf @method('DELETE')
<button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
</form>
</div>
</td>
</tr>
@endforeach
@if($branches->isEmpty())
<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>No branches found</td></tr>
@endif
</tbody>
</table>
</div>
</div>
@endsection
