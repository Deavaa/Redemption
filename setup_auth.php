<?php
echo "Creating login page...\n";
@mkdir('resources/views/auth', 0755, true);
 $c = <<<'HTML'
@extends('layouts.app')
@section('title','Login - School of Redemption')
@section('content')
<section style="min-height:80vh;display:flex;align-items:center;background:linear-gradient(135deg,#0d0d2b,#1a1a5e)">
<div class="container">
<div class="row justify-content-center">
<div class="col-md-5">
<div class="card p-4" style="border-radius:15px;border:none">
<div class="text-center mb-4">
<div style="font-size:11px;color:#c9a84c;text-transform:uppercase;letter-spacing:3px">School of</div>
<h3 style="font-family:'Playfair Display',serif;color:#0d0d2b">REDEMPTION</h3>
<p class="text-muted small">Sign in to access the admin panel</p>
</div>
@if(session('error'))
<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>{{session('error')}}</div>
@endif
<form method="POST" action="{{url('login')}}">
@csrf
<div class="mb-3">
<label class="form-label">Email Address</label>
<div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span>
<input type="email" name="email" class="form-control" value="{{old('email')}}" required></div>
</div>
<div class="mb-3">
<label class="form-label">Password</label>
<div class="input-group"><span class="input-group-text"><i class="fas fa-lock"></i></span>
<input type="password" name="password" class="form-control" required></div>
</div>
<button type="submit" class="btn w-100" style="background:#c9a84c;color:#0d0d2b;font-weight:600;padding:12px;border-radius:30px;border:none">
<i class="fas fa-sign-in-alt me-2"></i>Sign In
</button>
</form>
<div class="text-center mt-3">
<a href="{{url('/')}}" class="text-muted small"><i class="fas fa-arrow-left me-1"></i>Back to Website</a>
</div>
</div>
<div class="text-center mt-3">
<p class="text-white small opacity-75">Demo: admin@school.com / password</p>
</div>
</div></div></div></div>
</section>
@endsection
HTML;
file_put_contents('resources/views/auth/login.blade.php', $c);
echo "DONE: auth/login.blade.php\n";

echo "Creating admin dashboard...\n";
@mkdir('resources/views/admin', 0755, true);
 $c = <<<'HTML'
@extends('layouts.admin')
@section('title','Dashboard - School of Redemption')
@section('content')
<div class="mb-4">
<h4 class="text-primary">Dashboard</h4>
<p class="text-muted">Welcome to the School of Redemption Admin Panel</p>
</div>
<div class="row g-3 mb-4">
<div class="col-lg-3 col-md-6">
<div class="sc"><div class="d-flex align-items-center">
<div class="me-3" style="width:50px;height:50px;border-radius:10px;background:#e3f2fd;display:flex;align-items:center;justify-content:center"><i class="fas fa-user-graduate fa-lg" style="color:#1976d2"></i></div>
<div><h3 class="mb-0">{{\App\Models\Student::count()}}</h3><small class="text-muted">Students</small></div>
</div></div>
</div>
<div class="col-lg-3 col-md-6">
<div class="sc"><div class="d-flex align-items-center">
<div class="me-3" style="width:50px;height:50px;border-radius:10px;background:#fff3e0;display:flex;align-items:center;justify-content:center"><i class="fas fa-chalkboard-teacher fa-lg" style="color:#f57c00"></i></div>
<div><h3 class="mb-0">{{\App\Models\Teacher::count()}}</h3><small class="text-muted">Teachers</small></div>
</div></div>
</div>
<div class="col-lg-3 col-md-6">
<div class="sc"><div class="d-flex align-items-center">
<div class="me-3" style="width:50px;height:50px;border-radius:10px;background:#e8f5e9;display:flex;align-items:center;justify-content:center"><i class="fas fa-chalkboard fa-lg" style="color:#388e3c"></i></div>
<div><h3 class="mb-0">{{\App\Models\Classroom::count()}}</h3><small class="text-muted">Classes</small></div>
</div></div>
</div>
<div class="col-lg-3 col-md-6">
<div class="sc"><div class="d-flex align-items-center">
<div class="me-3" style="width:50px;height:50px;border-radius:10px;background:#fce4ec;display:flex;align-items:center;justify-content:center"><i class="fas fa-money-bill-wave fa-lg" style="color:#c62828"></i></div>
<div><h3 class="mb-0">{{\App\Models\FeePayment::count()}}</h3><small class="text-muted">Payments</small></div>
</div></div>
</div>
</div>
<div class="row g-3 mb-4">
<div class="col-lg-3 col-md-6">
<div class="sc"><div class="d-flex align-items-center">
<div class="me-3" style="width:50px;height:50px;border-radius:10px;background:#f3e5f5;display:flex;align-items:center;justify-content:center"><i class="fas fa-book fa-lg" style="color:#7b1fa2"></i></div>
<div><h3 class="mb-0">{{\App\Models\Subject::count()}}</h3><small class="text-muted">Subjects</small></div>
</div></div>
</div>
<div class="col-lg-3 col-md-6">
<div class="sc"><div class="d-flex align-items-center">
<div class="me-3" style="width:50px;height:50px;border-radius:10px;background:#e0f7fa;display:flex;align-items:center;justify-content:center"><i class="fas fa-calendar fa-lg" style="color:#00838f"></i></div>
<div><h3 class="mb-0">{{\App\Models\Exam::count()}}</h3><small class="text-muted">Exams</small></div>
</div></div>
</div>
<div class="col-lg-3 col-md-6">
<div class="sc"><div class="d-flex align-items-center">
<div class="me-3" style="width:50px;height:50px;border-radius:10px;background:#fff8e1;display:flex;align-items:center;justify-content:center"><i class="fas fa-users fa-lg" style="color:#f9a825"></i></div>
<div><h3 class="mb-0">{{\App\Models\ParentModel::count()}}</h3><small class="text-muted">Parents</small></div>
</div></div>
</div>
<div class="col-lg-3 col-md-6">
<div class="sc"><div class="d-flex align-items-center">
<div class="me-3" style="width:50px;height:50px;border-radius:10px;background:#efebe9;display:flex;align-items:center;justify-content:center"><i class="fas fa-envelope fa-lg" style="color:#5d4037"></i></div>
<div><h3 class="mb-0">{{\App\Models\ContactMessage::count()}}</h3><small class="text-muted">Messages</small></div>
</div></div>
</div>
</div>
<div class="row g-3">
<div class="col-md-6"><div class="sc">
<h5><i class="fas fa-bolt gold-text me-2"></i>Quick Actions</h5>
<hr>
<div class="d-flex flex-wrap gap-2 mt-3">
<a href="/admin/students/create" class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-1"></i>Add Student</a>
<a href="/admin/teachers/create" class="btn btn-warning btn-sm"><i class="fas fa-user-tie me-1"></i>Add Teacher</a>
<a href="/admin/exams/create" class="btn btn-success btn-sm"><i class="fas fa-file-alt me-1"></i>Create Exam</a>
<a href="/admin/mark-entries/create" class="btn btn-info btn-sm text-white"><i class="fas fa-pen me-1"></i>Enter Marks</a>
<a href="/admin/fees" class="btn btn-danger btn-sm"><i class="fas fa-money-bill me-1"></i>Manage Fees</a>
<a href="/admin/sliders" class="btn btn-secondary btn-sm"><i class="fas fa-images me-1"></i>Manage Sliders</a>
</div></div>
</div>
<div class="col-md-6"><div class="sc">
<h5><i class="fas fa-info-circle gold-text me-2"></i>System Info</h5>
<hr>
<table class="table table-sm small mb-0">
<tr><td class="text-muted">Application</td><td><strong>School of Redemption ERP</strong></td></tr>
<tr><td class="text-muted">Framework</td><td>Laravel {{app()->version()}}</td></tr>
<tr><td class="text-muted">PHP Version</td><td>{{PHP_VERSION}}</td></tr>
<tr><td class="text-muted">Environment</td><td>{{app()->environment()}}</td></tr>
<tr><td class="text-muted">Database</td><td>MySQL Connected</td></tr>
</table></div>
</div>
</div>
@endsection
HTML;
file_put_contents('resources/views/admin/dashboard.blade.php', $c);
echo "DONE: admin/dashboard.blade.php\n";
