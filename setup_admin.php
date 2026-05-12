<?php
echo "Creating admin layout...\n";
 $v = 'resources/views/layouts/admin.blade.php';
 $c = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','Admin - SOR')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--sb:#0d0d2b;--gold:#c9a84c}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Poppins',sans-serif;background:#f0f2f5}
.sidebar{width:250px;background:var(--sb);color:#fff;position:fixed;top:0;left:0;bottom:0;overflow-y:auto;z-index:1050;transition:.3s}
.sidebar.hide{left:-250px}
.sb-brand{padding:18px;border-bottom:1px solid rgba(255,255,255,.1);text-align:center}
.sb-brand .sm{font-size:9px;color:var(--gold);text-transform:uppercase;letter-spacing:3px}
.sb-brand .lg{font-size:16px;font-family:'Playfair Display',serif;color:#fff}
.sb-menu{padding:10px 0}
.sb-label{padding:8px 18px;font-size:10px;text-transform:uppercase;color:rgba(255,255,255,.35);letter-spacing:2px}
.sb-menu a{display:flex;align-items:center;padding:9px 18px;color:rgba(255,255,255,.7);text-decoration:none;font-size:12.5px;transition:.2s;border-left:3px solid transparent}
.sb-menu a:hover,.sb-menu a.active{background:rgba(255,255,255,.05);color:var(--gold);border-left-color:var(--gold)}
.sb-menu a i{width:18px;margin-right:10px;text-align:center;font-size:13px}
.mc{margin-left:250px;transition:.3s}
.mc.full{margin-left:0}
.topbar{background:#fff;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,.08);position:sticky;top:0;z-index:1040}
.content{padding:20px}
.sc{background:#fff;border-radius:10px;padding:18px;box-shadow:0 1px 5px rgba(0,0,0,.06)}
@media(max-width:768px){.sidebar{left:-250px}.sidebar.show{left:0}.mc{margin-left:0}}
</style>
@stack('styles')
</head>
<body>
<div class="sidebar" id="sb">
<div class="sb-brand"><div class="sm">School of</div><div class="lg">REDEMPTION</div><div class="sm mt-1" style="letter-spacing:1px">Admin Panel</div></div>
<div class="sb-menu">
<a href="/admin/dashboard"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
<div class="sb-label">Academic</div>
<a href="/admin/academic-years"><i class="fas fa-calendar"></i>Academic Years</a>
<a href="/admin/terms"><i class="fas fa-bookmark"></i>Terms</a>
<a href="/admin/classes"><i class="fas fa-chalkboard"></i>Classes</a>
<a href="/admin/sections"><i class="fas fa-th-large"></i>Sections</a>
<a href="/admin/subjects"><i class="fas fa-book"></i>Subjects</a>
<div class="sb-label">People</div>
<a href="/admin/teachers"><i class="fas fa-chalkboard-teacher"></i>Teachers / Staff</a>
<a href="/admin/students"><i class="fas fa-user-graduate"></i>Students</a>
<a href="/admin/parents"><i class="fas fa-users"></i>Parents</a>
<a href="/admin/teacher-assignments"><i class="fas fa-tasks"></i>Assignments</a>
<div class="sb-label">Examinations</div>
<a href="/admin/exams"><i class="fas fa-file-alt"></i>Exams</a>
<a href="/admin/mark-entries"><i class="fas fa-pen"></i>Mark Entries</a>
<a href="/admin/certificates"><i class="fas fa-certificate"></i>Certificates</a>
<a href="/admin/id-cards"><i class="fas fa-id-card"></i>ID Cards</a>
<a href="/admin/progress-reports"><i class="fas fa-chart-line"></i>Progress Reports</a>
<a href="/admin/performance-reports"><i class="fas fa-chart-bar"></i>Performance</a>
<div class="sb-label">Finance</div>
<a href="/admin/fees"><i class="fas fa-money-bill-wave"></i>Fees</a>
<a href="/admin/fee-payments"><i class="fas fa-credit-card"></i>Fee Payments</a>
<a href="/admin/budgets"><i class="fas fa-wallet"></i>Budgets</a>
<a href="/admin/income-expenses"><i class="fas fa-exchange-alt"></i>Income / Expense</a>
<a href="/admin/finance-statements"><i class="fas fa-file-invoice-dollar"></i>Finance Statements</a>
<div class="sb-label">HR &amp; Assets</div>
<a href="/admin/leaves"><i class="fas fa-calendar-minus"></i>Leaves</a>
<a href="/admin/payrolls"><i class="fas fa-money-check-alt"></i>Payroll</a>
<a href="/admin/audits"><i class="fas fa-clipboard-check"></i>Audit</a>
<a href="/admin/class-assets"><i class="fas fa-box"></i>Class Assets</a>
<a href="/admin/employee-assets"><i class="fas fa-briefcase"></i>Employee Assets</a>
<div class="sb-label">Content</div>
<a href="/admin/branches"><i class="fas fa-building"></i>Branches</a>
<a href="/admin/team-members"><i class="fas fa-user-tie"></i>Team Members</a>
<a href="/admin/gallery-images"><i class="fas fa-image"></i>Gallery Images</a>
<a href="/admin/gallery-videos"><i class="fas fa-video"></i>Gallery Videos</a>
<a href="/admin/sliders"><i class="fas fa-images"></i>Sliders</a>
<a href="/admin/settings"><i class="fas fa-cog"></i>Settings</a>
<a href="/admin/contact-messages"><i class="fas fa-envelope"></i>Messages</a>
<hr style="border-color:rgba(255,255,255,.1);margin:10px 18px">
<a href="/" target="_blank"><i class="fas fa-globe"></i>View Website</a>
<a href="#" onclick="event.preventDefault();document.getElementById('lo').submit()"><i class="fas fa-sign-out-alt"></i>Logout</a>
<form id="lo" method="POST" action="{{route('logout')}}" style="display:none">@csrf</form>
</div>
</div>
<div class="mc" id="mc">
<div class="topbar">
<button class="btn btn-sm" onclick="document.getElementById('sb').classList.toggle('hide');document.getElementById('mc').classList.toggle('full')"><i class="fas fa-bars"></i></button>
<div class="d-flex align-items-center gap-2">
<i class="fas fa-user-circle text-primary" style="font-size:22px"></i>
<div><small class="text-muted">Welcome,</small><br><strong style="font-size:14px">{{auth()->user()->name??'Admin'}}</strong></div>
</div>
</div>
<div class="content">@yield('content')</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
HTML;
file_put_contents($v, $c);
echo "DONE: $v\n";
