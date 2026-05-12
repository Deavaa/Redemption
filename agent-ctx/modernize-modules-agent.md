# Task: Modernize 4 Modules for Laravel School Management System

## Summary
Modernized 4 modules (Section, Subject, AcademicYear, Term) to use the exact same modern design system as the Branch module.

## Files Modified/Created

### Section Module
- **Updated** `app/Models/Section.php` - Added `classroom()`, `teacher()` relationships alongside existing `classRoom()`
- **Updated** `app/Http/Controllers/Section/SectionController.php` - Updated to match spec: eager loading, passing $classes/$teachers to create/edit, using `$section` variable for route-model binding
- **Created** `resources/views/admin/Section/index.blade.php` - Stats: Total Sections, With Teacher, Without Teacher, Max Capacity
- **Created** `resources/views/admin/Section/create.blade.php` - Form with Section Name, Class select, Teacher select, Max Students
- **Created** `resources/views/admin/Section/edit.blade.php` - Same as create, pre-populated with $section data

### Subject Module
- **Rewrote** `resources/views/admin/Subject/index.blade.php` - Stats: Total Subjects, Compulsory, Elective, Active; modern table with badges
- **Rewrote** `resources/views/admin/Subject/create.blade.php` - Modern form with Section Details
- **Rewrote** `resources/views/admin/Subject/edit.blade.php` - Edit form with $data variable (kept from existing controller)

### AcademicYear Module
- **Rewrote** `resources/views/admin/AcademicYear/index.blade.php` - Stats: Total Years, Active, Upcoming
- **Rewrote** `resources/views/admin/AcademicYear/create.blade.php` - Modern form with Ethiopian date conversion
- **Rewrote** `resources/views/admin/AcademicYear/edit.blade.php` - Edit form with $data variable, Ethiopian date hints

### Term Module
- **Rewrote** `resources/views/admin/Term/index.blade.php` - Stats: Total Terms, Active, Current Academic Year
- **Rewrote** `resources/views/admin/Term/create.blade.php` - Modern form with $academicYears select, Ethiopian date conversion
- **Rewrote** `resources/views/admin/Term/edit.blade.php` - Edit form with $term/$academicYears, Ethiopian date hints

## Design System
All views follow the Branch module's exact CSS class naming:
- `modern-page`, `modern-page-header`, `modern-breadcrumb`, `modern-page-title`
- `modern-stats-row`, `modern-stat-card`, `modern-stat-icon-*`
- `modern-card`, `modern-card-header`, `modern-card-body`
- `modern-table`, `modern-badge`, `modern-btn-icon`, `modern-action-group`
- `modern-form-section`, `modern-form-grid`, `modern-form-group`, `modern-input-wrapper`
- `modern-input-icon`, `modern-input`, `modern-toggle`
- CSS via `@push('styles')`, scripts via `@push('scripts')`
- Colors: primary=#4361ee, primary-dark=#3a0ca3

## Key Decisions
- Kept existing controller variable names ($data for paginated lists, $data for Subject edit, $section for Section edit, $term for Term edit)
- Kept existing route names (admin.sections.*, admin.subjects.*, admin.academic-years.*, admin.terms.*)
- Preserved Ethiopian date conversion in AcademicYear and Term forms
- Added `modern-eth-hint` styled Ethiopian date display replacing old inline styles
- Added `modern-badge-warning` for "Unassigned" teacher in Section index
- Added `modern-badge-info` for "Elective" type in Subject index
- Added `modern-code-badge` for subject codes in Subject index
- Added `modern-alert-danger` for error messages in AcademicYear index
