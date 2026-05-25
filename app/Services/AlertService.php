<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\CalendarEvent;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;

/**
 * AlertService — Generates role-based and responsibility-based alerts/notifications.
 *
 * Alerts are sent to users based on their role and responsibility:
 * - System changes (new student enrolled, fee payment, exam scheduled, etc.)
 * - Academic calendar events (approved events notify relevant users)
 * - Branch-scoped events (only notify users in that branch)
 */
class AlertService
{
    /**
     * Notify users about a new calendar event.
     * - School-wide events: notify all admin-panel users
     * - Branch-specific events: notify users in that branch only
     */
    public static function notifyCalendarEvent(CalendarEvent $event): void
    {
        // Only notify for approved events
        if (!$event->is_approved) {
            return;
        }

        $title = 'Calendar: ' . $event->title;
        $message = $event->description ?? ($event->category . ' event on ' . $event->start_date->format('M d, Y'));
        $icon = 'fas fa-calendar-alt';
        $link = route('admin.calendar.index');

        if ($event->scope === 'school') {
            // School-wide: notify all admin-panel users
            static::notifyAllStaff($event->academic_year_id, $title, $message, $icon, $link);
        } else {
            // Branch-specific: notify users in that branch
            static::notifyBranch($event->branch_id, $title, $message, $icon, $link);
        }
    }

    /**
     * Notify when a calendar event is approved (was pending).
     */
    public static function notifyCalendarEventApproved(CalendarEvent $event): void
    {
        $title = 'Calendar Event Approved: ' . $event->title;
        $message = 'The calendar event "' . $event->title . '" has been approved.';
        $icon = 'fas fa-check-circle';
        $link = route('admin.calendar.index');

        // Notify the creator
        if ($event->created_by) {
            Notification::createForUser($event->created_by, 'calendar', $title, $message, $icon, $link);
        }

        // Notify relevant staff
        if ($event->scope === 'school') {
            static::notifyAllStaff($event->academic_year_id, $title, $message, $icon, $link, [$event->created_by]);
        } else {
            static::notifyBranch($event->branch_id, $title, $message, $icon, $link, [$event->created_by]);
        }
    }

    /**
     * Notify about a new student enrollment.
     * - Branch principal of the student's branch
     * - General manager
     * - Admin
     */
    public static function notifyStudentEnrolled(int $branchId, string $studentName, ?int $academicYearId = null): void
    {
        $title = 'New Student Enrolled';
        $message = "Student {$studentName} has been enrolled.";
        $icon = 'fas fa-user-plus';
        $link = route('admin.students.index');

        // Notify branch principal(s)
        static::notifyBranchPrincipals($branchId, $title, $message, $icon, $link);

        // Notify general manager
        static::notifyGeneralManagers($title, $message, $icon, $link);

        // Notify admins
        static::notifyAdmins($title, $message, $icon, $link);
    }

    /**
     * Notify about a fee payment.
     * - Branch principal of the student's branch
     * - Finance team
     * - General manager (summary)
     */
    public static function notifyFeePayment(int $branchId, string $studentName, float $amount): void
    {
        $title = 'Fee Payment Received';
        $message = "Payment of {$amount} ETB received from {$studentName}.";
        $icon = 'fas fa-money-bill-wave';
        $link = route('admin.fee-payments.index');

        // Notify branch principal(s)
        static::notifyBranchPrincipals($branchId, $title, $message, $icon, $link);

        // Notify finance users
        static::notifyFinanceUsers($title, $message, $icon, $link);

        // Notify general manager
        static::notifyGeneralManagers($title, $message, $icon, $link);
    }

    /**
     * Notify about student transfer.
     * - Principals of both source and destination branches
     * - General manager
     */
    public static function notifyStudentTransfer(int $fromBranchId, int $toBranchId, string $studentName): void
    {
        $title = 'Student Transfer';
        $message = "Student {$studentName} has been transferred.";
        $icon = 'fas fa-exchange-alt';
        $link = route('admin.students.index');

        // Notify both branch principals
        static::notifyBranchPrincipals($fromBranchId, $title, $message, $icon, $link);
        static::notifyBranchPrincipals($toBranchId, $title, $message, $icon, $link);

        // Notify general manager
        static::notifyGeneralManagers($title, $message, $icon, $link);
    }

    /**
     * Notify about teacher transfer between branches.
     * - Principals of both source and destination branches
     * - General manager
     */
    public static function notifyTeacherTransfer(int $fromBranchId, int $toBranchId, string $teacherName): void
    {
        $title = 'Teacher Transfer';
        $message = "Teacher {$teacherName} has been transferred.";
        $icon = 'fas fa-exchange-alt';
        $link = route('admin.teachers.index');

        // Notify both branch principals
        static::notifyBranchPrincipals($fromBranchId, $title, $message, $icon, $link);
        static::notifyBranchPrincipals($toBranchId, $title, $message, $icon, $link);

        // Notify general manager
        static::notifyGeneralManagers($title, $message, $icon, $link);
    }

    /**
     * Notify about exam scheduling.
     * - Teachers assigned to the class/subject
     * - Branch principal
     * - General manager
     */
    public static function notifyExamScheduled(int $branchId, string $examName): void
    {
        $title = 'Exam Scheduled';
        $message = "A new exam has been scheduled: {$examName}.";
        $icon = 'fas fa-file-alt';
        $link = route('admin.exams.index');

        static::notifyBranch($branchId, $title, $message, $icon, $link);
        static::notifyGeneralManagers($title, $message, $icon, $link);
    }

    /**
     * Notify about report submission.
     * - Teachers report to branch principal
     * - Principals/managers report to general manager
     */
    public static function notifyReportSubmitted(string $reportType, string $fromUserName, int $fromBranchId, ?int $toUserId = null): void
    {
        $title = 'New Report Submitted';
        $message = "{$fromUserName} has submitted a {$reportType} report.";
        $icon = 'fas fa-file-signature';
        $link = route('admin.report-exchange.index');

        if ($toUserId) {
            // Direct notification to specific recipient (e.g., principal or GM)
            Notification::createForUser($toUserId, 'report', $title, $message, $icon, $link);
        } else {
            // Default: notify branch principals for teacher reports
            static::notifyBranchPrincipals($fromBranchId, $title, $message, $icon, $link);
        }
    }

    /**
     * Notify about attendance changes.
     * - Branch principal
     */
    public static function notifyAttendanceAlert(int $branchId, string $message): void
    {
        $title = 'Attendance Alert';
        $icon = 'fas fa-clipboard-list';
        $link = route('admin.attendance.index');

        static::notifyBranchPrincipals($branchId, $title, $message, $icon, $link);
    }

    /**
     * Notify about a system change (generic).
     * Sends to specific roles based on the change type.
     */
    public static function notifySystemChange(string $title, string $message, string $targetRoles = 'all', ?int $branchId = null): void
    {
        $icon = 'fas fa-bell';
        $link = route('admin.dashboard');

        switch ($targetRoles) {
            case 'admin':
                static::notifyAdmins($title, $message, $icon, $link);
                break;
            case 'general_manager':
                static::notifyGeneralManagers($title, $message, $icon, $link);
                break;
            case 'branch_principal':
                if ($branchId) {
                    static::notifyBranchPrincipals($branchId, $title, $message, $icon, $link);
                }
                break;
            case 'finance':
                static::notifyFinanceUsers($title, $message, $icon, $link);
                break;
            case 'all':
            default:
                // Notify all admin panel users
                $users = User::where('is_active', true)
                    ->whereIn('role', ['admin', 'super_admin', 'general_manager', 'branch_principal', 'teacher', 'staff', 'finance', 'registrar', 'hr', 'cashier', 'librarian'])
                    ->when($branchId, fn($q) => $q->where(function ($q2) use ($branchId) {
                        $q2->where('branch_id', $branchId)
                           ->orWhereIn('role', ['admin', 'super_admin', 'general_manager']);
                    }))
                    ->pluck('id');
                foreach ($users as $userId) {
                    Notification::createForUser($userId, 'system', $title, $message, $icon, $link);
                }
                break;
        }
    }

    // ── Helper Methods ──────────────────────────────────────────

    /**
     * Notify all admin-panel users (optionally excluding specific user IDs).
     */
    private static function notifyAllStaff(?int $academicYearId, string $title, string $message, string $icon, string $link, array $excludeUserIds = []): void
    {
        $users = User::where('is_active', true)
            ->whereIn('role', ['admin', 'super_admin', 'general_manager', 'branch_principal', 'teacher', 'staff', 'finance', 'registrar', 'hr', 'cashier', 'librarian'])
            ->when(!empty($excludeUserIds), fn($q) => $q->whereNotIn('id', $excludeUserIds))
            ->pluck('id');

        foreach ($users as $userId) {
            Notification::createForUser($userId, 'calendar', $title, $message, $icon, $link);
        }
    }

    /**
     * Notify all users assigned to a specific branch.
     */
    private static function notifyBranch(int $branchId, string $title, string $message, string $icon, string $link, array $excludeUserIds = []): void
    {
        $users = User::where('is_active', true)
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereIn('role', ['admin', 'super_admin', 'general_manager']);
            })
            ->when(!empty($excludeUserIds), fn($q) => $q->whereNotIn('id', $excludeUserIds))
            ->pluck('id');

        foreach ($users as $userId) {
            Notification::createForUser($userId, 'calendar', $title, $message, $icon, $link);
        }
    }

    /**
     * Notify branch principal(s) of a specific branch.
     */
    private static function notifyBranchPrincipals(int $branchId, string $title, string $message, string $icon, string $link): void
    {
        // Find users with branch_principal role in this branch
        $principals = User::where('is_active', true)
            ->where('role', 'branch_principal')
            ->where('branch_id', $branchId)
            ->pluck('id');

        // Also check the branch_principals pivot table
        try {
            $branch = Branch::find($branchId);
            if ($branch && $branch->principals()->exists()) {
                $pivotPrincipalIds = $branch->principals()->pluck('users.id');
                $principals = $principals->merge($pivotPrincipalIds)->unique();
            }
        } catch (\Throwable $e) {}

        foreach ($principals as $userId) {
            Notification::createForUser($userId, 'branch', $title, $message, $icon, $link);
        }
    }

    /**
     * Notify general managers.
     */
    private static function notifyGeneralManagers(string $title, string $message, string $icon, string $link): void
    {
        $gms = User::where('is_active', true)
            ->where('role', 'general_manager')
            ->pluck('id');

        foreach ($gms as $userId) {
            Notification::createForUser($userId, 'general_manager', $title, $message, $icon, $link);
        }
    }

    /**
     * Notify admin users.
     */
    private static function notifyAdmins(string $title, string $message, string $icon, string $link): void
    {
        $admins = User::where('is_active', true)
            ->whereIn('role', ['admin', 'super_admin'])
            ->pluck('id');

        foreach ($admins as $userId) {
            Notification::createForUser($userId, 'admin', $title, $message, $icon, $link);
        }
    }

    /**
     * Notify finance team users.
     */
    private static function notifyFinanceUsers(string $title, string $message, string $icon, string $link): void
    {
        $finance = User::where('is_active', true)
            ->where('role', 'finance')
            ->pluck('id');

        foreach ($finance as $userId) {
            Notification::createForUser($userId, 'finance', $title, $message, $icon, $link);
        }
    }

    /**
     * Send upcoming calendar event reminders.
     * Called by scheduled job to alert users about events happening soon.
     */
    public static function sendUpcomingEventReminders(int $daysAhead = 1): int
    {
        $targetDate = now()->addDays($daysAhead)->toDateString();

        $events = CalendarEvent::where('is_approved', true)
            ->where('start_date', $targetDate)
            ->get();

        $count = 0;
        foreach ($events as $event) {
            $title = 'Upcoming: ' . $event->title;
            $message = "Reminder: \"{$event->title}\" is scheduled for tomorrow ({$event->start_date->format('M d, Y')}).";
            $icon = 'fas fa-clock';
            $link = route('admin.calendar.index');

            if ($event->scope === 'school') {
                static::notifyAllStaff($event->academic_year_id, $title, $message, $icon, $link);
            } else {
                static::notifyBranch($event->branch_id, $title, $message, $icon, $link);
            }
            $count++;
        }

        return $count;
    }
}
