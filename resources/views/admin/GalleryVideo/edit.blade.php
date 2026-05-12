@extends('layouts.admin')
@section('title','Edit GalleryVideo')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Edit GalleryVideo</h4>
<a href="{{route('admin.gallery-videos.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.gallery-videos.update',$data->id)}}">
@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{$data->title ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Url</label><input type="text" name="url" class="form-control" value="{{$data->url ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="{{$data->category ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Description</label><input type="text" name="description" class="form-control" value="{{$data->description ?? ''}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
</form></div>
@endsection
