@extends('layouts.admin')
@section('title','Edit Budget')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Edit Budget</h4>
<a href="{{route('admin.budgets.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.budgets.update',$data->id)}}">
@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{$data->name ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Amount</label><input type="text" name="amount" class="form-control" value="{{$data->amount ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Academic Year Id</label><input type="text" name="academic_year_id" class="form-control" value="{{$data->academic_year_id ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="{{$data->category ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{$data->description ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Status</label><input type="text" name="status" class="form-control" value="{{$data->status ?? ''}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
</form></div>
@endsection
