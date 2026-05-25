<?php

namespace App\Http\Controllers\CalendarEvent;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\TelegramSetting;
use App\Models\BranchTelegramSetting;
use App\Models\TelegramMessage;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarEventController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $branches = Branch::orderBy('name')->get();
        $categories = CalendarEvent::categoryList();
        $user = Auth::user();

        // Determine if user can add events
        $canAddEvents = $user->canAddCalendarEvents();
        $canApproveEvents = $user->canApproveCalendarEvents();
        $isBranchPrincipal = $user->isBranchPrincipal();

        // For branch principals: only show their branch + school-wide events
        $branchScope = request()->attributes->get('branch_scope');

        // Get pending approval count for GM/Admin
        $pendingApprovalCount = 0;
        if ($canApproveEvents) {
            $pendingApprovalCount = CalendarEvent::where('is_approved', false)->count();
        }

        return view('admin.CalendarEvent.index', compact(
            'academicYears', 'branches', 'categories',
            'canAddEvents', 'canApproveEvents', 'isBranchPrincipal',
            'branchScope', 'pendingApprovalCount'
        ));
    }

    public function store(Request $r)
    {
        $user = Auth::user();

        // Teachers cannot add events
        if ($user->role === 'teacher') {
            if ($r->ajax() || $r->wantsJson()) {
                return response()->json(['error' => 'Teachers cannot add calendar events.'], 403);
            }
            return redirect()->route('admin.calendar.index')
                ->with('error', 'Teachers cannot add calendar events. Only branch principals and managers can add events.');
        }

        $r->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'category'       => 'required|in:holiday,exam,event,meeting,deadline,other',
            'color'          => 'nullable|string|max:7',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'start_time'     => 'nullable|date_format:H:i',
            'end_time'       => 'nullable|date_format:H:i',
            'is_all_day'     => 'nullable',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'branch_id'      => 'nullable|exists:branches,id',
        ]);

        $data = $r->only([
            'title', 'description', 'category', 'color', 'start_date', 'end_date',
            'start_time', 'end_time', 'academic_year_id', 'branch_id',
        ]);
        $isAllDay = $r->input('is_all_day');
        $data['is_all_day'] = in_array($isAllDay, [true, 1, '1', 'on', 'true'], true) ? true : (!$r->filled('start_time'));
        $data['is_announcement'] = true;
        $data['created_by'] = Auth::id();

        if (empty($data['color'])) {
            $data['color'] = CalendarEvent::categoryColors()[$data['category']] ?? '#4361ee';
        }

        // Branch principal: events are automatically branch-scoped
        if ($user->isBranchPrincipal()) {
            $data['scope'] = 'branch';
            $data['branch_id'] = $user->branch_id; // Force to their branch
            // Branch principal events need GM approval for school-wide visibility
            $data['is_approved'] = false;
        } else if ($user->canApproveCalendarEvents()) {
            // Admin/GM: events are school-wide and auto-approved
            $data['scope'] = $r->input('scope', 'school');
            $data['is_approved'] = true;
            $data['approved_by'] = $user->id;
            $data['approved_at'] = now();
        } else {
            // Other staff: school-scoped but need approval
            $data['scope'] = $r->input('scope', 'school');
            $data['is_approved'] = false;
        }

        $event = CalendarEvent::create($data);

        // Auto-notify via Telegram (only for approved events)
        $telegramSent = false;
        if ($event->is_approved && !$r->has('skip_telegram')) {
            $telegramSent = $this->notifyTelegram($event);
        }

        // Send SMS notification (only for approved events)
        $smsSent = false;
        if ($event->is_approved && !$r->has('skip_sms')) {
            $smsSent = $this->notifySms($event);
        }

        // Notify users about approved events
        if ($event->is_approved) {
            $this->notifyUsers($event);
        } else {
            // Notify GM/Admin about pending approval
            $this->notifyApprovers($event);
        }

        $statusParts = [];
        if ($event->is_approved) {
            $statusParts[] = 'Event created and posted as announcement.';
            if ($telegramSent) $statusParts[] = 'Telegram notification sent.';
            if ($smsSent) $statusParts[] = 'SMS notification sent.';
        } else {
            $statusParts[] = 'Event created and submitted for approval.';
            if ($user->isBranchPrincipal()) {
                $statusParts[] = 'This event will only be visible to your branch.';
            }
        }

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['success' => true, 'message' => implode(' ', $statusParts), 'event' => $event]);
        }

        return redirect()->route('admin.calendar.index')->with('success', implode(' ', $statusParts));
    }

    public function update(Request $r, CalendarEvent $calendar_event)
    {
        $user = Auth::user();

        // Teachers cannot modify events
        if ($user->role === 'teacher') {
            if ($r->ajax() || $r->wantsJson()) {
                return response()->json(['error' => 'Teachers cannot modify calendar events.'], 403);
            }
            return redirect()->route('admin.calendar.index')
                ->with('error', 'Teachers cannot modify calendar events.');
        }

        $r->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'category'       => 'required|in:holiday,exam,event,meeting,deadline,other',
            'color'          => 'nullable|string|max:7',
            'start_date'     => 'required|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'start_time'     => 'nullable|date_format:H:i',
            'end_time'       => 'nullable|date_format:H:i',
            'is_all_day'     => 'nullable',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'branch_id'      => 'nullable|exists:branches,id',
        ]);

        $data = $r->only([
            'title', 'description', 'category', 'color', 'start_date', 'end_date',
            'start_time', 'end_time', 'academic_year_id', 'branch_id',
        ]);
        $isAllDay = $r->input('is_all_day');
        $data['is_all_day'] = in_array($isAllDay, [true, 1, '1', 'on', 'true'], true) ? true : (!$r->filled('start_time'));
        $data['is_announcement'] = true;

        // Branch principal: force branch-scoped
        if ($user->isBranchPrincipal()) {
            $data['scope'] = 'branch';
            $data['branch_id'] = $user->branch_id;
        }

        $calendar_event->update($data);

        // Re-notify if approved
        $telegramSent = false;
        $smsSent = false;
        if ($calendar_event->is_approved) {
            if (!$r->has('skip_telegram')) {
                $telegramSent = $this->notifyTelegram($calendar_event);
            }
            if (!$r->has('skip_sms')) {
                $smsSent = $this->notifySms($calendar_event);
            }
            $this->notifyUsers($calendar_event);
        }

        $statusParts = ['Event updated.'];
        if ($telegramSent) $statusParts[] = 'Telegram notification sent.';
        if ($smsSent) $statusParts[] = 'SMS notification sent.';

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['success' => true, 'message' => implode(' ', $statusParts), 'event' => $calendar_event]);
        }

        return redirect()->route('admin.calendar.index')->with('success', implode(' ', $statusParts));
    }

    /**
     * Approve a calendar event (GM/Admin only).
     */
    public function approve(Request $r, CalendarEvent $calendar_event)
    {
        $user = Auth::user();

        if (!$user->canApproveCalendarEvents()) {
            if ($r->ajax() || $r->wantsJson()) {
                return response()->json(['error' => 'Only General Manager or Admin can approve events.'], 403);
            }
            return back()->with('error', 'Only General Manager or Admin can approve events.');
        }

        $calendar_event->update([
            'is_approved' => true,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Now send notifications
        $this->notifyTelegram($calendar_event);
        $this->notifySms($calendar_event);
        $this->notifyUsers($calendar_event);

        // Send approval-specific alert to relevant users
        try {
            \App\Services\AlertService::notifyCalendarEventApproved($calendar_event);
        } catch (\Exception $e) {
            \Log::warning('Event approval notification failed: ' . $e->getMessage());
        }

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Event approved and notifications sent.']);
        }

        return back()->with('success', 'Event approved and notifications sent.');
    }

    /**
     * Reject a calendar event (GM/Admin only).
     */
    public function reject(Request $r, CalendarEvent $calendar_event)
    {
        $user = Auth::user();

        if (!$user->canApproveCalendarEvents()) {
            if ($r->ajax() || $r->wantsJson()) {
                return response()->json(['error' => 'Only General Manager or Admin can reject events.'], 403);
            }
            return back()->with('error', 'Only General Manager or Admin can reject events.');
        }

        $calendar_event->delete();

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Event rejected and removed.']);
        }

        return back()->with('success', 'Event rejected and removed.');
    }

    public function destroy(CalendarEvent $calendar_event)
    {
        $user = Auth::user();

        // Only creator, admin, or GM can delete
        if ($calendar_event->created_by !== $user->id && !$user->canApproveCalendarEvents()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'You can only delete your own events.'], 403);
            }
            return back()->with('error', 'You can only delete your own events.');
        }

        $calendar_event->delete();
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Event deleted successfully.']);
        }
        return back()->with('success', 'Event deleted successfully.');
    }

    /**
     * API endpoint for the announcement ticker bar.
     * Returns approved, active/upcoming announcements.
     */
    public function apiAnnouncements(Request $r)
    {
        $query = CalendarEvent::where('is_announcement', true)
            ->where('is_approved', true)
            ->where(function ($q) {
                $q->where('start_date', '>=', now()->toDateString())
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('end_date')
                         ->where('end_date', '>=', now()->toDateString());
                  });
            });

        // Branch-scoped filtering
        if ($r->filled('branch_id')) {
            $query->where(function ($q) use ($r) {
                $q->where('branch_id', $r->branch_id)
                  ->orWhereNull('branch_id')
                  ->orWhere('scope', 'school');
            });
        }

        $announcements = $query->orderBy('start_date')->limit(20)->get()->map(function ($e) {
            return [
                'id'          => $e->id,
                'title'       => $e->title,
                'description' => $e->description,
                'category'    => $e->category,
                'color'       => $e->color,
                'start_date'  => $e->start_date->format('M d, Y'),
                'end_date'    => $e->end_date?->format('M d, Y'),
                'is_all_day'  => $e->is_all_day,
                'scope'       => $e->scope,
                'branch_name' => $e->branch?->name,
            ];
        });

        return response()->json($announcements);
    }

    public function apiEvents(Request $r)
    {
        $user = Auth::user();
        $query = CalendarEvent::with(['academicYear', 'branch']);

        if ($r->filled('start')) {
            $query->where(function ($q) use ($r) {
                $q->where('start_date', '>=', $r->start)
                  ->orWhere(function ($q2) use ($r) {
                      $q2->where('start_date', '<', $r->start)
                         ->whereNotNull('end_date')
                         ->where('end_date', '>=', $r->start);
                  });
            });
        }
        if ($r->filled('end')) {
            $query->where('start_date', '<=', $r->end);
        }
        if ($r->filled('category')) {
            $query->where('category', $r->category);
        }
        if ($r->filled('academic_year_id')) {
            $query->where('academic_year_id', $r->academic_year_id);
        }

        // Branch scope filtering
        $branchScope = request()->attributes->get('branch_scope');
        if ($branchScope) {
            $query->where(function ($q) use ($branchScope) {
                $q->where('scope', 'school')
                  ->orWhere(function ($q2) use ($branchScope) {
                      $q2->where('scope', 'branch')->where('branch_id', $branchScope);
                  });
            });
        }

        // Teachers and students only see approved events
        if (in_array($user->role, ['teacher', 'student', 'parent'])) {
            $query->where('is_approved', true);
        }

        // Filter by approval status for GM/Admin
        if ($r->filled('approval_status')) {
            if ($r->approval_status === 'pending') {
                $query->where('is_approved', false);
            } elseif ($r->approval_status === 'approved') {
                $query->where('is_approved', true);
            }
        }

        // Filter by scope
        if ($r->filled('scope')) {
            $query->where('scope', $r->scope);
        }

        $events = $query->get()->map(function ($e) {
            $start = $e->start_date->format('Y-m-d');
            if ($e->start_time && !$e->is_all_day) {
                $start .= 'T' . $e->start_time;
            }
            $end = null;
            if ($e->end_date) {
                $endDate = $e->is_all_day ? $e->end_date->copy()->addDay() : $e->end_date;
                $end = $endDate->format('Y-m-d');
                if ($e->end_time && !$e->is_all_day) {
                    $end .= 'T' . $e->end_time;
                }
            }
            return [
                'id'          => $e->id,
                'title'       => $e->title . (!$e->is_approved ? ' (Pending)' : ''),
                'start'       => $start,
                'end'         => $end,
                'allDay'      => $e->is_all_day,
                'backgroundColor' => $e->is_approved ? $e->color : '#9ca3af',
                'borderColor' => $e->is_approved ? $e->color : '#9ca3af',
                'textColor'   => '#fff',
                'extendedProps' => [
                    'category'     => $e->category,
                    'description'  => $e->description,
                    'academic_year' => $e->academicYear?->name,
                    'branch'       => $e->branch?->name,
                    'scope'        => $e->scope,
                    'is_approved'  => $e->is_approved,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Notify approvers (GM/Admin) about pending events.
     */
    private function notifyApprovers(CalendarEvent $event): void
    {
        try {
            $approvers = \App\Models\User::where('is_active', true)
                ->whereIn('role', ['admin', 'super_admin', 'general_manager'])
                ->get();

            foreach ($approvers as $approver) {
                \App\Models\Notification::create([
                    'user_id' => $approver->id,
                    'title' => 'Calendar Event Pending Approval',
                    'message' => "New event '{$event->title}' created by " . $event->creator?->name . " needs approval.",
                    'type' => 'event_approval',
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Event approver notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send event notification to Telegram groups/channels.
     */
    private function notifyTelegram(CalendarEvent $event): bool
    {
        $categoryEmoji = [
            'holiday' => '\xF0\x9F\x8E\x89', 'exam' => '\xF0\x9F\x93\x9D',
            'event' => '\xF0\x9F\x93\x85', 'meeting' => '\xF0\x9F\x91\xA5',
            'deadline' => '\xE2\x8F\xB0', 'other' => '\xF0\x9F\x93\x8C',
        ];
        $emoji = $categoryEmoji[$event->category] ?? '\xF0\x9F\x93\x8C';

        $message = "$emoji *{$event->title}*\n";
        $message .= "\xF0\x9F\x93\x85 " . $event->start_date->format('M d, Y');
        if ($event->start_time && !$event->is_all_day) {
            $message .= " at " . $event->start_time;
        }
        if ($event->end_date && $event->end_date != $event->start_date) {
            $message .= " - " . $event->end_date->format('M d, Y');
        }
        $message .= "\n\xF0\x9F\x8F\x7D " . ucfirst($event->category);
        if ($event->scope === 'branch' && $event->branch) {
            $message .= "\n\xF0\x9F\x8F\xA2 " . $event->branch->name . " Branch Only";
        }
        if ($event->description) {
            $message .= "\n\n" . $event->description;
        }

        $targets = [];

        $global = TelegramSetting::getSettings();
        if ($global && $global->is_enabled && $global->bot_token && $global->chat_id) {
            $targets[] = ['bot_token' => $global->bot_token, 'chat_id' => $global->chat_id, 'name' => 'Global'];
        }

        if ($event->branch_id) {
            $bs = BranchTelegramSetting::getForBranch($event->branch_id);
            if ($bs && $bs->is_enabled && $bs->bot_token && $bs->chat_id) {
                $targets[] = ['bot_token' => $bs->bot_token, 'chat_id' => $bs->chat_id, 'name' => $event->branch->name ?? 'Branch'];
            }
        } else {
            $allBranch = BranchTelegramSetting::where('is_enabled', true)
                ->whereNotNull('bot_token')->whereNotNull('chat_id')->get();
            foreach ($allBranch as $bs) {
                $targets[] = ['bot_token' => $bs->bot_token, 'chat_id' => $bs->chat_id, 'name' => $bs->branch->name ?? 'Branch'];
            }
        }

        $sent = false;
        foreach ($targets as $target) {
            try {
                $response = \Illuminate\Support\Facades\Http::post("https://api.telegram.org/bot{$target['bot_token']}/sendMessage", [
                    'chat_id' => $target['chat_id'],
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
                $data = $response->json();
                $ok = ($data['ok'] ?? false) === true;
                TelegramMessage::create([
                    'chat_id' => $target['chat_id'],
                    'message' => $message,
                    'direction' => 'outgoing',
                    'status' => $ok ? 'sent' : 'failed',
                ]);
                if ($ok) $sent = true;
            } catch (\Exception $e) {
                \Log::warning('Telegram notification failed: ' . $e->getMessage());
            }
        }
        return $sent;
    }

    /**
     * Send in-app notification to relevant users about the event.
     * Uses AlertService for role-based notification routing.
     */
    private function notifyUsers(CalendarEvent $event): void
    {
        try {
            \App\Services\AlertService::notifyCalendarEvent($event);
        } catch (\Exception $e) {
            \Log::warning('Event user notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send SMS notification about the event.
     */
    private function notifySms(CalendarEvent $event): bool
    {
        try {
            $sms = new SmsService();
            $message = "Redemption School: {$event->title}";
            $message .= " on " . $event->start_date->format('M d, Y');
            if ($event->start_time && !$event->is_all_day) {
                $message .= " at " . $event->start_time;
            }
            if ($event->category) {
                $message .= " [" . ucfirst($event->category) . "]";
            }
            if (strlen($message) > 155) {
                $message = substr($message, 0, 152) . '...';
            }

            $allRecipients = [];
            $sms->sendToDefaults($message);

            $phoneQuery = \App\Models\ParentModel::whereNotNull('father_phone')
                ->where('father_phone', '!=', '');
            if ($event->branch_id) {
                $phoneQuery->whereHas('students', function ($q) use ($event) {
                    $q->where('branch_id', $event->branch_id);
                });
            }
            $parents = $phoneQuery->get();
            foreach ($parents as $parent) {
                if ($parent->father_phone) $allRecipients[] = $parent->father_phone;
            }

            $teacherQuery = \App\Models\Teacher::whereNotNull('phone')
                ->where('phone', '!=', '')
                ->where('status', 'active');
            if ($event->branch_id) {
                $teacherQuery->where('branch_id', $event->branch_id);
            }
            foreach ($teacherQuery->get() as $teacher) {
                if ($teacher->phone) $allRecipients[] = $teacher->phone;
            }

            $allRecipients = array_unique($allRecipients);
            if (empty($allRecipients)) return false;

            $anySent = false;
            foreach (array_chunk($allRecipients, 50) as $batch) {
                $result = $sms->send($batch, $message);
                if ($result['success']) $anySent = true;
            }
            return $anySent;
        } catch (\Exception $e) {
            \Log::warning('Event SMS notification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function apiEvent(CalendarEvent $calendar_event)
    {
        return response()->json($calendar_event->load(['academicYear', 'branch', 'creator', 'approver']));
    }
}
