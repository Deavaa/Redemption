@extends('layouts.admin')
@section('title','Edit IncomeExpense')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Edit IncomeExpense</h4>
<a href="{{route('admin.income-expenses.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.income-expenses.update',$data->id)}}">
@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Type</label><input type="text" name="type" class="form-control" value="{{$data->type ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="{{$data->category ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Amount</label><input type="text" name="amount" class="form-control" value="{{$data->amount ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Date</label><input type="text" name="date" class="form-control" value="{{$data->date ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{$data->description ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Reference</label><input type="text" name="reference" class="form-control" value="{{$data->reference ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Status</label><input type="text" name="status" class="form-control" value="{{$data->status ?? ''}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
</form></div>
@endsection
