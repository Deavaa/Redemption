<?php

namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\AcademicYear;
use App\Models\MarkEntry;
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

        $student = Student::with(['classroom', 'section', 'branch'])->findOrFail($r->student_id);
        $academicYear = $r->academic_year_id
            ? AcademicYear::find($r->academic_year_id)
            : AcademicYear::where('is_current', true)->first();

        // Detect template type
        $numericName = (int) ($student->classroom?->numeric_name ?? 0);
        $stream = self::detectStream($student);

        // Allow manual override
        $templateType = $r->template_type ?: self::detectTemplateType($numericName, $stream);

        // Get marks
        $marksQuery = MarkEntry::with('subject')->where('student_id', $student->id);
        if ($academicYear) {
            $marksQuery->where('academic_year_id', $academicYear->id);
        }
        $marks = $marksQuery->orderBy('subject_id')->get();

        $totalMarks = $marks->sum('grand_total');
        $totalPossible = $marks->count() * 100;
        $average = $marks->count() > 0 ? round($totalMarks / $marks->count(), 1) : 0;
        $rank = $this->calculateRank($student, $academicYear);

        // School info
        $schoolName    = Setting::getLocalizedName();
        $schoolAddress = Setting::get('school_address', '');
        $schoolPhone   = Setting::get('school_phone', '');
        $schoolLogo    = Setting::getLogoUrl();

        // Branch info
        $branch        = $student->branch;
        $branchName    = $branch ? $branch->name : $schoolName;
        $branchAddress = $branch ? $branch->address : $schoolAddress;
        $branchPhone   = $branch ? $branch->phone : $schoolPhone;

        // Template info
        $templateTypes = self::getTemplateTypes();
        $templateLabel = $templateTypes[$templateType]['label'] ?? 'Certificate';

        // Conduct / handwriting / creativity from marks (averaged)
        $conduct     = $marks->whereNotNull('conduct')->count() > 0 ? round($marks->whereNotNull('conduct')->avg('conduct'), 0) : null;
        $handwriting = $marks->whereNotNull('handwriting')->count() > 0 ? round($marks->whereNotNull('handwriting')->avg('handwriting'), 0) : null;
        $creativity  = $marks->whereNotNull('creativity')->count() > 0 ? round($marks->whereNotNull('creativity')->avg('creativity'), 0) : null;

        return view('admin.certificate-print.print', compact(
            'student', 'academicYear', 'marks', 'totalMarks', 'totalPossible',
            'average', 'rank', 'schoolName', 'schoolAddress', 'schoolPhone',
            'schoolLogo', 'branchName', 'branchAddress', 'branchPhone',
            'templateType', 'templateLabel', 'numericName', 'stream',
            'conduct', 'handwriting', 'creativity'
        ));
    }

    private function calculateRank(Student $student, ?AcademicYear $academicYear): ?int
    {
        if (!$academicYear || !$student->class_id) {
            return null;
        }

        $classStudents = Student::where('class_id', $student->class_id)
            ->where('academic_year_id', $academicYear->id)
            ->active()
            ->pluck('id');

        if ($classStudents->isEmpty()) {
            return null;
        }

        $averages = [];
        foreach ($classStudents as $sid) {
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
}
