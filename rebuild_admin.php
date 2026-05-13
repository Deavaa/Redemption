<?php
echo "Rebuilding admin layout...\n";
 $v = 'resources/views/layouts/admin.blade.php';
// Build sidebar links with url() helper
 $links = [
'admin/dashboard'=>['Dashboard','fa-tachometer-alt',''],
'academic-years'=>['Academic Years','fa-calendar','Academic'],
'terms'=>['Terms','fa-bookmark',''],
'classes'=>['Classes','fa-chalkboard',''],
'sections'=>['Sections','fa-th-large',''],
'subjects'=>['Subjects','fa-book',''],
'teachers'=>['Teachers / Staff','fa-chalkboard-teacher','People'],
'students'=>['Students','fa-user-graduate',''],
'parents'=>['Parents','fa-users',''],
'teacher-assignments'=>['Assignments','fa-tasks',''],
'exams'=>['Exams','fa-file-alt','Examinations'],
'mark-entries'=>['Mark Entries','fa-pen',''],
'certificates'=>['Certificates','fa-certificate',''],
'id-cards'=>['ID Cards','fa-id-card',''],
'progress-reports'=>['Progress Reports','fa-chart-line',''],
'performance-reports'=>['Performance','fa-chart-bar',''],
'fees'=>['Fees','fa-money-bill-wave','Finance'],
'fee-payments'=>['Fee Payments','fa-credit-card',''],
'budgets'=>['Budgets','fa-wallet',''],
'income-expenses'=>['Income / Expense','fa-exchange-alt',''],
'finance-statements'=>['Finance Statements','fa-file-invoice-dollar',''],
'leaves'=>['Leaves','fa-calendar-minus','HR & Assets'],
'payrolls'=>['Payroll','fa-money-check-alt',''],
'audits'=>['Audit','fa-clipboard-check',''],
'class-assets'=>['Class Assets','fa-box',''],
'employee-assets'=>['Employee Assets','fa-briefcase',''],
'branches'=>['Branches','fa-building','Content'],
'team-members'=>['Team Members','fa-user-tie',''],
'gallery-images'=>['Gallery Images','fa-image',''],
'gallery-videos'=>['Gallery Videos','fa-video',''],
'sliders'=>['Sliders','fa-images',''],
'settings'=>['Settings','fa-cog',''],
'contact-messages'=>['Messages','fa-envelope',''],
];
 $menu = '';
 $lastLabel = '';
foreach($links as $path => $info){
  if($info[2] && $info[2] !== $lastLabel){
    $menu .= '<div class="sb-label">'.htmlspecialchars($info[2])."</div>\n";
    $lastLabel = $info[2];
  }
  $menu .= '<a href="{!! url("'.$path.'") !!}"><i class="fas '.$info[1].'"></i>'.htmlspecialchars($info[0])."</a>\n";
}
 $html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield(\'title\',\'Admin - SOR\')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--sb:#0d0d2b;--gold:#c9a84c}*{margin:0;padding:0;box-sizing:border-box}body{font-family:Poppins,sans-serif;background:#f0f2f5}.sidebar{width:250px;background:var(--sb);color:#fff;position:fixed;top:0;left:0;bottom:0;overflow-y:auto;z-index:1050;transition:.3s}.sidebar.hide{left:-250px}.sb-brand{padding:18px;border-bottom:1px solid rgba(255,255,255,.1);text-align:center}.sb-brand .sm{font-size:9px;color:var(--gold);text-transform:uppercase;letter-spacing:3px}.sb-brand .lg{font-size:16px;font-family:Playfair Display,serif;color:#fff}.sb-menu{padding:10px 0}.sb-label{padding:8px 18px;font-size:10px;text-transform:uppercase;color:rgba(255,255,255,.35);letter-spacing:2px}.sb-menu a{display:flex;align-items:center;padding:9px 18px;color:rgba(255,255,255,.7);text-decoration:none;font-size:12.5px;transition:.2s;border-left:3px solid transparent}.sb-menu a:hover,.sb-menu a.active{background:rgba(255,255,255,.05);color:var(--gold);border-left-color:var(--gold)}.sb-menu a i{width:18px;margin-right:10px;text-align:center;font-size:13px}.mc{margin-left:250px;transition:.3s}.mc.full{margin-left:0}.topbar{background:#fff;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,.08);position:sticky;top:0;z-index:1040}.content{padding:20px}.sc{background:#fff;border-radius:10px;padding:18px;box-shadow:0 1px 5px rgba(0,0,0,.06)}@media(max-width:768px){.sidebar{left:-250px}.sidebar.show{left:0}.mc{margin-left:0}}
</style>
@stack(\'styles\')
</head>
<body>
<div class="sidebar" id="sb">
<div class="sb-brand"><div class="sm">School of</div><div class="lg">REDEMPTION</div><div class="sm mt-1" style="letter-spacing:1px">Admin Panel</div></div>
<div class="sb-menu">
'.$menu.'
<hr style="border-color:rgba(255,255,255,.1);margin:10px 18px">
<a href="{!! url(\'/\') !!}" target="_blank"><i class="fas fa-globe"></i>View Website</a>
<a href="#" onclick="event.preventDefault();document.getElementById(\'lo\').submit()"><i class="fas fa-sign-out-alt"></i>Logout</a>
<form id="lo" method="POST" action="{!! route(\'logout\') !!}" style="display:none">@csrf</form>
</div>
</div>
<div class="mc" id="mc">
<div class="topbar">
<button class="btn btn-sm" onclick="document.getElementById(\'sb\').classList.toggle(\'hide\');document.getElementById(\'mc\').classList.toggle(\'full\')"><i class="fas fa-bars"></i></button>
<div class="d-flex align-items-center gap-2">
<i class="fas fa-user-circle text-primary" style="font-size:22px"></i>
<div><small class="text-muted">Welcome,</small><br><strong style="font-size:14px">{{auth()->user()->name??\'Admin\'}}</strong></div>
</div>
</div>
<div class="content">@yield(\'content\')</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack(\'scripts\')
</body>
</html>';
file_put_contents($v,$html);
echo "DONE: $v\n";
