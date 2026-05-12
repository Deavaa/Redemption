@extends('layouts.admin')
@section('title','Edit ContactMessage')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Edit ContactMessage</h4>
<a href="{{route('admin.contact-messages.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.contact-messages.update',$data->id)}}">
@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{$data->name ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="text" name="email" class="form-control" value="{{$data->email ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{$data->phone ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="{{$data->subject ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Message</label><input type="text" name="message" class="form-control" value="{{$data->message ?? ''}}" required></div>
<div class="mb-3"><label class="form-label">Is Read</label><input type="text" name="is_read" class="form-control" value="{{$data->is_read ?? ''}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
</form></div>
@endsection
