<?php
echo "Fixing view() calls in controllers...\n";
 $map = [
'AcademicYear'=>'academic-years','Term'=>'terms','Classroom'=>'classes',
'Section'=>'sections','Subject'=>'subjects','Teacher'=>'teachers',
'Student'=>'students','ParentModel'=>'parents',
'TeacherAssignment'=>'teacher-assignments','Exam'=>'exams',
'MarkEntry'=>'mark-entries','Certificate'=>'certificates',
'IdCard'=>'id-cards','ProgressReport'=>'progress-reports',
'PerformanceReport'=>'performance-reports','ClassAsset'=>'class-assets',
'EmployeeAsset'=>'employee-assets','Fee'=>'fees',
'FeePayment'=>'fee-payments','Leave'=>'leaves',
'Payroll'=>'payrolls','Budget'=>'budgets',
'IncomeExpense'=>'income-expenses',
'FinanceStatement'=>'finance-statements','Audit'=>'audits',
'Branch'=>'branches','TeamMember'=>'team-members',
'GalleryImage'=>'gallery-images','GalleryVideo'=>'gallery-videos',
'Slider'=>'sliders','Setting'=>'settings',
'ContactMessage'=>'contact-messages',
];
 $actions = ['index','create','show','edit'];
foreach($map as $model => $route){
  $file = "app/Http/Controllers/$model/{$model}Controller.php";
  if(!file_exists($file)) continue;
  $c = file_get_contents($file);
  foreach($actions as $act){
    // Fix view() calls - should use MODEL name (folder name)
    $wrong = "view('admin.{$route}.{$act}'";
    $right = "view('admin.{$model}.{$act}'";
    $c = str_replace($wrong, $right, $c);
  }
  file_put_contents($file, $c);
  echo "Fixed: $model views\n";
}
echo "DONE\n";
