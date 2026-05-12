@extends('layouts.admin')
@section('title','GalleryVideo Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">GalleryVideo Details</h4>
<a href="{{route('admin.gallery-videos.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Title</th><td>{!! $data->title ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Url</th><td>{!! $data->url ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Category</th><td>{!! $data->category ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Description</th><td>{!! $data->description ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.gallery-videos.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
