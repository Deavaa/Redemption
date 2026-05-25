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
use Illuminate\Support\Facades\Http;

class CalendarEventController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $branches = Branch::orderBy('name')->get();
        $categories = CalendarEvent::categoryList();

        return view('admin.CalendarEvent.index', compact('academicYears', 'branches', 'categories'));
    }

    public function store(Request $r)
    {
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
        $data['is_announcement'] = $r->has('is_announcement') ? true : false; // Only announce if checkbox checked
        $data['created_by'] = Auth::id();

        if (empty($data['color'])) {
            $data['color'] = CalendarEvent::categoryColors()[$data['category']] ?? '#4361ee';
        }

        // Every event is automatically posted as an announcement
        $data['is_announcement'] = true;

        $event = CalendarEvent::create($data);

        // Auto-notify via Telegram for every event (unless explicitly skipped)
        $telegramSent = false;
        if (!$r->has('skip_telegram')) {
            $telegramSent = $this->notifyTelegram($event);
        }

        // Send SMS notification (unless explicitly skipped)
        $smsSent = false;
        if (!$r->has('skip_sms')) {
            $smsSent = $this->notifySms($event);
        }

        // Also send as notification/message to relevant users
        $this->notifyUsers($event);

        $statusParts = ['Event created and posted as announcement.'];
        if ($telegramSent) $statusParts[] = 'Telegram notification sent.';
        if ($smsSent) $statusParts[] = 'SMS notification sent.';

        // Return JSON for AJAX requests, redirect for normal
        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['success' => true, 'message' => implode(' ', $statusParts), 'event' => $event]);
        }

        return redirect()->route('admin.calendar.index')->with('success', implode(' ', $statusParts));
    }

    public function update(Request $r, CalendarEvent $calendar_event)
    {
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
        // Every event is automatically posted as an announcement
        $data['is_announcement'] = true;

        $calendar_event->update($data);

        // Auto-notify via Telegram for every event (unless explicitly skipped)
        $telegramSent = false;
        if (!$r->has('skip_telegram')) {
            $telegramSent = $this->notifyTelegram($calendar_event);
        }

        // Send SMS notification (unless explicitly skipped)
        $smsSent = false;
        if (!$r->has('skip_sms')) {
            $smsSent = $this->notifySms($calendar_event);
        }

        // Also send as notification/message to relevant users
        $this->notifyUsers($calendar_event);

        $statusParts = ['Event updated and posted as announcement.'];
        if ($telegramSent) $statusParts[] = 'Telegram notification sent.';
        if ($smsSent) $statusParts[] = 'SMS notification sent.';

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['success' => true, 'message' => implode(' ', $statusParts), 'event' => $calendar_event]);
        }

        return redirect()->route('admin.calendar.index')->with('success', implode(' ', $statusParts));
    }

    public function destroy(CalendarEvent $calendar_event)
    {
        $calendar_event->delete();
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Event deleted successfully.']);
        }
        return back()->with('success', 'Event deleted successfully.');
    }

    /**
     * API endpoint for the announcement ticker bar.
     * Returns active/upcoming announcements.
     */
    public function apiAnnouncements(Request $r)
    {
        $query = CalendarEvent::where('is_announcement', true)
            ->where(function ($q) {
                $q->where('start_date', '>=', now()->toDateString())
                  ->orWhere(function ($q2) {
                      $q2->whereNotNull('end_date')
                         ->where('end_date', '>=', now()->toDateString());
                  });
            });

        if ($r->filled('branch_id')) {
            $query->where(function ($q) use ($r) {
                $q->where('branch_id', $r->branch_id)
                  ->orWhereNull('branch_id');
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
            ];
        });

        return response()->json($announcements);
    }

    public function apiEvents(Request $r)
    {
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

        $events = $query->get()->map(function ($e) {
            $start = $e->start_date->format('Y-m-d');
            if ($e->start_time && !$e->is_all_day) {
                $start .= 'T' . $e->start_time;
            }
            $end = null;
            if ($e->end_date) {
                // FullCalendar end date is exclusive, so add 1 day for all-day events
                // Use copy() to avoid mutating the original Carbon instance
                $endDate = $e->is_all_day ? $e->end_date->copy()->addDay() : $e->end_date;
                $end = $endDate->format('Y-m-d');
                if ($e->end_time && !$e->is_all_day) {
                    $end .= 'T' . $e->end_time;
                }
            }
            return [
                'id'          => $e->id,
                'title'       => $e->title,
                'start'       => $start,
                'end'         => $end,
                'allDay'      => $e->is_all_day,
                'backgroundColor' => $e->color,
                'borderColor' => $e->color,
                'textColor'   => '#fff',
                'extendedProps' => [
                    'category'     => $e->category,
                    'description'  => $e->description,
                    'academic_year' => $e->academicYear?->name,
                    'branch'       => $e->branch?->name,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Send event notification to Telegram groups/channels.
     * Returns true if at least one message was sent successfully.
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
        if ($event->description) {
            $message .= "\n\n" . $event->description;
        }

        $targets = [];

        // Global bot
        $global = TelegramSetting::getSettings();
        if ($global && $global->is_enabled && $global->bot_token && $global->chat_id) {
            $targets[] = ['bot_token' => $global->bot_token, 'chat_id' => $global->chat_id, 'name' => 'Global'];
        }

        // Branch-specific bot
        if ($event->branch_id) {
            $bs = BranchTelegramSetting::getForBranch($event->branch_id);
            if ($bs && $bs->is_enabled && $bs->bot_token && $bs->chat_id) {
                $targets[] = ['bot_token' => $bs->bot_token, 'chat_id' => $bs->chat_id, 'name' => $event->branch->name ?? 'Branch'];
            }
        } else {
            // Send to all enabled branches
            $allBranch = BranchTelegramSetting::where('is_enabled', true)
                ->whereNotNull('bot_token')->whereNotNull('chat_id')->get();
            foreach ($allBranch as $bs) {
                $targets[] = ['bot_token' => $bs->bot_token, 'chat_id' => $bs->chat_id, 'name' => $bs->branch->name ?? 'Branch'];
            }
        }

        $sent = false;
        foreach ($targets as $target) {
            try {
                $response = Http::post("https://api.telegram.org/bot{$target['bot_token']}/sendMessage", [
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
     */
    private function notifyUsers(CalendarEvent $event): void
    {
        try {
            // Get users who should be notified based on branch
            $query = \App\Models\User::where('is_active', true);
            if ($event->branch_id) {
                $query->where(function ($q) use ($event) {
                    $q->where('branch_id', $event->branch_id)
                      ->orWhereIn('role', ['admin', 'super_admin', 'general_manager']);
                });
            }

            $users = $query->get();
            $notificationMessage = "Event: {$event->title} on " . $event->start_date->format('M d, Y');
            if ($event->start_time && !$event->is_all_day) {
                $notificationMessage .= " at " . $event->start_time;
            }

            foreach ($users as $user) {
                \App\Models\Notification::create([
                    'user_id' => $user->id,
                    'title' => 'New Event: ' . $event->title,
                    'message' => $notificationMessage,
                    'type' => 'event',
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Event user notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send SMS notification about the event to default recipients and parent phone numbers.
     * Returns true if at least one SMS was sent successfully.
     */
    private function notifySms(CalendarEvent $event): bool
    {
        try {
            $sms = new SmsService();

            // Build SMS message (keep it short - 160 chars for standard SMS)
            $message = "Redemption School: {$event->title}";
            $message .= " on " . $event->start_date->format('M d, Y');
            if ($event->start_time && !$event->is_all_day) {
                $message .= " at " . $event->start_time;
            }
            if ($event->category) {
                $message .= " [" . ucfirst($event->category) . "]";
            }

            // Truncate to 160 chars for standard SMS
            if (strlen($message) > 155) {
                $message = substr($message, 0, 152) . '...';
            }

            $allRecipients = [];

            // 1. Send to default configured recipients (admin phones)
            if ($sms->sendToDefaults($message)['success']) {
                // Successfully sent to defaults
            }

            // 2. Collect parent/guardian phone numbers for the relevant branch
            $phoneQuery = \App\Models\ParentModel::whereNotNull('father_phone')
                ->where('father_phone', '!=', '');

            if ($event->branch_id) {
                // Only parents with students in this branch
                $phoneQuery->whereHas('students', function ($q) use ($event) {
                    $q->where('branch_id', $event->branch_id);
                });
            }

            $parents = $phoneQuery->get();
            foreach ($parents as $parent) {
                if ($parent->father_phone) {
                    $allRecipients[] = $parent->father_phone;
                }
            }

            // 3. Also collect teacher phone numbers
            $teacherQuery = \App\Models\Teacher::whereNotNull('phone')
                ->where('phone', '!=', '')
                ->where('status', 'active');

            if ($event->branch_id) {
                $teacherQuery->where('branch_id', $event->branch_id);
            }

            $teachers = $teacherQuery->get();
            foreach ($teachers as $teacher) {
                if ($teacher->phone) {
                    $allRecipients[] = $teacher->phone;
                }
            }

            // Deduplicate
            $allRecipients = array_unique($allRecipients);

            if (empty($allRecipients)) {
                return false;
            }

            // Send in batches of 50 to avoid API limits
            $batches = array_chunk($allRecipients, 50);
            $anySent = false;

            foreach ($batches as $batch) {
                $result = $sms->send($batch, $message);
                if ($result['success']) {
                    $anySent = true;
                }
            }

            return $anySent;
        } catch (\Exception $e) {
            \Log::warning('Event SMS notification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function apiEvent(CalendarEvent $calendar_event)
    {
        return response()->json($calendar_event->load(['academicYear', 'branch']));
    }
}
