@extends('layouts.admin')
@section('title','Add GalleryImage')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Add GalleryImage</h4>
<a href="{{route('admin.gallery-images.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.gallery-images.store')}}">
@csrf
<div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{old('title')}}" required></div>
<div class="mb-3"><label class="form-label">Image Path</label><input type="text" name="image_path" class="form-control" value="{{old('image_path')}}" required></div>
<div class="mb-3"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="{{old('category')}}" required></div>
<div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{old('description')}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
</form></div>
@endsection
