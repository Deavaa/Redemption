<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentOption;
use App\Models\AssessmentAnswer;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentQuestionController extends Controller
{
    // ── Teacher: List Questions ─────────────────────────────

    public function index(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        $query = AssessmentQuestion::with(['subject', 'classroom', 'options', 'answers']);

        // Non-admin: only see own questions
        if (!in_array($user->role, ['admin', 'super_admin', 'branch_principal', 'general_manager'])) {
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        }

        // Filters
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }
        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }
        if ($request->filled('topic')) {
            $query->where('topic', 'like', "%{$request->topic}%");
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('topic', 'like', "%{$search}%");
            });
        }

        $questions = $query->latest()->paginate(20);

        // Get subjects the teacher is assigned to
        $activeAy = AcademicYear::where('is_current', true)->first();
        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);
        $topics = AssessmentQuestion::when($teacher, fn($q) => $q->where('teacher_id', $teacher->id))
            ->whereNotNull('topic')->distinct()->orderBy('topic')->pluck('topic');

        return view('admin.assessment-questions.index', compact(
            'questions', 'subjects', 'classes', 'topics', 'teacher', 'activeAy'
        ));
    }

    // ── Teacher: Create Question ────────────────────────────

    public function create()
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();
        $activeAy = AcademicYear::where('is_current', true)->first();

        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);

        return view('admin.assessment-questions.create', compact('subjects', 'classes', 'teacher', 'activeAy'));
    }

    // ── Teacher: Store Question ─────────────────────────────

    public function store(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        if (!$teacher && !in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Teacher profile not found. Please contact the administrator.')->withInput();
        }

        $questionType = $request->input('question_type', 'multiple_choice');

        // Build validation rules based on question type
        $rules = [
            'title' => 'nullable|string|max:255',
            'question_text' => 'required|string|max:5000',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'topic' => 'nullable|string|max:255',
            'marks' => 'nullable|integer|min:1|max:100',
            'hint' => 'nullable|string|max:2000',
            'explanation' => 'nullable|string|max:10000',
            'worked_out_solution' => 'nullable|string|max:20000',
            'is_active' => 'nullable',
        ];

        // Only validate options for MCQ — use nullable so empty rows don't fail validation
        if ($questionType === 'multiple_choice') {
            $rules['options'] = 'required|array|min:1|max:6';
            $rules['options.*.option_text'] = 'nullable|string|max:1000';
            $rules['options.*.option_label'] = 'nullable|string|max:1';
            $rules['options.*.is_correct'] = 'nullable';
        }

        // Only validate correct_tf for true/false
        if ($questionType === 'true_false') {
            $rules['correct_tf'] = 'required|in:true,false';
        }

        $validated = $request->validate($rules);

        $activeAy = AcademicYear::where('is_current', true)->first();

        // If no active academic year, try to get any academic year
        if (!$activeAy) {
            $activeAy = AcademicYear::orderBy('id', 'desc')->first();
        }

        try {
            // Filter out empty MCQ options (only keep ones with non-empty text)
            $mcqOptions = [];
            if ($questionType === 'multiple_choice' && !empty($validated['options'])) {
                foreach ($validated['options'] as $idx => $opt) {
                    if (!empty(trim($opt['option_text'] ?? ''))) {
                        $mcqOptions[] = $opt;
                    }
                }
                if (count($mcqOptions) < 2) {
                    return back()->with('error', 'Multiple choice questions need at least 2 options with text. Only ' . count($mcqOptions) . ' option(s) have text.')->withInput();
                }
            }

            // Questions are class-level — apply to ALL branches and ALL sections
            $questionData = [
                'subject_id' => $validated['subject_id'],
                'class_id' => $validated['class_id'],
                'section_id' => null,
                'branch_id' => null,
                'title' => $validated['title'] ?? null,
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'hint' => $validated['hint'] ?? null,
                'explanation' => $validated['explanation'] ?? null,
                'worked_out_solution' => $validated['worked_out_solution'] ?? null,
                'difficulty' => $validated['difficulty'],
                'topic' => $validated['topic'] ?? null,
                'marks' => $validated['marks'] ?? 1,
                'is_active' => $request->has('is_active'),
            ];

            // Set teacher_id — only if the column allows null OR we have a teacher
            if ($teacher) {
                $questionData['teacher_id'] = $teacher->id;
            }

            // Set academic_year_id — only if we have one
            if ($activeAy) {
                $questionData['academic_year_id'] = $activeAy->id;
            }

            $question = AssessmentQuestion::create($questionData);

            // Create options based on question type
            if ($questionType === 'multiple_choice' && !empty($mcqOptions)) {
                foreach ($mcqOptions as $idx => $opt) {
                    AssessmentOption::create([
                        'assessment_question_id' => $question->id,
                        'option_text' => $opt['option_text'],
                        'option_label' => $opt['option_label'] ?? chr(65 + $idx),
                        'is_correct' => isset($opt['is_correct']) && $opt['is_correct'],
                        'sort_order' => $idx,
                    ]);
                }
            } elseif ($questionType === 'true_false') {
                AssessmentOption::create([
                    'assessment_question_id' => $question->id,
                    'option_text' => 'True',
                    'option_label' => 'A',
                    'is_correct' => $validated['correct_tf'] === 'true',
                    'sort_order' => 0,
                ]);
                AssessmentOption::create([
                    'assessment_question_id' => $question->id,
                    'option_text' => 'False',
                    'option_label' => 'B',
                    'is_correct' => $validated['correct_tf'] === 'false',
                    'sort_order' => 1,
                ]);
            }

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Assessment question save failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to save question. Database error: ' . $e->getMessage())->withInput();
        } catch (\Exception $e) {
            \Log::error('Assessment question save failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to save question: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.assessment-questions.index')
            ->with('success', 'Question created successfully.');
    }

    // ── Show single question ────────────────────────────────

    public function show(AssessmentQuestion $assessment_question)
    {
        $assessment_question->load(['subject', 'classroom', 'options', 'teacher', 'answers.student']);

        $answerStats = $assessment_question->getStudentAnswerStats();

        // Option distribution
        $optionDistribution = [];
        if ($assessment_question->question_type !== 'short_answer') {
            foreach ($assessment_question->options as $option) {
                $optionDistribution[$option->option_label] = [
                    'text' => $option->option_text,
                    'count' => AssessmentAnswer::where('assessment_question_id', $assessment_question->id)
                        ->where('assessment_option_id', $option->id)
                        ->count(),
                    'is_correct' => $option->is_correct,
                ];
            }
        }

        return view('admin.assessment-questions.show', [
            'question' => $assessment_question,
            'answerStats' => $answerStats,
            'optionDistribution' => $optionDistribution,
        ]);
    }

    // ── Edit Question ───────────────────────────────────────

    public function edit(AssessmentQuestion $assessment_question)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        // Only creator or admin can edit
        if (!in_array($user->role, ['admin', 'super_admin']) && $assessment_question->teacher_id !== $teacher?->id) {
            abort(403, 'You can only edit your own questions.');
        }

        $assessment_question->load('options');
        $activeAy = AcademicYear::where('is_current', true)->first();
        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);

        return view('admin.assessment-questions.edit', compact(
            'assessment_question', 'subjects', 'classes', 'teacher', 'activeAy'
        ));
    }

    // ── Update Question ─────────────────────────────────────

    public function update(Request $request, AssessmentQuestion $assessment_question)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        if (!in_array($user->role, ['admin', 'super_admin']) && $assessment_question->teacher_id !== $teacher?->id) {
            abort(403, 'You can only edit your own questions.');
        }

        $questionType = $request->input('question_type', $assessment_question->question_type);

        // Build validation rules based on question type
        $rules = [
            'title' => 'nullable|string|max:255',
            'question_text' => 'required|string|max:5000',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'topic' => 'nullable|string|max:255',
            'marks' => 'nullable|integer|min:1|max:100',
            'hint' => 'nullable|string|max:2000',
            'explanation' => 'nullable|string|max:10000',
            'worked_out_solution' => 'nullable|string|max:20000',
            'is_active' => 'nullable',
        ];

        if ($questionType === 'multiple_choice') {
            $rules['options'] = 'required|array|min:1|max:6';
            $rules['options.*.option_text'] = 'nullable|string|max:1000';
            $rules['options.*.option_label'] = 'nullable|string|max:1';
            $rules['options.*.is_correct'] = 'nullable';
        }

        if ($questionType === 'true_false') {
            $rules['correct_tf'] = 'required|in:true,false';
        }

        $validated = $request->validate($rules);

        try {
            // Filter out empty MCQ options
            $mcqOptions = [];
            if ($questionType === 'multiple_choice' && !empty($validated['options'])) {
                foreach ($validated['options'] as $idx => $opt) {
                    if (!empty(trim($opt['option_text'] ?? ''))) {
                        $mcqOptions[] = $opt;
                    }
                }
                if (count($mcqOptions) < 2) {
                    return back()->with('error', 'Multiple choice questions need at least 2 options with text. Only ' . count($mcqOptions) . ' option(s) have text.')->withInput();
                }
            }

            $updateData = [
                'title' => $validated['title'] ?? null,
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'subject_id' => $validated['subject_id'],
                'class_id' => $validated['class_id'],
                'section_id' => null,
                'branch_id' => null,
                'difficulty' => $validated['difficulty'],
                'topic' => $validated['topic'] ?? null,
                'marks' => $validated['marks'] ?? 1,
                'hint' => $validated['hint'] ?? null,
                'explanation' => $validated['explanation'] ?? null,
                'worked_out_solution' => $validated['worked_out_solution'] ?? null,
                'is_active' => $request->has('is_active'),
            ];

            $assessment_question->update($updateData);

            // Delete old options and recreate
            $assessment_question->options()->delete();

            if ($questionType === 'multiple_choice' && !empty($mcqOptions)) {
                foreach ($mcqOptions as $idx => $opt) {
                    AssessmentOption::create([
                        'assessment_question_id' => $assessment_question->id,
                        'option_text' => $opt['option_text'],
                        'option_label' => $opt['option_label'] ?? chr(65 + $idx),
                        'is_correct' => isset($opt['is_correct']) && $opt['is_correct'],
                        'sort_order' => $idx,
                    ]);
                }
            } elseif ($questionType === 'true_false') {
                AssessmentOption::create([
                    'assessment_question_id' => $assessment_question->id,
                    'option_text' => 'True',
                    'option_label' => 'A',
                    'is_correct' => $validated['correct_tf'] === 'true',
                    'sort_order' => 0,
                ]);
                AssessmentOption::create([
                    'assessment_question_id' => $assessment_question->id,
                    'option_text' => 'False',
                    'option_label' => 'B',
                    'is_correct' => $validated['correct_tf'] === 'false',
                    'sort_order' => 1,
                ]);
            }

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Assessment question update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update question. Database error: ' . $e->getMessage())->withInput();
        } catch (\Exception $e) {
            \Log::error('Assessment question update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update question: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.assessment-questions.index')
            ->with('success', 'Question updated successfully.');
    }

    // ── Delete Question ─────────────────────────────────────

    public function destroy(AssessmentQuestion $assessment_question)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        if (!in_array($user->role, ['admin', 'super_admin']) && $assessment_question->teacher_id !== $teacher?->id) {
            abort(403, 'You can only delete your own questions.');
        }

        $assessment_question->delete();

        return back()->with('success', 'Question deleted successfully.');
    }

    // ── Toggle Active ───────────────────────────────────────

    public function toggleActive(AssessmentQuestion $assessment_question)
    {
        $assessment_question->update(['is_active' => !$assessment_question->is_active]);
        return back()->with('success', 'Question status updated.');
    }

    // ── Bulk Create (Quick Add) ────────────────────────────

    public function bulkCreate()
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();
        $activeAy = AcademicYear::where('is_current', true)->first();
        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);

        return view('admin.assessment-questions.bulk-create', compact('subjects', 'classes', 'teacher', 'activeAy'));
    }

    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'topic' => 'nullable|string|max:255',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:5000',
            'questions.*.question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'questions.*.hint' => 'nullable|string|max:2000',
            'questions.*.explanation' => 'nullable|string|max:10000',
            'questions.*.worked_out_solution' => 'nullable|string|max:20000',
            'questions.*.marks' => 'nullable|integer|min:1|max:100',
        ]);

        $activeAy = AcademicYear::where('is_current', true)->first();
        if (!$activeAy) {
            $activeAy = AcademicYear::orderBy('id', 'desc')->first();
        }

        $count = 0;

        try {
            foreach ($validated['questions'] as $qData) {
                $questionData = [
                    'subject_id' => $validated['subject_id'],
                    'class_id' => $validated['class_id'],
                    'section_id' => null,
                    'branch_id' => null,
                    'question_text' => $qData['question_text'],
                    'question_type' => $qData['question_type'],
                    'hint' => $qData['hint'] ?? null,
                    'explanation' => $qData['explanation'] ?? null,
                    'worked_out_solution' => $qData['worked_out_solution'] ?? null,
                    'difficulty' => $validated['difficulty'],
                    'topic' => $validated['topic'] ?? null,
                    'marks' => $qData['marks'] ?? 1,
                    'is_active' => true,
                ];

                if ($teacher) {
                    $questionData['teacher_id'] = $teacher->id;
                }
                if ($activeAy) {
                    $questionData['academic_year_id'] = $activeAy->id;
                }

                AssessmentQuestion::create($questionData);
                $count++;
            }
        } catch (\Exception $e) {
            \Log::error('Bulk assessment questions save failed: ' . $e->getMessage());
            return back()->with('error', "Saved {$count} questions before error: " . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.assessment-questions.index')
            ->with('success', "{$count} questions created successfully.");
    }

    // ── Download Excel template for bulk import ──────────────

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="assessment_questions_template.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Header row (questions are class-level: apply to all branches & sections)
            fputcsv($file, [
                'question_text',
                'question_type',
                'subject_name',
                'class_name',
                'difficulty',
                'marks',
                'topic',
                'title',
                'hint',
                'option_A',
                'option_B',
                'option_C',
                'option_D',
                'correct_option',
                'explanation',
                'worked_out_solution',
            ]);

            // Example rows
            fputcsv($file, [
                'What is the capital of France?',
                'multiple_choice',
                'Geography',
                'Grade 7',
                'easy',
                '1',
                'Chapter 1 - Europe',
                'European Capitals',
                'Think about major European cities',
                'London',
                'Paris',
                'Berlin',
                'Madrid',
                'B',
                'Paris is the capital and most populous city of France, located in the north-central part of the country.',
                'Step 1: Identify the country (France)\nStep 2: Recall that the capital of France is Paris\nTherefore, the answer is Paris (Option B)',
            ]);

            fputcsv($file, [
                'Water boils at 100 degrees Celsius.',
                'true_false',
                'Physics',
                'Grade 8',
                'easy',
                '1',
                'Chapter 2 - Heat',
                '',
                '',
                '',
                '',
                '',
                '',
                'true',
                'At standard atmospheric pressure (1 atm), water boils at 100 degrees Celsius or 212 degrees Fahrenheit.',
                'At 1 atm pressure, the boiling point of water is exactly 100°C. This is a standard reference point on the Celsius scale.',
            ]);

            fputcsv($file, [
                'Explain photosynthesis in one sentence.',
                'short_answer',
                'Biology',
                'Grade 9',
                'medium',
                '2',
                'Chapter 4 - Plant Biology',
                '',
                'Focus on the conversion process',
                '',
                '',
                '',
                '',
                '',
                'Photosynthesis is the process by which green plants convert sunlight, water, and carbon dioxide into glucose and oxygen.',
                'Sunlight (energy) + CO2 + H2O → C6H12O6 (glucose) + O2 (oxygen)\nThis process occurs in chloroplasts, primarily in the leaves.',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Import questions from CSV upload ─────────────────────

    public function bulkImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        if (!$teacher && !in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Teacher profile not found.')->withInput();
        }

        $activeAy = AcademicYear::where('is_current', true)->first();
        $file = $request->file('import_file');
        $path = $file->getRealPath();

        $imported = 0;
        $skipped = 0;
        $errors = [];

        try {
            // Handle CSV files
            if ($file->getClientOriginalExtension() === 'csv' || $file->getClientOriginalExtension() === 'txt') {
                $handle = fopen($path, 'r');
                $header = fgetcsv($handle); // Skip header row

                $rowNum = 1;
                while (($row = fgetcsv($handle)) !== false) {
                    $rowNum++;
                    try {
                        $result = $this->importQuestionRow($row, $teacher, $activeAy, $user, $rowNum);
                        if ($result === true) {
                            $imported++;
                        } else {
                            $skipped++;
                            $errors[] = $result;
                        }
                    } catch (\Exception $e) {
                        $skipped++;
                        $errors[] = "Row {$rowNum}: " . $e->getMessage();
                    }
                }
                fclose($handle);
            } else {
                // Handle Excel files using PhpSpreadsheet
                if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                    return back()->with('error', 'Excel file support requires PhpSpreadsheet. Please upload a CSV file instead.')->withInput();
                }
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();

                // Skip header row
                for ($i = 1; $i < count($rows); $i++) {
                    $rowNum = $i + 1;
                    $row = $rows[$i];
                    try {
                        $result = $this->importQuestionRow($row, $teacher, $activeAy, $user, $rowNum);
                        if ($result === true) {
                            $imported++;
                        } else {
                            $skipped++;
                            $errors[] = $result;
                        }
                    } catch (\Exception $e) {
                        $skipped++;
                        $errors[] = "Row {$rowNum}: " . $e->getMessage();
                    }
                }
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to read file: ' . $e->getMessage())->withInput();
        }

        $message = "{$imported} questions imported successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} rows skipped.";
        }

        return redirect()->route('admin.assessment-questions.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /**
     * Import a single question row from CSV/Excel.
     * Returns true on success, or an error message string on failure.
     */
    private function importQuestionRow($row, $teacher, $activeAy, $user, $rowNum)
    {
        $questionText = trim($row[0] ?? '');
        $questionType = trim($row[1] ?? 'multiple_choice');
        $subjectName  = trim($row[2] ?? '');
        $className    = trim($row[3] ?? '');
        // Column 4 (section_name) is no longer used — questions are class-level
        $difficulty   = trim($row[4] ?? 'medium');
        if (empty($difficulty) || !in_array($difficulty, ['easy', 'medium', 'hard'])) {
            // If column 4 looks like a difficulty value, it was an old-format row
            // where section_name was column 4 and difficulty was column 5
            // Try reading from column 5 instead
            $possibleDifficulty = trim($row[5] ?? '');
            if (in_array($possibleDifficulty, ['easy', 'medium', 'hard'])) {
                $difficulty = $possibleDifficulty;
                $colOffset = 1; // old format has extra column
            } else {
                $difficulty = 'medium';
                $colOffset = 0;
            }
        } else {
            $colOffset = 0; // new format (no section_name column)
        }
        $marks        = intval($row[5 + $colOffset] ?? 1);
        $topic        = trim($row[6 + $colOffset] ?? '');
        $title        = trim($row[7 + $colOffset] ?? '');
        $hint         = trim($row[8 + $colOffset] ?? '');
        $optionA      = trim($row[9 + $colOffset] ?? '');
        $optionB      = trim($row[10 + $colOffset] ?? '');
        $optionC      = trim($row[11 + $colOffset] ?? '');
        $optionD      = trim($row[12 + $colOffset] ?? '');
        $correctOption = strtoupper(trim($row[13 + $colOffset] ?? ''));
        $explanation  = trim($row[14 + $colOffset] ?? '');
        $workedOutSolution = trim($row[15 + $colOffset] ?? '');

        // Validate required fields
        if (empty($questionText)) {
            return "Row {$rowNum}: Question text is empty.";
        }

        if (!in_array($questionType, ['multiple_choice', 'true_false', 'short_answer'])) {
            return "Row {$rowNum}: Invalid question type '{$questionType}'. Use: multiple_choice, true_false, or short_answer.";
        }

        if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
            $difficulty = 'medium';
        }

        // Resolve subject
        $subject = null;
        if (!empty($subjectName)) {
            $subject = Subject::where('name', $subjectName)->first();
            if (!$subject) {
                return "Row {$rowNum}: Subject '{$subjectName}' not found.";
            }
        } else {
            // Try first subject from teacher's assignments
            $subjects = $this->getTeacherSubjects($teacher, $activeAy);
            $subject = $subjects->first();
            if (!$subject) {
                return "Row {$rowNum}: No subject specified and no teacher assignments found.";
            }
        }

        // Resolve class
        $class = null;
        if (!empty($className)) {
            $class = ClassRoom::where('name', $className)->first();
            if (!$class) {
                return "Row {$rowNum}: Class '{$className}' not found.";
            }
        } else {
            $classes = $this->getTeacherClasses($teacher, $activeAy);
            $class = $classes->first();
            if (!$class) {
                return "Row {$rowNum}: No class specified and no teacher assignments found.";
            }
        }

        // Questions are class-level — apply to ALL branches and ALL sections
        // Create the question
        $question = AssessmentQuestion::create([
            'teacher_id' => $teacher?->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'section_id' => null, // null = applies to all sections
            'academic_year_id' => $activeAy?->id,
            'branch_id' => null, // null = applies to all branches
            'title' => $title ?: null,
            'question_text' => $questionText,
            'question_type' => $questionType,
            'hint' => $hint ?: null,
            'explanation' => $explanation ?: null,
            'worked_out_solution' => $workedOutSolution ?: null,
            'difficulty' => $difficulty,
            'topic' => $topic ?: null,
            'marks' => max(1, min(100, $marks)),
            'is_active' => true,
        ]);

        // Create options based on question type
        if ($questionType === 'multiple_choice') {
            $options = [];
            if (!empty($optionA)) $options[] = ['text' => $optionA, 'label' => 'A'];
            if (!empty($optionB)) $options[] = ['text' => $optionB, 'label' => 'B'];
            if (!empty($optionC)) $options[] = ['text' => $optionC, 'label' => 'C'];
            if (!empty($optionD)) $options[] = ['text' => $optionD, 'label' => 'D'];

            if (count($options) < 2) {
                $question->delete();
                return "Row {$rowNum}: Multiple choice needs at least 2 options (A and B).";
            }

            foreach ($options as $idx => $opt) {
                AssessmentOption::create([
                    'assessment_question_id' => $question->id,
                    'option_text' => $opt['text'],
                    'option_label' => $opt['label'],
                    'is_correct' => $opt['label'] === $correctOption,
                    'sort_order' => $idx,
                ]);
            }
        } elseif ($questionType === 'true_false') {
            $isTrue = in_array(strtolower($correctOption), ['true', 'a', 'yes', '1']);
            AssessmentOption::create([
                'assessment_question_id' => $question->id,
                'option_text' => 'True',
                'option_label' => 'A',
                'is_correct' => $isTrue,
                'sort_order' => 0,
            ]);
            AssessmentOption::create([
                'assessment_question_id' => $question->id,
                'option_text' => 'False',
                'option_label' => 'B',
                'is_correct' => !$isTrue,
                'sort_order' => 1,
            ]);
        }

        return true;
    }

    // ── Report: Student performance ─────────────────────────

    public function report(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();
        $activeAy = AcademicYear::where('is_current', true)->first();

        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);

        $query = AssessmentAnswer::with(['student.classroom', 'student.section', 'question.subject']);

        // Filter by teacher's questions
        if ($teacher && !in_array($user->role, ['admin', 'super_admin'])) {
            $query->whereHas('question', fn($q) => $q->where('teacher_id', $teacher->id));
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('question', fn($q) => $q->where('subject_id', $request->subject_id));
        }
        if ($request->filled('class_id')) {
            $query->whereHas('question', fn($q) => $q->where('class_id', $request->class_id));
        }

        $answers = $query->latest()->paginate(30);

        // Summary stats
        $totalQuestions = AssessmentQuestion::when($teacher, fn($q) => $q->where('teacher_id', $teacher?->id))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->count();

        $totalAnswers = $query->count();
        $correctAnswers = (clone $query)->where('is_correct', true)->count();

        return view('admin.assessment-questions.report', compact(
            'answers', 'subjects', 'classes', 'totalQuestions', 'totalAnswers', 'correctAnswers'
        ));
    }

    // ── Helpers ─────────────────────────────────────────────

    private function getTeacherSubjects($teacher, $activeAy)
    {
        // Admin/super_admin always sees all subjects
        $user = Auth::user();
        if ($user && in_array($user->role, ['admin', 'super_admin', 'branch_principal', 'general_manager'])) {
            return Subject::orderBy('name')->get();
        }

        if (!$teacher) {
            return Subject::orderBy('name')->get();
        }

        // Try every possible way to find teacher assignments
        $subjectIds = collect();

        // 1. Try teacher->id (teachers.id)
        if ($activeAy) {
            $subjectIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeAy->id)
                ->pluck('subject_id')->unique();
        }

        // 2. Try teacher->id without academic year filter
        if ($subjectIds->isEmpty()) {
            $subjectIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->pluck('subject_id')->unique();
        }

        // 3. Try user_id on teachers table
        if ($subjectIds->isEmpty() && $teacher->user_id) {
            $subjectIds = TeacherAssignment::where('teacher_id', $teacher->user_id)
                ->pluck('subject_id')->unique();
        }

        // 4. Try matching by email through users table
        if ($subjectIds->isEmpty() && $teacher->email) {
            $userByEmail = \App\Models\User::where('email', $teacher->email)->first();
            if ($userByEmail) {
                $subjectIds = TeacherAssignment::where('teacher_id', $userByEmail->id)
                    ->pluck('subject_id')->unique();
            }
        }

        // If still nothing found, show all subjects so the form is usable
        if ($subjectIds->isEmpty()) {
            return Subject::orderBy('name')->get();
        }

        return Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
    }

    private function getTeacherClasses($teacher, $activeAy)
    {
        // Admin/super_admin always sees all classes
        $user = Auth::user();
        if ($user && in_array($user->role, ['admin', 'super_admin', 'branch_principal', 'general_manager'])) {
            return ClassRoom::orderBy('name')->get();
        }

        if (!$teacher) {
            return ClassRoom::orderBy('name')->get();
        }

        // Try every possible way to find teacher assignments
        $classIds = collect();

        // 1. Try teacher->id (teachers.id)
        if ($activeAy) {
            $classIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeAy->id)
                ->pluck('class_id')->unique();
        }

        // 2. Try teacher->id without academic year filter
        if ($classIds->isEmpty()) {
            $classIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->pluck('class_id')->unique();
        }

        // 3. Try user_id on teachers table
        if ($classIds->isEmpty() && $teacher->user_id) {
            $classIds = TeacherAssignment::where('teacher_id', $teacher->user_id)
                ->pluck('class_id')->unique();
        }

        // 4. Try matching by email through users table
        if ($classIds->isEmpty() && $teacher->email) {
            $userByEmail = \App\Models\User::where('email', $teacher->email)->first();
            if ($userByEmail) {
                $classIds = TeacherAssignment::where('teacher_id', $userByEmail->id)
                    ->pluck('class_id')->unique();
            }
        }

        // If still nothing found, show all classes so the form is usable
        if ($classIds->isEmpty()) {
            return ClassRoom::orderBy('name')->get();
        }

        return ClassRoom::whereIn('id', $classIds)->orderBy('name')->get();
    }
}
