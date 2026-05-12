@extends('layouts.admin')
@section('title','Add Audit')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Add Audit</h4>
<a href="{{route('admin.audits.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.audits.store')}}">
@csrf
<div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{old('name')}}" required></div>
<div class="mb-3"><label class="form-label">Type</label><input type="text" name="type" class="form-control" value="{{old('type')}}" required></div>
<div class="mb-3"><label class="form-label">Auditor</label><input type="text" name="auditor" class="form-control" value="{{old('auditor')}}" required></div>
<div class="mb-3"><label class="form-label">Audit Date</label><input type="text" name="audit_date" class="form-control" value="{{old('audit_date')}}" required></div>
<div class="mb-3"><label class="form-label">Findings</label><input type="text" name="findings" class="form-control" value="{{old('findings')}}" required></div>
<div class="mb-3"><label class="form-label">Recommendations</label><input type="text" name="recommendations" class="form-control" value="{{old('recommendations')}}" required></div>
<div class="mb-3"><label class="form-label">Status</label><input type="text" name="status" class="form-control" value="{{old('status')}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
</form></div>
@endsection
