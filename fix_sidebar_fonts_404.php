<?php
 $base = getcwd();
 $changes = [];

// ============================================================
// FIX 1: Subject Assignment 404 - Check & fix controller
// ============================================================
echo "===== FIX 1: Subject Assignment 404 =====\n";

 $sacPath = $base . '/app/Http/Controllers/Admin/SubjectAssignmentController.php';
if (file_exists($sacPath)) {
    $sac = file_get_contents($sacPath);
    echo "  Controller found. Checking namespace...\n";
    
    // Check namespace
    if (strpos($sac, 'namespace App\\Http\\Controllers\\Admin') !== false) {
        echo "  ✓ Namespace is correct: App\\Http\\Controllers\\Admin\n";
    } else {
        echo "  ✗ Namespace mismatch!\n";
        // Show current namespace
        preg_match('/namespace\s+([^;]+)/', $sac, $nsMatch);
        echo "    Current: " . ($nsMatch[1] ?? 'NOT FOUND') . "\n";
    }
    
    // Check if key methods exist
    $methods = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
    foreach ($methods as $m) {
        if (strpos($sac, "function $m") !== false) {
            echo "  ✓ Method $m exists\n";
        } else {
            echo "  ✗ Method $m MISSING\n";
        }
    }
    
    // Check the route registration - the import line
    $routePath = $base . '/routes/web.php';
    $routeContent = file_get_contents($routePath);
    
    // The route says: use App\Http\Controllers\Admin\SubjectAssignmentController;
    // And: Route::resource('subject-assignments', SubjectAssignmentController::class);
    // This should work if the namespace matches
    
    // Let's check if there's a route list issue
    echo "\n  Checking route registration...\n";
    if (strpos($routeContent, "SubjectAssignmentController") !== false) {
        echo "  ✓ Controller imported in routes\n";
    }
    
    // Check if the route is inside the admin prefix group
    $lines = explode("\n", $routeContent);
    $inAdminGroup = false;
    $depth = 0;
    foreach ($lines as $i => $line) {
        if (strpos($line, "Route::prefix('admin')") !== false || strpos($line, "Route::middleware(['auth'") !== false) {
            $inAdminGroup = true;
            $depth = 0;
        }
        if ($inAdminGroup) {
            if (strpos($line, "subject-assignments") !== false) {
                echo "  ✓ subject-assignments route found inside admin group at line " . ($i+1) . "\n";
                echo "    " . trim($line) . "\n";
                break;
            }
            $depth += substr_count($line, '{');
            $depth -= substr_count($line, '}');
            if ($depth <= 0 && strpos($line, '}') !== false && $i > 5) {
                $inAdminGroup = false;
            }
        }
    }
    
    // Check views
    $viewDir = $base . '/resources/views/admin/subject-assignments';
    if (is_dir($viewDir)) {
        $views = glob($viewDir . '/*.blade.php');
        echo "  ✓ View directory exists with " . count($views) . " files:\n";
        foreach ($views as $v) {
            echo "    - " . basename($v) . "\n";
        }
    } else {
        echo "  ✗ View directory missing: $viewDir\n";
    }
    
} else {
    echo "  ✗ Controller NOT FOUND at: $sacPath\n";
}

// ============================================================
// FIX 2: Sidebar - Highlight active CHILD, not parent group
// ============================================================
echo "\n===== FIX 2: Active child menu highlight =====\n";

 $layoutPath = $base . '/resources/views/layouts/admin.blade.php';
if (file_exists($layoutPath)) {
    $layout = file_get_contents($layoutPath);
    
    // Build the new sidebar menu with active classes on child links
    $newSidebar = <<<'BLADE'
            <ul class="sidebar-menu">
                <li class="menu-header">MAIN</li>
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
                </li>

                <li class="menu-header">ACADEMIC</li>
                <li class="{{ $isAcademicActive ? 'has-active-child' : '' }}">
                    <a href="#academicSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-graduation-cap"></i><span>Academic</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isAcademicActive ? 'show' : '' }}" id="academicSubmenu">
                        <li><a href="{{ route('admin.academic-years.index') }}" class="{{ request()->routeIs('admin.academic-years.*') ? 'active' : '' }}"><i class="fas fa-calendar"></i> Academic Years</a></li>
                        <li><a href="{{ route('admin.terms.index') }}" class="{{ request()->routeIs('admin.terms.*') ? 'active' : '' }}"><i class="fas fa-bookmark"></i> Terms</a></li>
                        <li><a href="{{ route('admin.subjects.index') }}" class="{{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"><i class="fas fa-book"></i> Subjects</a></li>
                        <li><a href="{{ route('admin.subject-assignments.index') }}" class="{{ request()->routeIs('admin.subject-assignments.*') ? 'active' : '' }}"><i class="fas fa-link"></i> Assign Subjects</a></li>
                        <li><a href="{{ route('admin.exams.index') }}" class="{{ request()->routeIs('admin.exams.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Exams</a></li>
                        <li><a href="{{ route('admin.classrooms.index') }}" class="{{ request()->routeIs('admin.classrooms.*') ? 'active' : '' }}"><i class="fas fa-building"></i> Classes</a></li>
                        <li><a href="{{ route('admin.mark-entries.index') }}" class="{{ request()->routeIs('admin.mark-entries.*') ? 'active' : '' }}"><i class="fas fa-pen"></i> Mark Entry</a></li>
                    </ul>
                </li>

                <li class="menu-header">PEOPLE</li>
                <li class="{{ $isPeopleActive ? 'has-active-child' : '' }}">
                    <a href="#peopleSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-users"></i><span>People</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isPeopleActive ? 'show' : '' }}" id="peopleSubmenu">
                        <li><a href="{{ route('admin.students.index') }}" class="{{ request()->routeIs('admin.students.*') ? 'active' : '' }}"><i class="fas fa-user-graduate"></i> Students</a></li>
                        <li><a href="{{ route('admin.teachers.index') }}" class="{{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                        <li><a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'active' : '' }}"><i class="fas fa-id-badge"></i> Staff</a></li>
                        <li><a href="{{ route('admin.team-members.index') }}" class="{{ request()->routeIs('admin.team-members.*') ? 'active' : '' }}"><i class="fas fa-users"></i> Team Members</a></li>
                    </ul>
                </li>

                <li class="menu-header">FINANCE</li>
                <li class="{{ $isFinanceActive ? 'has-active-child' : '' }}">
                    <a href="#financeSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-wallet"></i><span>Finance</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isFinanceActive ? 'show' : '' }}" id="financeSubmenu">
                        <li><a href="{{ route('admin.fees.index') }}" class="{{ request()->routeIs('admin.fees.*') ? 'active' : '' }}"><i class="fas fa-money-bill-wave"></i> Fees</a></li>
                        <li><a href="{{ route('admin.fee-payments.index') }}" class="{{ request()->routeIs('admin.fee-payments.*') ? 'active' : '' }}"><i class="fas fa-credit-card"></i> Payments</a></li>
                        <li><a href="{{ route('admin.payrolls.index') }}" class="{{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}"><i class="fas fa-file-invoice-dollar"></i> Payroll</a></li>
                        <li><a href="{{ route('admin.budgets.index') }}" class="{{ request()->routeIs('admin.budgets.*') ? 'active' : '' }}"><i class="fas fa-chart-pie"></i> Budgets</a></li>
                        <li><a href="{{ route('admin.income-expenses.index') }}" class="{{ request()->routeIs('admin.income-expenses.*') ? 'active' : '' }}"><i class="fas fa-exchange-alt"></i> Income/Expense</a></li>
                        <li><a href="{{ route('admin.finance-statements.index') }}" class="{{ request()->routeIs('admin.finance-statements.*') ? 'active' : '' }}"><i class="fas fa-file-alt"></i> Statements</a></li>
                        <li><a href="{{ route('admin.leaves.index') }}" class="{{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}"><i class="fas fa-calendar-minus"></i> Leaves</a></li>
                        <li><a href="{{ route('admin.employee-assets.index') }}" class="{{ request()->routeIs('admin.employee-assets.*') ? 'active' : '' }}"><i class="fas fa-boxes"></i> Employee Assets</a></li>
                    </ul>
                </li>

                <li class="menu-header">WEBSITE</li>
                <li class="{{ $isWebsiteActive ? 'has-active-child' : '' }}">
                    <a href="#websiteSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-globe"></i><span>Website</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isWebsiteActive ? 'show' : '' }}" id="websiteSubmenu">
                        <li><a href="{{ route('admin.branches.index') }}" class="{{ request()->routeIs('admin.branches.*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> Branches</a></li>
                        <li><a href="{{ route('admin.sliders.index') }}" class="{{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}"><i class="fas fa-images"></i> Sliders</a></li>
                        <li><a href="{{ route('admin.gallery-images.index') }}" class="{{ request()->routeIs('admin.gallery-images.*') ? 'active' : '' }}"><i class="fas fa-image"></i> Gallery Images</a></li>
                        <li><a href="{{ route('admin.gallery-videos.index') }}" class="{{ request()->routeIs('admin.gallery-videos.*') ? 'active' : '' }}"><i class="fas fa-video"></i> Gallery Videos</a></li>
                        <li><a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}"><i class="fas fa-envelope"></i> Messages</a></li>
                    </ul>
                </li>

                <li class="menu-header">SYSTEM</li>
                <li class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="fas fa-cog"></i><span>Settings</span></a>
                </li>
                <li class="{{ request()->routeIs('admin.audits.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.audits.index') }}" class="{{ request()->routeIs('admin.audits.*') ? 'active' : '' }}"><i class="fas fa-clipboard-list"></i><span>Audit Log</span></a>
                </li>
            </ul>
BLADE;

    // Replace the old sidebar menu
    // Find <ul class="sidebar-menu"> ... </ul>
    $pattern = '/<ul class="sidebar-menu">.*?<\/ul>\s*<div class="sidebar-footer">/s';
    if (preg_match($pattern, $layout, $match)) {
        $layout = preg_replace($pattern, $newSidebar . "\n\n            <div class=\"sidebar-footer\">", $layout, 1, $count);
        if ($count > 0) {
            file_put_contents($layoutPath, $layout);
            $changes[] = "FIXED: Sidebar now highlights active child menu items";
            echo "  ✓ Sidebar updated - active class on child links\n";
        } else {
            echo "  ✗ Could not replace sidebar menu\n";
        }
    } else {
        echo "  ✗ Could not find sidebar menu pattern\n";
        // Try alternative approach
        echo "  Trying alternative replacement...\n";
        $old = '<ul class="sidebar-menu">';
        $new = '<ul class="sidebar-menu"><!-- UPDATED -->';
        $layout = str_replace($old, $new, $layout, $count);
        if ($count > 0) {
            echo "  Found sidebar-menu tag. Doing manual replacement...\n";
            // Do it the hard way - replace individual lines
            $layout = file_get_contents($layoutPath);
            
            // Replace each child <a> to add active class
            $replacements = [
                // Academic children
                '<li><a href="{{ route(\'admin.academic-years.index\') }}"><i class="fas fa-calendar"></i> Academic Years</a></li>' 
                    => '<li><a href="{{ route(\'admin.academic-years.index\') }}" class="{{ request()->routeIs(\'admin.academic-years.*\') ? \'active\' : \'\' }}"><i class="fas fa-calendar"></i> Academic Years</a></li>',
                '<li><a href="{{ route(\'admin.terms.index\') }}"><i class="fas fa-bookmark"></i> Terms</a></li>'
                    => '<li><a href="{{ route(\'admin.terms.index\') }}" class="{{ request()->routeIs(\'admin.terms.*\') ? \'active\' : \'\' }}"><i class="fas fa-bookmark"></i> Terms</a></li>',
                '<li><a href="{{ route(\'admin.subjects.index\') }}"><i class="fas fa-book"></i> Subjects</a></li>'
                    => '<li><a href="{{ route(\'admin.subjects.index\') }}" class="{{ request()->routeIs(\'admin.subjects.*\') ? \'active\' : \'\' }}"><i class="fas fa-book"></i> Subjects</a></li>',
                '<li><a href="{{ route(\'admin.subject-assignments.index\') }}"><i class="fas fa-link"></i> Assign Subjects</a></li>'
                    => '<li><a href="{{ route(\'admin.subject-assignments.index\') }}" class="{{ request()->routeIs(\'admin.subject-assignments.*\') ? \'active\' : \'\' }}"><i class="fas fa-link"></i> Assign Subjects</a></li>',
                '<li><a href="{{ route(\'admin.exams.index\') }}"><i class="fas fa-file-alt"></i> Exams</a></li>'
                    => '<li><a href="{{ route(\'admin.exams.index\') }}" class="{{ request()->routeIs(\'admin.exams.*\') ? \'active\' : \'\' }}"><i class="fas fa-file-alt"></i> Exams</a></li>',
                '<li><a href="{{ route(\'admin.classrooms.index\') }}"><i class="fas fa-building"></i> Classes</a></li>'
                    => '<li><a href="{{ route(\'admin.classrooms.index\') }}" class="{{ request()->routeIs(\'admin.classrooms.*\') ? \'active\' : \'\' }}"><i class="fas fa-building"></i> Classes</a></li>',
                '<li><a href="{{ route(\'admin.mark-entries.index\') }}"><i class="fas fa-pen"></i> Mark Entry</a></li>'
                    => '<li><a href="{{ route(\'admin.mark-entries.index\') }}" class="{{ request()->routeIs(\'admin.mark-entries.*\') ? \'active\' : \'\' }}"><i class="fas fa-pen"></i> Mark Entry</a></li>',
                // People children
                '<li><a href="{{ route(\'admin.students.index\') }}"><i class="fas fa-user-graduate"></i> Students</a></li>'
                    => '<li><a href="{{ route(\'admin.students.index\') }}" class="{{ request()->routeIs(\'admin.students.*\') ? \'active\' : \'\' }}"><i class="fas fa-user-graduate"></i> Students</a></li>',
                '<li><a href="{{ route(\'admin.teachers.index\') }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>'
                    => '<li><a href="{{ route(\'admin.teachers.index\') }}" class="{{ request()->routeIs(\'admin.teachers.*\') ? \'active\' : \'\' }}"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>',
                '<li><a href="{{ route(\'admin.staff.index\') }}"><i class="fas fa-id-badge"></i> Staff</a></li>'
                    => '<li><a href="{{ route(\'admin.staff.index\') }}" class="{{ request()->routeIs(\'admin.staff.*\') ? \'active\' : \'\' }}"><i class="fas fa-id-badge"></i> Staff</a></li>',
                '<li><a href="{{ route(\'admin.team-members.index\') }}"><i class="fas fa-users"></i> Team Members</a></li>'
                    => '<li><a href="{{ route(\'admin.team-members.index\') }}" class="{{ request()->routeIs(\'admin.team-members.*\') ? \'active\' : \'\' }}"><i class="fas fa-users"></i> Team Members</a></li>',
                // Finance children
                '<li><a href="{{ route(\'admin.fees.index\') }}"><i class="fas fa-money-bill-wave"></i> Fees</a></li>'
                    => '<li><a href="{{ route(\'admin.fees.index\') }}" class="{{ request()->routeIs(\'admin.fees.*\') ? \'active\' : \'\' }}"><i class="fas fa-money-bill-wave"></i> Fees</a></li>',
                '<li><a href="{{ route(\'admin.fee-payments.index\') }}"><i class="fas fa-credit-card"></i> Payments</a></li>'
                    => '<li><a href="{{ route(\'admin.fee-payments.index\') }}" class="{{ request()->routeIs(\'admin.fee-payments.*\') ? \'active\' : \'\' }}"><i class="fas fa-credit-card"></i> Payments</a></li>',
                '<li><a href="{{ route(\'admin.payrolls.index\') }}"><i class="fas fa-file-invoice-dollar"></i> Payroll</a></li>'
                    => '<li><a href="{{ route(\'admin.payrolls.index\') }}" class="{{ request()->routeIs(\'admin.payrolls.*\') ? \'active\' : \'\' }}"><i class="fas fa-file-invoice-dollar"></i> Payroll</a></li>',
                '<li><a href="{{ route(\'admin.budgets.index\') }}"><i class="fas fa-chart-pie"></i> Budgets</a></li>'
                    => '<li><a href="{{ route(\'admin.budgets.index\') }}" class="{{ request()->routeIs(\'admin.budgets.*\') ? \'active\' : \'\' }}"><i class="fas fa-chart-pie"></i> Budgets</a></li>',
                '<li><a href="{{ route(\'admin.income-expenses.index\') }}"><i class="fas fa-exchange-alt"></i> Income/Expense</a></li>'
                    => '<li><a href="{{ route(\'admin.income-expenses.index\') }}" class="{{ request()->routeIs(\'admin.income-expenses.*\') ? \'active\' : \'\' }}"><i class="fas fa-exchange-alt"></i> Income/Expense</a></li>',
                '<li><a href="{{ route(\'admin.finance-statements.index\') }}"><i class="fas fa-file-alt"></i> Statements</a></li>'
                    => '<li><a href="{{ route(\'admin.finance-statements.index\') }}" class="{{ request()->routeIs(\'admin.finance-statements.*\') ? \'active\' : \'\' }}"><i class="fas fa-file-alt"></i> Statements</a></li>',
                '<li><a href="{{ route(\'admin.leaves.index\') }}"><i class="fas fa-calendar-minus"></i> Leaves</a></li>'
                    => '<li><a href="{{ route(\'admin.leaves.index\') }}" class="{{ request()->routeIs(\'admin.leaves.*\') ? \'active\' : \'\' }}"><i class="fas fa-calendar-minus"></i> Leaves</a></li>',
                '<li><a href="{{ route(\'admin.employee-assets.index\') }}"><i class="fas fa-boxes"></i> Employee Assets</a></li>'
                    => '<li><a href="{{ route(\'admin.employee-assets.index\') }}" class="{{ request()->routeIs(\'admin.employee-assets.*\') ? \'active\' : \'\' }}"><i class="fas fa-boxes"></i> Employee Assets</a></li>',
                // Website children
                '<li><a href="{{ route(\'admin.branches.index\') }}"><i class="fas fa-map-marker-alt"></i> Branches</a></li>'
                    => '<li><a href="{{ route(\'admin.branches.index\') }}" class="{{ request()->routeIs(\'admin.branches.*\') ? \'active\' : \'\' }}"><i class="fas fa-map-marker-alt"></i> Branches</a></li>',
                '<li><a href="{{ route(\'admin.sliders.index\') }}"><i class="fas fa-images"></i> Sliders</a></li>'
                    => '<li><a href="{{ route(\'admin.sliders.index\') }}" class="{{ request()->routeIs(\'admin.sliders.*\') ? \'active\' : \'\' }}"><i class="fas fa-images"></i> Sliders</a></li>',
                '<li><a href="{{ route(\'admin.gallery-images.index\') }}"><i class="fas fa-image"></i> Gallery Images</a></li>'
                    => '<li><a href="{{ route(\'admin.gallery-images.index\') }}" class="{{ request()->routeIs(\'admin.gallery-images.*\') ? \'active\' : \'\' }}"><i class="fas fa-image"></i> Gallery Images</a></li>',
                '<li><a href="{{ route(\'admin.gallery-videos.index\') }}"><i class="fas fa-video"></i> Gallery Videos</a></li>'
                    => '<li><a href="{{ route(\'admin.gallery-videos.index\') }}" class="{{ request()->routeIs(\'admin.gallery-videos.*\') ? \'active\' : \'\' }}"><i class="fas fa-video"></i> Gallery Videos</a></li>',
                '<li><a href="{{ route(\'admin.contact-messages.index\') }}"><i class="fas fa-envelope"></i> Messages</a></li>'
                    => '<li><a href="{{ route(\'admin.contact-messages.index\') }}" class="{{ request()->routeIs(\'admin.contact-messages.*\') ? \'active\' : \'\' }}"><i class="fas fa-envelope"></i> Messages</a></li>',
                // Dashboard
                '<li class="{{ request()->routeIs(\'admin.dashboard\') ? \'active\' : '' }}">
                    <a href="{{ route(\'admin.dashboard\') }}"><i class="fas fa-th-large"></i><span>Dashboard</span></a>'
                    => '<li class="{{ request()->routeIs(\'admin.dashboard\') ? \'active\' : \'\' }}">
                    <a href="{{ route(\'admin.dashboard\') }}" class="{{ request()->routeIs(\'admin.dashboard\') ? \'active\' : \'\' }}"><i class="fas fa-th-large"></i><span>Dashboard</span></a>',
                // Settings
                '<li class="{{ request()->routeIs(\'admin.settings.*\') ? \'active\' : '' }}">
                    <a href="{{ route(\'admin.settings.index\') }}"><i class="fas fa-cog"></i><span>Settings</span></a>'
                    => '<li class="{{ request()->routeIs(\'admin.settings.*\') ? \'active\' : \'\' }}">
                    <a href="{{ route(\'admin.settings.index\') }}" class="{{ request()->routeIs(\'admin.settings.*\') ? \'active\' : \'\' }}"><i class="fas fa-cog"></i><span>Settings</span></a>',
                // Audit Log
                '<li class="{{ request()->routeIs(\'admin.audits.*\') ? \'active\' : '' }}">
                    <a href="{{ route(\'admin.audits.index\') }}"><i class="fas fa-clipboard-list"></i><span>Audit Log</span></a>'
                    => '<li class="{{ request()->routeIs(\'admin.audits.*\') ? \'active\' : \'\' }}">
                    <a href="{{ route(\'admin.audits.index\') }}" class="{{ request()->routeIs(\'admin.audits.*\') ? \'active\' : \'\' }}"><i class="fas fa-clipboard-list"></i><span>Audit Log</span></a>',
            ];
            
            // Also change parent <li> from 'active' to 'has-active-child'
            $layout = str_replace(
                ['$isAcademicActive ? \'active\'', '$isPeopleActive ? \'active\'', '$isFinanceActive ? \'active\'', '$isWebsiteActive ? \'active\''],
                ['$isAcademicActive ? \'has-active-child\'', '$isPeopleActive ? \'has-active-child\'', '$isFinanceActive ? \'has-active-child\'', '$isWebsiteActive ? \'has-active-child\''],
                $layout
            );
            
            foreach ($replacements as $old => $new) {
                $layout = str_replace($old, $new, $layout, $c);
                if ($c > 0) echo "  ✓ Replaced: " . substr($old, 0, 60) . "...\n";
            }
            
            file_put_contents($layoutPath, $layout);
            $changes[] = "FIXED: Sidebar active child highlighting (manual method)";
            echo "  ✓ Sidebar updated with active child classes\n";
        }
    }
} else {
    echo "  ✗ admin.blade.php not found\n";
}

// ============================================================
// FIX 3: CSS - Active child highlight + font size increase
// ============================================================
echo "\n===== FIX 3: CSS updates =====\n";

 $cssPath = $base . '/public/css/admin.css';
if (file_exists($cssPath)) {
    $css = file_get_contents($cssPath);
    
    // Remove any previous FIX blocks
    $css = preg_replace('/\/\* ===== ACTIVE MENU.*?\*\//s', '', $css);
    $css = preg_replace('/\/\* ===== FONT SIZE FIX.*?\*\//s', '', $css);
    // Also remove any appended blocks from previous fixes
    $css = preg_replace('/\/\* Active child menu.*$/s', '', $css);
    $css = preg_replace('/\/\* FONT SIZE FIX.*$/s', '', $css);
    
    $newCSS = <<<CSS

/* ===== ACTIVE CHILD MENU HIGHLIGHT ===== */
/* Parent group: subtle indicator only, no highlight */
.sidebar-menu > li.has-active-child > .submenu-toggle {
    color: rgba(255, 255, 255, 0.95) !important;
    background: transparent !important;
}
.sidebar-menu > li.has-active-child > .submenu-toggle .sidebar-chevron {
    transform: rotate(180deg);
}

/* Active child link: clear highlight with left border */
.sidebar-menu .collapse a.active,
.sidebar-menu .collapse.show a.active {
    background: rgba(255, 255, 255, 0.18) !important;
    color: #fff !important;
    border-left: 3px solid #fff !important;
    padding-left: calc(1rem - 3px) !important;
    font-weight: 600;
    border-radius: 0;
}

/* Top-level active link (Dashboard, Settings, Audit) */
.sidebar-menu > li.active > a.active {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #fff !important;
    border-left: 3px solid #fff !important;
    padding-left: calc(1rem - 3px) !important;
    font-weight: 600;
}

/* Remove the old parent-only active highlight */
.sidebar-menu > li.active {
    background: transparent !important;
}
.sidebar-menu > li.active > a:not(.active) {
    background: transparent !important;
    color: rgba(255, 255, 255, 0.8) !important;
    border-left: none !important;
}

/* ===== FONT SIZE INCREASE ===== */
/* Increase body and UI text slightly - NOT page titles */
.sidebar-menu a {
    font-size: 0.88rem !important;
}
.sidebar-menu .menu-header {
    font-size: 0.7rem !important;
}
.modern-form-label {
    font-size: 0.87rem !important;
}
.modern-form-label small {
    font-size: 0.77rem !important;
}
.modern-input,
.modern-select,
.modern-textarea {
    font-size: 0.9rem !important;
}
.modern-table {
    font-size: 0.87rem !important;
}
.modern-table td {
    font-size: 0.87rem !important;
}
.modern-table th {
    font-size: 0.77rem !important;
}
.btn-modern {
    font-size: 0.87rem !important;
}
.btn-modern-sm {
    font-size: 0.8rem !important;
}
.modern-badge {
    font-size: 0.75rem !important;
}
.modern-form-error {
    font-size: 0.77rem !important;
}
.modern-input-hint {
    font-size: 0.78rem !important;
}
.topbar-breadcrumb {
    font-size: 0.95rem !important;
}
/* Keep page title the SAME size - do not increase */
.modern-index-title,
.card-header h5 {
    font-size: inherit !important;
}

CSS;

    // Check if we already added this
    if (strpos($css, 'ACTIVE CHILD MENU HIGHLIGHT') === false) {
        file_put_contents($cssPath, $css . $newCSS);
        $changes[] = "ADDED: Active child menu CSS + font size increase CSS";
        echo "  ✓ Added active child highlight CSS\n";
        echo "  ✓ Added font size increase CSS (not page titles)\n";
    } else {
        // Replace existing block
        $css = preg_replace('/\/\* ===== ACTIVE CHILD MENU HIGHLIGHT.*$/s', '', $css);
        file_put_contents($cssPath, $css . $newCSS);
        $changes[] = "UPDATED: Active child menu CSS + font size CSS refreshed";
        echo "  ✓ Updated active child + font size CSS\n";
    }
} else {
    echo "  ✗ admin.css not found\n";
}

// ============================================================
// SUMMARY
// ============================================================
echo "\n============================================\n";
echo "ALL CHANGES:\n";
echo "============================================\n";
foreach ($changes as $c) {
    echo "  ✓ $c\n";
}

echo "\n============================================\n";
echo "NEXT STEPS:\n";
echo "============================================\n";
echo "1. Run: php artisan route:clear && php artisan view:clear && php artisan cache:clear\n";
echo "2. Test Subject Assignment page - if still 404, check controller output above\n";
echo "3. Test sidebar - active child should be highlighted, parent group should NOT be highlighted\n";
echo "4. Test font sizes - body text larger, page titles same\n";
echo "============================================\n";

