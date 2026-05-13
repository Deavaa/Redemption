<?php
echo "=== CRUD View Generator ===\n";
echo "Reading controller to detect variable naming...\n";
 $sample = file_get_contents('app/Http/Controllers/AcademicYear/AcademicYearController.php');
preg_match("/compact\(['\"]([^'\"]+)['\"]/", $sample, $cm);
 $pv = isset($cm[1]) ? $cm[1] : 'data';
 $sv = preg_replace('/s$/', '', $pv);
echo "Detected: \$$pv (plural), \$$sv (singular)\n\n";

 $M = [
'AcademicYear'=>['academic-years',['name','start_date','end_date','is_current']],
'Term'=>['terms',['name','academic_year_id','start_date','end_date','is_current']],
'Classroom'=>['classes',['name','section','academic_year_id','teacher_id','capacity']],
'Section'=>['sections',['name','class_id','teacher_id','capacity']],
'Subject'=>['subjects',['name','code','type','description']],
'Teacher'=>['teachers',['first_name','last_name','email','phone','qualification','department','hire_date','salary','status']],
'Student'=>['students',['first_name','last_name','email','phone','date_of_birth','gender','address','class_id','section_id','academic_year_id','parent_id','admission_date','status']],
'ParentModel'=>['parents',['first_name','last_name','email','phone','occupation','address']],
'TeacherAssignment'=>['teacher-assignments',['teacher_id','class_id','section_id','subject_id','academic_year_id']],
'Exam'=>['exams',['name','type','academic_year_id','term_id','start_date','end_date','status']],
'MarkEntry'=>['mark-entries',['student_id','exam_id','subject_id','class_id','marks','total_marks','grade','remarks']],
'Certificate'=>['certificates',['student_id','certificate_type','certificate_number','issue_date','description']],
'IdCard'=>['id-cards',['student_id','card_number','issue_date','expiry_date','status']],
'ProgressReport'=>['progress-reports',['student_id','academic_year_id','term_id','class_id','overall_grade','remarks']],
'PerformanceReport'=>['performance-reports',['student_id','academic_year_id','term_id','class_id','attendance_rate','behavior_grade','remarks']],
'ClassAsset'=>['class-assets',['name','class_id','quantity','condition','purchase_date','description']],
'EmployeeAsset'=>['employee-assets',['name','employee_id','quantity','condition','purchase_date','description']],
'Fee'=>['fees',['name','amount','academic_year_id','term_id','class_id','due_date','status']],
'FeePayment'=>['fee-payments',['student_id','fee_id','amount','payment_date','payment_method','reference','status']],
'Leave'=>['leaves',['employee_id','leave_type','start_date','end_date','reason','status']],
'Payroll'=>['payrolls',['employee_id','basic_salary','allowances','deductions','net_salary','pay_date','status']],
'Budget'=>['budgets',['name','amount','academic_year_id','category','description','status']],
'IncomeExpense'=>['income-expenses',['type','category','amount','date','description','reference','status']],
'FinanceStatement'=>['finance-statements',['name','type','period_start','period_end','total_income','total_expense','status']],
'Audit'=>['audits',['name','type','auditor','audit_date','findings','recommendations','status']],
'Branch'=>['branches',['name','address','phone','email','is_main']],
'TeamMember'=>['team-members',['name','position','department','email','phone','photo','bio','order']],
'GalleryImage'=>['gallery-images',['title','image_path','category','description']],
'GalleryVideo'=>['gallery-videos',['title','url','category','description']],
'Slider'=>['sliders',['title','subtitle','image_path','link','order','is_active']],
'Setting'=>['settings',['key','value','group','description']],
'ContactMessage'=>['contact-messages',['name','email','phone','subject','message','is_read']],
];

 $n = 0;
foreach ($M as $model => $info) {
    list($route, $fields) = $info;
    $dir = "resources/views/admin/$model";
    @mkdir($dir, 0755, true);

    // Build table headers and cells
    $th = ''; foreach ($fields as $f) $th .= "<th>" . ucwords(str_replace('_',' ',$f)) . "</th>";
    $td = ''; foreach ($fields as $f) $td .= "<td>{{\$item->$f ?? '-'}}</td>";
    $formFields = '';
    foreach ($fields as $f) {
        $l = ucwords(str_replace('_',' ',$f));
        $formFields .= "<div class=\"mb-3\"><label class=\"form-label\">$l</label>\n<input type=\"text\" name=\"$f\" class=\"form-control\" value=\"{{old('$f')}}\" required></div>\n";
    }
    $editFields = '';
    foreach ($fields as $f) {
        $l = ucwords(str_replace('_',' ',$f));
        $editFields .= "<div class=\"mb-3\"><label class=\"form-label\">$l</label>\n<input type=\"text\" name=\"$f\" class=\"form-control\" value=\"{{\$$sv->$f ?? ''}}\" required></div>\n";
    }
    $showRows = '';
    foreach ($fields as $f) {
        $l = ucwords(str_replace('_',' ',$f));
        $showRows .= "<tr><th width=\"200\" class=\"table-light\">$l</th><td>{{\$$sv->$f ?? '-'}}</td></tr>\n";
    }

    // INDEX VIEW
    file_put_contents("$dir/index.blade.php",
"@extends('layouts.admin')\n@section('title','$model')\n@section('content')\n" .
"<div class=\"d-flex justify-content-between align-items-center mb-3\">\n<h4 class=\"mb-0\">$model Management</h4>\n" .
"<a href=\"{{route('admin.$route.create')}}\" class=\"btn btn-primary btn-sm\"><i class=\"fas fa-plus me-1\"></i>Add New</a>\n</div>\n" .
"<div class=\"sc\">\n" .
"@if(session('success'))\n<div class=\"alert alert-success alert-dismissible fade show\">\n<i class=\"fas fa-check-circle me-2\"></i>{{session('success')}}\n<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>\n</div>\n@endif\n" .
"@if(isset(\$errors) && \$errors->any())\n<div class=\"alert alert-danger\">@foreach(\$errors->all() as \$e){{\$e}}<br>@endforeach</div>\n@endif\n" .
"<div class=\"table-responsive\"><table class=\"table table-hover table-bordered\">\n" .
"<thead class=\"table-dark\"><tr><th>#</th>$th<th>Actions</th></tr></thead>\n" .
"<tbody>@foreach(\$$pv as \$item)\n<tr><td>{{\$loop->iteration}}</td>$td\n" .
"<td class=\"nowrap\">\n<a href=\"{{route('admin.$route.edit',\$item->id)}}\" class=\"btn btn-sm btn-warning me-1\" title=\"Edit\"><i class=\"fas fa-edit\"></i></a>\n" .
"<form method=\"POST\" action=\"{{route('admin.$route.destroy',\$item->id)}}\" style=\"display:inline\" onsubmit=\"return confirm('Delete this item?')\">\n" .
"@csrf @method('DELETE')\n<button type=\"submit\" class=\"btn btn-sm btn-danger\" title=\"Delete\"><i class=\"fas fa-trash\"></i></button>\n" .
"</form></td></tr>\n@endforeach</tbody></table></div></div>\n@endsection\n");

    // CREATE VIEW
    file_put_contents("$dir/create.blade.php",
"@extends('layouts.admin')\n@section('title','Add $model')\n@section('content')\n" .
"<div class=\"d-flex justify-content-between align-items-center mb-3\">\n<h4 class=\"mb-0\">Add $model</h4>\n" .
"<a href=\"{{route('admin.$route.index')}}\" class=\"btn btn-secondary btn-sm\"><i class=\"fas fa-arrow-left me-1\"></i>Back</a>\n</div>\n" .
"<div class=\"sc\">\n<form method=\"POST\" action=\"{{route('admin.$route.store')}}\">\n@csrf\n$formFields" .
"<button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-save me-1\"></i>Save</button>\n" .
"</form></div>\n@endsection\n");

    // EDIT VIEW
    file_put_contents("$dir/edit.blade.php",
"@extends('layouts.admin')\n@section('title','Edit $model')\n@section('content')\n" .
"<div class=\"d-flex justify-content-between align-items-center mb-3\">\n<h4 class=\"mb-0\">Edit $model</h4>\n" .
"<a href=\"{{route('admin.$route.index')}}\" class=\"btn btn-secondary btn-sm\"><i class=\"fas fa-arrow-left me-1\"></i>Back</a>\n</div>\n" .
"<div class=\"sc\">\n<form method=\"POST\" action=\"{{route('admin.$route.update',\$$sv->id)}}\">\n@csrf @method('PUT')\n$editFields" .
"<button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-save me-1\"></i>Update</button>\n" .
"</form></div>\n@endsection\n");

    // SHOW VIEW
    file_put_contents("$dir/show.blade.php",
"@extends('layouts.admin')\n@section('title','$model Details')\n@section('content')\n" .
"<div class=\"d-flex justify-content-between align-items-center mb-3\">\n<h4 class=\"mb-0\">$model Details</h4>\n" .
"<a href=\"{{route('admin.$route.index')}}\" class=\"btn btn-secondary btn-sm\"><i class=\"fas fa-arrow-left me-1\"></i>Back</a>\n</div>\n" .
"<div class=\"sc\">\n<table class=\"table table-bordered\">\n$showRows</table>\n" .
"<a href=\"{{route('admin.$route.edit',\$$sv->id)}}\" class=\"btn btn-warning btn-sm mt-3\"><i class=\"fas fa-edit me-1\"></i>Edit</a>\n" .
"</div>\n@endsection\n");

    $n++;
    echo "  Created 4 views for $model\n";
}
echo "\nDONE: $n modules x 4 views = " . ($n * 4) . " views generated!\n";
