<?php

namespace App\Http\Controllers\MarkSheet;

use App\Http\Controllers\Controller;
use App\Models\MarkEntry;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Branch;
use App\Models\Setting;
use Illuminate\Http\Request;

class MarkSheetFullController extends Controller
{
    /**
     * Resolve the logged-in user's Teacher record.
     * Tries user_id FK first, then falls back to email match.
     * Returns null for non-teacher users or if no Teacher record found.
     */
    private function getTeacherForUser()
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'teacher') return null;

        // Try user_id FK first
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            // Fall back to email match (legacy)
            $teacher = Teacher::where('email', $user->email)->first();
        }
        return $teacher;
    }

    /**
     * Show the filter form for the full mark sheet (Term1 + Term2 + Annual).
     */
    public function index(Request $request)
    {
        $branchScope = $request->attributes->get('branch_scope');
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        $isTeacher = false;
        $teacher = $this->getTeacherForUser();

        if ($teacher) {
            $isTeacher = true;

            // Lock AY to active one for teachers
            $activeAy = AcademicYear::where('is_current', true)->first();
            if ($activeAy) {
                $academicYears = collect([$activeAy]); // Only show active AY for teachers
            }

            // Show classes from homeroom assignments
            $homeroomClassIds = $teacher->classRooms()->pluck('id');
            // Also show classes from section homeroom duties
            $sectionHomeroomClassIds = Section::where('teacher_id', $teacher->id)->pluck('class_id')->unique();
            $classIds = $homeroomClassIds->merge($sectionHomeroomClassIds)->unique();

            $classes = ClassRoom::whereIn('id', $classIds)->orderBy('numeric_name')->orderBy('name')->get();
        } else {
            $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))->orderBy('numeric_name')->orderBy('name')->get();
        }

        return view('admin.mark-sheet.full', compact('academicYears', 'classes', 'isTeacher'));
    }

    /**
     * Generate the full mark sheet: students × subjects with Term1, Term2, Annual columns.
     * Subjects are displayed as 90°-rotated column headers.
     * Includes: average row, term-specific ranks, annual = avg(T1+T2).
     */
    public function generate(Request $r)
    {
        $branchScope = $r->attributes->get('branch_scope');
        // If GET request with no filters, redirect to index
        if ($r->isMethod('GET') && !$r->filled('academic_year_id')) {
            return redirect()->route('admin.mark-sheet-full.index');
        }

        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id'         => 'required|exists:classes,id',
            'section_id'       => 'nullable|exists:sections,id',
        ]);

        // ── Authorization check for teachers ──
        $teacher = $this->getTeacherForUser();
        $isTeacher = (bool) $teacher;
        if ($teacher) {
            $isHomeroom = $teacher->classRooms()->where('id', $r->class_id)->exists();
            // Also check if they are homeroom for a section in this class
            if (!$isHomeroom) {
                $isHomeroom = Section::where('class_id', $r->class_id)
                    ->where('teacher_id', $teacher->id)
                    ->exists();
            }
            if (!$isHomeroom) {
                // Redirect back with a friendly message instead of 403
                return redirect()->route('admin.mark-sheet-full.index')
                    ->with('error', 'Only home room teachers have this access. You are not assigned as a homeroom teacher for this class.');
            }
        }

        $academicYearId = $r->academic_year_id;
        $classId        = $r->class_id;
        $sectionId      = $r->filled('section_id') ? $r->section_id : null;

        // Get all terms for the academic year
        $terms = Term::where('academic_year_id', $academicYearId)
            ->orderBy('id', 'asc')
            ->get();

        $term1 = $terms->first();
        $term2 = $terms->count() >= 2 ? $terms[1] : null;

        if (!$term1) {
            $academicYears = AcademicYear::orderBy('id', 'desc')->get();
            $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))->orderBy('numeric_name')->orderBy('name')->get();
            return view('admin.mark-sheet.full', compact('academicYears', 'classes', 'isTeacher'))
                ->with('error', 'No terms found for the selected academic year. Please create terms first.');
        }

        // Ensure term1 and term2 are objects (not arrays)
        if (is_array($term1)) $term1 = (object)$term1;
        if ($term2 && is_array($term2)) $term2 = (object)$term2;

        $term1Id = $term1->id;
        $term2Id = $term2 ? $term2->id : null;

        // Query all students in the class/section
        $studentQuery = Student::where('class_id', $classId)
            ->where('status', 'active');
        if ($sectionId) {
            $studentQuery->where('section_id', $sectionId);
        }
        $students = $studentQuery
            ->selectRaw("*, CAST(roll_number AS UNSIGNED) as rn_sort")
            ->orderByRaw('rn_sort ASC')
            ->orderBy('full_name')
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
        $subjects = $allMarks->pluck('subject')->filter()->unique('id')->sortBy(function($s) { return [is_object($s) ? ($s->priority ?? 0) : 0, is_object($s) ? ($s->name ?? '') : '']; })->values();

        // If no marks yet, get subjects from teacher assignments
        if ($subjects->isEmpty()) {
            $subjects = \App\Models\Subject::whereHas('teacherAssignments', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            })->orderBy('priority')->orderBy('name')->get();
        }

        // Ensure all subjects are proper objects (not arrays)
        $subjects = $subjects->map(function($s) {
            return is_object($s) ? $s : (object)$s;
        });

        // Helper: safely get ID from subject (may be object or array)
        $subjId = function($s) { return is_object($s) ? $s->id : ($s['id'] ?? null); };
        $subjName = function($s) { return is_object($s) ? ($s->name ?? '') : ($s['name'] ?? ''); };

        // Build the data structure: [studentId][termId][subjectId] = mark data
        $markData = [];
        foreach ($allMarks as $entry) {
            $markData[$entry->student_id][$entry->term_id][$entry->subject_id] = [
                'grand_total' => $entry->grand_total,
                'grade'       => $entry->grade,
                'ca_total'    => $entry->ca_total,
                'exam_total'  => $entry->exam_total,
                'conduct'     => $entry->conduct,
            ];
        }

        // ── Calculate aggregated conduct per student per term ──
        // Averages the conduct scores from ALL subjects, then converts to a
        // PERCENTILE (not raw score) so it's comparable across classes with
        // different numbers of subjects. Conduct is out of 5, so:
        // percentile = (avg_conduct / 5) * 100
        // Also assigns a letter grade: A(>=80), B(>=70), C(>=60), D(>=50), F(<50)
        $conductAgg = []; // [studentId][termId] = ['value' => 80.0, 'grade' => 'A']
        foreach ($markData as $sId => $termData) {
            foreach ($termData as $tId => $subjData) {
                $conducts = [];
                foreach ($subjData as $sKey => $data) {
                    if (isset($data['conduct']) && $data['conduct'] !== null) {
                        $conducts[] = floatval($data['conduct']);
                    }
                }
                if (count($conducts) > 0) {
                    $avg = array_sum($conducts) / count($conducts); // out of 5
                    $percentile = round(($avg / 5) * 100, 1); // percentile 0-100
                    $conductGrade = 'F';
                    if ($percentile >= 80) $conductGrade = 'A';
                    elseif ($percentile >= 70) $conductGrade = 'B';
                    elseif ($percentile >= 60) $conductGrade = 'C';
                    elseif ($percentile >= 50) $conductGrade = 'D';
                    $conductAgg[$sId][$tId] = ['value' => $percentile, 'grade' => $conductGrade];
                }
            }
        }

        // ── Load first-term override marks for mid-year entrants ──
        // These are per-subject marks entered manually from the student's previous school.
        $overrideQuery = \App\Models\FirstTermOverride::where('academic_year_id', $academicYearId)
            ->where('class_id', $classId);
        // Only filter by section_id if it's set (otherwise load ALL sections for this class)
        if ($sectionId) {
            $overrideQuery->where('section_id', $sectionId);
        }
        $overrideMap = $overrideQuery->get()
            ->keyBy(function($o) { return (string)$o->student_id . '_' . (string)$o->subject_id; });

        // Build roster rows with Term1, Term2, and Annual calculations
        $roster = [];
        foreach ($students as $student) {
            // Safety: skip if student is not a valid object
            if (!is_object($student)) continue;

            $studentId = $student->id;

            // Check if student joined in term 2 (mid-year entrant)
            $isMidYearEntrant = (int)($student->joined_term ?? 1) === 2;

            $row = [
                'student'          => $student,
                'is_mid_year'      => $isMidYearEntrant,
                'is_special_needs' => (bool)($student->special_needs ?? false),
                'term1'        => [],
                'term2'        => [],
                'annual'       => [],
                'term1_total'  => 0,
                'term2_total'  => 0,
                'annual_total' => 0,
                'term1_count'  => 0,
                'term2_count'  => 0,
                'annual_count' => 0,
                'term1_avg'    => 0,
                'term2_avg'    => 0,
                'annual_avg'   => 0,
                'conduct_t1'   => isset($conductAgg[$studentId][$term1Id]) ? $conductAgg[$studentId][$term1Id]['grade'] : null,
                'conduct_t1_val' => isset($conductAgg[$studentId][$term1Id]) ? $conductAgg[$studentId][$term1Id]['value'] : null,
                'conduct_t2'   => $term2Id ? (isset($conductAgg[$studentId][$term2Id]) ? $conductAgg[$studentId][$term2Id]['grade'] : null) : null,
                'conduct_t2_val' => $term2Id ? (isset($conductAgg[$studentId][$term2Id]) ? $conductAgg[$studentId][$term2Id]['value'] : null) : null,
            ];

            // ── MID-YEAR ENTRANT: use per-subject override marks for Term 1 ──
            if ($isMidYearEntrant) {
                // Load per-subject first-term overrides from the first_term_overrides table
                foreach ($subjects as $subj) {
                    $overrideKey = (string)$studentId . '_' . (string)$subjId($subj);
                    $override = $overrideMap[$overrideKey] ?? null;
                    if ($override && $override->grand_total !== null) {
                        $row['term1'][$subjId($subj)] = [
                            'grand_total' => floatval($override->grand_total),
                            'grade'       => $override->grade ?? $this->calcGrade(floatval($override->grand_total)),
                            'ca_total'    => null,
                            'exam_total'  => null,
                            'is_override' => true,
                        ];
                        $row['term1_total'] += floatval($override->grand_total);
                        $row['term1_count']++;
                    } else {
                        $row['term1'][$subjId($subj)] = null;
                    }
                }
            }

            foreach ($subjects as $subj) {
                // Term 1 (skip for mid-year entrants — already set above)
                if (!$isMidYearEntrant) {
                    $t1 = $markData[$studentId][$term1Id][$subjId($subj)] ?? null;
                    $row['term1'][$subjId($subj)] = $t1;
                    if ($t1 && $t1['grand_total'] !== null) {
                        $row['term1_total'] += floatval($t1['grand_total']);
                        $row['term1_count']++;
                    }
                }

                // Term 2
                $t2 = null;
                if ($term2) {
                    $t2 = $markData[$studentId][$term2Id][$subjId($subj)] ?? null;
                }
                $row['term2'][$subjId($subj)] = $t2;
                if ($t2 && $t2['grand_total'] !== null) {
                    $row['term2_total'] += floatval($t2['grand_total']);
                    $row['term2_count']++;
                }

                // Annual calculation
                if ($isMidYearEntrant) {
                    // ── MID-YEAR ENTRANT: annual = Term 2 only ──
                    // Their annual total/rank is based ONLY on Term 2 marks.
                    // Term 1 override mark is display-only and does NOT affect
                    // the annual calculation or other students' annual ranks.
                    if ($t2 && $t2['grand_total'] !== null) {
                        $row['annual'][$subjId($subj)] = [
                            'grand_total' => $t2['grand_total'],
                            'grade'       => $t2['grade'],
                        ];
                        $row['annual_total'] += floatval($t2['grand_total']);
                        $row['annual_count']++;
                    } else {
                        $row['annual'][$subjId($subj)] = null;
                    }
                } else {
                    // ── REGULAR STUDENT: annual = average of Term1 and Term2 ──
                    if ($t1 && $t2 && $t1['grand_total'] !== null && $t2['grand_total'] !== null) {
                        $annualMark = round((floatval($t1['grand_total']) + floatval($t2['grand_total'])) / 2, 1);
                        $row['annual'][$subjId($subj)] = [
                            'grand_total' => $annualMark,
                            'grade'       => $this->calcGrade($annualMark),
                        ];
                        $row['annual_total'] += $annualMark;
                        $row['annual_count']++;
                    } elseif ($t1 && $t1['grand_total'] !== null && (!$t2 || $t2['grand_total'] === null)) {
                        $row['annual'][$subjId($subj)] = [
                            'grand_total' => $t1['grand_total'],
                            'grade'       => $t1['grade'],
                        ];
                        $row['annual_total'] += floatval($t1['grand_total']);
                        $row['annual_count']++;
                    } elseif ($t2 && $t2['grand_total'] !== null && (!$t1 || $t1['grand_total'] === null)) {
                        $row['annual'][$subjId($subj)] = [
                            'grand_total' => $t2['grand_total'],
                            'grade'       => $t2['grade'],
                        ];
                        $row['annual_total'] += floatval($t2['grand_total']);
                        $row['annual_count']++;
                    } else {
                        $row['annual'][$subjId($subj)] = null;
                    }
                }
            }

            // Calculate per-student averages
            $row['term1_avg'] = $row['term1_count'] > 0
                ? round($row['term1_total'] / $row['term1_count'], 1) : 0;
            $row['term2_avg'] = $row['term2_count'] > 0
                ? round($row['term2_total'] / $row['term2_count'], 1) : 0;
            $row['annual_avg'] = $row['annual_count'] > 0
                ? round($row['annual_total'] / $row['annual_count'], 1) : 0;

            $roster[] = $row;
        }

        // ── SPECIAL NEEDS: excluded from ALL rankings ──
        // Their marks are still displayed, but they get rank 'SN' (Special Needs)
        // and don't affect other students' ranks.

        // Calculate Term1 ranks
        // Excluded: mid-year entrants (manual override) + special needs students
        $this->assignRanks($roster, 'term1_total', 'term1_rank', function($row) {
            return !$row['is_mid_year'] && !$row['is_special_needs'];
        });
        // Apply manual rank overrides for mid-year entrants
        foreach ($roster as &$row) {
            if ($row['is_mid_year'] && $row['student']->first_term_rank_override !== null) {
                $row['term1_rank'] = $row['student']->first_term_rank_override;
            }
            // Special needs students get 'SN' instead of a number rank
            if ($row['is_special_needs']) {
                $row['term1_rank'] = 'SN';
            }
        }
        unset($row);

        // Calculate Term2 ranks (exclude special needs)
        $this->assignRanks($roster, 'term2_total', 'term2_rank', function($row) {
            return !$row['is_special_needs'];
        });
        foreach ($roster as &$row) {
            if ($row['is_special_needs']) $row['term2_rank'] = 'SN';
        }
        unset($row);

        // Calculate Annual ranks (exclude special needs)
        $this->assignRanks($roster, 'annual_total', 'annual_rank', function($row) {
            return !$row['is_special_needs'];
        });
        foreach ($roster as &$row) {
            if ($row['is_special_needs']) $row['annual_rank'] = 'SN';
        }
        unset($row);

        // Calculate class averages for each subject per term
        $averages = [
            'term1'  => [],
            'term2'  => [],
            'annual' => [],
            'term1_total_avg'  => 0,
            'term2_total_avg'  => 0,
            'annual_total_avg' => 0,
        ];

        $studentCount = count($roster);
        if ($studentCount > 0) {
            foreach ($subjects as $subj) {
                // Term1 subject average
                $t1Sum = 0; $t1Cnt = 0;
                foreach ($roster as $row) {
                    $t1 = $row['term1'][$subjId($subj)] ?? null;
                    if ($t1 && $t1['grand_total'] !== null) {
                        $t1Sum += floatval($t1['grand_total']);
                        $t1Cnt++;
                    }
                }
                $averages['term1'][$subjId($subj)] = $t1Cnt > 0 ? round($t1Sum / $t1Cnt, 1) : null;

                // Term2 subject average
                $t2Sum = 0; $t2Cnt = 0;
                foreach ($roster as $row) {
                    $t2 = $row['term2'][$subjId($subj)] ?? null;
                    if ($t2 && $t2['grand_total'] !== null) {
                        $t2Sum += floatval($t2['grand_total']);
                        $t2Cnt++;
                    }
                }
                $averages['term2'][$subjId($subj)] = $t2Cnt > 0 ? round($t2Sum / $t2Cnt, 1) : null;

                // Annual subject average
                $aSum = 0; $aCnt = 0;
                foreach ($roster as $row) {
                    $ann = $row['annual'][$subjId($subj)] ?? null;
                    if ($ann && $ann['grand_total'] !== null) {
                        $aSum += floatval($ann['grand_total']);
                        $aCnt++;
                    }
                }
                $averages['annual'][$subjId($subj)] = $aCnt > 0 ? round($aSum / $aCnt, 1) : null;
            }

            // Overall total averages
            $t1TotalSum = array_sum(array_column($roster, 'term1_total'));
            $t2TotalSum = array_sum(array_column($roster, 'term2_total'));
            $aTotalSum  = array_sum(array_column($roster, 'annual_total'));

            $averages['term1_total_avg']  = round($t1TotalSum / $studentCount, 1);
            $averages['term2_total_avg']  = round($t2TotalSum / $studentCount, 1);
            $averages['annual_total_avg'] = round($aTotalSum / $studentCount, 1);
        }

        // Lookup reference models
        $class        = ClassRoom::find($classId);
        $section      = $sectionId ? Section::find($sectionId) : null;
        $academicYear = AcademicYear::find($academicYearId);

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        // Re-apply teacher class filtering for the form dropdown
        if ($teacher) {
            // Lock AY to active one for teachers
            $activeAy = AcademicYear::where('is_current', true)->first();
            if ($activeAy) {
                $academicYears = collect([$activeAy]);
            }
            $classes = $teacher->classRooms()->orderBy('numeric_name')->orderBy('name')->get();
        } else {
            $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))->orderBy('numeric_name')->orderBy('name')->get();
        }

        // Get school name, logo, and branch for the report header
        $schoolName = Setting::getLocalizedName();
        $logoUrl = Setting::getLogoUrl();
        $branch = null;
        if ($class && $class->branch_id) {
            $branch = Branch::find($class->branch_id);
        }
        if (!$branch && $branchScope) {
            $branch = Branch::find($branchScope);
        }

        return view('admin.mark-sheet.full', compact(
            'roster',
            'subjects',
            'class',
            'section',
            'academicYear',
            'term1',
            'term2',
            'academicYears',
            'classes',
            'averages',
            'isTeacher',
            'schoolName',
            'logoUrl',
            'branch'
        ));
    }

    /**
     * JSON API — return sections for a given class_id.
     */
    public function getSections(Request $r)
    {
        $teacher = $this->getTeacherForUser();

        $query = Section::where('class_id', $r->class_id);

        if ($teacher) {
            // Only return sections where teacher is homeroom
            $query->where('teacher_id', $teacher->id);
        }

        $sections = $query->orderBy('name')->get(['id', 'name']);

        return response()->json($sections);
    }

    /**
     * Assign ranks to roster based on a given total field.
     * Handles ties (same total = same rank, next rank skips).
     */
    private function assignRanks(array &$roster, string $totalField, string $rankField, ?\Closure $filter = null): void
    {
        // Filter roster if a closure is provided (e.g. exclude mid-year entrants from Term 1 ranking)
        $eligible = collect($roster);
        if ($filter) {
            $eligible = $eligible->filter($filter);
        }

        $ranked = $eligible->sortByDesc($totalField)->values();
        $rank = 1;
        $rankMap = [];

        foreach ($ranked as $i => $r) {
            if ($i > 0 && $r[$totalField] < $ranked[$i - 1][$totalField]) {
                $rank = $i + 1;
            }
            $rankMap[$r['student']->id] = $rank;
        }

        foreach ($roster as &$row) {
            // Only assign rank if the student was eligible (in the rankMap)
            // Students not in the rankMap get '-' (will be overridden later for mid-year entrants)
            $row[$rankField] = $rankMap[$row['student']->id] ?? '-';
        }
        unset($row);
    }

    /**
     * Calculate grade from mark.
     */
    private function calcGrade(float $mark): string
    {
        if ($mark <= 0) return 'I';
        if ($mark >= 80) return 'A';
        if ($mark >= 60) return 'B';
        if ($mark >= 50) return 'C';
        if ($mark >= 40) return 'D';
        return 'F';
    }
}
