@extends('layouts.admin')
@section('title','Edit Setting')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Edit Setting</h4>
<a href="{{route('admin.settings.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.settings.update',$data->id)}}">
@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Key</label><input type="text" name="key" class="form-control" value="{{$data->key ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Value</label><input type="text" name="value" class="form-control" value="{{$data->value ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Group</label><input type="text" name="group" class="form-control" value="{{$data->group ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{$data->description ?? ''}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
</form></div>
@endsection
