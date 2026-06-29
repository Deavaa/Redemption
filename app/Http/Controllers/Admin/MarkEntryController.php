<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Term;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\Student;
use App\Models\MarkEntry;
use App\Models\MarkEntryConfig;
use App\Models\MarkEntryLock;
use App\Models\MarkEntryPermission;
use App\Models\MarkEntryDisallowal;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class MarkEntryController extends Controller
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

    public function index(Request $request) {
        $academicYears = AcademicYear::orderBy('id','desc')->get();
        $currentAy = $academicYears->first();
        $terms = $currentAy ? Term::where('academic_year_id', $currentAy->id)->orderBy('id','asc')->get() : collect();
        $currentTerm = $terms->first();

        // ── Branch scope ──
        // Branch principals are restricted to their own branch (via the
        // branch-scope middleware). Admins / general managers see all.
        // Teachers now ALSO see all branches by default (per user request)
        // — they can pick any branch from the dropdown. Their per-class /
        // per-section access is still filtered by their teacher assignments
        // (see the $teacher logic below), so this does NOT give them data
        // they shouldn't see — it just lets them pick which branch's
        // classes/sections to browse in the dropdowns.
        $branchScope = $request->attributes->get('branch_scope');
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        if ($branchScope) {
            $branches = $branches->where('id', $branchScope);
        }
        $userBranchId = auth()->user()->branch_id;

        // Kept for backward compat with the view (which still references it
        // to decide whether to disable the branch dropdown). Always false
        // now — teachers are no longer hard-restricted to their own branch.
        $isTeacherBranchScoped = false;

        // Teacher-scoped filtering
        $isTeacher = false;
        $teacherAssignments = collect();
        $teacher = $this->getTeacherForUser();

        // ── Load classes for server-side dropdown population ──
        $classes = collect();

        if ($teacher) {
            $isTeacher = true;

            // Auto-select current academic year and active term for teachers
            $activeAy = AcademicYear::where('is_current', true)->first();
            if ($activeAy) {
                $currentAy = $activeAy;
                $academicYears = collect([$activeAy]); // Only show active AY for teachers
                $activeTerm = Term::where('academic_year_id', $activeAy->id)->where('is_active', true)->first();
                if ($activeTerm) {
                    $currentTerm = $activeTerm;
                    $terms = collect([$activeTerm]); // Only show active term for teachers
                }
            }

            // Only show sections from teacher's assignments + homeroom sections
            $assignmentSectionIds = $teacher->assignments()->pluck('section_id')->filter()->unique();
            $homeroomSectionIds = $teacher->sections()->pluck('id');
            $sectionIds = $assignmentSectionIds->merge($homeroomSectionIds)->unique();

            // Also include sections where teacher is assigned to a class (section_id is null on assignment)
            $assignmentClassIds = $teacher->assignments()->pluck('class_id')->unique();
            $homeroomClassIds = $teacher->classRooms()->pluck('id');
            $classIds = $assignmentClassIds->merge($homeroomClassIds)->unique();

            // Load teacher-scoped classes for dropdown (also filter by branch if scoped)
            $classes = ClassRoom::whereIn('id', $classIds)
                ->when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
                ->orderBy('numeric_name','asc')->orderBy('name','asc')->get(['id','name','branch_id']);

            $sections = Section::with('classRoom')
                ->where(function($q) use ($sectionIds, $classIds) {
                    $q->whereIn('id', $sectionIds)
                      ->orWhereIn('class_id', $classIds);
                })
                ->when($branchScope, function($q) use ($branchScope) {
                    $q->whereHas('classRoom', fn($cq) => $cq->where('branch_id', $branchScope));
                })
                ->orderBy('class_id','asc')
                ->orderBy('name','asc')
                ->get();

            $teacherAssignments = $teacher->assignments()
                ->with(['classRoom', 'section', 'subject'])
                ->get()
                ->map(function($a) {
                    return [
                        'id' => $a->id,
                        'teacher_id' => $a->teacher_id,
                        'class_id' => $a->class_id,
                        'class_name' => $a->classRoom ? $a->classRoom->name : null,
                        'section_id' => $a->section_id,
                        'section_name' => $a->section ? $a->section->name : null,
                        'subject_id' => $a->subject_id,
                        'subject_name' => $a->subject ? $a->subject->name : null,
                        'academic_year_id' => $a->academic_year_id,
                        'is_homeroom' => false,
                    ];
                });

            // Mark homeroom assignments + add homeroom classes/sections that may not have subject assignments
            $homeroomClassIds = $teacher->classRooms()->pluck('id');
            $homeroomSectionIds = $teacher->sections()->pluck('id');

            $teacherAssignments = $teacherAssignments->map(function($a) use ($homeroomClassIds, $homeroomSectionIds) {
                if ($homeroomSectionIds->contains($a['section_id']) || $homeroomClassIds->contains($a['class_id'])) {
                    $a['is_homeroom'] = true;
                }
                return $a;
            });

            // Add homeroom classes/sections that aren't already in assignments
            $existingKeys = $teacherAssignments->map(function($a) {
                return $a['class_id'] . '_' . $a['section_id'] . '_' . $a['subject_id'];
            })->toArray();

            foreach ($teacher->classRooms as $hrClass) {
                foreach ($teacher->sections->where('class_id', $hrClass->id) as $hrSection) {
                    $key = $hrClass->id . '_' . $hrSection->id . '_null';
                    if (!in_array($key, $existingKeys)) {
                        $teacherAssignments->push([
                            'id' => null,
                            'teacher_id' => $teacher->id,
                            'class_id' => $hrClass->id,
                            'class_name' => $hrClass->name,
                            'section_id' => $hrSection->id,
                            'section_name' => $hrSection->name,
                            'subject_id' => null,
                            'subject_name' => null,
                            'academic_year_id' => null,
                            'is_homeroom' => true,
                        ]);
                    }
                }
            }
        } else {
            // Admin: load classes for the current academic year (with fallback), filtered by branch scope
            $classes = ClassRoom::where('academic_year_id', $currentAy?->id)
                ->when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
                ->orderBy('numeric_name','asc')->orderBy('name','asc')->get(['id','name','branch_id']);
            if ($classes->isEmpty()) {
                $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
                    ->orderBy('numeric_name','asc')->orderBy('name','asc')->get(['id','name','branch_id']);
            }
            $sections = Section::with('classRoom')
                ->when($branchScope, function($q) use ($branchScope) {
                    $q->whereHas('classRoom', fn($cq) => $cq->where('branch_id', $branchScope));
                })
                ->orderBy('class_id','asc')->orderBy('name','asc')->get();
        }

        return view('admin.mark-entries.index', compact('academicYears', 'terms', 'sections', 'classes', 'currentAy', 'currentTerm', 'isTeacher', 'teacherAssignments', 'branches', 'branchScope', 'userBranchId', 'isTeacherBranchScoped'));
    }

    public function apiClasses(Request $request) {
        $ayId = $request->query('academic_year_id');
        $branchId = $request->query('branch_id');
        $teacher = $this->getTeacherForUser();

        // Branch scope: principals only see their branch classes
        $branchScope = $request->attributes->get('branch_scope');
        $effectiveBranchId = $branchScope ?? $branchId;

        // Default to current academic year if none provided
        if (!$ayId) {
            $currentAy = AcademicYear::where('is_current', true)->first()
                ?? AcademicYear::orderBy('id', 'desc')->first();
            $ayId = $currentAy?->id;
        }

        if ($teacher) {
            // Only return classes from teacher's assignments + homeroom classes
            $assignmentClassIds = $teacher->assignments()->pluck('class_id')->unique();
            $homeroomClassIds = $teacher->classRooms()->pluck('id');
            $classIds = $assignmentClassIds->merge($homeroomClassIds)->unique();

            $query = ClassRoom::whereIn('id', $classIds);
            if ($ayId) {
                $query->where('academic_year_id', $ayId);
            }
            if ($effectiveBranchId) {
                $query->where('branch_id', $effectiveBranchId);
            }
            $classes = $query->orderBy('numeric_name','asc')->orderBy('name','asc')->get(['id','name','branch_id']);

            // Fallback: if no classes found for this AY, show all teacher classes
            if ($classes->isEmpty()) {
                $fallbackQuery = ClassRoom::whereIn('id', $classIds);
                if ($effectiveBranchId) {
                    $fallbackQuery->where('branch_id', $effectiveBranchId);
                }
                $classes = $fallbackQuery->orderBy('numeric_name','asc')->orderBy('name','asc')->get(['id','name','branch_id']);
            }

            return response()->json($classes);
        }

        $query = ClassRoom::query();
        if ($ayId) {
            $query->where('academic_year_id', $ayId);
        }
        if ($effectiveBranchId) {
            $query->where('branch_id', $effectiveBranchId);
        }
        $classes = $query->orderBy('numeric_name','asc')->orderBy('name','asc')->get(['id','name','branch_id']);

        // Fallback: if no classes for this AY, show all classes for the branch
        if ($classes->isEmpty() && $ayId) {
            $fallbackQuery = ClassRoom::query();
            if ($effectiveBranchId) {
                $fallbackQuery->where('branch_id', $effectiveBranchId);
            }
            $classes = $fallbackQuery->orderBy('numeric_name','asc')->orderBy('name','asc')->get(['id','name','branch_id']);
        }

        return response()->json($classes);
    }

    public function apiBranches(Request $request) {
        $academicYearId = $request->get('academic_year_id');

        // Branch scope: principals only see their branch
        $branchScope = $request->attributes->get('branch_scope');

        if ($branchScope) {
            return response()->json(Branch::where('id', $branchScope)->where('is_active', true)->get());
        }

        $branches = Branch::where('is_active', true)
            ->when($academicYearId, function ($q) use ($academicYearId) {
                $q->whereHas('classes', function ($q2) use ($academicYearId) {
                    $q2->where('academic_year_id', $academicYearId);
                });
            })
            ->orderBy('name')
            ->get();

        return response()->json($branches);
    }

    public function apiTerms(Request $request) {
        $ayId = $request->query('academic_year_id');
        if (!$ayId) return response()->json([]);
        return response()->json(Term::where('academic_year_id',$ayId)->orderBy('id','asc')->get(['id','name']));
    }

    public function apiSections(Request $request) {
        $classId = $request->query('class_id');
        $classGrade = $request->query('class_grade');
        $teacher = $this->getTeacherForUser();

        if ($classId) {
            $query = Section::where('class_id',$classId);

            if ($teacher) {
                // Only return sections from teacher's assignments for this class
                // + sections where they are homeroom teacher
                $assignmentSectionIds = $teacher->assignments()
                    ->where('class_id', $classId)
                    ->pluck('section_id')
                    ->filter()
                    ->unique();
                $homeroomSectionIds = $teacher->sections()
                    ->where('class_id', $classId)
                    ->pluck('id');

                $allowedSectionIds = $assignmentSectionIds->merge($homeroomSectionIds)->unique();

                // If teacher has assignments with null section_id for this class, they can see all sections
                $hasNullSectionAssignment = $teacher->assignments()
                    ->where('class_id', $classId)
                    ->whereNull('section_id')
                    ->exists();

                if (!$hasNullSectionAssignment) {
                    $query->whereIn('id', $allowedSectionIds);
                }
                // If they have null-section assignment, they see all sections in that class
            }

            return response()->json($query->orderBy('name','asc')->get(['id','name']));
        }

        if ($classGrade) {
            $sections = Student::where('class_grade',$classGrade)
                ->whereNotNull('section')
                ->where('section','<>','')
                ->distinct()
                ->orderBy('section','asc')
                ->pluck('section');
            return response()->json($sections);
        }

        return response()->json([]);
    }

    public function apiSubjects(Request $request) {
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $ayId = $request->query('academic_year_id');
        if (!$classId) return response()->json([]);

        $teacher = $this->getTeacherForUser();

        $query = TeacherAssignment::with('subject')->where('class_id', $classId);

        // Filter by section: show section-specific + class-wide (null section) assignments
        if ($sectionId) {
            $query->where(function($q) use ($sectionId) {
                $q->where('section_id', $sectionId)->orWhereNull('section_id');
            });
        }
        // If no section_id provided, show all subjects for the class

        // Filter by academic year: show AY-specific + no-AY assignments
        if ($ayId) {
            $query->where(function($q) use ($ayId) {
                $q->whereNull('academic_year_id')->orWhere('academic_year_id', $ayId);
            });
        }

        if ($teacher) {
            // Only return subjects assigned to this teacher for the class+section
            // OR if teacher is homeroom for this class/section, show all subjects
            $isHomeroomClass = $classId ? $teacher->classRooms()->where('id', $classId)->exists() : false;
            $isHomeroomSection = $sectionId ? $teacher->sections()->where('id', $sectionId)->exists() : false;

            if (!$isHomeroomClass && !$isHomeroomSection) {
                // Not homeroom — only show their own assigned subjects
                $query->where('teacher_id', $teacher->id);
            }
            // If homeroom, show all subjects (no additional filter)
        }

        $assignments = $query->get();
        $subjects = $assignments->map(function($a) {
            $subj = $a->subject;
            if (!$subj) return null;
            return ['id'=>$subj->id,'name'=>$subj->name,'code'=>$subj->code??'','type'=>strtolower($subj->type??''),'priority'=>$subj->priority??0,'is_core'=>is_null($a->section_id)];
        })->filter();
        return response()->json($subjects->sortBy('priority')->unique('id')->values());
    }

    public function apiStudents(Request $request) {
        $classId = $request->get('class_id');
        $sectionId = $request->get('section_id');
        $ayId = $request->get('academic_year_id');

        // Try enrollment-based lookup first
        if ($classId && $sectionId) {
            $ayId = $ayId ?: (AcademicYear::where('is_current', true)->value('id') ?? AcademicYear::max('id'));

            $enrolledStudentIds = \App\Models\StudentEnrollment::where('academic_year_id', $ayId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('status', 'enrolled')
                ->pluck('student_id');

            if ($enrolledStudentIds->isNotEmpty()) {
                $query = Student::whereIn('id', $enrolledStudentIds)->where('status', 'active');
            } else {
                // Fallback: direct student table lookup
                $query = Student::where('class_id', $classId)->where('section_id', $sectionId)->where('status', 'active');
            }
        } else {
            $query = Student::where('status', 'active');

            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }
            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }
        }

        if ($request->filled('class_grade')) {
            $query->where('class_grade', $request->class_grade);
        }
        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        // Sort alphabetically by full_name
        $students = $query->orderBy('full_name', 'asc')->get();

        $subjectId = $request->get('subject_id');
        $ayId = $request->get('academic_year_id');
        $termId = $request->get('term_id');
        $marks = [];

        if ($subjectId) {
            $mq = MarkEntry::where('subject_id', $subjectId);
            if ($ayId) $mq->where('academic_year_id', $ayId);
            if ($termId) $mq->where('term_id', $termId);
            foreach ($mq->get() as $m) {
                $marks[$m->student_id] = $m;
            }
        }

        $studentsData = [];
        $marksData = [];

        foreach ($students as $student) {
            $studentsData[] = [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'admission_number' => $student->admission_number,
                'roll_number' => $student->roll_number,
                'class_grade' => $student->class_grade,
                'section' => $student->section,
            ];

            if (isset($marks[$student->id])) {
                $marksData[$student->id] = $marks[$student->id]->only([
                    'ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10',
                    'conduct','handwriting','creativity','test1','test2','mid_term','final_exam'
                ]);
            }
        }

        return response()->json(['students' => $studentsData, 'marks' => $marksData]);
    }

    public function apiLoadStudents(Request $request) {
        $ayId=$request->query('academic_year_id'); $termId=$request->query('term_id');
        $classId=$request->query('class_id'); $sectionId=$request->query('section_id'); $subjectId=$request->query('subject_id');
        if (!$ayId||!$termId||!$classId||!$sectionId||!$subjectId) return response()->json(['error'=>'All filters required'],400);

        // First try: find students via enrollment records (enrollment-based lookup)
        $enrolledStudentIds = \App\Models\StudentEnrollment::where('academic_year_id', $ayId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('status', 'enrolled')
            ->pluck('student_id');

        if ($enrolledStudentIds->isNotEmpty()) {
            $students = Student::whereIn('id', $enrolledStudentIds)
                ->where('status', 'active')
                ->orderBy('full_name', 'asc')
                ->select('id as student_id', 'full_name as student_name', 'roll_number', 'gender')
                ->get();
        } else {
            // Fallback: direct student table lookup (for legacy data without enrollment records)
            $students = Student::where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('status', 'active')
                ->orderBy('full_name', 'asc')
                ->select('id as student_id', 'full_name as student_name', 'roll_number', 'gender')
                ->get();
        }
        $existingMarks = MarkEntry::where('academic_year_id',$ayId)->where('term_id',$termId)
            ->where('class_id',$classId)->where('section_id',$sectionId)->where('subject_id',$subjectId)->get()->keyBy('student_id');
        $markFields = MarkEntry::getMarkFields();
        // Build CA and exam field lists from config
        $caFields = [];
        $examFields = [];
        foreach ($markFields as $field) {
            $cat = $field['category'] ?? 'ca';
            if ($cat === 'ca' || $cat === 'extra_ca') {
                $caFields[] = $field['col'];
            } elseif ($cat === 'exam') {
                $examFields[] = $field['col'];
            }
        }
        $caWeight = MarkEntryConfig::getCaWeight();
        $examWeight = MarkEntryConfig::getExamWeight();
        $precision = MarkEntryConfig::getRoundingPrecision();
        // Calculate CA raw total from field max values
        $caRawTotal = 0;
        foreach ($markFields as $field) {
            $cat = $field['category'] ?? 'ca';
            if ($cat === 'ca' || $cat === 'extra_ca') {
                $caRawTotal += $field['max'];
            }
        }
        $rows = [];
        foreach ($students as $s) {
            $mark = $existingMarks->get($s->student_id);
            $row = ['student_id'=>$s->student_id,'student_name'=>$s->student_name??'Unknown','roll_number'=>$s->roll_number,'gender'=>$s->gender];
            foreach ($markFields as $field) { $col=$field['col']; $row[$col]=$mark?floatval($mark->$col):null; }
            // Recalculate totals from raw fields (don't trust stored values)
            if ($mark) {
                $caRaw = 0;
                foreach ($caFields as $f) { $caRaw += floatval($mark->$f ?? 0); }
                $examRaw = 0;
                foreach ($examFields as $f) { $examRaw += floatval($mark->$f ?? 0); }
                $row['ca_total'] = $caRawTotal > 0 ? round(($caRaw / $caRawTotal) * $caWeight, $precision) : 0;
                $row['exam_total'] = min($examRaw, $examWeight);
                $row['grand_total'] = round($row['ca_total'] + $row['exam_total'], $precision);
            } else {
                $row['ca_total'] = 0;
                $row['exam_total'] = 0;
                $row['grand_total'] = 0;
            }
            $rows[]=$row;
        }
        $subject=Subject::find($subjectId); $term=Term::find($termId); $class=ClassRoom::find($classId); $section=Section::find($sectionId);
        return response()->json(['students'=>$rows,'markFields'=>$markFields,
            'subject'=>$subject?$subject->name:'','term'=>$term?$term->name:'',
            'class'=>$class?$class->name:'','section'=>$section?$section->name:'']);
    }

    public function apiSave(Request $request) {
        // Relaxed validation — auto-save sends minimal data
        $request->validate([
            'student_id' => 'required',
            'subject_id' => 'required',
            'term_id' => 'required',
        ]);

        // Touch session to keep it alive during mark entry
        $request->session()->put('_last_mark_save', time());
        // Force-save session to database immediately (critical for database driver)
        $request->session()->save();

        $studentId = $request->input('student_id');
        $subjectId = $request->input('subject_id');
        $ayId = $request->input('academic_year_id') ?: null;
        $termId = $request->input('term_id');

        // ── Authorization check for teachers ──
        $teacher = $this->getTeacherForUser();
        if ($teacher) {
            $classId = $request->input('class_id');
            $sectionId = $request->input('section_id');

            // Resolve class_id from class_grade if needed
            if (empty($classId) && $request->filled('class_grade')) {
                $classRoom = ClassRoom::where('name', $request->input('class_grade'))->first();
                if ($classRoom) $classId = $classRoom->id;
            }
            // Resolve section_id from section if needed
            if (empty($sectionId) && $request->filled('section') && !empty($classId)) {
                $sectionModel = Section::where('class_id', $classId)
                    ->where('name', $request->input('section'))->first();
                if ($sectionModel) $sectionId = $sectionModel->id;
            }

            // Check if teacher is assigned to this class+section+subject
            $isAssigned = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where(function($q) use ($sectionId) {
                    $q->where('section_id', $sectionId)->orWhereNull('section_id');
                })
                ->exists();

            // Or check if teacher is homeroom for this class or section
            $isHomeroomClass = !empty($classId) && $teacher->classRooms()->where('id', $classId)->exists();
            $isHomeroomSection = !empty($sectionId) && $teacher->sections()->where('id', $sectionId)->exists();

            if (!$isAssigned && !$isHomeroomClass && !$isHomeroomSection) {
                return response()->json([
                    'success' => false,
                    'error' => 'You are not authorized to enter marks for this class/section/subject.',
                ], 403);
            }

            // ── Disallowal check: branch principal can disallow specific teachers ──
            $effectiveAyId = $ayId;
            $effectiveTermId = $termId;
            if (!$effectiveAyId) {
                $activeAy = AcademicYear::where('is_current', true)->first();
                $effectiveAyId = $activeAy?->id;
            }
            if (!$effectiveTermId && $effectiveAyId) {
                $activeTerm = Term::where('academic_year_id', $effectiveAyId)->where('is_active', true)->first();
                $effectiveTermId = $activeTerm?->id;
            }

            $isDisallowed = MarkEntryDisallowal::isDisallowed(
                $teacher->id,
                $classId,
                $sectionId,
                $subjectId,
                $effectiveAyId,
                $effectiveTermId
            );

            if ($isDisallowed) {
                return response()->json([
                    'success' => false,
                    'error' => 'Your mark entry permission has been restricted for this class/section/subject. Contact your branch principal for assistance.',
                    'is_disallowed' => true,
                ], 403);
            }
        }

        // ── Mark Entry Lock & Permission Check ──
        $user = auth()->user();
        $isAdmin = $user && in_array($user->role, ['admin', 'super_admin']);

        if (!$isAdmin) {
            // Resolve class_id for lock check
            $lockClassId = $request->input('class_id');
            if (empty($lockClassId) && $request->filled('class_grade')) {
                $cr = ClassRoom::where('name', $request->input('class_grade'))->first();
                if ($cr) $lockClassId = $cr->id;
            }
            // Get branch_id from the class
            $branchId = null;
            if ($lockClassId) {
                $classModel = ClassRoom::find($lockClassId);
                if ($classModel) $branchId = $classModel->branch_id;
            }
            // Fallback: use user's branch
            if (!$branchId && $user) {
                $branchId = $user->branch_id;
            }

            $isLocked = $branchId && $ayId && $termId && MarkEntryLock::isLocked($branchId, $ayId, $termId);

            if ($isLocked) {
                // Mark entry is locked — check if teacher has special permission
                $hasPermission = false;
                if ($teacher) {
                    $hasPermission = MarkEntryPermission::hasPermission(
                        $teacher->id, $studentId, $subjectId, $ayId, $termId
                    );
                }

                if (!$hasPermission) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Mark entry is locked for this term. You do not have permission to edit these marks.',
                        'is_locked' => true,
                    ], 403);
                }
            }
        }

        // Collect only the mark fields that were actually sent (from config)
        $markFieldNames = array_map(function($f) { return $f['col']; }, MarkEntry::getMarkFields());
        $data = [];
        $data['student_id'] = $studentId;
        $data['subject_id'] = $subjectId;
        $data['academic_year_id'] = $ayId;
        $data['term_id'] = $termId;

        // Copy explicit fields from request
        // NOTE: 'section' and 'class_grade' are text columns that may not exist
        // in all databases (only class_grade was added by migration; section is
        // tracked via section_id FK). We skip them here — section_id and class_id
        // are the authoritative references. This prevents 'Unknown column section'
        // errors when saving marks.
        foreach (['class_id','section_id','exam_id','grade','remarks'] as $f) {
            if ($request->has($f)) {
                $data[$f] = $request->input($f) ?: null;
            }
        }
        // class_grade is a real column — set it from the class name if available
        if (!empty($data['class_id'])) {
            $cr = \App\Models\ClassRoom::find($data['class_id']);
            if ($cr) $data['class_grade'] = $cr->name;
        }

        // Resolve class_id and section_id from class_grade/section if not provided
        if (empty($data['class_id']) && !empty($data['class_grade'])) {
            $classRoom = ClassRoom::where('name', $data['class_grade'])->first();
            if ($classRoom) {
                $data['class_id'] = $classRoom->id;
            }
        }
        if (empty($data['section_id']) && !empty($data['section']) && !empty($data['class_id'])) {
            $sectionModel = Section::where('class_id', $data['class_id'])
                ->where('name', $data['section'])->first();
            if ($sectionModel) {
                $data['section_id'] = $sectionModel->id;
            }
        }

        // Set teacher_id — use user_id FK first, then email fallback
        $teacherId = null;
        if (auth()->check()) {
            $t = Teacher::where('user_id', auth()->user()->id)->first();
            if (!$t) {
                $t = Teacher::where('email', auth()->user()->email)->first();
            }
            if ($t) {
                $teacherId = $t->id;
            }
        }
        $data['teacher_id'] = $teacherId;

        // ── Load existing record FIRST so calcTotals has complete data ──
        $existingQuery = MarkEntry::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('term_id', $termId);
        if (!empty($ayId)) {
            $existingQuery->where('academic_year_id', $ayId);
        } else {
            $existingQuery->whereNull('academic_year_id');
        }
        $existing = $existingQuery->first();

        // Seed $data with existing values so calcTotals works with ALL fields
        if ($existing) {
            foreach ($markFieldNames as $f) {
                if (!isset($data[$f])) {
                    $data[$f] = $existing->$f;
                }
            }
        }

        // Handle single-field auto-save (mark_key + mark_value) — override the one field
        if ($request->filled('mark_key') && $request->has('mark_value')) {
            $markKey = $request->mark_key;
            $markValue = $request->mark_value;

            // ── SERVER-SIDE MAX ENFORCEMENT ──
            // The client-side enforceMax() is the first line of defense, but
            // we ALSO enforce on the server so that bypassed/old clients can't
            // store over-limit values. Find the field config and clamp.
            if ($markValue !== '' && $markValue !== null) {
                $fieldConfig = null;
                foreach (MarkEntryConfig::getMarkFields() as $f) {
                    if ($f['col'] === $markKey) { $fieldConfig = $f; break; }
                }
                if ($fieldConfig) {
                    $max = floatval($fieldConfig['max']);
                    $v = floatval($markValue);
                    if ($max > 0 && $v > $max) {
                        $markValue = $max;
                    }
                    if ($v < 0) {
                        $markValue = 0;
                    }
                }
            }
            $data[$markKey] = ($markValue === '' || $markValue === null) ? null : $markValue;
        } else {
            // Full save — copy all mark fields from request
            foreach ($markFieldNames as $f) {
                if ($request->has($f)) {
                    $val = $request->input($f);

                    // ── SERVER-SIDE MAX ENFORCEMENT for full saves too ──
                    if ($val !== '' && $val !== null) {
                        $fieldConfig = null;
                        foreach (MarkEntryConfig::getMarkFields() as $fc) {
                            if ($fc['col'] === $f) { $fieldConfig = $fc; break; }
                        }
                        if ($fieldConfig) {
                            $max = floatval($fieldConfig['max']);
                            $v = floatval($val);
                            if ($max > 0 && $v > $max) $val = $max;
                            if ($v < 0) $val = 0;
                        }
                    }
                    $data[$f] = ($val === '' || $val === null) ? null : $val;
                }
            }
        }

        // Calculate totals — now $data has ALL mark fields, not just the changed one
        $data = MarkEntry::calcTotals($data);

        // Set marks_obtained = grand_total for backward compatibility (column is NOT NULL in original migration)
        $data['marks_obtained'] = $data['grand_total'] ?? 0;

        try {
            // $existing was already loaded above (before calcTotals)
            if ($existing) {
                // Only update the fields we actually have data for
                $updateData = array_filter($data, function($value, $key) use ($markFieldNames) {
                    // Always update these core fields
                    if (in_array($key, ['student_id','subject_id','academic_year_id','term_id',
                        'class_id','section_id','teacher_id','marks_obtained',
                        'ca_total','exam_total','grand_total'])) return true;
                    // Update mark fields only if they were explicitly set
                    if (in_array($key, $markFieldNames)) return true;
                    // Update other fields like class_grade, exam_id, grade, remarks
                    // NOTE: 'section' column doesn't exist in all DBs — only class_grade
                    if (in_array($key, ['class_grade','exam_id','grade','remarks'])) return true;
                    return false;
                }, ARRAY_FILTER_USE_BOTH);

                $existing->update($updateData);
                $entry = $existing->fresh();
            } else {
                // Set defaults for required fields on create
                if (!isset($data['marks_obtained'])) $data['marks_obtained'] = 0;
                $entry = MarkEntry::create($data);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('MarkEntry save error: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            Log::error('MarkEntry save exception: ' . $e->getMessage(), [
                'class' => get_class($e),
                'data' => $data
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'entry' => $entry,
            'ca_total' => $entry->ca_total,
            'exam_total' => $entry->exam_total,
            'grand_total' => $entry->grand_total,
            'grade' => $entry->grade ?? null,
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * Bulk save marks for multiple students at once.
     * The teacher selects a mark field (e.g., 'ca1', 'exam1') and enters
     * values for all students in a table. This endpoint saves them all.
     */
    public function apiBulkSave(Request $request)
    {
        // Handle JSON body — Laravel doesn't auto-parse JSON arrays into $request->all()
        $input = $request->all();
        
        // If marks was sent as a JSON string, decode it
        if (is_string($input['marks'] ?? null)) {
            $input['marks'] = json_decode($input['marks'], true);
        }

        // Manual validation — return JSON errors, not redirects
        $errors = [];
        if (empty($input['subject_id'])) $errors['subject_id'] = ['Subject is required.'];
        if (empty($input['term_id'])) $errors['term_id'] = ['Term is required.'];
        if (empty($input['mark_key'])) $errors['mark_key'] = ['Mark field is required.'];
        if (empty($input['marks']) || !is_array($input['marks'])) $errors['marks'] = ['Marks array is required.'];

        if (!empty($errors)) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . json_encode($errors),
                'errors' => $errors,
            ], 422);
        }

        // Touch session to keep it alive
        $request->session()->put('_last_mark_save', time());
        $request->session()->save();

        $subjectId = $input['subject_id'];
        $ayId = $input['academic_year_id'] ?? null;
        $termId = $input['term_id'];
        $markKey = $input['mark_key'];
        $classId = $input['class_id'] ?? null;
        $sectionId = $input['section_id'] ?? null;
        $marks = $input['marks'];

        // Authorization check for teachers
        $teacher = $this->getTeacherForUser();
        if ($teacher) {
            $isAssigned = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where(function($q) use ($sectionId) {
                    $q->where('section_id', $sectionId)->orWhereNull('section_id');
                })
                ->exists();

            $isHomeroomClass = $teacher->classRooms()->where('id', $classId)->exists();
            $isHomeroomSection = $sectionId && $teacher->sections()->where('id', $sectionId)->exists();

            if (!$isAssigned && !$isHomeroomClass && !$isHomeroomSection) {
                return response()->json(['success' => false, 'error' => 'Not authorized.'], 403);
            }
        }

        $saved = 0;
        $errors = [];

        foreach ($marks as $item) {
            $studentId = $item['student_id'];
            $value = $item['value'];
            if ($value === '' || $value === null) {
                $value = null;
            }

            // Load existing record
            $existingQuery = MarkEntry::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->where('term_id', $termId);
            if ($ayId) {
                $existingQuery->where('academic_year_id', $ayId);
            } else {
                $existingQuery->whereNull('academic_year_id');
            }
            $existing = $existingQuery->first();

            // Build data with ALL fields (seed from existing so calcTotals works)
            $data = [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'academic_year_id' => $ayId,
                'term_id' => $termId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'teacher_id' => $teacher?->id,
            ];

            if ($existing) {
                $markFieldNames = array_map(fn($f) => $f['col'], MarkEntry::getMarkFields());
                foreach ($markFieldNames as $f) {
                    $data[$f] = $existing->$f;
                }
            }

            // Override the single field being bulk-edited
            $data[$markKey] = $value;

            // Calculate totals
            $data = MarkEntry::calcTotals($data);
            $data['marks_obtained'] = $data['grand_total'] ?? 0;

            try {
                if ($existing) {
                    $existing->update($data);
                } else {
                    MarkEntry::create($data);
                }
                $saved++;
            } catch (\Throwable $e) {
                $errors[] = "Student {$studentId}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'saved' => $saved,
            'errors' => $errors,
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * Export marks for a class/section/subject/term as a CSV file.
     * Admin-only. Downloads current marks + student list.
     */
    public function export(Request $request)
    {
        $ayId = $request->query('academic_year_id');
        $termId = $request->query('term_id');
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $subjectId = $request->query('subject_id');

        if (!$ayId || !$termId || !$classId || !$sectionId || !$subjectId) {
            return back()->with('error', 'All filters (academic year, term, class, section, subject) are required for export.');
        }

        $markFields = MarkEntry::getMarkFields();

        // Load students
        $enrolledStudentIds = \App\Models\StudentEnrollment::where('academic_year_id', $ayId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('status', 'enrolled')
            ->pluck('student_id');

        if ($enrolledStudentIds->isNotEmpty()) {
            $students = Student::whereIn('id', $enrolledStudentIds)
                ->where('status', 'active')
                ->orderBy('full_name', 'asc')
                ->select('id', 'full_name', 'roll_number')
                ->get();
        } else {
            $students = Student::where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('status', 'active')
                ->orderBy('full_name', 'asc')
                ->select('id', 'full_name', 'roll_number')
                ->get();
        }

        $existingMarks = MarkEntry::where('academic_year_id', $ayId)
            ->where('term_id', $termId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('subject_id', $subjectId)
            ->get()->keyBy('student_id');

        // Build CSV
        $headers = ['student_id', 'student_name', 'roll_number'];
        foreach ($markFields as $f) {
            $headers[] = $f['col'] . ' (/' . $f['max'] . ')';
        }

        $rows = [];
        foreach ($students as $s) {
            $mark = $existingMarks->get($s->id);
            $row = [$s->id, $s->full_name, $s->roll_number];
            foreach ($markFields as $f) {
                $col = $f['col'];
                $val = $mark ? $mark->$col : null;
                $row[] = ($val !== null && $val !== '') ? $val : '';
            }
            $rows[] = $row;
        }

        $subject = Subject::find($subjectId);
        $term = Term::find($termId);
        $class = ClassRoom::find($classId);
        $section = Section::find($sectionId);
        $academicYear = AcademicYear::find($ayId);

        $filename = 'marks_' . ($class->name ?? 'class') . '_' . ($section->name ?? 'sec') . '_' . ($subject->name ?? 'subj') . '_' . ($term->name ?? 'term') . '.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Export a blank template CSV for bulk mark entry.
     * Same as export but with empty mark columns.
     */
    public function exportTemplate(Request $request)
    {
        $ayId = $request->query('academic_year_id');
        $termId = $request->query('term_id');
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $subjectId = $request->query('subject_id');

        if (!$ayId || !$termId || !$classId || !$sectionId || !$subjectId) {
            return back()->with('error', 'All filters (academic year, term, class, section, subject) are required for template export.');
        }

        $markFields = MarkEntry::getMarkFields();

        // Load students (template = empty marks)
        $enrolledStudentIds = \App\Models\StudentEnrollment::where('academic_year_id', $ayId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('status', 'enrolled')
            ->pluck('student_id');

        if ($enrolledStudentIds->isNotEmpty()) {
            $students = Student::whereIn('id', $enrolledStudentIds)
                ->where('status', 'active')
                ->orderBy('full_name', 'asc')
                ->select('id', 'full_name', 'roll_number')
                ->get();
        } else {
            $students = Student::where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('status', 'active')
                ->orderBy('full_name', 'asc')
                ->select('id', 'full_name', 'roll_number')
                ->get();
        }

        $headers = ['student_id', 'student_name', 'roll_number'];
        foreach ($markFields as $f) {
            $headers[] = $f['col'] . ' (/' . $f['max'] . ')';
        }

        $filename = 'mark_template_' . ($subjectId) . '.csv';

        return response()->streamDownload(function () use ($headers, $students, $markFields) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            foreach ($students as $s) {
                $row = [$s->id, $s->full_name, $s->roll_number];
                foreach ($markFields as $f) {
                    $row[] = ''; // empty mark
                }
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import marks from a CSV file.
     * Admin-only. Parses the CSV and saves marks via the same logic as apiSave.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120', // max 5MB
            'academic_year_id' => 'required',
            'term_id' => 'required',
            'class_id' => 'required',
            'section_id' => 'required',
            'subject_id' => 'required',
        ]);

        $ayId = $request->input('academic_year_id');
        $termId = $request->input('term_id');
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $subjectId = $request->input('subject_id');

        // Detect if the client wants JSON (modern fetch) vs. redirect (legacy form post)
        $wantsJson = $request->expectsJson() || $request->ajax() ||
                     $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                     $request->header('Accept') === 'application/json';

        $markFields = MarkEntry::getMarkFields();
        $markFieldCols = array_map(fn($f) => $f['col'], $markFields);
        $markFieldMap = []; // header label → field col
        foreach ($markFields as $f) {
            $markFieldMap[$f['col'] . ' (/' . $f['max'] . ')'] = $f['col'];
            $markFieldMap[$f['col']] = $f['col']; // also accept bare col name
        }

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return $wantsJson
                ? response()->json(['success' => false, 'error' => 'Cannot open uploaded file.'], 400)
                : back()->with('error', 'Cannot open uploaded file.');
        }
        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            fseek($handle, 0);
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            $msg = 'CSV file is empty or invalid.';
            return $wantsJson
                ? response()->json(['success' => false, 'error' => $msg], 400)
                : back()->with('error', $msg);
        }
        // Normalize headers (trim + strip invisible chars that Excel sometimes adds)
        $headers = array_map(function($h) {
            $h = trim($h);
            $h = preg_replace('/[\x00-\x1F\x7F\xEF\xBB\xBF]/u', '', $h); // strip BOM/control chars
            return $h;
        }, $headers);

        // Find student_id column index (also accept 'Student ID', 'student id', 'ID')
        $sidIdx = null;
        foreach ($headers as $i => $h) {
            $norm = strtolower($h);
            if ($norm === 'student_id' || $norm === 'student id' || $norm === 'id') {
                $sidIdx = $i;
                break;
            }
        }
        if ($sidIdx === null) {
            fclose($handle);
            $msg = 'CSV must have a "student_id" column. Headers found: ' . implode(', ', $headers);
            return $wantsJson
                ? response()->json(['success' => false, 'error' => $msg], 400)
                : back()->with('error', $msg);
        }

        // Map header indices to mark field cols
        $colMap = []; // index → mark field col
        foreach ($headers as $i => $h) {
            if (isset($markFieldMap[$h])) {
                $colMap[$i] = $markFieldMap[$h];
            }
        }

        if (empty($colMap)) {
            fclose($handle);
            $msg = 'CSV must have at least one mark field column (e.g. ca1, test1, ca1 (/50), etc.). Headers found: ' . implode(', ', $headers);
            return $wantsJson
                ? response()->json(['success' => false, 'error' => $msg], 400)
                : back()->with('error', $msg);
        }

        $saved = 0;
        $skipped = 0;
        $errors = [];
        $lineNum = 1;

        // Eager-load all students that appear in the CSV in ONE query — avoids
        // N+1 Student::find() calls inside the loop (the previous implementation
        // did one DB query per row, which is slow for large classes and also
        // made the import feel "stuck" after the first row on slow servers).
        // We do two passes: first collect student_ids, then bulk-fetch.
        $allStudentIds = [];
        $rowsBuffer = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rowsBuffer[] = $row;
        }
        fclose($handle);

        foreach ($rowsBuffer as $row) {
            $sidVal = isset($row[$sidIdx]) ? trim($row[$sidIdx]) : '';
            if ($sidVal !== '') $allStudentIds[] = $sidVal;
        }
        $allStudentIds = array_unique($allStudentIds);
        $studentsMap = !empty($allStudentIds)
            ? Student::whereIn('id', $allStudentIds)->get()->keyBy('id')
            : collect();

        // Also eager-load existing mark entries for the same subject/term/ay
        // so we don't query the DB once per row inside the loop.
        $existingMap = !empty($allStudentIds)
            ? MarkEntry::where('subject_id', $subjectId)
                ->where('term_id', $termId)
                ->where('academic_year_id', $ayId)
                ->whereIn('student_id', $allStudentIds)
                ->get()->keyBy('student_id')
            : collect();

        foreach ($rowsBuffer as $row) {
            $lineNum++;

            // Skip totally blank rows (Excel trailing-empty-row artifact)
            $isEmptyRow = true;
            foreach ($row as $cell) {
                if (trim((string)$cell) !== '') { $isEmptyRow = false; break; }
            }
            if ($isEmptyRow) {
                continue;
            }

            $studentId = isset($row[$sidIdx]) ? trim($row[$sidIdx]) : '';
            if ($studentId === '') {
                $errors[] = "Line $lineNum: missing student_id — skipped.";
                $skipped++;
                continue;
            }

            // Verify student exists (lookup from eager-loaded map)
            $student = $studentsMap[$studentId] ?? null;
            if (!$student) {
                $errors[] = "Line $lineNum: student_id $studentId not found — skipped.";
                $skipped++;
                continue;
            }

            // Build mark data
            $data = [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'academic_year_id' => $ayId,
                'term_id' => $termId,
                'class_id' => $classId,
                'section_id' => $sectionId,
                'teacher_id' => null,
            ];

            // Load existing record (from eager-loaded map) to preserve other fields
            $existing = $existingMap[$studentId] ?? null;
            if ($existing) {
                foreach ($markFieldCols as $f) {
                    $data[$f] = $existing->$f;
                }
            }

            // Override with imported values (with server-side max enforcement)
            $hasAnyMarkValue = false;
            foreach ($colMap as $idx => $col) {
                $val = isset($row[$idx]) ? trim($row[$idx]) : '';
                if ($val === '') {
                    // Empty cell: preserve existing value, don't overwrite with null
                    // (the previous implementation null'd empty cells, which destroyed
                    // existing marks when the user only wanted to update some columns)
                    if (!isset($data[$col])) $data[$col] = null;
                    continue;
                }
                $hasAnyMarkValue = true;
                // Find field config for max enforcement
                $fieldConfig = null;
                foreach ($markFields as $fc) {
                    if ($fc['col'] === $col) { $fieldConfig = $fc; break; }
                }
                if ($fieldConfig) {
                    $max = floatval($fieldConfig['max']);
                    $v = floatval($val);
                    if ($max > 0 && $v > $max) $v = $max;
                    if ($v < 0) $v = 0;
                    $data[$col] = $v;
                } else {
                    $data[$col] = floatval($val);
                }
            }

            // Skip rows that have no mark values at all (just student_id + name)
            // — this prevents creating empty mark records when the user uploads
            // the template back unchanged.
            if (!$hasAnyMarkValue && !$existing) {
                $skipped++;
                continue;
            }

            // Calculate totals
            $data = MarkEntry::calcTotals($data);
            $data['marks_obtained'] = $data['grand_total'] ?? 0;

            try {
                if ($existing) {
                    $existing->update($data);
                } else {
                    // Fill in class_grade text for new records (section column may not exist)
                    $classRoom = \App\Models\ClassRoom::find($classId);
                    $data['class_grade'] = $classRoom?->name;
                    // NOTE: do NOT set $data['section'] — that column doesn't exist in all
                    // databases (only class_grade was added by migration). section_id is the FK.
                    MarkEntry::create($data);
                }
                $saved++;
            } catch (\Throwable $e) {
                $errors[] = "Line $lineNum (student $studentId): " . $e->getMessage();
                $skipped++;
            }
        }

        $msg = "Imported $saved marks successfully.";
        if ($skipped > 0) {
            $msg .= " Skipped $skipped row(s).";
        }
        if (count($errors) > 0) {
            $msg .= " Errors: " . implode(' | ', array_slice($errors, 0, 10));
            if (count($errors) > 10) $msg .= ' (and ' . (count($errors) - 10) . ' more)';
        }

        if ($wantsJson) {
            return response()->json([
                'success' => $saved > 0,
                'imported' => $saved,
                'skipped'  => $skipped,
                'errors'   => $errors,
                'message'  => $msg,
            ]);
        }
        return back()->with($saved > 0 ? 'success' : 'error', $msg);
    }
}
