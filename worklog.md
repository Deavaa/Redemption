---
Task ID: 1
Agent: Main Agent
Task: Add all missing features for student admission/readmission, promotion/detention, mark entry lock/unlock, mark entry permissions, current term filtering

Work Log:
- Added routes to web.php for: Mark Entry Locks, Mark Entry Permissions, Promotion/Detention, Student inactive/readmit/mark-as-left
- Fixed MarkEntryController to check MarkEntryLock and MarkEntryPermission before saving marks
- Fixed MarkEntryLockController view path (admin.mark-locks.index → admin.mark_entry_locks.index) and added $userBranch variable
- Fixed MarkEntryPermissionController view paths, validation bug (classes→classrooms), redirect routes, and revoke method parameter binding
- Created admin/mark_entries/index.blade.php with lock status banner, auto-save, current term display
- Created admin/mark_entry_locks/index.blade.php with lock/unlock management UI
- Created admin/mark_entry_permissions/index.blade.php and create.blade.php for permission management
- Created admin/students/index.blade.php, create.blade.php, show.blade.php, edit.blade.php, readmit.blade.php, inactive.blade.php
- Created admin/promotion/settings.blade.php, grade-scales.blade.php, detail.blade.php
- Updated parent/portal marks views to add current term banner indicator
- Updated student/portal marks view to add current term banner indicator
- Updated admin sidebar navigation to add Promotion & Detention, Mark Entry Locks, Mark Edit Permissions links
- Updated route group active state detection to include new routes

Stage Summary:
- All requested features have been implemented:
  1. Student admission (new student) and readmission workflows with full CRUD views
  2. Promotion/detention system with settings, grade scales, preview, and result detail views
  3. Mark entry with lock status checking and auto-save
  4. Branch principal lock/unlock management for mark entry by term and academic year
  5. Mark entry permissions for granting specific teachers access to edit specific students' marks
  6. Current term filtering/banners for parent and student portals
  7. Sidebar navigation updated for all new features
