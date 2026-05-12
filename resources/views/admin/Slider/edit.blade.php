@extends('layouts.admin')
@section('title','Edit Slider')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Edit Slider</h4>
<a href="{{route('admin.sliders.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.sliders.update',$data->id)}}">
@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="{{$data->title ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Subtitle</label><input type="text" name="subtitle" class="form-control" value="{{$data->subtitle ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Image Path</label><input type="text" name="image_path" class="form-control" value="{{$data->image_path ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Link</label><input type="text" name="link" class="form-control" value="{{$data->link ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Order</label><input type="text" name="order" class="form-control" value="{{$data->order ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Is Active</label><input type="text" name="is_active" class="form-control" value="{{$data->is_active ?? ''}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
</form></div>
@endsection
