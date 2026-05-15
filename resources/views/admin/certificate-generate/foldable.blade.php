<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <title>Foldable Certificate - {{ $student->first_name }}</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Segoe UI', Arial, sans-serif; background: #e5e7eb; }

        /* Each page = A4 landscape, split EXACTLY 50/50 at 148.5mm for perfect fold */
        .page {
            width: 297mm; height: 210mm; display: flex; margin: 10px auto;
            background: #fff; overflow: hidden;
        }
        .panel {
            width: 50%; height: 210mm; display: flex; flex-direction: column;
        }

        /* ===== PAGE 1 OUTSIDE: LEFT = Grading Info, RIGHT = Cover ===== */
        .back-panel {
            background: #fff; padding: 7mm 7mm; overflow-y: auto;
            border-right: 2px dashed #bbb;
        }
        .back-panel h3 {
            font-size: 0.95rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1.2px; color: #2d2d3a; margin-bottom: 6px;
            padding-bottom: 4px; border-bottom: 2.5px solid #2d2d3a; display: inline-block;
        }
        .back-panel h4 { font-size: 0.8rem; font-weight: 700; color: #1a1a2e; margin: 8px 0 3px; }
        .back-panel p, .back-panel li { font-size: 0.7rem; color: #4b5563; line-height: 1.5; }
        .back-panel ul { padding-left: 14px; margin-bottom: 4px; }
        .grading-scale { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 0.72rem; }
        .grading-scale th { border-bottom: 2px solid #2d2d3a; color: #2d2d3a; padding: 5px 6px; text-align: center; font-size: 0.68rem; font-weight: 700; }
        .grading-scale td { padding: 4px 6px; border-bottom: 1px solid #eee; text-align: center; font-size: 0.72rem; }
        .grading-scale td:first-child { font-weight: 700; text-align: left; }
        .conduct-scale { width: 100%; border-collapse: collapse; margin: 4px 0; font-size: 0.72rem; }
        .conduct-scale th { border-bottom: 2px solid #2d2d3a; color: #2d2d3a; padding: 5px 6px; text-align: center; font-size: 0.68rem; font-weight: 700; }
        .conduct-scale td { padding: 4px 6px; border-bottom: 1px solid #eee; text-align: center; font-size: 0.72rem; }

        /* FRONT COVER PANEL */
        .front-panel {
            background: #fff; color: #2d2d3a; display: flex; flex-direction: column;
            justify-content: space-between; position: relative;
            border-left: 3px solid #2d2d3a;
        }
        .front-top { position: relative; z-index: 1; text-align: center; padding-top: 22mm; }
        .front-logo { max-height: 95px; max-width: 130px; object-fit: contain; margin: 0 auto 18px; display: block; border-radius: 12px; border: 2px solid #2d2d3a; padding: 6px; }
        .front-school-name { font-size: 1.85rem; font-weight: 800; letter-spacing: 3.5px; margin-bottom: 6px; }
        .front-school-name-am { font-size: 1.4rem; font-weight: 700; letter-spacing: 1.5px; margin-bottom: 12px; color: #4b5563; }
        .front-line { width: 75px; height: 0; border-top: 2px solid #2d2d3a; margin: 0 auto 14px; }
        .front-academic-year { font-size: 1.15rem; font-weight: 300; letter-spacing: 2.5px; color: #4b5563; }
        .front-cert-title { font-size: 1.05rem; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; margin-top: 14px; color: #6b7280; }
        .front-cert-title-am { font-size: 0.95rem; font-weight: 500; margin-top: 5px; color: #9ca3af; }

        .front-bottom {
            position: relative; z-index: 1;
            background: #fff; padding: 12mm 14mm 18mm;
            border-top: 2px solid #2d2d3a;
        }
        .front-student-name { font-size: 1.6rem; font-weight: 800; margin-bottom: 12px; color: #1a1a2e; }
        .front-info-row { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 9px; }
        .front-info-item { font-size: 0.9rem; color: #4b5563; }
        .front-info-item strong { color: #1a1a2e; font-weight: 600; }
        .front-status-badge { display: inline-block; padding: 6px 16px; border-radius: 5px; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; margin-top: 10px; border: 1.5px solid; }
        .front-status-promoted { border-color: #10b981; color: #059669; }
        .front-status-detained { border-color: #ef4444; color: #dc2626; }
        .front-status-conditional { border-color: #f59e0b; color: #d97706; }
        .front-status-na { border-color: #9ca3af; color: #6b7280; }

        /* ===== PAGE 2 INSIDE: LEFT = Marks Table, RIGHT = Comments + Signatures ===== */
        .inside-left {
            border-right: 2px dashed #e5e7eb; padding: 10mm 10mm; overflow-y: auto;
        }
        .inside-right {
            padding: 10mm 10mm; display: flex; flex-direction: column;
        }

        .section-title {
            font-size: 1.05rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1.5px; color: #2d2d3a; margin-bottom: 5px;
            padding-bottom: 5px; border-bottom: 3px solid #2d2d3a; display: inline-block;
        }
        .section-title-am {
            font-size: 0.9rem; font-weight: 600; color: #6b7280;
            margin-bottom: 12px; display: block;
        }

        .marks-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .marks-table th {
            border-bottom: 2px solid #2d2d3a; color: #2d2d3a; padding: 9px 9px;
            font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px; text-align: center; font-weight: 700;
        }
        .marks-table th:first-child { text-align: left; }
        .marks-table td { padding: 8px 9px; border-bottom: 1px solid #f0f0f0; text-align: center; font-size: 0.9rem; }
        .marks-table td:first-child { text-align: left; font-weight: 600; }
        .marks-table .summary-row { font-weight: 700; }
        .marks-table .summary-row td { border-top: 2px solid #2d2d3a; }

        .comment-section { margin-bottom: 18px; }
        .comment-label { font-size: 0.98rem; font-weight: 700; color: #2d2d3a; margin-bottom: 6px; }
        .comment-label-am { font-size: 0.82rem; font-weight: 500; color: #6b7280; margin-bottom: 6px; }
        .comment-box {
            border: 1px solid #2d2d3a; border-radius: 6px; padding: 12px;
            min-height: 65px; font-size: 0.92rem; color: #374151; line-height: 1.7;
        }

        .overall-summary {
            border: 2px solid #2d2d3a; border-radius: 8px;
            padding: 14px 18px; margin-bottom: 18px;
        }
        .overall-summary h4 {
            font-size: 1.05rem; color: #1a1a2e; margin-bottom: 8px;
        }
        .overall-row { display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 5px; }
        .overall-row strong { color: #1a1a2e; }

        .signatures { display: flex; justify-content: space-between; margin-top: auto; padding-top: 18px; }
        .sig { text-align: center; }
        .sig-line { width: 110px; border-top: 2px solid #333; margin: 30px auto 6px; }
        .sig span { font-size: 0.78rem; color: #6b7280; }

        .no-print { text-align: center; margin: 20px 0; }
        .no-print button { padding: 10px 24px; background: #2d2d3a; color: #fff; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; margin: 0 5px; }
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
            <h3>Grading &amp; Assessment Policy<br><span style="font-weight:600;font-size:0.72rem;color:#6b7280;">የውጤት ምዘና ፖሊሲ</span></h3>

            <h4>1. Academic Grading Scale / የአካዳሚክ ውጤት ደረጃ</h4>
            <p>Student performance is evaluated using the following grading scale. / የተማሪዎች ውጤት በሚከተለው የውጤት ደረጃ መሰረት ይገመገማል።</p>
            <table class="grading-scale">
                <thead><tr><th>Grade / ደረጃ</th><th>Score / ውጤት</th><th>Point / ነጥብ</th><th>Description / መግለጫ</th></tr></thead>
                <tbody>
                    <tr><td>A+</td><td>90 - 100</td><td>4.0</td><td>Excellent / በጣም ብሩህ</td></tr>
                    <tr><td>A</td><td>80 - 89</td><td>3.5</td><td>Very Good / በጣም ጥሩ</td></tr>
                    <tr><td>B+</td><td>75 - 79</td><td>3.0</td><td>Good / ጥሩ</td></tr>
                    <tr><td>B</td><td>70 - 74</td><td>2.5</td><td>Fairly Good / ተቀባይነት ያለው</td></tr>
                    <tr><td>C+</td><td>65 - 69</td><td>2.0</td><td>Above Average / ከመካከለኛ በላይ</td></tr>
                    <tr><td>C</td><td>60 - 64</td><td>1.5</td><td>Average / መካከለኛ</td></tr>
                    <tr><td>D</td><td>50 - 59</td><td>1.0</td><td>Below Average / ከመካከለኛ በታች</td></tr>
                    <tr><td>F</td><td>0 - 49</td><td>0.0</td><td>Fail / ያልተሳካ</td></tr>
                </tbody>
            </table>

            <h4>2. Behavioral Assessment / የባህሪ ምዘና</h4>
            <p>Behavior is assessed using the following 5-point scale. / የባህሪ ምዘና በሚከተለው 5-ነጥብ ሚዛን ይገመገማል።</p>
            <table class="conduct-scale">
                <thead><tr><th>Rating / ደረጃ</th><th>Score / ነጥብ</th><th>Description / መግለጫ</th></tr></thead>
                <tbody>
                    <tr><td>Excellent / በጣም ጥሩ</td><td>5</td><td>Outstanding conduct, exemplary behavior / በጣም ጥሩ ባህሪ፣ አርአያ ባህሪ</td></tr>
                    <tr><td>Very Good / በጣም ጥሩ</td><td>4</td><td>Consistently well-behaved, respectful / በየጊዜው ጥሩ ባህሪ፣ አክባሪ</td></tr>
                    <tr><td>Good / ጥሩ</td><td>3</td><td>Generally well-mannered, follows rules / በአጠቃላይ ጥሩ ባህሪ፣ ህግ ተከታይ</td></tr>
                    <tr><td>Fair / መካከለኛ</td><td>2</td><td>Needs improvement, frequent reminders / ማሻሻያ ይፈለጋል፣ ተደጋጋሚ ማስታወሻ</td></tr>
                    <tr><td>Poor / ደካማ</td><td>1</td><td>Consistently disruptive, intervention needed / በየጊዜው መበከል፣ ጣልቃ መግባት ይፈለጋል</td></tr>
                </tbody>
            </table>
        </div>

        <div class="panel front-panel" style="position:relative;">
            {{-- Logo Watermark for front panel --}}
            @if($logoUrl)
            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:280px;height:280px;opacity:0.06;pointer-events:none;z-index:0;">
                <img src="{{ $logoUrl }}" style="width:100%;height:100%;object-fit:contain;" alt="">
            </div>
            @endif
            <div class="front-top" style="position:relative;z-index:1;">
                @if($logoUrl)<img src="{{ $logoUrl }}" class="front-logo" alt="Logo">@endif
                <div class="front-school-name">{{ strtoupper($schoolName) }}</div>
                <div class="front-school-name-am">የትምህርት ቤቱ ስም</div>
                <div class="front-line"></div>
                <div class="front-academic-year">{{ $student->academicYear->name ?? date('Y') }}</div>
                <div class="front-cert-title">Student Report Card</div>
                <div class="front-cert-title-am">የተማሪ ውጤት ካርድ</div>
            </div>

            <div class="front-bottom">
                <div class="front-student-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                <div class="front-info-row">
                    <div class="front-info-item"><strong>Class / ክፍል:</strong> {{ $student->classroom->name ?? '-' }}</div>
                    <div class="front-info-item"><strong>Section / መደብ:</strong> {{ $student->section->name ?? '-' }}</div>
                    <div class="front-info-item"><strong>Roll No / ቁጥር:</strong> {{ $student->roll_number }}</div>
                </div>
                <div class="front-info-row">
                    <div class="front-info-item"><strong>Admission No / የመግቢያ ቁጥር:</strong> {{ $student->admission_number }}</div>
                    <div class="front-info-item"><strong>Gender / ጾታ:</strong> {{ $student->gender ?? '-' }}</div>
                    <div class="front-info-item"><strong>DOB / የልደት ቀን:</strong> {{ $student->date_of_birth ?? '-' }}</div>
                </div>
                <div class="front-status-badge front-status-{{ $statusClass }}">
                    @if($promotionStatus !== 'N/A')
                        {{ ucfirst($promotionStatus) }}{{ ($promoClass ?? '') ? ' to ' . $promoClass : '' }}
                    @else
                        Result Pending / ውጤት መጠባበቅ ላይ
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- PAGE 2: INSIDE - Left (marks table with ALL subjects) + Right (comments + overall + signatures) -->
    <div class="page page-break" style="position:relative;">
        {{-- Logo Watermark for inside page --}}
        @if($logoUrl)
        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:320px;height:320px;opacity:0.05;pointer-events:none;z-index:0;">
            <img src="{{ $logoUrl }}" style="width:100%;height:100%;object-fit:contain;" alt="">
        </div>
        @endif
        <div class="panel inside-left">
            <div class="section-title">Academic Results</div>
            <div class="section-title-am">የአካዳሚክ ውጤቶች</div>
            <table class="marks-table">
                <thead>
                    <tr>
                        <th>Subject / ስም</th>
                        <th>Term 1<br>ወር 1</th>
                        <th>Term 2<br>ወር 2</th>
                        <th>Yearly<br>ዓመታዊ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($markData as $md)
                    <tr>
                        <td>{{ $md['name'] }}</td>
                        <td>{{ $md['term1'] ?? '-' }}</td>
                        <td>{{ $md['term2'] ?? '-' }}</td>
                        <td><strong>{{ $md['yearly'] ?? '-' }}</strong></td>
                    </tr>
                    @endforeach
                    <tr class="summary-row">
                        <td>Total / ጠቅላላ</td>
                        <td>{{ $totalTerm1 ?: '-' }}</td>
                        <td>{{ $totalTerm2 ?: '-' }}</td>
                        <td>{{ $totalYearly ?: '-' }}</td>
                    </tr>
                    <tr class="summary-row">
                        <td>Average / አማካይ</td>
                        <td>{{ $countWithMarks > 0 ? round($totalTerm1 / $countWithMarks, 1) : '-' }}</td>
                        <td>{{ $countWithMarks > 0 ? round($totalTerm2 / $countWithMarks, 1) : '-' }}</td>
                        <td>{{ $avgYearly }}</td>
                    </tr>
                    <tr class="summary-row">
                        <td>Rank / ደረጃ</td>
                        <td colspan="3">-</td>
                    </tr>
                    <tr class="summary-row">
                        <td>Conduct / ባህሪ</td>
                        <td colspan="3">{{ $conduct }}/5</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="panel inside-right">
            <!-- Overall Summary Box -->
            <div class="overall-summary">
                <h4>Overall Summary / ጠቅላላ ማጠቃለያ</h4>
                <div class="overall-row"><span>Overall Average / አማካይ ውጤት:</span> <strong>{{ $avgYearly }}%</strong></div>
                <div class="overall-row"><span>Overall Grade / ደረጃ:</span> <strong>{{ $overallGrade }}</strong></div>
                <div class="overall-row"><span>Status / ሁኔታ:</span> <strong>{{ $overallStatus }}</strong></div>
                <div class="overall-row"><span>Conduct / ባህሪ:</span> <strong>{{ $conduct }}/5 ({{ is_numeric($conduct) && $conduct >= 4 ? 'Very Good / በጣም ጥሩ' : (is_numeric($conduct) && $conduct >= 3 ? 'Good / ጥሩ' : 'Fair / መካከለኛ') }})</strong></div>
                <div class="overall-row"><span>Subjects Passed / የተሳኩ ዘርፎች:</span> <strong>{{ collect($markData)->where('yearly', '>=', 50)->count() }} / {{ count($markData) }}</strong></div>
            </div>

            <div class="comment-section">
                <div class="comment-label">Semester 1 - Homeroom Teacher's Comment</div>
                <div class="comment-label-am">የ1ኛ ሴሚስተር - የክፍል መምህር አስተያየት</div>
                <div class="comment-box">
                    @if($t1Comment)
                        {{ $t1Comment }}
                    @else
                        {{ $student->first_name }} has shown {{ $avgYearly >= 70 ? 'excellent / በጣም ጥሩ' : ($avgYearly >= 50 ? 'good / ጥሩ' : 'below average / ከመካከለኛ በታች') }} performance in the first semester. {{ $avgYearly >= 70 ? 'Continue to encourage this level of dedication. / ይህን የትጋት ደረጃ ማስቀጠል ይቻላል።' : ($avgYearly >= 50 ? 'There is room for improvement. Additional focus will help. / ማሻሻያ አለ። ተጨማሪ ትኩረት ይረዳል።' : 'Significant improvement is needed. Please provide extra support at home. / ከፍተኛ ማሻሻያ ይፈለጋል። እባክዎ በቤት ተጨማሪ ድጋፍ ያድርጉ።') }}
                    @endif
                </div>
            </div>

            <div class="comment-section">
                <div class="comment-label">Semester 2 - Homeroom Teacher's Comment</div>
                <div class="comment-label-am">የ2ኛ ሴሚስተር - የክፍል መምህር አስተያየት</div>
                <div class="comment-box">
                    @if($t2Comment)
                        {{ $t2Comment }}
                    @else
                        {{ $student->first_name }} has shown {{ $avgYearly >= 70 ? 'excellent / በጣም ጥሩ' : ($avgYearly >= 50 ? 'good / ጥሩ' : 'below average / ከመካከለኛ በታች') }} performance in the second semester. {{ $promotionStatus === 'promoted' ? 'I am pleased to recommend this student for promotion. / ይህን ተማሪ ለማስተማር መመረካችን አለ።' : ($promotionStatus === 'detained' ? 'Unfortunately, this student will need to repeat this class. / ለማዘንበት ይህ ተማሪ ክፍሉን መድገም አለበት።' : 'This student is conditionally promoted and must improve. / ይህ ተማሪ በሁኔታ የተማረከ ሲሆን ማሻሻል አለበት።') }}
                    @endif
                </div>
            </div>

            <div class="signatures">
                <div class="sig"><div class="sig-line"></div><span>Homeroom Teacher<br>የክፍል መምህር</span></div>
                <div class="sig"><div class="sig-line"></div><span>Principal / ዳይሬክተር</span></div>
                <div class="sig"><div class="sig-line"></div><span>Parent/Guardian<br>ወላጅ/አሳዳጊ</span></div>
            </div>
        </div>
    </div>
</body>
</html>
