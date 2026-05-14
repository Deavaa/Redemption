<?php
 $base = getcwd();
 $layoutPath = $base . '/resources/views/layouts/admin.blade.php';
 $layout = file_get_contents($layoutPath);
 $changes = 0;

echo "===== ADDING MISSING MENU ITEMS =====\n\n";

// Step 1: Update the PHP block for active route detection
// Add new route groups
 $oldPhp = '$academicRoutes = [\'admin.academic-years.*\',\'admin.terms.*\',\'admin.subjects.*\',\'admin.subject-assignments.*\',\'admin.exams.*\',\'admin.mark-entries.*\',\'admin.classrooms.*\',\'admin.sections.*\'];';
 $newPhp = '$academicRoutes = [\'admin.academic-years.*\',\'admin.terms.*\',\'admin.subjects.*\',\'admin.subject-assignments.*\',\'admin.exams.*\',\'admin.mark-entries.*\',\'admin.classrooms.*\',\'admin.sections.*\',\'admin.mark-sheet.*\',\'admin.mark-sheet-full.*\',\'admin.mark-roster.*\',\'admin.report-card.*\',\'admin.progress-reports.*\'];';

 $layout = str_replace($oldPhp, $newPhp, $layout, $c);
if ($c > 0) { echo "OK: Updated academicRoutes\n"; $changes += $c; }

// Add generate routes group
 $oldPeopleRoutes = '$peopleRoutes = [\'admin.students.*\',\'admin.teachers.*\',\'admin.staff.*\',\'admin.team-members.*\',\'admin.parents.*\'];';
 $newPeopleRoutes = '$peopleRoutes = [\'admin.students.*\',\'admin.teachers.*\',\'admin.staff.*\',\'admin.team-members.*\',\'admin.parents.*\',\'admin.teacher-assignments.*\'];';

 $layout = str_replace($oldPeopleRoutes, $newPeopleRoutes, $layout, $c);
if ($c > 0) { echo "OK: Updated peopleRoutes\n"; $changes += $c; }

// Add generate routes detection
 $oldWebsiteRoutes = '$websiteRoutes = [\'admin.sliders.*\',\'admin.gallery-*\',\'admin.branches.*\',\'admin.contact-messages.*\'];';
 $newWebsiteRoutes = '$websiteRoutes = [\'admin.sliders.*\',\'admin.gallery-*\',\'admin.branches.*\',\'admin.contact-messages.*\'];' . "\n" . '                $generateRoutes = [\'admin.id-card-generate.*\',\'admin.certificate-generate.*\',\'admin.id-cards.*\',\'admin.certificates.*\'];';

 $layout = str_replace($oldWebsiteRoutes, $newWebsiteRoutes, $layout, $c);
if ($c > 0) { echo "OK: Added generateRoutes\n"; $changes += $c; }

// Add isGenerateActive variable
 $oldIsWebsite = '$isWebsiteActive = request()->routeIs($websiteRoutes);';
 $newIsWebsite = '$isWebsiteActive = request()->routeIs($websiteRoutes);' . "\n" . '                $isGenerateActive = request()->routeIs($generateRoutes);';

 $layout = str_replace($oldIsWebsite, $newIsWebsite, $layout, $c);
if ($c > 0) { echo "OK: Added isGenerateActive\n"; $changes += $c; }

// Step 2: Add Mark Sheet, Mark Roster, Report Cards, Progress Reports after Mark Entry
 $oldMarkEntry = '<li><a href="{{ route(\'admin.mark-entries.index\') }}" class="{{ request()->routeIs(\'admin.mark-entries.*\') ? \'active\' : \'\' }}"><i class="fas fa-pen"></i> Mark Entry</a></li>';
 $newMarkEntry = '<li><a href="{{ route(\'admin.mark-entries.index\') }}" class="{{ request()->routeIs(\'admin.mark-entries.*\') ? \'active\' : \'\' }}"><i class="fas fa-pen"></i> Mark Entry</a></li>
                        <li><a href="{{ route(\'admin.mark-sheet.index\') }}" class="{{ request()->routeIs(\'admin.mark-sheet.*\') ? \'active\' : \'\' }}"><i class="fas fa-file-alt"></i> Mark Sheet</a></li>
                        <li><a href="{{ route(\'admin.mark-sheet-full.index\') }}" class="{{ request()->routeIs(\'admin.mark-sheet-full.*\') ? \'active\' : \'\' }}"><i class="fas fa-table"></i> Full Mark Sheet</a></li>
                        <li><a href="{{ route(\'admin.mark-roster.index\') }}" class="{{ request()->routeIs(\'admin.mark-roster.*\') ? \'active\' : \'\' }}"><i class="fas fa-list-ol"></i> Mark Roster</a></li>
                        <li><a href="{{ route(\'admin.report-card.index\') }}" class="{{ request()->routeIs(\'admin.report-card.*\') ? \'active\' : \'\' }}"><i class="fas fa-id-card"></i> Report Cards</a></li>
                        <li><a href="{{ route(\'admin.progress-reports.index\') }}" class="{{ request()->routeIs(\'admin.progress-reports.*\') ? \'active\' : \'\' }}"><i class="fas fa-chart-line"></i> Progress Reports</a></li>';

 $layout = str_replace($oldMarkEntry, $newMarkEntry, $layout, $c);
if ($c > 0) { echo "OK: Added Mark Sheet, Full Mark Sheet, Mark Roster, Report Cards, Progress Reports\n"; $changes += $c; }

// Step 3: Add Parents and Teacher Assignments to People submenu
 $oldTeamMembers = '<li><a href="{{ route(\'admin.team-members.index\') }}" class="{{ request()->routeIs(\'admin.team-members.*\') ? \'active\' : \'\' }}"><i class="fas fa-users"></i> Team Members</a></li>
                    </ul>
                </li>

                <li class="menu-header">FINANCE</li>';

 $newTeamMembers = '<li><a href="{{ route(\'admin.team-members.index\') }}" class="{{ request()->routeIs(\'admin.team-members.*\') ? \'active\' : \'\' }}"><i class="fas fa-users"></i> Team Members</a></li>
                        <li><a href="{{ route(\'admin.parents.index\') }}" class="{{ request()->routeIs(\'admin.parents.*\') ? \'active\' : \'\' }}"><i class="fas fa-user-friends"></i> Parents</a></li>
                        <li><a href="{{ route(\'admin.teacher-assignments.index\') }}" class="{{ request()->routeIs(\'admin.teacher-assignments.*\') ? \'active\' : \'\' }}"><i class="fas fa-chalkboard"></i> Teacher Assignments</a></li>
                    </ul>
                </li>

                <li class="menu-header">FINANCE</li>';

 $layout = str_replace($oldTeamMembers, $newTeamMembers, $layout, $c);
if ($c > 0) { echo "OK: Added Parents, Teacher Assignments to People\n"; $changes += $c; }

// Step 4: Add GENERATE section before WEBSITE
 $oldWebsiteHeader = '<li class="menu-header">WEBSITE</li>';
 $newGenerateSection = '<li class="menu-header">GENERATE</li>
                <li class="{{ $isGenerateActive ? \'has-active-child\' : \'\' }}">
                    <a href="#generateSubmenu" data-bs-toggle="collapse" class="submenu-toggle">
                        <i class="fas fa-magic"></i><span>Generate</span><i class="fas fa-chevron-down sidebar-chevron"></i>
                    </a>
                    <ul class="collapse {{ $isGenerateActive ? \'show\' : \'\' }}" id="generateSubmenu">
                        <li><a href="{{ route(\'admin.id-card-generate.index\') }}" class="{{ request()->routeIs(\'admin.id-card-generate.*\') ? \'active\' : \'\' }}"><i class="fas fa-id-badge"></i> Student ID Cards</a></li>
                        <li><a href="{{ route(\'admin.certificate-generate.index\') }}" class="{{ request()->routeIs(\'admin.certificate-generate.*\') ? \'active\' : \'\' }}"><i class="fas fa-award"></i> Certificates</a></li>
                    </ul>
                </li>

                <li class="menu-header">WEBSITE</li>';

 $layout = str_replace($oldWebsiteHeader, $newGenerateSection, $layout, $c);
if ($c > 0) { echo "OK: Added GENERATE section (ID Cards, Certificates)\n"; $changes += $c; }

// Step 5: Add Calendar to SYSTEM section
 $oldSettings = '<li class="menu-header">SYSTEM</li>
                <li class="{{ request()->routeIs(\'admin.settings.*\') ? \'active\' : \'\' }}">';

 $newSettings = '<li class="menu-header">SYSTEM</li>
                <li class="{{ request()->routeIs(\'admin.calendar-events.*\') ? \'active\' : \'\' }}">
                    <a href="{{ route(\'admin.calendar-events.index\') }}" class="{{ request()->routeIs(\'admin.calendar-events.*\') ? \'active\' : \'\' }}"><i class="fas fa-calendar-alt"></i><span>Calendar</span></a>
                </li>
                <li class="{{ request()->routeIs(\'admin.settings.*\') ? \'active\' : \'\' }}">';

 $layout = str_replace($oldSettings, $newSettings, $layout, $c);
if ($c > 0) { echo "OK: Added Calendar to SYSTEM\n"; $changes += $c; }

// Save
if ($changes > 0) {
    file_put_contents($layoutPath, $layout);
    echo "\n===== SAVED: $changes changes made =====\n";
} else {
    echo "\n===== NO CHANGES - patterns may not match =====\n";
    // Debug: show the current sidebar area
    echo "\nLooking for patterns that might differ...\n";
    if (strpos($layout, 'mark-entries') !== false) {
        echo "  mark-entries found in layout\n";
    }
    if (strpos($layout, 'mark-sheet') !== false) {
        echo "  mark-sheet ALREADY in layout\n";
    }
    if (strpos($layout, 'mark-roster') !== false) {
        echo "  mark-roster ALREADY in layout\n";
    }
    if (strpos($layout, 'team-members') !== false) {
        echo "  team-members found in layout\n";
    }
}

echo "\nRun: php artisan view:clear && php artisan cache:clear\n";
