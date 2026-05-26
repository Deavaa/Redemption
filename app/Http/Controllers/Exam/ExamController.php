<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CalendarEvent;
use App\Models\Exam;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExamController extends Controller
{
    public function index()
    {
        $data = Exam::with(['academicYear', 'term'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.Exam.index', compact('data'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();

        return view('admin.Exam.create', compact('academicYears', 'allTerms'));
    }

    public function store(Request $r)
    {
        try {
            $r->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:exam,quiz,test,midterm,final,assignment,project',
                'total_marks' => 'required|numeric|min:0|max:99999',
                'academic_year_id' => 'required|exists:academic_years,id',
                'term_id' => 'required|exists:terms,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'nullable',
                'end_time' => 'nullable',
                'description' => 'nullable|string|max:1000',
            ]);

            $data = [
                'name' => $r->name,
                'type' => $r->type,
                'total_marks' => $r->total_marks,
                'academic_year_id' => $r->academic_year_id,
                'term_id' => $r->term_id,
                'start_date' => $r->start_date,
                'end_date' => $r->end_date,
                'start_time' => $r->filled('start_time') ? $r->start_time . ':00' : null,
                'end_time' => $r->filled('end_time') ? $r->end_time . ':00' : null,
                'description' => $r->description,
            ];

            Log::info('Exam store: attempting create', ['data' => $data]);

            $exam = Exam::create($data);

            Log::info('Exam store: created successfully', ['exam_id' => $exam->id]);

            // Auto-create calendar event for this exam
            $this->syncCalendarEvent($exam);

            return redirect()->route('admin.exams.index')
                ->with('success', 'Exam scheduled successfully and added to academic calendar.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Exam store: validation failed', ['errors' => $e->errors()]);
            throw $e; // Re-throw so Laravel handles the redirect with errors
        } catch (\Exception $e) {
            Log::error('Exam store: exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()
                ->with('error', 'Failed to save exam: ' . $e->getMessage());
        }
    }

    public function show(Exam $exam)
    {
        $exam->load(['academicYear', 'term']);

        return view('admin.Exam.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $exam->load(['academicYear', 'term']);
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();

        return view('admin.Exam.edit', compact('exam', 'academicYears', 'allTerms'));
    }

    public function update(Request $r, Exam $exam)
    {
        try {
            $r->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:exam,quiz,test,midterm,final,assignment,project',
                'total_marks' => 'required|numeric|min:0|max:99999',
                'academic_year_id' => 'required|exists:academic_years,id',
                'term_id' => 'required|exists:terms,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'nullable',
                'end_time' => 'nullable',
                'description' => 'nullable|string|max:1000',
            ]);

            $data = [
                'name' => $r->name,
                'type' => $r->type,
                'total_marks' => $r->total_marks,
                'academic_year_id' => $r->academic_year_id,
                'term_id' => $r->term_id,
                'start_date' => $r->start_date,
                'end_date' => $r->end_date,
                'start_time' => $r->filled('start_time') ? $r->start_time . ':00' : null,
                'end_time' => $r->filled('end_time') ? $r->end_time . ':00' : null,
                'description' => $r->description,
            ];

            $exam->update($data);

            // Sync calendar event with updated exam details
            $this->syncCalendarEvent($exam);

            return redirect()->route('admin.exams.index')
                ->with('success', 'Exam updated successfully and academic calendar synced.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Exam update: exception', [
                'message' => $e->getMessage(),
                'exam_id' => $exam->id,
            ]);
            return back()->withInput()
                ->with('error', 'Failed to update exam: ' . $e->getMessage());
        }
    }

    public function destroy(Exam $exam)
    {
        // Delete the linked calendar event if it exists
        CalendarEvent::where('exam_id', $exam->id)->delete();

        $exam->delete();

        return back()->with('success', 'Exam deleted successfully and removed from academic calendar.');
    }

    /**
     * Create or update the calendar event linked to an exam.
     * Exam calendar events are school-wide, auto-approved, and categorized as 'exam'.
     */
    private function syncCalendarEvent(Exam $exam): void
    {
        try {
            $typeLabel = ucfirst($exam->type);
            $title = "{$typeLabel}: {$exam->name}";

            // Build description with exam details
            $descriptionParts = [];
            if ($exam->term) {
                $descriptionParts[] = "Term: {$exam->term->name}";
            }
            if ($exam->total_marks) {
                $descriptionParts[] = "Total Marks: {$exam->total_marks}";
            }
            if ($exam->classRoom) {
                $descriptionParts[] = "Class: {$exam->classRoom->name}";
            }
            if ($exam->subject) {
                $descriptionParts[] = "Subject: {$exam->subject->name}";
            }
            if ($exam->description) {
                $descriptionParts[] = $exam->description;
            }
            $description = implode(' | ', $descriptionParts);

            $data = [
                'title'             => $title,
                'description'       => $description,
                'category'          => 'exam',
                'color'             => CalendarEvent::categoryColors()['exam'] ?? '#f59e0b',
                'start_date'        => $exam->start_date,
                'end_date'          => $exam->end_date,
                'start_time'        => $exam->start_time ? substr($exam->start_time, 0, 5) : null,
                'end_time'          => $exam->end_time ? substr($exam->end_time, 0, 5) : null,
                'is_all_day'        => empty($exam->start_time),
                'is_announcement'   => true,
                'is_approved'       => true,
                'approved_by'       => auth()->id(),
                'approved_at'       => now(),
                'academic_year_id'  => $exam->academic_year_id,
                'scope'             => 'school',
                'source_type'       => 'exam',
            ];

            // Find existing calendar event for this exam, or create new
            $calendarEvent = CalendarEvent::where('exam_id', $exam->id)->first();

            if ($calendarEvent) {
                $calendarEvent->update($data);
            } else {
                $data['exam_id'] = $exam->id;
                $data['created_by'] = auth()->id();
                CalendarEvent::create($data);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to sync calendar event for exam', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
