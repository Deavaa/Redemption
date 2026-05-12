@extends('layouts.admin')
@section('title','Add FinanceStatement')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Add FinanceStatement</h4>
<a href="{{route('admin.finance-statements.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.finance-statements.store')}}">
@csrf
<div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{old('name')}}" required></div>
<div class="mb-3"><label class="form-label">Type</label><input type="text" name="type" class="form-control" value="{{old('type')}}" required></div>
<div class="mb-3"><label class="form-label">Period Start</label><input type="text" name="period_start" class="form-control" value="{{old('period_start')}}" required></div>
<div class="mb-3"><label class="form-label">Period End</label><input type="text" name="period_end" class="form-control" value="{{old('period_end')}}" required></div>
<div class="mb-3"><label class="form-label">Total Income</label><input type="text" name="total_income" class="form-control" value="{{old('total_income')}}" required></div>
<div class="mb-3"><label class="form-label">Total Expense</label><input type="text" name="total_expense" class="form-control" value="{{old('total_expense')}}" required></div>
<div class="mb-3"><label class="form-label">Status</label><input type="text" name="status" class="form-control" value="{{old('status')}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
</form></div>
@endsection
