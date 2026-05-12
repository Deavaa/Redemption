<?php
echo "Checking models...\n";
 $models = glob('app/Models/*.php');
 $names = [];
foreach($models as $m){
    $names[] = basename($m,'.php');
}
echo "Found: ".implode(', ',$names)."\n\n";

// Map dashboard widgets to correct model names
 $map = [
    'Student' => in_array('Student',$names) ? 'Student' : null,
    'Teacher' => in_array('Teacher',$names) ? 'Teacher' : (in_array('Staff',$names) ? 'Staff' : null),
    'Classroom' => in_array('Classroom',$names) ? 'Classroom' : null,
    'FeePayment' => in_array('FeePayment',$names) ? 'FeePayment' : null,
    'Subject' => in_array('Subject',$names) ? 'Subject' : null,
    'Exam' => in_array('Exam',$names) ? 'Exam' : null,
    'ParentModel' => in_array('ParentModel',$names) ? 'ParentModel' : (in_array('Parent',$names) ? 'Parent' : null),
    'ContactMessage' => in_array('ContactMessage',$names) ? 'ContactMessage' : null,
];

// Build dashboard with available models only
 $cards = '';
 $colors = ['#e3f2fd,#1976d2,fa-user-graduate','#fff3e0,#f57c00,fa-chalkboard-teacher','#e8f5e9,#388e3c,fa-chalkboard','#fce4ec,#c62828,fa-money-bill-wave','#f3e5f5,#7b1fa2,fa-book','#e0f7fa,#00838f,fa-calendar','#fff8e1,#f9a825,fa-users','#efebe9,#5d4037,fa-envelope'];
 $labels = ['Students','Teachers','Classes','Payments','Subjects','Exams','Parents','Messages'];
 $i = 0;
foreach($map as $model => $cls){
    if(!$cls){ $i++; continue; }
    $c = explode(',',$colors[$i]);
    $cards .= "<div class=\"col-lg-3 col-md-6\">\n<div class=\"sc\"><div class=\"d-flex align-items-center\">\n<div class=\"me-3\" style=\"width:50px;height:50px;border-radius:10px;background:{$c[0]};display:flex;align-items:center;justify-content:center\"><i class=\"fas {$c[2]} fa-lg\" style=\"color:{$c[1]}\"></i></div>\n<div><h3 class=\"mb-0\">{{\\App\\Models\\{$cls}::count()}}</h3><small class=\"text-muted\">{$labels[$i]}</small></div>\n</div></div></div>\n";
    $i++;
}

 $v = 'resources/views/admin/dashboard.blade.php';
 $c = "@extends('layouts.admin')
@section('title','Dashboard - School of Redemption')
@section('content')
<div class=\"mb-4\">
<h4 class=\"text-primary\">Dashboard</h4>
<p class=\"text-muted\">Welcome to the School of Redemption Admin Panel</p>
</div>
<div class=\"row g-3 mb-4\">
 $cards
</div>
<div class=\"row g-3\">
<div class=\"col-md-6\"><div class=\"sc\">
<h5><i class=\"fas fa-bolt text-warning me-2\"></i>Quick Actions</h5>
<hr>
<div class=\"d-flex flex-wrap gap-2 mt-3\">
<a href=\"/admin/students/create\" class=\"btn btn-primary btn-sm\"><i class=\"fas fa-user-plus me-1\"></i>Add Student</a>
<a href=\"/admin/teachers/create\" class=\"btn btn-warning btn-sm\"><i class=\"fas fa-user-tie me-1\"></i>Add Teacher</a>
<a href=\"/admin/exams/create\" class=\"btn btn-success btn-sm\"><i class=\"fas fa-file-alt me-1\"></i>Create Exam</a>
<a href=\"/admin/mark-entries/create\" class=\"btn btn-info btn-sm text-white\"><i class=\"fas fa-pen me-1\"></i>Enter Marks</a>
<a href=\"/admin/fees\" class=\"btn btn-danger btn-sm\"><i class=\"fas fa-money-bill me-1\"></i>Manage Fees</a>
<a href=\"/admin/sliders\" class=\"btn btn-secondary btn-sm\"><i class=\"fas fa-images me-1\"></i>Manage Sliders</a>
</div></div>
</div>
<div class=\"col-md-6\"><div class=\"sc\">
<h5><i class=\"fas fa-info-circle text-warning me-2\"></i>System Info</h5>
<hr>
<table class=\"table table-sm small mb-0\">
<tr><td class=\"text-muted\">Application</td><td><strong>School of Redemption ERP</strong></td></tr>
<tr><td class=\"text-muted\">Framework</td><td>Laravel {{app()->version()}}</td></tr>
<tr><td class=\"text-muted\">PHP Version</td><td>{{PHP_VERSION}}</td></tr>
<tr><td class=\"text-muted\">Environment</td><td>{{app()->environment()}}</td></tr>
<tr><td class=\"text-muted\">Database</td><td>MySQL Connected</td></tr>
</table></div>
</div>
</div>
@endsection";

file_put_contents($v,$c);
echo "DONE: Dashboard fixed with available models\n";
