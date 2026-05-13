<?php

namespace App\Http\Controllers\CalendarEvent;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\AcademicYear;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'is_all_day'     => 'nullable|boolean',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'branch_id'      => 'nullable|exists:branches,id',
        ]);

        $data = $r->only([
            'title', 'description', 'category', 'color', 'start_date', 'end_date',
            'start_time', 'end_time', 'academic_year_id', 'branch_id',
        ]);
        $data['is_all_day'] = $r->has('is_all_day') ? true : (!$r->filled('start_time'));
        $data['created_by'] = Auth::id();

        if (empty($data['color'])) {
            $data['color'] = CalendarEvent::categoryColors()[$data['category']] ?? '#4361ee';
        }

        CalendarEvent::create($data);

        return redirect()->route('admin.calendar.index')->with('success', 'Event created successfully.');
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
            'is_all_day'     => 'nullable|boolean',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'branch_id'      => 'nullable|exists:branches,id',
        ]);

        $data = $r->only([
            'title', 'description', 'category', 'color', 'start_date', 'end_date',
            'start_time', 'end_time', 'academic_year_id', 'branch_id',
        ]);
        $data['is_all_day'] = $r->has('is_all_day') ? true : (!$r->filled('start_time'));

        if (empty($data['color'])) {
            $data['color'] = CalendarEvent::categoryColors()[$data['category']] ?? '#4361ee';
        }

        $calendar_event->update($data);

        return redirect()->route('admin.calendar.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(CalendarEvent $calendar_event)
    {
        $calendar_event->delete();
        return back()->with('success', 'Event deleted successfully.');
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
                $endDate = $e->is_all_day ? $e->end_date->addDay() : $e->end_date;
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

    public function apiEvent(CalendarEvent $calendar_event)
    {
        return response()->json($calendar_event->load(['academicYear', 'branch']));
    }
}
