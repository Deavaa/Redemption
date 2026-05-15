<?php

namespace App\Http\Controllers\CalendarEvent;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\TelegramSetting;
use App\Models\BranchTelegramSetting;
use App\Models\TelegramMessage;
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
        $data['is_announcement'] = $r->has('is_announcement') ? true : true; // Auto-announce all events
        $data['created_by'] = Auth::id();

        if (empty($data['color'])) {
            $data['color'] = CalendarEvent::categoryColors()[$data['category']] ?? '#4361ee';
        }

        $event = CalendarEvent::create($data);

        // Auto-notify via Telegram if enabled
        if ($r->has('notify_telegram')) {
            $this->notifyTelegram($event);
        }

        // Return JSON for AJAX requests, redirect for normal
        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Event created successfully.', 'event' => $event]);
        }

        return redirect()->route('admin.calendar.index')->with('success', 'Event created successfully.' . ($r->has('notify_telegram') ? ' Telegram notification sent.' : ''));
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
        $data['is_announcement'] = $r->has('is_announcement') ? true : true; // Auto-announce

        if (empty($data['color'])) {
            $data['color'] = CalendarEvent::categoryColors()[$data['category']] ?? '#4361ee';
        }

        $calendar_event->update($data);

        // Auto-notify via Telegram if enabled
        if ($r->has('notify_telegram')) {
            $this->notifyTelegram($calendar_event);
        }

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Event updated successfully.', 'event' => $calendar_event]);
        }

        return redirect()->route('admin.calendar.index')->with('success', 'Event updated successfully.' . ($r->has('notify_telegram') ? ' Telegram notification sent.' : ''));
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
     */
    private function notifyTelegram(CalendarEvent $event)
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

        foreach ($targets as $target) {
            try {
                $response = Http::post("https://api.telegram.org/bot{$target['bot_token']}/sendMessage", [
                    'chat_id' => $target['chat_id'],
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                ]);
                $data = $response->json();
                TelegramMessage::create([
                    'chat_id' => $target['chat_id'],
                    'message' => $message,
                    'direction' => 'outgoing',
                    'status' => ($data['ok'] ?? false) ? 'sent' : 'failed',
                ]);
            } catch (\Exception $e) {
                \Log::warning('Telegram notification failed: ' . $e->getMessage());
            }
        }
    }

    public function apiEvent(CalendarEvent $calendar_event)
    {
        return response()->json($calendar_event->load(['academicYear', 'branch']));
    }
}
