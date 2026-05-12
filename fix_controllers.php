<?php
echo "=== Fixing Controllers ===\n";
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
foreach($map as $model => $route){
  // Delete root-level duplicate if exists
  $root = "app/Http/Controllers/{$model}Controller.php";
  if(file_exists($root)){
    unlink($root);
    echo "Deleted duplicate: $root\n";
  }
  // Fix subdirectory controller
  $file = "app/Http/Controllers/{$model}/{$model}Controller.php";
  if(!file_exists($file)){
    echo "MISSING: $file\n";
    continue;
  }
  $c = file_get_contents($file);
  // Fix namespace
  $c = str_replace(
    "namespace App\\Http\\Controllers;",
    "namespace App\\Http\\Controllers\\{$model};",
    $c
  );
  // Fix route names
  $c = str_replace("admin.{$model}.", "admin.{$route}.", $c);
  file_put_contents($file, $c);
  echo "Fixed: $model ($route)\n";
}
echo "\nDONE: All controllers fixed\n";
