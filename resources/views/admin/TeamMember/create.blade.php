@extends('layouts.admin')
@section('title','Add TeamMember')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Add TeamMember</h4>
<a href="{{route('admin.team-members.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.team-members.store')}}">
@csrf
<div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{old('name')}}" required></div>
<div class="mb-3"><label class="form-label">Position</label><input type="text" name="position" class="form-control" value="{{old('position')}}" required></div>
<div class="mb-3"><label class="form-label">Department</label><input type="text" name="department" class="form-control" value="{{old('department')}}" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="text" name="email" class="form-control" value="{{old('email')}}" required></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="{{old('phone')}}" required></div>
<div class="mb-3"><label class="form-label">Photo</label><input type="text" name="photo" class="form-control" value="{{old('photo')}}" required></div>
<div class="mb-3"><label class="form-label">Bio</label><input type="text" name="bio" class="form-control" value="{{old('bio')}}" required></div>
<div class="mb-3"><label class="form-label">Order</label><input type="text" name="order" class="form-control" value="{{old('order')}}" required></div>

<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
</form></div>
@endsection
