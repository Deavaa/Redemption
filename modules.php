<?php return array (
  0 => 
  array (
    0 => 'AcademicYear',
    1 => 'academic-years',
    2 => 'name,start_date,end_date,is_current',
  ),
  1 => 
  array (
    0 => 'Term',
    1 => 'terms',
    2 => 'name,academic_year_id,start_date,end_date,is_current',
  ),
  2 => 
  array (
    0 => 'Classroom',
    1 => 'classes',
    2 => 'name,section,academic_year_id,teacher_id,capacity',
  ),
  3 => 
  array (
    0 => 'Section',
    1 => 'sections',
    2 => 'name,class_id,teacher_id,capacity',
  ),
  4 => 
  array (
    0 => 'Subject',
    1 => 'subjects',
    2 => 'name,code,type,description',
  ),
  5 => 
  array (
    0 => 'Teacher',
    1 => 'teachers',
    2 => 'first_name,last_name,email,phone,qualification,department,hire_date,salary,status',
  ),
  6 => 
  array (
    0 => 'Student',
    1 => 'students',
    2 => 'first_name,last_name,email,phone,date_of_birth,gender,address,class_id,section_id,academic_year_id,parent_id,admission_date,status',
  ),
  7 => 
  array (
    0 => 'ParentModel',
    1 => 'parents',
    2 => 'first_name,last_name,email,phone,occupation,address',
  ),
  8 => 
  array (
    0 => 'TeacherAssignment',
    1 => 'teacher-assignments',
    2 => 'teacher_id,class_id,section_id,subject_id,academic_year_id',
  ),
  9 => 
  array (
    0 => 'Exam',
    1 => 'exams',
    2 => 'name,type,academic_year_id,term_id,start_date,end_date,status',
  ),
  10 => 
  array (
    0 => 'MarkEntry',
    1 => 'mark-entries',
    2 => 'student_id,exam_id,subject_id,class_id,marks,total_marks,grade,remarks',
  ),
  11 => 
  array (
    0 => 'Certificate',
    1 => 'certificates',
    2 => 'student_id,certificate_type,certificate_number,issue_date,description',
  ),
  12 => 
  array (
    0 => 'IdCard',
    1 => 'id-cards',
    2 => 'student_id,card_number,issue_date,expiry_date,status',
  ),
  13 => 
  array (
    0 => 'ProgressReport',
    1 => 'progress-reports',
    2 => 'student_id,academic_year_id,term_id,class_id,overall_grade,remarks',
  ),
  14 => 
  array (
    0 => 'PerformanceReport',
    1 => 'performance-reports',
    2 => 'student_id,academic_year_id,term_id,class_id,attendance_rate,behavior_grade,remarks',
  ),
  15 => 
  array (
    0 => 'ClassAsset',
    1 => 'class-assets',
    2 => 'name,class_id,quantity,condition,purchase_date,description',
  ),
  16 => 
  array (
    0 => 'EmployeeAsset',
    1 => 'employee-assets',
    2 => 'name,employee_id,quantity,condition,purchase_date,description',
  ),
  17 => 
  array (
    0 => 'Fee',
    1 => 'fees',
    2 => 'name,amount,academic_year_id,term_id,class_id,due_date,status',
  ),
  18 => 
  array (
    0 => 'FeePayment',
    1 => 'fee-payments',
    2 => 'student_id,fee_id,amount,payment_date,payment_method,reference,status',
  ),
  19 => 
  array (
    0 => 'Leave',
    1 => 'leaves',
    2 => 'employee_id,leave_type,start_date,end_date,reason,status',
  ),
  20 => 
  array (
    0 => 'Payroll',
    1 => 'payrolls',
    2 => 'employee_id,basic_salary,allowances,deductions,net_salary,pay_date,status',
  ),
  21 => 
  array (
    0 => 'Budget',
    1 => 'budgets',
    2 => 'name,amount,academic_year_id,category,description,status',
  ),
  22 => 
  array (
    0 => 'IncomeExpense',
    1 => 'income-expenses',
    2 => 'type,category,amount,date,description,reference,status',
  ),
  23 => 
  array (
    0 => 'FinanceStatement',
    1 => 'finance-statements',
    2 => 'name,type,period_start,period_end,total_income,total_expense,status',
  ),
  24 => 
  array (
    0 => 'Audit',
    1 => 'audits',
    2 => 'name,type,auditor,audit_date,findings,recommendations,status',
  ),
  25 => 
  array (
    0 => 'Branch',
    1 => 'branches',
    2 => 'name,address,phone,email,is_main',
  ),
  26 => 
  array (
    0 => 'TeamMember',
    1 => 'team-members',
    2 => 'name,position,department,email,phone,photo,bio,order',
  ),
  27 => 
  array (
    0 => 'GalleryImage',
    1 => 'gallery-images',
    2 => 'title,image_path,category,description',
  ),
  28 => 
  array (
    0 => 'GalleryVideo',
    1 => 'gallery-videos',
    2 => 'title,url,category,description',
  ),
  29 => 
  array (
    0 => 'Slider',
    1 => 'sliders',
    2 => 'title,subtitle,image_path,link,order,is_active',
  ),
  30 => 
  array (
    0 => 'Setting',
    1 => 'settings',
    2 => 'key,value,group,description',
  ),
  31 => 
  array (
    0 => 'ContactMessage',
    1 => 'contact-messages',
    2 => 'name,email,phone,subject,message,is_read',
  ),
);