@extends('layouts.admin')
@section('title','Add Slider')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Add Slider</h4>
<a href="{{route('admin.sliders.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.sliders.store')}}">
@csrf
<div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{old('title')}}" required></div>
<div class="mb-3"><label class="form-label">Subtitle</label><input type="text" name="subtitle" class="form-control" value="{{old('subtitle')}}" required></div>
<div class="mb-3"><label class="form-label">Image Path</label><input type="text" name="image_path" class="form-control" value="{{old('image_path')}}" required></div>
<div class="mb-3"><label class="form-label">Link</label><input type="text" name="link" class="form-control" value="{{old('link')}}" required></div>
<div class="mb-3"><label class="form-label">Order</label><input type="text" name="order" class="form-control" value="{{old('order')}}" required></div>
<div class="mb-3"><label class="form-label">Is Active</label><input type="text" name="is_active" class="form-control" value="{{old('is_active')}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
</form></div>
@endsection
