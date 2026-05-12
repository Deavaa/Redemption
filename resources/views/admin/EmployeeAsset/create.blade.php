@extends('layouts.admin')
@section('title','Add EmployeeAsset')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Add EmployeeAsset</h4>
<a href="{{route('admin.employee-assets.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.employee-assets.store')}}">
@csrf
<div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{old('name')}}" required></div>
<div class="mb-3"><label class="form-label">Employee Id</label><input type="text" name="employee_id" class="form-control" value="{{old('employee_id')}}" required></div>
<div class="mb-3"><label class="form-label">Quantity</label><input type="text" name="quantity" class="form-control" value="{{old('quantity')}}" required></div>
<div class="mb-3"><label class="form-label">Condition</label><input type="text" name="condition" class="form-control" value="{{old('condition')}}" required></div>
<div class="mb-3"><label class="form-label">Purchase Date</label><input type="text" name="purchase_date" class="form-control" value="{{old('purchase_date')}}" required></div>
<div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{old('description')}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
</form></div>
@endsection
