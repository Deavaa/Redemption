@extends('layouts.admin')
@section('title','Slider')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Slider</h4>
<a href="{{route('admin.sliders.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add New</a>
</div>
<div class="sc">
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
{{session('success')}}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="table-responsive">
<table class="table table-hover table-bordered">
<thead class="table-dark"><tr><th>#</th><th>Title</th><th>Subtitle</th><th>Image Path</th><th>Link</th><th>Order</th><th>Is Active</th><th>Actions</th></tr></thead>
<tbody>
@foreach($data as $item)
<tr><td>{{$loop->iteration}}</td><td>{!! $item->title ?? '-' !!}</td><td>{!! $item->subtitle ?? '-' !!}</td><td>{!! $item->image_path ?? '-' !!}</td><td>{!! $item->link ?? '-' !!}</td><td>{!! $item->order ?? '-' !!}</td><td>{!! $item->is_active ?? '-' !!}</td>
<td class="nowrap">
<a href="{{route('admin.sliders.edit',$item->id)}}" class="btn btn-sm btn-warning me-1" title="Edit"><i class="fas fa-edit"></i></a>
<form method="POST" action="{{route('admin.sliders.destroy',$item->id)}}" style="display:inline" onsubmit="return confirm('Delete?')">
@csrf @method('DELETE')
<button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
</form></td></tr>
@endforeach
</tbody></table></div></div>
@endsection
