<?php
 $base = getcwd();
 $layoutPath = $base . '/resources/views/layouts/admin.blade.php';
 $layout = file_get_contents($layoutPath);
 $changes = 0;

echo "===== FIX: Sidebar active child highlighting =====\n\n";

// Step 1: Change parent <li> from 'active' to 'has-active-child' for group items
// Academic parent
 $old = '$isAcademicActive ? \'active\'';
 $new = '$isAcademicActive ? \'has-active-child\'';
 $layout = str_replace($old, $new, $layout, $c);
if ($c > 0) { echo "OK: Changed Academic parent to has-active-child\n"; $changes += $c; }

// People parent  
 $old = '$isPeopleActive ? \'active\'';
 $new = '$isPeopleActive ? \'has-active-child\'';
 $layout = str_replace($old, $new, $layout, $c);
if ($c > 0) { echo "OK: Changed People parent to has-active-child\n"; $changes += $c; }

// Finance parent
 $old = '$isFinanceActive ? \'active\'';
 $new = '$isFinanceActive ? \'has-active-child\'';
 $layout = str_replace($old, $new, $layout, $c);
if ($c > 0) { echo "OK: Changed Finance parent to has-active-child\n"; $changes += $c; }

// Website parent
 $old = '$isWebsiteActive ? \'active\'';
 $new = '$isWebsiteActive ? \'has-active-child\'';
 $layout = str_replace($old, $new, $layout, $c);
if ($c > 0) { echo "OK: Changed Website parent to has-active-child\n"; $changes += $c; }

// Step 2: Add active class to child <a> links
// We use regex to find child links and add the active class

 $childLinks = [
    // [route name pattern for request()->routeIs(), route name for href, icon, label]
    ['admin.academic-years.*', "admin.academic-years.index", 'fas fa-calendar', 'Academic Years'],
    ['admin.terms.*', "admin.terms.index", 'fas fa-bookmark', 'Terms'],
    ['admin.subjects.*', "admin.subjects.index", 'fas fa-book', 'Subjects'],
    ['admin.subject-assignments.*', "admin.subject-assignments.index", 'fas fa-link', 'Assign Subjects'],
    ['admin.exams.*', "admin.exams.index", 'fas fa-file-alt', 'Exams'],
    ['admin.classrooms.*', "admin.classrooms.index", 'fas fa-building', 'Classes'],
    ['admin.mark-entries.*', "admin.mark-entries.index", 'fas fa-pen', 'Mark Entry'],
    ['admin.students.*', "admin.students.index", 'fas fa-user-graduate', 'Students'],
    ['admin.teachers.*', "admin.teachers.index", 'fas fa-chalkboard-teacher', 'Teachers'],
    ['admin.staff.*', "admin.staff.index", 'fas fa-id-badge', 'Staff'],
    ['admin.team-members.*', "admin.team-members.index", 'fas fa-users', 'Team Members'],
    ['admin.fees.*', "admin.fees.index", 'fas fa-money-bill-wave', 'Fees'],
    ['admin.fee-payments.*', "admin.fee-payments.index", 'fas fa-credit-card', 'Payments'],
    ['admin.payrolls.*', "admin.payrolls.index", 'fas fa-file-invoice-dollar', 'Payroll'],
    ['admin.budgets.*', "admin.budgets.index", 'fas fa-chart-pie', 'Budgets'],
    ['admin.income-expenses.*', "admin.income-expenses.index", 'fas fa-exchange-alt', 'Income/Expense'],
    ['admin.finance-statements.*', "admin.finance-statements.index", 'fas fa-file-alt', 'Statements'],
    ['admin.leaves.*', "admin.leaves.index", 'fas fa-calendar-minus', 'Leaves'],
    ['admin.employee-assets.*', "admin.employee-assets.index", 'fas fa-boxes', 'Employee Assets'],
    ['admin.branches.*', "admin.branches.index", 'fas fa-map-marker-alt', 'Branches'],
    ['admin.sliders.*', "admin.sliders.index", 'fas fa-images', 'Sliders'],
    ['admin.gallery-images.*', "admin.gallery-images.index", 'fas fa-image', 'Gallery Images'],
    ['admin.gallery-videos.*', "admin.gallery-videos.index", 'fas fa-video', 'Gallery Videos'],
    ['admin.contact-messages.*', "admin.contact-messages.index", 'fas fa-envelope', 'Messages'],
];

foreach ($childLinks as $link) {
    list($routePattern, $routeName, $icon, $label) = $link;
    
    // Build the old pattern: <li><a href="{{ route('admin.XXX.index') }}"><i class="icon"></i> Label</a></li>
    $oldLink = "<li><a href=\"{{ route('" . $routeName . "') }}\"><i class=\"" . $icon . "\"></i> " . $label . "</a></li>";
    
    // Build the new pattern with active class
    $activeClass = "{{ request()->routeIs('" . $routePattern . "') ? 'active' : '' }}";
    $newLink = "<li><a href=\"{{ route('" . $routeName . "') }}\" class=\"" . $activeClass . "\"><i class=\"" . $icon . "\"></i> " . $label . "</a></li>";
    
    $layout = str_replace($oldLink, $newLink, $layout, $c);
    if ($c > 0) {
        echo "OK: Added active class to $label\n";
        $changes += $c;
    } else {
        // Try with single quotes variation
        $oldLink2 = "<li><a href='" . "{{ route('" . $routeName . "') }}" . "'><i class='" . $icon . "'></i> " . $label . "</a></li>";
        $layout = str_replace($oldLink2, $newLink, $layout, $c2);
        if ($c2 > 0) {
            echo "OK: Added active class to $label (alt format)\n";
            $changes += $c2;
        }
    }
}

// Step 3: Add active class to top-level items (Dashboard, Settings, Audit)
// Dashboard
 $old = '<a href="{{ route(\'admin.dashboard\') }}"><i class="fas fa-th-large"></i><span>Dashboard</span></a>';
 $new = '<a href="{{ route(\'admin.dashboard\') }}" class="{{ request()->routeIs(\'admin.dashboard\') ? \'active\' : \'\' }}"><i class="fas fa-th-large"></i><span>Dashboard</span></a>';
 $layout = str_replace($old, $new, $layout, $c);
if ($c > 0) { echo "OK: Added active class to Dashboard link\n"; $changes += $c; }

// Settings
 $old = '<a href="{{ route(\'admin.settings.index\') }}"><i class="fas fa-cog"></i><span>Settings</span></a>';
 $new = '<a href="{{ route(\'admin.settings.index\') }}" class="{{ request()->routeIs(\'admin.settings.*\') ? \'active\' : \'\' }}"><i class="fas fa-cog"></i><span>Settings</span></a>';
 $layout = str_replace($old, $new, $layout, $c);
if ($c > 0) { echo "OK: Added active class to Settings link\n"; $changes += $c; }

// Audit Log
 $old = '<a href="{{ route(\'admin.audits.index\') }}"><i class="fas fa-clipboard-list"></i><span>Audit Log</span></a>';
 $new = '<a href="{{ route(\'admin.audits.index\') }}" class="{{ request()->routeIs(\'admin.audits.*\') ? \'active\' : \'\' }}"><i class="fas fa-clipboard-list"></i><span>Audit Log</span></a>';
 $layout = str_replace($old, $new, $layout, $c);
if ($c > 0) { echo "OK: Added active class to Audit Log link\n"; $changes += $c; }

file_put_contents($layoutPath, $layout);

echo "\nTotal changes: $changes\n";
if ($changes > 0) {
    echo "SUCCESS: Sidebar updated!\n";
} else {
    echo "WARNING: No changes made. The sidebar may already be updated or the patterns didn't match.\n";
}

echo "\nRun: php artisan view:clear && php artisan cache:clear\n";
