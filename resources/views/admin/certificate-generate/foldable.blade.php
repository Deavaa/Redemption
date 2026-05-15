<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <title>Foldable Certificate - {{ $student->first_name }}</title>
    <style>
        @page { size: A5 landscape; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; background: #e5e7eb; }
        .page { width: 210mm; height: 148mm; display: flex; margin: 10px auto; background: #fff; overflow: hidden; }
        .panel { width: 105mm; height: 148mm; display: flex; flex-direction: column; }

        /* PAGE 1 OUTSIDE */
        .back-panel { background: #fff; border-right: 1px solid #ddd; padding: 10mm 9mm; overflow-y: auto; }
        .back-panel h3 { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #4361ee; margin-bottom: 5px; padding-bottom: 3px; border-bottom: 2px solid #4361ee; display: inline-block; }
        .back-panel h4 { font-size: 0.68rem; font-weight: 700; color: #1a1a2e; margin: 7px 0 3px; }
        .back-panel p, .back-panel li { font-size: 0.58rem; color: #4b5563; line-height: 1.55; }
        .back-panel ul { padding-left: 12px; margin-bottom: 4px; }
        .grading-scale { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 0.55rem; }
        .grading-scale th { background: #1a1a2e; color: #fff; padding: 3px 5px; text-align: center; }
        .grading-scale td { padding: 2px 5px; border-bottom: 1px solid #eee; text-align: center; }
        .grading-scale td:first-child { font-weight: 700; text-align: left; }
        .conduct-scale { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 0.55rem; }
        .conduct-scale th { background: #4361ee; color: #fff; padding: 3px 5px; text-align: center; }
        .conduct-scale td { padding: 2px 5px; border-bottom: 1px solid #eee; text-align: center; }

        .front-panel {
            background: linear-gradient(160deg, #1a1a2e 0%, #3a0ca3 50%, #4361ee 100%);
            color: #fff; display: flex; flex-direction: column;
            justify-content: space-between; position: relative;
        }
        .front-panel::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .front-top { position: relative; z-index: 1; text-align: center; padding-top: 18mm; }
        .front-logo { max-height: 55px; max-width: 85px; object-fit: contain; margin: 0 auto 10px; display: block; border-radius: 8px; background: rgba(255,255,255,0.15); padding: 4px; }
        .front-school-name { font-size: 1.1rem; font-weight: 800; letter-spacing: 3px; margin-bottom: 6px; }
        .front-line { width: 45px; height: 2px; background: rgba(255,255,255,0.5); margin: 0 auto 8px; }
        .front-academic-year { font-size: 0.72rem; font-weight: 300; letter-spacing: 2px; opacity: 0.85; }
        .front-cert-title { font-size: 0.6rem; font-weight: 600; letter-spacing: 2px; text-transform: uppercase; margin-top: 8px; opacity: 0.7; }

        .front-bottom {
            position: relative; z-index: 1;
            background: rgba(0,0,0,0.25); padding: 8mm 8mm 10mm;
            border-top: 1px solid rgba(255,255,255,0.15);
        }
        .front-student-name { font-size: 1rem; font-weight: 800; margin-bottom: 6px; }
        .front-info-row { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 3px; }
        .front-info-item { font-size: 0.55rem; opacity: 0.85; }
        .front-info-item strong { opacity: 1; font-weight: 600; }
        .front-status-badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 0.52rem; font-weight: 700; text-transform: uppercase; margin-top: 4px; }
        .front-status-promoted { background: rgba(16,185,129,0.3); color: #6ee7b7; }
        .front-status-detained { background: rgba(220,38,38,0.3); color: #fca5a5; }
        .front-status-conditional { background: rgba(217,119,6,0.3); color: #fcd34d; }
        .front-status-na { background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.7); }

        /* PAGE 2 INSIDE */
        .inside-left { border-right: 2px solid #e5e7eb; padding: 5mm 5mm; overflow-y: auto; }
        .inside-right { padding: 5mm 5mm; display: flex; flex-direction: column; }

        .section-title { font-size: 0.58rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #4361ee; margin-bottom: 4px; padding-bottom: 2px; border-bottom: 2px solid #4361ee; display: inline-block; }

        .marks-table { width: 100%; border-collapse: collapse; font-size: 0.55rem; }
        .marks-table th { background: #1a1a2e; color: #fff; padding: 3px 3px; font-size: 0.48rem; text-transform: uppercase; letter-spacing: 0.3px; text-align: center; }
        .marks-table th:first-child { text-align: left; }
        .marks-table td { padding: 2px 3px; border-bottom: 1px solid #f0f0f0; text-align: center; font-size: 0.55rem; }
        .marks-table td:first-child { text-align: left; font-weight: 600; }
        .marks-table .summary-row { background: #f8f9ff; font-weight: 700; }
        .marks-table .summary-row td { border-top: 2px solid #4361ee; }

        .comment-section { margin-bottom: 6px; }
        .comment-label { font-size: 0.58rem; font-weight: 700; color: #4361ee; margin-bottom: 3px; }
        .comment-box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 6px; background: #fafbfc; min-height: 40px; font-size: 0.62rem; color: #374151; }

        .signatures { display: flex; justify-content: space-between; margin-top: auto; padding-top: 6px; }
        .sig { text-align: center; }
        .sig-line { width: 80px; border-top: 1px solid #333; margin: 20px auto 3px; }
        .sig span { font-size: 0.5rem; color: #6b7280; }

        .no-print { text-align: center; margin: 20px 0; }
        .no-print button { padding: 10px 24px; background: #4361ee; color: #fff; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; margin: 0 5px; }
        .page-break { page-break-before: always; }
        @media print { .no-print { display: none; } body { background: #fff; } .page { margin: 0; box-shadow: none; } }
    </style>
</head>
<body>
    @php
        $logoUrl = \App\Models\Setting::getLogoUrl();
        $schoolName = \App\Models\Setting::get('school_name', 'School of Redemption');

        // Get ALL subjects assigned to this student's class
        $classId = $student->class_id ?? $student->classroom_id ?? null;
        $academicYearId = $student->academic_year_id ?? 1;
        $assignedSubjects = DB::table('teacher_assignments')
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->pluck('subject_id')
            ->unique()
            ->toArray();

        // If no teacher_assignments found, fall back to subjects from marks
        if(empty($assignedSubjects)) {
            $assignedSubjects = $marks->pluck('subject_id')->unique()->toArray();
        }

        // Get all subjects
        $allSubjects = DB::table('subjects')->whereIn('id', $assignedSubjects)->orderBy('name')->get();

        // Get terms
        $terms = DB::table('terms')->orderBy('id')->get();
        $term1Id = $terms->where('name', 'like', '%1%')->first()->id ?? ($terms->first()->id ?? 1);
        $term2Id = $terms->where('name', 'like', '%2%')->first()->id ?? ($terms->count() > 1 ? $terms->skip(1)->first()->id : $term1Id);

        // Build marks data: subject => [term1, term2, yearly]
        $markData = [];
        $totalTerm1 = 0; $totalTerm2 = 0; $totalYearly = 0;
        $countWithMarks = 0;

        foreach($allSubjects as $subject) {
            $t1 = $marks->where('subject_id', $subject->id)->where('term_id', $term1Id)->first();
            $t2 = $marks->where('subject_id', $subject->id)->where('term_id', $term2Id)->first();

            $t1Score = $t1 ? ($t1->grand_total ?? ($t1->exam_total ?? ($t1->marks_obtained ?? null))) : null;
            $t2Score = $t2 ? ($t2->grand_total ?? ($t2->exam_total ?? ($t2->marks_obtained ?? null))) : null;

            // Yearly = average of term1 and term2, or just the available term
            if($t1Score !== null && $t2Score !== null) {
                $yearly = round(($t1Score + $t2Score) / 2, 1);
            } elseif($t1Score !== null) {
                $yearly = $t1Score;
            } elseif($t2Score !== null) {
                $yearly = $t2Score;
            } else {
                $yearly = null;
            }

            $grade = '-';
            if($yearly !== null) {
                $grade = $yearly >= 90 ? 'A+' : ($yearly >= 80 ? 'A' : ($yearly >= 75 ? 'B+' : ($yearly >= 70 ? 'B' : ($yearly >= 65 ? 'C+' : ($yearly >= 60 ? 'C' : ($yearly >= 50 ? 'D' : 'F'))))));
            }

            $markData[] = [
                'name' => $subject->name,
                'term1' => $t1Score,
                'term2' => $t2Score,
                'yearly' => $yearly,
                'grade' => $grade,
            ];

            if($t1Score !== null) { $totalTerm1 += $t1Score; }
            if($t2Score !== null) { $totalTerm2 += $t2Score; }
            if($yearly !== null) { $totalYearly += $yearly; $countWithMarks++; }
        }

        $avgYearly = $countWithMarks > 0 ? round($totalYearly / $countWithMarks, 1) : 0;
        $overallGrade = $avgYearly >= 90 ? 'A+' : ($avgYearly >= 80 ? 'A' : ($avgYearly >= 75 ? 'B+' : ($avgYearly >= 70 ? 'B' : ($avgYearly >= 65 ? 'C+' : ($avgYearly >= 60 ? 'C' : ($avgYearly >= 50 ? 'D' : 'F'))))));
        $overallStatus = $avgYearly >= 50 ? 'PASS' : 'FAIL';

        // Conduct from marks
        $conduct = $marks->first()->conduct ?? '-';

        // Promotion status
        $promotionStatus = 'N/A';
        $promoClass = '';
        try {
            if (Schema::hasTable('promotion_results')) {
                $promoResult = DB::table('promotion_results')->where('student_id', $student->id)->orderBy('id', 'desc')->first();
                if($promoResult) {
                    $promotionStatus = $promoResult->status;
                    if($promoResult->to_class_id) {
                        $promoClass = DB::table('classes')->where('id', $promoResult->to_class_id)->value('name') ?? '';
                    }
                }
            }
        } catch (\Throwable $e) {
            // Table doesn't exist yet — promotion status stays N/A
        }
        $statusClass = $promotionStatus === 'promoted' ? 'promoted' : ($promotionStatus === 'detained' ? 'detained' : ($promotionStatus === 'conditional' ? 'conditional' : 'na'));

        // Teacher comments per term
        $t1Comment = $marks->where('term_id', $term1Id)->first()->remarks ?? null;
        $t2Comment = $marks->where('term_id', $term2Id)->first()->remarks ?? null;
    @endphp

    <div class="no-print">
        <button onclick="window.print()">Print Foldable Certificate</button>
        <button onclick="history.back()">Back</button>
    </div>

    <!-- PAGE 1: OUTSIDE - Back (left: grading rules) + Front (right: cover with student info) -->
    <div class="page">
        <div class="panel back-panel">
            <h3>Grading &amp; Assessment Policy</h3>

            <h4>1. Academic Grading Scale</h4>
            <p>Student performance is evaluated using the following grading scale. Each grade corresponds to a specific range of percentage scores and carries a designated grade point value used for computing cumulative averages.</p>
            <table class="grading-scale">
                <thead><tr><th>Grade</th><th>Score Range</th><th>Point</th><th>Description</th></tr></thead>
                <tbody>
                    <tr><td>A+</td><td>90 - 100</td><td>4.0</td><td>Excellent</td></tr>
                    <tr><td>A</td><td>80 - 89</td><td>3.5</td><td>Very Good</td></tr>
                    <tr><td>B+</td><td>75 - 79</td><td>3.0</td><td>Good</td></tr>
                    <tr><td>B</td><td>70 - 74</td><td>2.5</td><td>Fairly Good</td></tr>
                    <tr><td>C+</td><td>65 - 69</td><td>2.0</td><td>Above Average</td></tr>
                    <tr><td>C</td><td>60 - 64</td><td>1.5</td><td>Average</td></tr>
                    <tr><td>D</td><td>50 - 59</td><td>1.0</td><td>Below Average</td></tr>
                    <tr><td>F</td><td>0 - 49</td><td>0.0</td><td>Fail</td></tr>
                </tbody>
            </table>

            <h4>2. Marking Composition</h4>
            <ul>
                <li><strong>Continuous Assessment (CA):</strong> Comprises class tests, quizzes, homework, projects, and class participation.</li>
                <li><strong>Mid-Term Examination:</strong> A comprehensive assessment administered at the midpoint of each term.</li>
                <li><strong>Final Examination:</strong> A summative assessment at the end of each term evaluating the full term curriculum.</li>
            </ul>

            <h4>3. Behavioral Assessment</h4>
            <p>Behavioral and character development is assessed alongside academic performance:</p>
            <table class="conduct-scale">
                <thead><tr><th>Rating</th><th>Score</th><th>Description</th></tr></thead>
                <tbody>
                    <tr><td>Excellent</td><td>5</td><td>Outstanding conduct, exemplary behavior</td></tr>
                    <tr><td>Very Good</td><td>4</td><td>Consistently well-behaved, respectful</td></tr>
                    <tr><td>Good</td><td>3</td><td>Generally well-mannered, follows rules</td></tr>
                    <tr><td>Fair</td><td>2</td><td>Needs improvement, frequent reminders</td></tr>
                    <tr><td>Poor</td><td>1</td><td>Consistently disruptive, intervention needed</td></tr>
                </tbody>
            </table>

            <h4>4. Promotion Policy</h4>
            <ul>
                <li><strong>Promoted:</strong> Overall average of 50% or above with minimum passing grades in core subjects.</li>
                <li><strong>Conditionally Promoted:</strong> Average between 40%-49% or fails no more than 2 subjects.</li>
                <li><strong>Detained:</strong> Average below 40% or fails more than 2 subjects. Student must repeat the grade.</li>
            </ul>

            <h4>5. Attendance Requirement</h4>
            <p>Students must maintain a minimum attendance rate of 75% throughout the academic year. Failure to meet this requirement may affect promotion eligibility regardless of academic performance.</p>
        </div>

        <div class="panel front-panel">
            <div class="front-top">
                @if($logoUrl)<img src="{{ $logoUrl }}" class="front-logo" alt="Logo">@endif
                <div class="front-school-name">{{ strtoupper($schoolName) }}</div>
                <div class="front-line"></div>
                <div class="front-academic-year">{{ $student->academicYear->name ?? date('Y') }}</div>
                <div class="front-cert-title">Student Report Card</div>
            </div>

            <div class="front-bottom">
                <div class="front-student-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                <div class="front-info-row">
                    <div class="front-info-item"><strong>Class:</strong> {{ $student->classroom->name ?? '-' }}</div>
                    <div class="front-info-item"><strong>Section:</strong> {{ $student->section->name ?? '-' }}</div>
                    <div class="front-info-item"><strong>Roll No:</strong> {{ $student->roll_number }}</div>
                </div>
                <div class="front-info-row">
                    <div class="front-info-item"><strong>Admission No:</strong> {{ $student->admission_number }}</div>
                    <div class="front-info-item"><strong>Gender:</strong> {{ $student->gender ?? '-' }}</div>
                    <div class="front-info-item"><strong>DOB:</strong> {{ $student->date_of_birth ?? '-' }}</div>
                </div>
                <div class="front-status-badge front-status-{{ $statusClass }}">
                    @if($promotionStatus !== 'N/A')
                        {{ ucfirst($promotionStatus) }}{{ ($promoClass ?? '') ? ' to ' . $promoClass : '' }}
                    @else
                        Result Pending
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 2: INSIDE - Left (marks table with ALL subjects) + Right (semester comments + signatures) -->
    <div class="page page-break">
        <div class="panel inside-left">
            <div class="section-title">Academic Results</div>
            <table class="marks-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Term 1</th>
                        <th>Term 2</th>
                        <th>Yearly</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($markData as $md)
                    <tr>
                        <td>{{ $md['name'] }}</td>
                        <td>{{ $md['term1'] ?? '-' }}</td>
                        <td>{{ $md['term2'] ?? '-' }}</td>
                        <td><strong>{{ $md['yearly'] ?? '-' }}</strong></td>
                        <td>{{ $md['grade'] }}</td>
                    </tr>
                    @endforeach
                    <tr class="summary-row">
                        <td>Total</td>
                        <td>{{ $totalTerm1 ?: '-' }}</td>
                        <td>{{ $totalTerm2 ?: '-' }}</td>
                        <td>{{ $totalYearly ?: '-' }}</td>
                        <td>-</td>
                    </tr>
                    <tr class="summary-row">
                        <td>Average</td>
                        <td>{{ $countWithMarks > 0 ? round($totalTerm1 / $countWithMarks, 1) : '-' }}</td>
                        <td>{{ $countWithMarks > 0 ? round($totalTerm2 / $countWithMarks, 1) : '-' }}</td>
                        <td>{{ $avgYearly }}</td>
                        <td>{{ $overallGrade }}</td>
                    </tr>
                    <tr class="summary-row">
                        <td>Rank</td>
                        <td colspan="4">-</td>
                    </tr>
                    <tr class="summary-row">
                        <td>Conduct</td>
                        <td colspan="3">{{ $conduct }}/5</td>
                        <td>{{ is_numeric($conduct) && $conduct >= 4 ? 'Very Good' : (is_numeric($conduct) && $conduct >= 3 ? 'Good' : 'Fair') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="panel inside-right">
            <div class="comment-section">
                <div class="comment-label">Semester 1 - Homeroom Teacher's Comment</div>
                <div class="comment-box">
                    @if($t1Comment)
                        {{ $t1Comment }}
                    @else
                        <p>{{ $student->first_name }} has shown {{ $avgYearly >= 70 ? 'excellent' : ($avgYearly >= 50 ? 'good' : 'below average') }} performance in the first semester. {{ $avgYearly >= 70 ? 'Continue to encourage this level of dedication.' : ($avgYearly >= 50 ? 'There is room for improvement. Additional focus will help.' : 'Significant improvement is needed. Please provide extra support at home.') }}</p>
                    @endif
                </div>
            </div>

            <div class="comment-section">
                <div class="comment-label">Semester 2 - Homeroom Teacher's Comment</div>
                <div class="comment-box">
                    @if($t2Comment)
                        {{ $t2Comment }}
                    @else
                        <p>{{ $student->first_name }} has shown {{ $avgYearly >= 70 ? 'excellent' : ($avgYearly >= 50 ? 'good' : 'below average') }} performance in the second semester. {{ $promotionStatus === 'promoted' ? 'I am pleased to recommend this student for promotion.' : ($promotionStatus === 'detained' ? 'Unfortunately, this student will need to repeat this class.' : 'This student is conditionally promoted and must improve.') }}</p>
                    @endif
                </div>
            </div>

            <div class="signatures">
                <div class="sig"><div class="sig-line"></div><span>Homeroom Teacher</span></div>
                <div class="sig"><div class="sig-line"></div><span>Principal</span></div>
                <div class="sig"><div class="sig-line"></div><span>Parent/Guardian</span></div>
            </div>
        </div>
    </div>
</body>
</html>
