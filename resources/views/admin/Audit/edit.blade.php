@extends('layouts.admin')
@section('title','Edit Audit')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Edit Audit</h4>
<a href="{{route('admin.audits.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.audits.update',$data->id)}}">
@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{$data->name ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Type</label><input type="text" name="type" class="form-control" value="{{$data->type ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Auditor</label><input type="text" name="auditor" class="form-control" value="{{$data->auditor ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Audit Date</label><input type="text" name="audit_date" class="form-control" value="{{$data->audit_date ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Findings</label><input type="text" name="findings" class="form-control" value="{{$data->findings ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Recommendations</label><input type="text" name="recommendations" class="form-control" value="{{$data->recommendations ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Status</label><input type="text" name="status" class="form-control" value="{{$data->status ?? ''}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
</form></div>
@endsection
