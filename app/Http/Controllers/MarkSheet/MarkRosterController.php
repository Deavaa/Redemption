<?php

namespace App\Http\Controllers\MarkSheet;

use App\Http\Controllers\Controller;
use App\Models\MarkEntry;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Student;
use Illuminate\Http\Request;

class MarkRosterController extends Controller
{
    /**
     * Show the filter form for generating a mark roster.
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms         = Term::orderBy('id', 'desc')->get();
        $classes       = ClassRoom::orderBy('name')->get();

        return view('admin.mark-roster.index', compact('academicYears', 'terms', 'classes'));
    }

    /**
     * Generate the mark roster — a table of ALL students with ALL subject marks side by side.
     */
    public function generate(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'required|exists:terms,id',
            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'nullable|exists:sections,id',
        ]);

        // ── Build the query ──────────────────────────────────────────
        $query = MarkEntry::with(['student', 'subject'])
            ->where('academic_year_id', $r->academic_year_id)
            ->where('term_id', $r->term_id)
            ->where('class_id', $r->class_id);

        if ($r->filled('section_id')) {
            $query->where('section_id', $r->section_id);
        }

        $marks = $query->orderBy('student_id')->orderBy('subject_id')->get();

        // ── Unique subjects (preserve the order they appear in) ──────
        $subjects = $marks->pluck('subject')->filter()->unique('id')->values();

        // ── Group marks by student and build the roster rows ─────────
        $grouped = $marks->groupBy('student_id');

        $roster = $grouped->map(function ($entries, $studentId) {
            $student = $entries->first()->student;

            $subjectMarks = [];
            $grandTotal   = 0;

            foreach ($entries as $entry) {
                if ($entry->subject) {
                    $subjectMarks[$entry->subject_id] = [
                        'marks_obtained' => $entry->marks_obtained,
                        'ca_total'       => $entry->ca_total,
                        'exam_total'     => $entry->exam_total,
                        'grand_total'    => $entry->grand_total,
                        'grade'          => $entry->grade,
                    ];
                    $grandTotal += floatval($entry->grand_total ?? 0);
                }
            }

            return [
                'student'      => $student,
                'subjectMarks' => $subjectMarks,
                'grandTotal'   => round($grandTotal, 2),
            ];
        })->values();

        // Sort roster by roll number (if available) then by name
        $roster = $roster->sort(function ($a, $b) {
            $rollA = $a['student']->roll_number ?? '';
            $rollB = $b['student']->roll_number ?? '';
            if ($rollA !== $rollB) {
                return strcmp($rollA, $rollB);
            }
            return strcmp(
                ($a['student']->first_name ?? '') . ($a['student']->last_name ?? ''),
                ($b['student']->first_name ?? '') . ($b['student']->last_name ?? '')
            );
        })->values();

        // ── Lookup reference models ──────────────────────────────────
        $class        = ClassRoom::find($r->class_id);
        $section      = $r->filled('section_id') ? Section::find($r->section_id) : null;
        $academicYear = AcademicYear::find($r->academic_year_id);
        $term         = Term::find($r->term_id);

        // Re-fetch filter dropdown data for the form
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms         = Term::orderBy('id', 'desc')->get();
        $classes       = ClassRoom::orderBy('name')->get();

        return view('admin.mark-roster.index', compact(
            'roster',
            'subjects',
            'class',
            'section',
            'academicYear',
            'term',
            'academicYears',
            'terms',
            'classes'
        ));
    }

    /**
     * JSON API — return sections for a given class_id.
     */
    public function getSections(Request $r)
    {
        $sections = Section::where('class_id', $r->class_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($sections);
    }
}
