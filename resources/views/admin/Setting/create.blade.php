@extends('layouts.admin')
@section('title','Add Setting')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Add Setting</h4>
<a href="{{route('admin.settings.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.settings.store')}}">
@csrf
<div class="mb-3"><label class="form-label">Key</label><input type="text" name="key" class="form-control" value="{{old('key')}}" required></div>
<div class="mb-3"><label class="form-label">Value</label><input type="text" name="value" class="form-control" value="{{old('value')}}" required></div>
<div class="mb-3"><label class="form-label">Group</label><input type="text" name="group" class="form-control" value="{{old('group')}}" required></div>
<div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{old('description')}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
</form></div>
@endsection
