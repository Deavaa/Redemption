@extends('layouts.admin')
@section('title','Add IncomeExpense')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Add IncomeExpense</h4>
<a href="{{route('admin.income-expenses.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.income-expenses.store')}}">
@csrf
<div class="mb-3"><label class="form-label">Type</label><input type="text" name="type" class="form-control" value="{{old('type')}}" required></div>
<div class="mb-3"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="{{old('category')}}" required></div>
<div class="mb-3"><label class="form-label">Amount</label><input type="text" name="amount" class="form-control" value="{{old('amount')}}" required></div>
<div class="mb-3"><label class="form-label">Date</label><input type="text" name="date" class="form-control" value="{{old('date')}}" required></div>
<div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{old('description')}}" required></div>
<div class="mb-3"><label class="form-label">Reference</label><input type="text" name="reference" class="form-control" value="{{old('reference')}}" required></div>
<div class="mb-3"><label class="form-label">Status</label><input type="text" name="status" class="form-control" value="{{old('status')}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
</form></div>
@endsection
