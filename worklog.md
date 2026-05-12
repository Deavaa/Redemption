---
Task ID: 1
Agent: Main Agent
Task: Fix all schema mismatches and code bugs from modernization

Work Log:
- Analyzed all migrations vs model fillable vs controller fields
- Found 10+ schema/code mismatches after modernization
- Created 3 new migrations:
  - 2026_05_13_000002_add_is_headquarters_to_branches_table.php
  - 2026_05_13_000003_add_term_number_to_terms_table.php
  - 2026_05_13_000004_update_teacher_assignments_fk_to_teachers_table.php
- Fixed TermController update() using non-existent 'status' column -> 'is_active'
- Fixed TeacherController status validation (Active/Inactive -> active/inactive)
- Added address and photo to Teacher model fillable
- Added full_name accessor to Teacher model
- Fixed TeacherAssignment model relationship (User -> Teacher)
- Fixed SubjectAssignmentController to use Teacher model instead of DB::table('users')
- Fixed User model classes() relationship -> teacherProfile()
- Added numeric_name to ClassroomController store/update
- Updated subject-assignment views for teacher->full_name
- Committed all changes but could not push (no git credentials)

Stage Summary:
- All schema mismatches and code bugs from modernization have been fixed
- 3 new migrations need to be run: php artisan migrate
- Changes committed locally but need push to GitHub (credentials required)
