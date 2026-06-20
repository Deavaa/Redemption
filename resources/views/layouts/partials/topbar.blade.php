{{--
    ============================================================================
    TOPBAR PARTIAL — School of Redemption Admin Layout
    ============================================================================

    Cleaner, more functional topbar:
    • Sidebar toggle (hamburger)
    • Breadcrumb trail (auto-generated from route name)
    • Global search input (desktop only — hidden < 1024px)
    • Language switcher, Chat, Notifications (with unread counts)
    • Settings + View Website (admin/GM only)
    • User dropdown (Profile, Logout)

    Expects $menuLevel to be set by the parent layout (used to gate the
    Settings icon visibility).
    ----------------------------------------------------------------------------
--}}

@php
    // ── Defensive: if there's no authenticated user, set safe defaults
    // and skip the topbar entirely (same guard as the sidebar partial).
    // This prevents "Undefined variable $menuLevel" and "Trying to get
    // property of null" errors when the topbar is rendered in a context
    // where Auth::user() is null (e.g. error page rendering).
    $authUser = \Illuminate\Support\Facades\Auth::user();
    if (!$authUser) {
        $menuLevel = 'full';
        $showGreeting = false;
        $breadcrumbs = [];
        $chatUnread = 0;
        $notifUnread = 0;
        $latestNotifs = collect([]);
        $showTopbar = false;
    } else {
        $showTopbar = true;

    // Build a simple breadcrumb from the route name
    // e.g. admin.mark-entries.index → [Dashboard, Mark Entries]
    //      admin.students.show      → [Dashboard, Students, Details]
    $routeName = request()->route() ? request()->route()->getName() : '';
    $breadcrumbs = [['label' => __('app.dashboard'), 'url' => route('admin.dashboard')]];
    if (Str::startsWith($routeName, 'admin.')) {
        $parts = explode('.', Str::after($routeName, 'admin.'));
        // Skip 'dashboard' (it's already the root)
        $parts = array_values(array_filter($parts, function ($p) { return $p !== 'dashboard'; }));
        $labelMap = [
            'mark-entries' => 'Mark Entry',
            'mark-sheet' => 'Mark Sheet',
            'mark-sheet-full' => 'Full Mark Sheet',
            'mark-roster' => 'Mark Roster',
            'mark-entry-locks' => 'Mark Entry Locks',
            'mark-entry-permissions' => 'Mark Edit Permissions',
            'mark-entry-disallowals' => 'Mark Entry Disallowals',
            'mark-entry-configs' => 'Mark Entry Config',
            'academic-years' => 'Academic Years',
            'subject-assignments' => 'Assign Subjects',
            'teacher-assignments' => 'Teacher Assignments',
            'teacher-reviews' => 'Teacher Reviews',
            'teacher-efficiency' => 'Teacher Efficiency',
            'teacher-evaluations' => 'Teacher Evaluations',
            'class-assets' => 'Class Assets',
            'fee-payments' => 'Fee Payments',
            'finance-statements' => 'Finance Statements',
            'income-expenses' => 'Income / Expense',
            'budget-comparison' => 'Budget Comparison',
            'financial-comparison' => 'Financial Comparison',
            'performance-comparison' => 'Branch Performance',
            'performance-reports' => 'Performance Reports',
            'progress-reports' => 'Progress Reports',
            'psychological-analysis' => 'Psychological Analysis',
            'performance' => 'Performance Analysis',
            'attendance-delegation' => 'Attendance Delegation',
            'attendance-api' => 'Attendance API',
            'lesson-plans' => 'Lesson Plans',
            'content-notes' => 'Note Bank',
            'assessment-questions' => 'Self-Assessment',
            'exam-questions' => 'Exam Questions',
            'certificate-generate' => 'Certificate Generator',
            'certificate-print' => 'Print on Certificate',
            'leaving-certificate' => 'Leaving Certificate',
            'id-card-generate' => 'ID Card Generator',
            'id-cards' => 'ID Cards',
            'certificates' => 'Certificates',
            'report-card' => 'Report Cards',
            'report-exchange' => 'Report Exchange',
            'video-library' => 'Video Library',
            'library' => 'Book Library',
            'calendar' => 'Calendar',
            'announcements' => 'Announcements',
            'telegram' => 'Telegram',
            'chat' => 'Chat',
            'branches' => 'Branches',
            'web-content' => 'Web Content',
            'gallery-images' => 'Gallery Images',
            'gallery-videos' => 'Gallery Videos',
            'contact-messages' => 'Messages',
            'news' => 'News',
            'sliders' => 'Sliders',
            'slider-alerts' => 'Slider Alerts',
            'user-access' => 'User Access',
            'roles' => 'Roles & Permissions',
            'settings' => 'Settings',
            'backup' => 'Database Backup',
            'audits' => 'Audit Log',
            'email-inbox' => 'Email Inbox',
            'email-inbox-settings' => 'Email Inbox Settings',
            'bank-integration' => 'Bank Integration',
            'bank-integration-settings' => 'Bank Integration Settings',
            'club-follow-up-configs' => 'Club Follow-up Config',
            'graphical-reports' => 'Graphical Reports',
            'team-members' => 'Team Members',
            'parents' => 'Parents',
            'teachers' => 'Teachers',
            'students' => 'Students',
            'staff' => 'Staff',
            'enrollments' => 'Enrollments',
            'sections' => 'Sections',
            'classrooms' => 'Classes',
            'subjects' => 'Subjects',
            'terms' => 'Terms',
            'exams' => 'Exams',
            'fees' => 'Fees',
            'payrolls' => 'Payrolls',
            'budgets' => 'Budgets',
            'leaves' => 'Leaves',
            'employee-assets' => 'Employee Assets',
            'profile' => 'My Profile',
            'database-backup' => 'Database Backup',
        ];
        $actionMap = [
            'index' => 'List',
            'create' => 'New',
            'store' => 'New',
            'show' => 'Details',
            'edit' => 'Edit',
            'update' => 'Edit',
            'destroy' => 'Delete',
        ];
        $acc = '';
        foreach ($parts as $i => $p) {
            if ($i === 0) {
                $label = $labelMap[$p] ?? ucfirst(str_replace('-', ' ', $p));
                $acc = 'admin.' . $p . '.index';
                $url = \Illuminate\Support\Facades\Route::has($acc) ? route($acc) : null;
                $breadcrumbs[] = ['label' => $label, 'url' => $url];
            } else {
                $label = $actionMap[$p] ?? ucfirst(str_replace('-', ' ', $p));
                $breadcrumbs[] = ['label' => $label, 'url' => null, 'is_current' => true];
            }
        }
    }
    // If we ended up with just Dashboard, also show a greeting
    $showGreeting = count($breadcrumbs) === 1;
    } // end else (authenticated user)
@endphp

<?php if (!empty($showTopbar)): ?>
<nav class="admin-topbar">
    <div class="topbar-left">
        <button class="topbar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-breadcrumb">
            @if($showGreeting)
                <span>{{ __('app.hi') }}</span>
                <span class="breadcrumb-current">{{ Auth::user()->name }}</span>
            @else
                @foreach($breadcrumbs as $i => $crumb)
                    @if(!empty($crumb['is_current']) || $i === count($breadcrumbs) - 1)
                        <span class="breadcrumb-current">{{ $crumb['label'] }}</span>
                    @elseif(!empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                        <i class="fas fa-chevron-right breadcrumb-sep"></i>
                    @else
                        <span>{{ $crumb['label'] }}</span>
                        <i class="fas fa-chevron-right breadcrumb-sep"></i>
                    @endif
                @endforeach
            @endif
        </div>
    </div>

    <div class="topbar-right">
        {{-- Global search (desktop ≥ 1024px) --}}
        <div class="topbar-search d-none d-lg-block">
            <i class="fas fa-search"></i>
            <input type="text"
                   id="topbarGlobalSearch"
                   placeholder="Search students, teachers…"
                   autocomplete="off"
                   aria-label="Global search">
            <kbd>/</kbd>
        </div>

        {{-- Language switcher --}}
        <div class="dropdown">
            <button class="topbar-icon-btn" type="button"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside"
                    title="{{ __('app.language') }}"
                    aria-label="{{ __('app.language') }}">
                <i class="fas fa-globe"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" style="font-size:9px;padding:2px 5px;">
                    {{ strtoupper(app()->getLocale()) }}
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li class="dropdown-header"><i class="fas fa-globe me-1"></i> {{ __('app.language') }}</li>
                <li><hr class="dropdown-divider"></li>
                @foreach(config('app.available_locales') as $code => $name)
                    <li>
                        <a class="dropdown-item {{ app()->getLocale() === $code ? 'active' : '' }}"
                           href="{{ route('lang.switch', $code) }}">
                            @if(app()->getLocale() === $code)
                                <i class="fas fa-check me-2 text-success"></i>
                            @else
                                <i class="fas fa-circle me-2 text-muted" style="font-size:8px"></i>
                            @endif
                            {{ $name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Chat --}}
        @php
            $chatUnread = 0;
            try {
                $chatUnread = \App\Models\ChatMessage::whereHas('conversation.participants', function ($q) {
                    $q->where('user_id', Auth::id());
                })->where('sender_id', '!=', Auth::id())->where('is_read', false)->count();
            } catch (\Exception $e) {}
        @endphp
        <a href="{{ route('admin.chat.index') }}" class="topbar-icon-btn" title="{{ __('app.chat') }}">
            <i class="fas fa-comment-dots"></i>
            @if($chatUnread > 0)
                <span class="badge-dot">{{ $chatUnread > 99 ? '99+' : $chatUnread }}</span>
            @endif
        </a>

        {{-- Notifications --}}
        @php
            $notifUnread = 0;
            try {
                $notifUnread = \App\Models\Notification::where('user_id', Auth::id())
                    ->where('is_read', false)->count();
            } catch (\Exception $e) {}
            $latestNotifs = \App\Models\Notification::where('user_id', Auth::id())
                ->orderByDesc('created_at')->limit(5)->get();
        @endphp
        <div class="dropdown">
            <button class="topbar-icon-btn" type="button"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside"
                    title="{{ __('app.notifications') }}"
                    aria-label="{{ __('app.notifications') }}">
                <i class="fas fa-bell"></i>
                @if($notifUnread > 0)
                    <span class="badge-dot">{{ $notifUnread > 99 ? '99+' : $notifUnread }}</span>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:320px;max-width:90vw;">
                <li class="dropdown-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-bell me-1"></i> {{ __('app.notifications') }}</span>
                    @if($notifUnread > 0)
                        <form method="POST" action="{{ route('admin.notifications.markAllRead') }}" style="display:inline">@csrf
                            <button type="submit" class="btn btn-link p-0 text-primary" style="font-size:11px;text-decoration:none;">
                                {{ __('app.mark_all_read') }}
                            </button>
                        </form>
                    @endif
                </li>
                <li><hr class="dropdown-divider"></li>
                @forelse($latestNotifs as $notif)
                    <li>
                        <a class="dropdown-item {{ $notif->is_read ? '' : 'bg-light' }}"
                           href="{{ $notif->link ? route('admin.notifications.read', $notif->id) : route('admin.notifications.index') }}"
                           style="white-space:normal;padding:10px 14px;">
                            <div class="fw-semibold" style="font-size:13px;color:var(--color-text-dark);">
                                {{ Str::limit($notif->title ?? $notif->message ?? __('app.notification'), 50) }}
                            </div>
                            <div style="font-size:11px;color:var(--color-text-muted);">
                                {{ $notif->created_at->diffForHumans() }}
                            </div>
                        </a>
                    </li>
                @empty
                    <li class="dropdown-item text-center text-muted" style="font-size:12px;padding:24px;">
                        <i class="fas fa-bell-slash mb-2" style="font-size:22px;opacity:.4;display:block"></i>
                        {{ __('app.no_notifications') }}
                    </li>
                @endforelse
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-center text-primary fw-semibold" href="{{ route('admin.notifications.index') }}" style="font-size:12px;">
                        {{ __('app.view_all_notifications') }}
                    </a>
                </li>
            </ul>
        </div>

        @if(in_array($menuLevel, ['full', 'general_manager']))
        <a href="{{ route('admin.settings.index') }}" class="topbar-icon-btn d-none d-md-flex" title="Settings">
            <i class="fas fa-cog"></i>
        </a>
        @endif
        <a href="{{ url('/') }}" class="topbar-icon-btn d-none d-md-flex" target="_blank" title="{{ __('app.view_website') }}">
            <i class="fas fa-external-link-alt"></i>
        </a>

        {{-- User dropdown --}}
        <div class="dropdown">
            <button class="topbar-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="topbar-dropdown-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <span class="topbar-dropdown-name d-none d-md-inline">{{ Auth::user()->name }}</span>
                <i class="fas fa-chevron-down topbar-dropdown-chevron d-none d-md-inline"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="min-width:240px;">
                <li class="dropdown-header">
                    <div style="font-weight:700;font-size:14px;color:var(--color-text-dark);">{{ Auth::user()->name }}</div>
                    <div style="font-size:12px;color:var(--color-text-muted);">{{ Auth::user()->email }}</div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fas fa-user-cog me-2"></i>Profile &amp; Password</a></li>
                <li><a class="dropdown-item" href="{{ url('/') }}" target="_blank"><i class="fas fa-external-link-alt me-2"></i>{{ __('app.view_website') }}</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>{{ __('app.logout') }}</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<script>
{{-- Global search: redirect to relevant admin index based on the query.
     This is a lightweight "command palette" — press "/" to focus, type
     something like "students" or "marks" or a student name, hit Enter,
     and you'll be taken to the relevant page. --}}
(function () {
    var input = document.getElementById('topbarGlobalSearch');
    if (!input) return;

    // Press "/" to focus the search (skipped if already in an input)
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' &&
            document.activeElement.tagName !== 'TEXTAREA' && document.activeElement.tagName !== 'SELECT') {
            e.preventDefault();
            input.focus();
            input.select();
        }
        if (e.key === 'Escape' && document.activeElement === input) {
            input.value = '';
            input.blur();
        }
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            var q = input.value.trim().toLowerCase();
            if (!q) return;

            // Map common keywords to admin routes
            var routes = {
                'student': '{{ route('admin.students.index') }}',
                'students': '{{ route('admin.students.index') }}',
                'teacher': '{{ route('admin.teachers.index') }}',
                'teachers': '{{ route('admin.teachers.index') }}',
                'parent': '{{ route('admin.parents.index') }}',
                'parents': '{{ route('admin.parents.index') }}',
                'staff': '{{ route('admin.staff.index') }}',
                'mark': '{{ route('admin.mark-entries.index') }}',
                'marks': '{{ route('admin.mark-entries.index') }}',
                'attendance': '{{ route('admin.attendance.index') }}',
                'fee': '{{ route('admin.fees.index') }}',
                'fees': '{{ route('admin.fees.index') }}',
                'payment': '{{ route('admin.fee-payments.index') }}',
                'payments': '{{ route('admin.fee-payments.index') }}',
                'payroll': '{{ route('admin.payrolls.index') }}',
                'payrolls': '{{ route('admin.payrolls.index') }}',
                'exam': '{{ route('admin.exams.index') }}',
                'exams': '{{ route('admin.exams.index') }}',
                'class': '{{ route('admin.classrooms.index') }}',
                'classes': '{{ route('admin.classrooms.index') }}',
                'section': '{{ route('admin.sections.index') }}',
                'sections': '{{ route('admin.sections.index') }}',
                'subject': '{{ route('admin.subjects.index') }}',
                'subjects': '{{ route('admin.subjects.index') }}',
                'calendar': '{{ route('admin.calendar.index') }}',
                'announcement': '{{ route('admin.announcements.index') }}',
                'announcements': '{{ route('admin.announcements.index') }}',
                'chat': '{{ route('admin.chat.index') }}',
                'notification': '{{ route('admin.notifications.index') }}',
                'notifications': '{{ route('admin.notifications.index') }}',
                'dashboard': '{{ route('admin.dashboard') }}',
                'setting': '{{ route('admin.settings.index') }}',
                'settings': '{{ route('admin.settings.index') }}',
                'backup': '{{ route('admin.backup.index') }}',
                'audit': '{{ route('admin.audits.index') }}',
                'audits': '{{ route('admin.audits.index') }}',
                'library': '{{ route('admin.library.index') }}',
                'book': '{{ route('admin.library.index') }}',
                'books': '{{ route('admin.library.index') }}',
                'video': '{{ route('admin.video-library.index') }}',
                'certificate': '{{ route('admin.certificate-generate.index') }}',
                'certificates': '{{ route('admin.certificate-generate.index') }}',
                'id card': '{{ route('admin.id-card-generate.index') }}',
                'id-card': '{{ route('admin.id-card-generate.index') }}',
                'transcript': '{{ route('admin.transcript.index') }}',
                'report': '{{ route('admin.report-card.index') }}',
                'reports': '{{ route('admin.report-card.index') }}',
                'news': '{{ route('admin.news.index') }}',
                'branch': '{{ route('admin.branches.index') }}',
                'branches': '{{ route('admin.branches.index') }}',
                'enrollment': '{{ route('admin.enrollments.index') }}',
                'enrollments': '{{ route('admin.enrollments.index') }}',
                'role': '{{ route('admin.roles.index') }}',
                'roles': '{{ route('admin.roles.index') }}',
                'lesson': '{{ route('admin.lesson-plans.index') }}',
                'lesson plan': '{{ route('admin.lesson-plans.index') }}',
                'lesson-plan': '{{ route('admin.lesson-plans.index') }}',
                'training': '{{ route('admin.trainings.index') }}',
                'trainings': '{{ route('admin.trainings.index') }}',
                'stock': '{{ route('admin.stock.index') }}',
                'inventory': '{{ route('admin.stock.index') }}',
                'leave': '{{ route('admin.leaves.index') }}',
                'leaves': '{{ route('admin.leaves.index') }}',
                'telegram': '{{ route('admin.telegram.index') }}',
            };

            // Direct keyword match
            if (routes[q]) {
                window.location.href = routes[q];
                return;
            }

            // Partial match — first route whose key contains the query
            for (var key in routes) {
                if (key.indexOf(q) !== -1 || q.indexOf(key) !== -1) {
                    window.location.href = routes[key];
                    return;
                }
            }

            // No match — try searching students as a fallback
            window.location.href = '{{ route('admin.students.index') }}?search=' + encodeURIComponent(q);
        }
    });
})();
</script>
