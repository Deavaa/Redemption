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

class MarkSheetFullController extends Controller
{
    /**
     * Show the filter form for the full mark sheet (Term1 + Term2 + Annual).
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes       = ClassRoom::orderBy('name')->get();

        return view('admin.mark-sheet.full', compact('academicYears', 'classes'));
    }

    /**
     * Generate the full mark sheet: students × subjects with Term1, Term2, Annual columns.
     * Subjects are displayed as 90°-rotated column headers.
     */
    public function generate(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'nullable|exists:sections,id',
        ]);

        $academicYearId = $r->academic_year_id;
        $classId        = $r->class_id;
        $sectionId      = $r->filled('section_id') ? $r->section_id : null;

        // Get all terms for the academic year
        $terms = Term::where('academic_year_id', $academicYearId)
            ->orderBy('term_number')
            ->get();

        // If only 2 terms exist, Term1 and Term2; Annual is calculated
        $term1 = $terms->first();
        $term2 = $terms->count() >= 2 ? $terms->skip(1)->first() : null;

        // Query all students in the class/section
        $studentQuery = Student::where('class_id', $classId)
            ->where('status', 'active');
        if ($sectionId) {
            $studentQuery->where('section_id', $sectionId);
        }
        $students = $studentQuery
            ->selectRaw("*, CAST(roll_number AS UNSIGNED) as rn_sort")
            ->orderByRaw('rn_sort ASC')
            ->orderBy('first_name')
            ->get();

        // Query all mark entries for this academic year and class
        $marksQuery = MarkEntry::with(['subject', 'term'])
            ->where('academic_year_id', $academicYearId)
            ->where('class_id', $classId);
        if ($sectionId) {
            $marksQuery->where('section_id', $sectionId);
        }
        $allMarks = $marksQuery->get();

        // Get unique subjects from marks
        $subjects = $allMarks->pluck('subject')->filter()->unique('id')->sortBy('name')->values();

        // If no marks yet, get subjects from teacher assignments
        if ($subjects->isEmpty()) {
            $subjects = \App\Models\Subject::whereHas('teacherAssignments', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })->orderBy('name')->get();
        }

        // Build the data structure: [studentId][termId][subjectId] = mark data
        $markData = [];
        foreach ($allMarks as $entry) {
            $markData[$entry->student_id][$entry->term_id][$entry->subject_id] = [
                'grand_total' => $entry->grand_total,
                'grade'       => $entry->grade,
                'ca_total'    => $entry->ca_total,
                'exam_total'  => $entry->exam_total,
            ];
        }

        // Build roster rows with Term1, Term2, and Annual calculations
        $roster = [];
        foreach ($students as $student) {
            $row = [
                'student' => $student,
                'term1'   => [],
                'term2'   => [],
                'annual'  => [],
                'term1_total' => 0,
                'term2_total' => 0,
                'annual_total' => 0,
                'term1_count' => 0,
                'term2_count' => 0,
            ];

            foreach ($subjects as $subj) {
                // Term 1
                $t1 = $markData[$student->id][$term1->id][$subj->id] ?? null;
                $row['term1'][$subj->id] = $t1;
                if ($t1 && $t1['grand_total'] !== null) {
                    $row['term1_total'] += floatval($t1['grand_total']);
                    $row['term1_count']++;
                }

                // Term 2
                $t2 = null;
                if ($term2) {
                    $t2 = $markData[$student->id][$term2->id][$subj->id] ?? null;
                }
                $row['term2'][$subj->id] = $t2;
                if ($t2 && $t2['grand_total'] !== null) {
                    $row['term2_total'] += floatval($t2['grand_total']);
                    $row['term2_count']++;
                }

                // Annual = average of Term1 and Term2 (if both exist)
                if ($t1 && $t2 && $t1['grand_total'] !== null && $t2['grand_total'] !== null) {
                    $annualMark = round((floatval($t1['grand_total']) + floatval($t2['grand_total'])) / 2, 1);
                    $row['annual'][$subj->id] = [
                        'grand_total' => $annualMark,
                        'grade'       => $this->calcGrade($annualMark),
                    ];
                    $row['annual_total'] += $annualMark;
                } elseif ($t1 && $t1['grand_total'] !== null && !$t2) {
                    // Only Term1 exists, use it as annual
                    $row['annual'][$subj->id] = [
                        'grand_total' => $t1['grand_total'],
                        'grade'       => $t1['grade'],
                    ];
                    $row['annual_total'] += floatval($t1['grand_total']);
                } else {
                    $row['annual'][$subj->id] = null;
                }
            }

            $roster[] = $row;
        }

        // Calculate ranks based on annual total
        $ranked = collect($roster)->sortByDesc('annual_total')->values();
        $rank = 1;
        foreach ($ranked as $i => $r) {
            if ($i > 0 && $r['annual_total'] < $ranked[$i - 1]['annual_total']) {
                $rank = $i + 1;
            }
            // Find this student in roster and set rank
            foreach ($roster as &$row) {
                if ($row['student']->id === $r['student']->id) {
                    $row['rank'] = $rank;
                    break;
                }
            }
        }
        unset($row);

        // Lookup reference models
        $class        = ClassRoom::find($classId);
        $section      = $sectionId ? Section::find($sectionId) : null;
        $academicYear = AcademicYear::find($academicYearId);

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $classes       = ClassRoom::orderBy('name')->get();

        return view('admin.mark-sheet.full', compact(
            'roster',
            'subjects',
            'class',
            'section',
            'academicYear',
            'term1',
            'term2',
            'academicYears',
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

    /**
     * Calculate grade from mark.
     */
    private function calcGrade(float $mark): string
    {
        if ($mark >= 90) return 'A+';
        if ($mark >= 80) return 'A';
        if ($mark >= 75) return 'A-';
        if ($mark >= 70) return 'B+';
        if ($mark >= 65) return 'B';
        if ($mark >= 60) return 'B-';
        if ($mark >= 55) return 'C+';
        if ($mark >= 50) return 'C';
        if ($mark >= 45) return 'C-';
        if ($mark >= 40) return 'D';
        return 'F';
    }
}
