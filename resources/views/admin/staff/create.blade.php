@extends('layouts.admin')
@section('title', 'Add Staff')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Add Staff / Teacher</h4></div><a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="row justify-content-center"><div class="col-lg-8"><div class="card"><div class="card-header bg-light fw-semibold"><i class="bi bi-person-plus me-1"></i> Staff Information</div><div class="card-body">
        <form method="POST" action="{{ route('admin.staff.store') }}">@csrf
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required autofocus></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Phone</label><input type="text" name="phone" class="form-control" placeholder="+251-XXX-XXXXXX"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Role <span class="text-danger">*</span></label><select name="role" class="form-select" required><option value="teacher">Teacher</option><option value="admin">Admin</option></select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Gender</label><select name="gender" class="form-select"><option value="">-- Select --</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Qualification</label><input type="text" name="qualification" class="form-control" placeholder="BA, BSc, MA, PhD"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required minlength="6"></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label><input type="password" name="password_confirmation" class="form-control" required></div>
                <div class="col-12"><label class="form-label fw-semibold">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Save Staff</button><a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div></div></div>
</div>
@endsection