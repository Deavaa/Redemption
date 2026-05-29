<?php
namespace App\Http\Controllers\IdCard;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Section;
use App\Models\IdCard;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class IdCardGenerateController extends Controller
{
    public function index()
    {
        $classes = Classroom::orderBy('numeric_name')->orderBy('name')->get();
        $preselectedStudentId = request()->query('student_id');
        $preselectedStudent = null;

        if ($preselectedStudentId) {
            $preselectedStudent = Student::with(['classroom', 'section'])->find($preselectedStudentId);

            // If a student is selected from the student list, directly generate the ID card
            if ($preselectedStudent) {
                return $this->generateForStudents([$preselectedStudentId]);
            }
        }

        return view('admin.id-card-generate.index', compact('classes', 'preselectedStudent'));
    }

    public function getSections(Request $r)
    {
        return response()->json(
            Section::where('class_id', $r->class_id)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function getStudents(Request $r)
    {
        $query = Student::with('section', 'classroom');
        if ($r->filled('class_id')) $query->where('class_id', $r->class_id);
        if ($r->filled('section_id')) $query->where('section_id', $r->section_id);
        return response()->json(
            $query->orderBy('full_name')->get(['id', 'full_name', 'roll_number', 'class_id', 'section_id', 'photo'])
        );
    }

    /**
     * Get the short academic year code from an AcademicYear name.
     * e.g. "2024-2025" → "2425", "2025-2026" → "2526"
     */
    private function getAyShortCode(AcademicYear $ay): string
    {
        $parts = explode('-', $ay->name);
        $startYear = substr($parts[0], -2); // last 2 digits of start year
        $endYear = isset($parts[1]) ? substr($parts[1], -2) : $startYear;
        return $startYear . $endYear;
    }

    public function generate(Request $r)
    {
        $r->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        return $this->generateForStudents($r->student_ids);
    }

    /**
     * Shared generation logic used by both the form submit and the direct student link.
     */
    private function generateForStudents(array $studentIds)
    {
        // Fetch the current academic year from the system
        $currentAy = AcademicYear::where('is_current', true)->first();

        // If no academic year is marked current, fall back to the latest one
        if (!$currentAy) {
            $currentAy = AcademicYear::orderBy('id', 'desc')->first();
        }

        $students = Student::with(['classroom', 'section', 'branch', 'academicYear'])->whereIn('id', $studentIds)->get();

        // Determine the academic year short code for card numbers
        $ayShort = $currentAy ? $this->getAyShortCode($currentAy) : date('y') . (date('y') + 1);

        // Determine the valid_until date: use AY end_date, or fall back to 1 year from now
        $validUntil = ($currentAy && $currentAy->end_date)
            ? $currentAy->end_date->format('Y-m-d')
            : now()->addYear()->format('Y-m-d');

        // Auto-create ID card records for students who don't have one for this academic year
        foreach ($students as $student) {
            // Check if student already has an ID card for the current academic year
            $existingCard = $student->idCards()
                ->when($currentAy, fn($q) => $q->where('academic_year_id', $currentAy->id))
                ->first();

            if (!$existingCard) {
                // Generate card number with academic year prefix: ID-2425-00001
                $lastCard = IdCard::where('card_number', 'LIKE', "ID-{$ayShort}-%")
                    ->orderByRaw('LENGTH(card_number) DESC')
                    ->orderBy('card_number', 'desc')
                    ->first();

                $nextNum = 1;
                if ($lastCard && $lastCard->card_number) {
                    $parts = explode('-', $lastCard->card_number);
                    $nextNum = ((int) end($parts)) + 1;
                }

                $cardNumber = "ID-{$ayShort}-" . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

                IdCard::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $currentAy?->id,
                    'card_number' => $cardNumber,
                    'issue_date' => now()->format('Y-m-d'),
                    'valid_until' => $validUntil,
                    'status' => 'active',
                ]);
            }
        }

        // Refresh students to pick up newly created ID cards
        $students = Student::with(['classroom', 'section', 'branch', 'academicYear', 'idCards', 'user'])->whereIn('id', $studentIds)->get();

        return view('admin.id-card-generate.print', compact('students', 'currentAy'));
    }
}
