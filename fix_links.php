<?php
 $v = 'resources/views/layouts/admin.blade.php';
 $c = file_get_contents($v);
// Fix links missing admin/ prefix
 $fixes = [
'academic-years','terms','classes','sections','subjects',
'teachers','students','parents','teacher-assignments',
'exams','mark-entries','certificates','id-cards',
'progress-reports','performance-reports',
'fees','fee-payments','budgets','income-expenses','finance-statements',
'leaves','payrolls','audits','class-assets','employee-assets',
'branches','team-members','gallery-images','gallery-videos',
'sliders','settings','contact-messages'
];
foreach($fixes as $f){
  $c = str_replace('url("'.$f.'"', 'url("admin/'.$f.'"', $c);
}
file_put_contents($v, $c);
echo "DONE: All sidebar links fixed with admin/ prefix\n";
