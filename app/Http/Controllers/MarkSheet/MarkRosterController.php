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
     * Generate the mark roster — a SEPARATE TABLE per subject showing
     * every CA and Exam field as a column, grouped under
     * "Continuous Assessment" and "Exam" header rows.
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

        $marks = $query->orderBy('student_id')->orderByRaw('(SELECT priority FROM subjects WHERE subjects.id = mark_entries.subject_id) ASC')->orderBy('subject_id')->get();

        // ── Unique subjects (preserve the order they appear in) ──────
        $subjects = $marks->pluck('subject')->filter()->unique('id')->sortBy(function($s) { return [$s->priority ?? 0, $s->name]; })->values();

        // If no marks yet, get subjects from teacher assignments
        if ($subjects->isEmpty()) {
            $subjects = \App\Models\Subject::whereHas('teacherAssignments', function ($q) use ($r) {
                $q->where('class_id', $r->class_id);
            })->orderBy('priority')->orderBy('name')->get();
        }

        // ── Get all students in the class/section ────────────────────
        $studentQuery = Student::where('class_id', $r->class_id)
            ->where('status', 'active');
        if ($r->filled('section_id')) {
            $studentQuery->where('section_id', $r->section_id);
        }
        $students = $studentQuery
            ->selectRaw("*, CAST(roll_number AS UNSIGNED) as rn_sort")
            ->orderByRaw('rn_sort ASC')
            ->orderBy('first_name')
            ->get();

        // ── Build detailed mark data per subject ─────────────────────
        // Structure: [subjectId][studentId] = full mark entry row
        $markData = [];
        foreach ($marks as $entry) {
            $markData[$entry->subject_id][$entry->student_id] = $entry;
        }

        // ── Build subject rosters with all CA/Exam detail ────────────
        $subjectRosters = [];
        foreach ($subjects as $subj) {
            $rows = [];
            foreach ($students as $i => $student) {
                $entry = $markData[$subj->id][$student->id] ?? null;
                $rows[] = [
                    'serial'   => $i + 1,
                    'student'  => $student,
                    'ca1'        => $entry->ca1 ?? null,
                    'ca2'        => $entry->ca2 ?? null,
                    'ca3'        => $entry->ca3 ?? null,
                    'ca4'        => $entry->ca4 ?? null,
                    'ca5'        => $entry->ca5 ?? null,
                    'ca6'        => $entry->ca6 ?? null,
                    'ca7'        => $entry->ca7 ?? null,
                    'ca8'        => $entry->ca8 ?? null,
                    'ca9'        => $entry->ca9 ?? null,
                    'ca10'       => $entry->ca10 ?? null,
                    'conduct'    => $entry->conduct ?? null,
                    'handwriting'=> $entry->handwriting ?? null,
                    'creativity' => $entry->creativity ?? null,
                    'ca_total'   => $entry->ca_total ?? null,
                    'test1'      => $entry->test1 ?? null,
                    'test2'      => $entry->test2 ?? null,
                    'mid_term'   => $entry->mid_term ?? null,
                    'final_exam' => $entry->final_exam ?? null,
                    'exam_total' => $entry->exam_total ?? null,
                    'grand_total'=> $entry->grand_total ?? null,
                    'grade'      => $entry->grade ?? null,
                ];
            }

            // Compute column averages
            $colSums = [];
            $colCounts = [];
            $avgFields = ['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10',
                          'conduct','handwriting','creativity','ca_total',
                          'test1','test2','mid_term','final_exam','exam_total','grand_total'];
            foreach ($avgFields as $f) { $colSums[$f] = 0; $colCounts[$f] = 0; }
            foreach ($rows as $row) {
                foreach ($avgFields as $f) {
                    if ($row[$f] !== null) {
                        $colSums[$f] += floatval($row[$f]);
                        $colCounts[$f]++;
                    }
                }
            }
            $averages = [];
            $calcFields = ['ca_total', 'exam_total', 'grand_total'];
            foreach ($avgFields as $f) {
                if ($colCounts[$f] > 0) {
                    // Calculated fields get 2 decimal places, raw fields get 1
                    $decimals = in_array($f, $calcFields) ? 2 : 1;
                    $averages[$f] = round($colSums[$f] / $colCounts[$f], $decimals);
                } else {
                    $averages[$f] = null;
                }
            }

            $subjectRosters[] = [
                'subject'  => $subj,
                'rows'     => $rows,
                'averages' => $averages,
            ];
        }

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
            'subjectRosters',
            'subjects',
            'students',
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
