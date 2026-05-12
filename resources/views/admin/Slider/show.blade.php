@extends('layouts.admin')
@section('title','Slider Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Slider Details</h4>
<a href="{{route('admin.sliders.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Title</th><td>{!! $data->title ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Subtitle</th><td>{!! $data->subtitle ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Image Path</th><td>{!! $data->image_path ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Link</th><td>{!! $data->link ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Order</th><td>{!! $data->order ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Is Active</th><td>{!! $data->is_active ?? '-' !!}</td></tr>
</table>
<a href="{{route('admin.sliders.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
