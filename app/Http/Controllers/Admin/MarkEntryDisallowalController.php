<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\ClassRoom;
use App\Models\MarkEntryDisallowal;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\Term;
use Illuminate\Http\Request;

class MarkEntryDisallowalController extends Controller
{
    /**
     * Display mark entry disallowal management page.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Branch principals can only see their branch
        if ($user->role === 'branch_principal') {
            $branches = Branch::where('id', $user->branch_id)->get();
        } else {
            $branches = Branch::orderBy('name')->get();
        }

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $currentAy = AcademicYear::where('is_current', true)->first();
        $selectedAy = $request->filled('academic_year_id') ? AcademicYear::find($request->academic_year_id) : $currentAy;

        $terms = $selectedAy ? Term::where('academic_year_id', $selectedAy->id)->orderBy('id')->get() : collect();
        $currentTerm = $terms->firstWhere('is_active', true) ?? $terms->first();

        $selectedBranch = $request->filled('branch_id') ? Branch::find($request->branch_id) : $branches->first();
        $selectedTerm = $request->filled('term_id') ? Term::find($request->term_id) : $currentTerm;

        // Get classes for the selected branch
        $classes = ClassRoom::when($selectedBranch, fn($q) => $q->where('branch_id', $selectedBranch->id))
            ->orderBy('numeric_name', 'asc')->orderBy('name', 'asc')->get();

        // Get existing disallowals
        $disallowals = MarkEntryDisallowal::with(['teacher', 'classRoom', 'section', 'subject', 'academicYear', 'term', 'disallowedBy'])
            ->where('is_active', true)
            ->when($selectedAy, fn($q) => $q->where(function ($subQ) use ($selectedAy) {
                $subQ->whereNull('academic_year_id')->orWhere('academic_year_id', $selectedAy->id);
            }))
            ->when($selectedTerm, fn($q) => $q->where(function ($subQ) use ($selectedTerm) {
                $subQ->whereNull('term_id')->orWhere('term_id', $selectedTerm->id);
            }))
            ->when($selectedBranch, function ($q) use ($selectedBranch) {
                $q->whereHas('classRoom', fn($cq) => $cq->where('branch_id', $selectedBranch->id));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get teachers for dropdown (filtered by branch for branch principals)
        $teachers = Teacher::with('user')
            ->when($selectedBranch, function ($q) use ($selectedBranch) {
                $q->whereHas('assignments', function ($aq) use ($selectedBranch) {
                    $aq->whereHas('classRoom', fn($cq) => $cq->where('branch_id', $selectedBranch->id));
                });
            })
            ->orderBy('full_name')
            ->get();

        $userBranch = $user->role === 'branch_principal' ? Branch::find($user->branch_id) : null;

        return view('admin.mark-entry-disallowals.index', compact(
            'branches', 'academicYears', 'terms', 'classes', 'teachers',
            'disallowals', 'selectedBranch', 'selectedAy', 'selectedTerm', 'userBranch'
        ));
    }

    /**
     * Store a new disallowal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'reason' => 'nullable|string|max:500',
            'scope' => 'required|in:all,subject,section',
        ]);

        $user = auth()->user();

        // Authorization check
        if ($user->role === 'branch_principal') {
            // Branch principal can only disallow teachers in their own branch
            if (!empty($validated['class_id'])) {
                $class = ClassRoom::find($validated['class_id']);
                if ($class && $class->branch_id != $user->branch_id) {
                    abort(403, 'You can only manage disallowals for your own branch.');
                }
            }
        }

        $teacherId = $validated['teacher_id'];
        $academicYearId = $validated['academic_year_id'];
        $termId = $validated['term_id'];
        $reason = $validated['reason'];
        $scope = $validated['scope'];

        $created = 0;

        if ($scope === 'all') {
            // Disallow the teacher from ALL mark entry for this AY/term
            MarkEntryDisallowal::updateOrCreate(
                [
                    'teacher_id' => $teacherId,
                    'class_id' => null,
                    'section_id' => null,
                    'subject_id' => null,
                    'academic_year_id' => $academicYearId,
                    'term_id' => $termId,
                ],
                [
                    'disallowed_by' => $user->id,
                    'reason' => $reason,
                    'is_active' => true,
                ]
            );
            $created = 1;
        } elseif ($scope === 'subject') {
            // Disallow for specific subject(s)
            $subjectIds = $request->input('subject_ids', []);
            if (empty($subjectIds)) {
                return redirect()->back()->with('error', 'Please select at least one subject.')->withInput();
            }
            foreach ($subjectIds as $subjectId) {
                MarkEntryDisallowal::updateOrCreate(
                    [
                        'teacher_id' => $teacherId,
                        'class_id' => $validated['class_id'],
                        'section_id' => $validated['section_id'],
                        'subject_id' => $subjectId,
                        'academic_year_id' => $academicYearId,
                        'term_id' => $termId,
                    ],
                    [
                        'disallowed_by' => $user->id,
                        'reason' => $reason,
                        'is_active' => true,
                    ]
                );
                $created++;
            }
        } elseif ($scope === 'section') {
            // Disallow for specific section(s)
            $sectionIds = $request->input('section_ids', []);
            if (empty($sectionIds)) {
                return redirect()->back()->with('error', 'Please select at least one section.')->withInput();
            }
            foreach ($sectionIds as $sectionId) {
                MarkEntryDisallowal::updateOrCreate(
                    [
                        'teacher_id' => $teacherId,
                        'class_id' => $validated['class_id'],
                        'section_id' => $sectionId,
                        'subject_id' => $validated['subject_id'],
                        'academic_year_id' => $academicYearId,
                        'term_id' => $termId,
                    ],
                    [
                        'disallowed_by' => $user->id,
                        'reason' => $reason,
                        'is_active' => true,
                    ]
                );
                $created++;
            }
        }

        return redirect()->route('admin.mark-entry-disallowals.index', [
            'branch_id' => $request->branch_id,
            'academic_year_id' => $academicYearId,
            'term_id' => $termId,
        ])->with('success', "Mark entry disallowal created successfully ({$created} record(s)).");
    }

    /**
     * Revoke (deactivate) a disallowal.
     */
    public function revoke($id)
    {
        $disallowal = MarkEntryDisallowal::findOrFail($id);

        $user = auth()->user();
        if ($user->role === 'branch_principal') {
            if ($disallowal->classRoom && $disallowal->classRoom->branch_id != $user->branch_id) {
                abort(403, 'You can only manage disallowals for your own branch.');
            }
        }

        $disallowal->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Disallowal has been revoked. The teacher can now enter marks for this assignment.');
    }

    /**
     * API: Get teacher's assignments for a given AY/term (for the add form).
     */
    public function apiTeacherAssignments(Request $request)
    {
        $teacherId = $request->query('teacher_id');
        $academicYearId = $request->query('academic_year_id');
        $branchId = $request->query('branch_id');

        if (!$teacherId) {
            return response()->json([]);
        }

        $query = TeacherAssignment::with(['classRoom', 'section', 'subject'])
            ->where('teacher_id', $teacherId);

        if ($academicYearId) {
            $query->where(function ($q) use ($academicYearId) {
                $q->whereNull('academic_year_id')->orWhere('academic_year_id', $academicYearId);
            });
        }

        if ($branchId) {
            $query->whereHas('classRoom', fn($q) => $q->where('branch_id', $branchId));
        }

        $assignments = $query->get()->map(function ($a) {
            return [
                'id' => $a->id,
                'class_id' => $a->class_id,
                'class_name' => $a->classRoom?->name,
                'section_id' => $a->section_id,
                'section_name' => $a->section?->name,
                'subject_id' => $a->subject_id,
                'subject_name' => $a->subject?->name,
            ];
        });

        return response()->json($assignments);
    }

    /**
     * API: Get sections for a class.
     */
    public function apiSections(Request $request)
    {
        $classId = $request->query('class_id');
        if (!$classId) return response()->json([]);

        return response()->json(
            Section::where('class_id', $classId)->orderBy('name')->get(['id', 'name'])
        );
    }

    /**
     * API: Get subjects for a teacher in a class/section.
     */
    public function apiSubjects(Request $request)
    {
        $teacherId = $request->query('teacher_id');
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');

        if (!$teacherId || !$classId) return response()->json([]);

        $query = TeacherAssignment::with('subject')
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId);

        if ($sectionId) {
            $query->where(function ($q) use ($sectionId) {
                $q->where('section_id', $sectionId)->orWhereNull('section_id');
            });
        }

        $subjects = $query->get()->map(function ($a) {
            return $a->subject ? ['id' => $a->subject->id, 'name' => $a->subject->name] : null;
        })->filter()->unique('id')->values();

        return response()->json($subjects);
    }

    /**
     * Batch disallow: Disallow multiple teachers at once for a class/section.
     */
    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'exists:teachers,id',
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $created = 0;

        foreach ($validated['teacher_ids'] as $teacherId) {
            MarkEntryDisallowal::updateOrCreate(
                [
                    'teacher_id' => $teacherId,
                    'class_id' => $validated['class_id'],
                    'section_id' => $validated['section_id'],
                    'subject_id' => $validated['subject_id'],
                    'academic_year_id' => $validated['academic_year_id'],
                    'term_id' => $validated['term_id'],
                ],
                [
                    'disallowed_by' => $user->id,
                    'reason' => $validated['reason'],
                    'is_active' => true,
                ]
            );
            $created++;
        }

        return redirect()->back()->with('success', "Batch disallowal created ({$created} teacher(s)).");
    }
}
