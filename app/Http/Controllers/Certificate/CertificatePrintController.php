<?php

namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\AcademicYear;
use App\Models\MarkEntry;
use App\Models\StudentComment;
use App\Models\Branch;
use App\Models\Setting;
use Illuminate\Http\Request;

class CertificatePrintController extends Controller
{
    /**
     * Certificate template types mapped by grade level.
     * The key is the template identifier used in localStorage and the view.
     */
    public static function getTemplateTypes(): array
    {
        return [
            'kg'           => ['label' => 'KG', 'grades' => [0], 'fields' => 'kg'],
            'g1-2'         => ['label' => 'Grade 1-2', 'grades' => [1, 2], 'fields' => 'g1_2'],
            'g3-6'         => ['label' => 'Grade 3-6', 'grades' => [3, 4, 5, 6], 'fields' => 'g3_6'],
            'g7-8'         => ['label' => 'Grade 7-8', 'grades' => [7, 8], 'fields' => 'g7_8'],
            'g9-10'        => ['label' => 'Grade 9-10', 'grades' => [9, 10], 'fields' => 'g9_10'],
            'g11-12-nat'   => ['label' => 'Grade 11-12 (Natural)', 'grades' => [11, 12], 'stream' => 'natural', 'fields' => 'g11_12_nat'],
            'g11-12-social'=> ['label' => 'Grade 11-12 (Social)', 'grades' => [11, 12], 'stream' => 'social', 'fields' => 'g11_12_social'],
        ];
    }

    /**
     * Detect the certificate template type from the student's class numeric_name
     * and optional stream.
     */
    public static function detectTemplateType(int $numericName, ?string $stream = null): string
    {
        $types = self::getTemplateTypes();

        // If stream is provided for grade 11-12, pick the right one
        if (in_array($numericName, [11, 12]) && $stream) {
            $streamLower = strtolower($stream);
            if ($streamLower === 'natural' || $streamLower === 'science' || $streamLower === 'nat') {
                return 'g11-12-nat';
            }
            if ($streamLower === 'social' || $streamLower === 'arts' || $streamLower === 'social science') {
                return 'g11-12-social';
            }
        }

        // Default: match by grade number
        foreach ($types as $key => $config) {
            if (in_array($numericName, $config['grades'])) {
                // For 11-12 without explicit stream, default to natural
                if ($key === 'g11-12-nat') return 'g11-12-nat';
                return $key;
            }
        }

        return 'g3-6'; // fallback
    }

    /**
     * Get the stream from section name or classroom name.
     * Ethiopian convention: sections named "A" = Natural, "B" = Social, etc.
     * Or the classroom/section name may contain "Natural", "Social", "Science", "Arts".
     */
    public static function detectStream(Student $student): ?string
    {
        $sectionName = $student->section?->name ?? '';
        $className = $student->classroom?->name ?? '';

        // Check section name for stream keywords
        foreach ([$sectionName, $className] as $text) {
            $textLower = strtolower($text);
            if (str_contains($textLower, 'natural') || str_contains($textLower, 'science') || str_contains($textLower, 'nat')) {
                return 'natural';
            }
            if (str_contains($textLower, 'social') || str_contains($textLower, 'arts') || str_contains($textLower, 'art')) {
                return 'social';
            }
        }

        // Check if the section name pattern suggests stream (e.g. "11A" = Natural, "11B" = Social)
        // This is configurable — the user can override in the UI
        return null;
    }

    public function index()
    {
        $classes = ClassRoom::orderBy('numeric_name')->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $templateTypes = self::getTemplateTypes();

        return view('admin.certificate-print.index', compact('classes', 'academicYears', 'branches', 'templateTypes'));
    }

    public function getStudents(Request $r)
    {
        $query = Student::with('classroom', 'section');

        if ($r->filled('class_id')) {
            $query->where('class_id', $r->class_id);
        }
        if ($r->filled('section_id')) {
            $query->where('section_id', $r->section_id);
        }
        if ($r->filled('academic_year_id')) {
            $query->where('academic_year_id', $r->academic_year_id);
        }

        return response()->json($query->active()->orderBy('full_name')->get());
    }

    public function print(Request $r)
    {
        $r->validate([
            'student_id'       => 'required|exists:students,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'template_type'    => 'nullable|string',
        ]);

        $student = Student::with(['classroom', 'section.teacher', 'branch'])->findOrFail($r->student_id);
        $academicYear = $r->academic_year_id
            ? AcademicYear::find($r->academic_year_id)
            : AcademicYear::where('is_current', true)->first();

        // Detect template type
        $numericName = (int) ($student->classroom?->numeric_name ?? 0);
        $stream = self::detectStream($student);

        // Allow manual override
        $templateType = $r->template_type ?: self::detectTemplateType($numericName, $stream);

        // School info (always use the general school name, not branch)
        $schoolName    = Setting::getLocalizedName();
        $schoolAddress = Setting::get('school_address', '');
        $schoolPhone   = Setting::get('school_phone', '');
        $schoolLogo    = Setting::getLogoUrl();

        // Template info
        $templateTypes = self::getTemplateTypes();
        $templateLabel = $templateTypes[$templateType]['label'] ?? 'Certificate';

        // Homeroom teacher info (from section's teacher_id)
        $homeroomTeacher = $student->section?->teacher;
        $homeroomTeacherName = $homeroomTeacher?->full_name ?? '';

        // Homeroom teacher comment — try StudentComment first, then fall back to auto-generated
        $homeroomComment = '';
        $homeroomCommentTerm1 = '';
        $homeroomCommentTerm2 = '';
        if ($academicYear) {
            $reportComment = StudentComment::where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('is_report_comment', true)
                ->whereIn('comment_type', ['general', 'academic', 'progress'])
                ->orderBy('created_at', 'desc')
                ->first();
            $homeroomComment = $reportComment?->comment ?? '';
        }
        if (empty($homeroomComment)) {
            $homeroomComment = $student->teacher_comments ?? '';
        }

        // Auto-generate homeroom comments based on average (if no manual comment)
        // These will be filled after term summaries are calculated (below)
        $autoCommentTerm1 = '';
        $autoCommentTerm2 = '';

        // ========== TERM-BASED MARKS ==========
        // Initialize defaults
        $termMarks = [];
        $termKeys  = [];
        $termNames = [];
        $subjectRows = [];
        $termSummaries = [];
        $annualSummary = [
            'conduct' => null,
            'total'   => null,
            'average' => 0,
            'rank'    => null,
        ];

        // Get all terms for the academic year, ordered by term_number
        $terms = collect();
        if ($academicYear) {
            $terms = \App\Models\Term::where('academic_year_id', $academicYear->id)
                ->orderBy('term_number')
                ->get();
        }

        // Get all marks for the student in this academic year
        $allMarks = MarkEntry::with(['subject', 'term'])->where('student_id', $student->id);
        if ($academicYear) {
            $allMarks->where('academic_year_id', $academicYear->id);
        }
        $allMarks = $allMarks->get();

        // Build term-keyed marks collections (term1, term2, etc.)

        // ── MID-YEAR ENTRANT: load first-term override marks ──
        $isMidYear = (int)($student->joined_term ?? 1) === 2;
        $overrideMarks = collect();
        if ($isMidYear) {
            $overrideMarks = \App\Models\FirstTermOverride::where('student_id', $student->id)
                ->where('academic_year_id', $academicYear?->id)
                ->get()
                ->map(function($o) {
                    return (object)[
                        'subject_id' => $o->subject_id,
                        'term_id' => null,  // will be set to term1's ID below
                        'grand_total' => $o->grand_total,
                        'grade' => $o->grade,
                        'conduct' => null,
                        'subject' => \App\Models\Subject::find($o->subject_id),
                        'is_override' => true,
                    ];
                });
        }

        foreach ($terms as $idx => $term) {
            $key = 'term' . ($idx + 1);
            $termKeys[] = $key;
            $termNames[$key] = $term->name ?: ('Term ' . ($idx + 1));
            $termMarks[$key] = $allMarks->filter(fn($m) => $m->term_id == $term->id);

            // ── For mid-year entrants: merge override marks into term1 ──
            if ($isMidYear && $idx === 0 && $overrideMarks->isNotEmpty()) {
                // Set term_id on override marks to match term1
                $overrideMarks->each(fn($om) => $om->term_id = $term->id);
                // Merge: override marks replace any existing term1 marks for the same subject
                $existingSubjectIds = $termMarks[$key]->pluck('subject_id')->toArray();
                $merged = $termMarks[$key]->toArray();
                foreach ($overrideMarks as $om) {
                    if (!in_array($om->subject_id, $existingSubjectIds)) {
                        $merged[] = $om;
                    }
                }
                $termMarks[$key] = collect($merged);
            }
        }

        // If no terms found but marks exist, group by term_id
        if ($terms->isEmpty() && $allMarks->isNotEmpty()) {
            $grouped = $allMarks->groupBy('term_id');
            $idx = 0;
            foreach ($grouped as $termId => $group) {
                $idx++;
                $key = 'term' . $idx;
                $termKeys[] = $key;
                $termNames[$key] = 'Term ' . $idx;
                $termMarks[$key] = $group;
            }
        }

        // Build subject rows: each subject has grand_total per term + annual average
        $subjectIds = $allMarks->pluck('subject_id')->unique()->sort()->values();

        foreach ($subjectIds as $subjectId) {
            $subjectName = $allMarks->first(fn($m) => $m->subject_id == $subjectId)?->subject?->name ?? 'Unknown';
            $row = ['subject' => $subjectName];

            $termTotals = [];
            foreach ($termKeys as $key) {
                $mark = $termMarks[$key]->first(fn($m) => $m->subject_id == $subjectId);
                $total = $mark?->grand_total;
                $row[$key] = $total;
                if ($total !== null) {
                    $termTotals[] = $total;
                }
            }

            // Annual average = average of available term totals
            $row['annualAvg'] = count($termTotals) > 0
                ? round(array_sum($termTotals) / count($termTotals), 1)
                : null;

            $subjectRows[] = $row;
        }

        // Compute summary per term: conduct, total, average, rank
        foreach ($termKeys as $key) {
            $tm = $termMarks[$key] ?? collect();
            $total = $tm->sum('grand_total');
            $count = $tm->count();
            $avg = $count > 0 ? round($total / $count, 1) : 0;
            $conductVal = $tm->whereNotNull('conduct')->count() > 0
                ? round($tm->whereNotNull('conduct')->avg('conduct'), 0)
                : null;

            $termSummaries[$key] = [
                'conduct' => $conductVal,
                'total'   => $count > 0 ? $total : null,
                'average' => $avg,
                'rank'    => $this->calculateRankForTerm($student, $academicYear, $tm->first()?->term_id),
            ];
        }

        // Annual summary (average of term summaries)
        $validAverages = [];
        $validTotals   = [];
        $validConducts = [];
        foreach ($termKeys as $key) {
            if ($termSummaries[$key]['average'] > 0) $validAverages[] = $termSummaries[$key]['average'];
            if ($termSummaries[$key]['total'] !== null) $validTotals[] = $termSummaries[$key]['total'];
            if ($termSummaries[$key]['conduct'] !== null) $validConducts[] = $termSummaries[$key]['conduct'];
        }
        $annualSummary = [
            'conduct' => count($validConducts) > 0 ? round(array_sum($validConducts) / count($validConducts), 0) : null,
            'total'   => count($validTotals) > 0 ? array_sum($validTotals) : null,
            'average' => count($validAverages) > 0 ? round(array_sum($validAverages) / count($validAverages), 1) : 0,
            'rank'    => $this->calculateRank($student, $academicYear),
        ];

        // Legacy single-term variables for backward compatibility
        $marks        = $allMarks;
        $totalMarks   = $annualSummary['total'] ?? 0;
        $totalPossible = $subjectIds->count() * 100;
        $average      = $annualSummary['average'];
        $rank         = $annualSummary['rank'];
        $conduct      = $annualSummary['conduct'];
        $handwriting  = $allMarks->whereNotNull('handwriting')->count() > 0
            ? round($allMarks->whereNotNull('handwriting')->avg('handwriting'), 0) : null;
        $creativity   = $allMarks->whereNotNull('creativity')->count() > 0
            ? round($allMarks->whereNotNull('creativity')->avg('creativity'), 0) : null;

        // ── Auto-generate homeroom teacher comments based on average ──
        // Generates one comment per term + one for annual
        $homeroomCommentTerm1 = $this->generateHomeroomComment($termSummaries['term1']['average'] ?? 0);
        $homeroomCommentTerm2 = $this->generateHomeroomComment($termSummaries['term2']['average'] ?? 0);
        $homeroomCommentAnnual = $this->generateHomeroomComment($annualSummary['average']);

        // Determine promotion status
        $promotionStatus = 'promoted';
        if ($annualSummary['average'] > 0 && $annualSummary['average'] < 50) {
            $promotionStatus = 'detained';
        }
        $nextClassSection = '';
        if ($promotionStatus === 'promoted') {
            // Try to find the next class
            $currentNumeric = (int)($student->classroom?->numeric_name ?? 0);
            if ($currentNumeric > 0) {
                $nextClass = \App\Models\ClassRoom::where('numeric_name', '>', $currentNumeric)
                    ->orderBy('numeric_name')->first();
                if ($nextClass) {
                    $nextSections = \App\Models\Section::where('class_id', $nextClass->id)->orderBy('name')->get();
                    $nextClassSection = $nextClass->name . ($nextSections->first() ? ' - ' . $nextSections->first()->name : '');
                }
            }
        } else {
            $nextClassSection = ($student->classroom?->name ?? '') . ' - ' . ($student->section?->name ?? '');
        }

        // Student age calculation
        $studentAge = '';
        if ($student->date_of_birth) {
            try {
                $studentAge = \Carbon\Carbon::parse($student->date_of_birth)->age . ' years';
            } catch (\Throwable $e) {}
        }

        return view('admin.certificate-print.print', compact(
            'student', 'academicYear', 'marks', 'totalMarks', 'totalPossible',
            'average', 'rank', 'schoolName', 'schoolAddress', 'schoolPhone',
            'schoolLogo', 'templateType', 'templateLabel', 'numericName', 'stream',
            'conduct', 'handwriting', 'creativity',
            'homeroomTeacherName', 'homeroomComment',
            'homeroomCommentTerm1', 'homeroomCommentTerm2', 'homeroomCommentAnnual',
            'promotionStatus', 'nextClassSection', 'studentAge',
            'termKeys', 'termNames', 'subjectRows',
            'termSummaries', 'annualSummary'
        ));
    }

    /**
     * Auto-generate a homeroom teacher comment based on the student's average.
     */
    private function generateHomeroomComment(float $average): string
    {
        if ($average <= 0) return '';
        if ($average >= 90) return 'Outstanding performance! Keep up the excellent work and continue to strive for excellence.';
        if ($average >= 80) return 'Excellent performance. Your dedication and hard work are commendable. Keep it up!';
        if ($average >= 70) return 'Very good performance. You are doing well. With more effort, you can achieve excellence.';
        if ($average >= 60) return 'Good performance. Continue to work hard and you will see even better results.';
        if ($average >= 50) return 'Satisfactory performance. Put more effort into your studies to improve your results.';
        if ($average >= 40) return 'Below average performance. You need to work harder and seek help when needed.';
        return 'Poor performance. Please seek additional support and put more effort into your studies.';
    }

    private function calculateRank(Student $student, ?AcademicYear $academicYear): ?int
    {
        if (!$academicYear || !$student->class_id) {
            return null;
        }

        // ── SPECIAL NEEDS: return 'SN' (not ranked) ──
        if ($student->special_needs ?? false) {
            return null; // Certificate will show 'SN'
        }

        // ── MID-YEAR ENTRANT: annual rank is based on Term 2 only ──
        // Their Term 1 override mark does NOT affect other students' annual ranks.
        // All students (including mid-year entrants) are ranked together based on
        // their available marks. Mid-year entrants naturally have only Term 2 marks
        // in the mark_entries table, so the avg() query below will only consider
        // their Term 2 marks — which is correct.
        $classStudents = Student::where('class_id', $student->class_id)
            ->where('academic_year_id', $academicYear->id)
            ->active()
            ->pluck('id');

        if ($classStudents->isEmpty()) {
            return null;
        }

        $averages = [];
        foreach ($classStudents as $sid) {
            // For mid-year entrants, the avg() only includes their Term 2 marks
            // (they have no Term 1 mark_entries rows). This is the correct behavior.
            $avg = MarkEntry::where('student_id', $sid)
                ->where('academic_year_id', $academicYear->id)
                ->avg('grand_total');
            if ($avg !== null) {
                $averages[$sid] = round($avg, 2);
            }
        }

        arsort($averages);

        $rank = 1;
        foreach ($averages as $sid => $avg) {
            if ($sid == $student->id) {
                return $rank;
            }
            $rank++;
        }

        return null;
    }

    private function calculateRankForTerm(Student $student, ?AcademicYear $academicYear, ?int $termId): ?int
    {
        if (!$academicYear || !$student->class_id || !$termId) {
            return null;
        }

        // ── SPECIAL NEEDS: not ranked ──
        if ($student->special_needs ?? false) {
            return null;
        }

        // ── MID-YEAR ENTRANT: Term 1 rank is the manual override ──
        // Mid-year entrants are excluded from Term 1 ranking; their rank comes
        // from first_term_rank_override. For Term 2, they participate normally.
        $isMidYear = (int)($student->joined_term ?? 1) === 2;

        // Check if this is Term 1 (the first term of the academic year)
        $firstTerm = \App\Models\Term::where('academic_year_id', $academicYear->id)
            ->orderBy('id', 'asc')->first();
        $isTerm1 = $firstTerm && $firstTerm->id == $termId;

        if ($isMidYear && $isTerm1) {
            // Return the manual override rank for mid-year entrants in Term 1
            // Try per-subject override first, fall back to student-level override
            $overrideRank = \App\Models\FirstTermOverride::where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->whereNotNull('rank_override')
                ->avg('rank_override');
            if ($overrideRank !== null) {
                return (int)round($overrideRank);
            }
            return $student->first_term_rank_override !== null
                ? (int)$student->first_term_rank_override : null;
        }

        $classStudents = Student::where('class_id', $student->class_id)
            ->where('academic_year_id', $academicYear->id)
            ->active()
            ->pluck('id');

        if ($classStudents->isEmpty()) {
            return null;
        }

        // For Term 1 ranking, exclude mid-year entrants (they have no real Term 1 marks)
        if ($isTerm1) {
            $classStudents = $classStudents->filter(function($sid) {
                $s = Student::find($sid);
                return $s && (int)($s->joined_term ?? 1) !== 2;
            });
        }

        $averages = [];
        foreach ($classStudents as $sid) {
            $avg = MarkEntry::where('student_id', $sid)
                ->where('academic_year_id', $academicYear->id)
                ->where('term_id', $termId)
                ->avg('grand_total');
            if ($avg !== null) {
                $averages[$sid] = round($avg, 2);
            }
        }

        arsort($averages);

        $rank = 1;
        foreach ($averages as $sid => $avg) {
            if ($sid == $student->id) {
                return $rank;
            }
            $rank++;
        }

        return null;
    }
}
