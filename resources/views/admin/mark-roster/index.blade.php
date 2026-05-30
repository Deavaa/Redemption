@extends('layouts.admin')
@section('title', 'Mark Roster')

@push('styles')
<style>
.mr-page{animation:mrIn .4s ease-out}
@keyframes mrIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.mr-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.mr-header-left{flex:1}
.mr-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.mr-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.mr-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.mr-breadcrumb li{color:#adb5bd}
.mr-breadcrumb li a{color:#6c757d;text-decoration:none;transition:color .2s}
.mr-breadcrumb li a:hover{color:#4361ee}
.mr-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}
.mr-breadcrumb li.active{color:#4361ee;font-weight:500}

.mr-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.5rem}
.mr-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.mr-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.mr-card-icon.blue{background:#eef2ff;color:#4361ee}
.mr-card-icon.green{background:#ecfdf5;color:#10b981}
.mr-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.mr-card-desc{font-size:.82rem;color:#9ca3af;margin:.1rem 0 0}
.mr-card-body{padding:1.25rem 1.5rem}
.mr-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
.mr-group{display:flex;flex-direction:column}
.mr-label{font-weight:600;color:#374151;margin-bottom:.4rem;font-size:.85rem}
.mr-select{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.6rem 2.2rem .6rem .8rem;font-size:.88rem;color:#1a1a2e;background:#fff;appearance:none;cursor:pointer;transition:all .2s;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .6rem center;background-repeat:no-repeat;background-size:1.15rem}
.mr-select:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.mr-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:10px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3)}
.mr-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4)}
.mr-btn-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none}
.mr-btn-outline:hover{border-color:#4361ee;color:#4361ee;background:#f8f9ff;transform:none;box-shadow:none}
.mr-actions{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid #f0f0f0;background:#fafbfc}

/* Subject Section */
.mr-subject-section{margin-bottom:2rem}
.mr-subject-section:last-child{margin-bottom:0}
.mr-subject-head{display:flex;align-items:center;gap:.75rem;padding:.85rem 1.5rem;border-radius:14px 14px 0 0;color:#fff;font-size:1.05rem;font-weight:800}
.mr-subject-head .subj-icon{font-size:1.1rem}
.mr-subject-head .subj-badge{font-size:.72rem;background:rgba(255,255,255,.2);padding:.1rem .5rem;border-radius:5px;margin-left:auto}

/* Alternate subject colors */
.mr-subject-head.s0{background:linear-gradient(135deg,#4361ee,#3b82f6)}
.mr-subject-head.s1{background:linear-gradient(135deg,#8b5cf6,#7c3aed)}
.mr-subject-head.s2{background:linear-gradient(135deg,#10b981,#059669)}
.mr-subject-head.s3{background:linear-gradient(135deg,#f59e0b,#d97706)}
.mr-subject-head.s4{background:linear-gradient(135deg,#ef4444,#dc2626)}
.mr-subject-head.s5{background:linear-gradient(135deg,#06b6d4,#0891b2)}
.mr-subject-head.s6{background:linear-gradient(135deg,#ec4899,#db2777)}
.mr-subject-head.s7{background:linear-gradient(135deg,#6366f1,#4f46e5)}
.mr-subject-head.s8{background:linear-gradient(135deg,#14b8a6,#0d9488)}
.mr-subject-head.s9{background:linear-gradient(135deg,#f97316,#ea580c)}

/* Roster Table */
.mr-table-wrap{overflow-x:auto}
.mr-table{width:100%;border-collapse:collapse;font-size:.78rem}
.mr-table th{padding:.45rem .3rem;border:1px solid #e5e7eb;white-space:nowrap;text-align:center;font-weight:700;position:sticky;top:0}
.mr-table td{padding:.4rem .3rem;border:1px solid #e5e7eb;text-align:center}
.mr-table tbody tr:nth-child(even){background:#f9fafb}
.mr-table tbody tr:hover{background:#eef2ff}
.mr-table .stu-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e;min-width:130px;position:sticky;left:0;z-index:2;background:inherit}
.mr-table .stu-serial{font-weight:600;color:#6b7280;position:sticky;left:0;z-index:2;background:inherit;min-width:32px}

/* ── Rotated column headers ── */
.mr-table .rot-th{
    writing-mode:vertical-rl;
    transform:rotate(180deg);
    height:90px;
    min-width:28px;
    max-width:32px;
    padding:4px 2px;
    vertical-align:bottom;
    font-size:.7rem;
    line-height:1.1;
    letter-spacing:.3px;
}
.mr-table .rot-th small{
    font-weight:400;
    opacity:.65;
    font-size:.6rem;
    display:block;
    margin-top:2px;
}

/* Group header rows */
.mr-table .group-ca{background:#eff6ff;color:#1e40af;font-size:.78rem;letter-spacing:.5px}
.mr-table .group-exam{background:#fef3c7;color:#92400e;font-size:.78rem;letter-spacing:.5px}
.mr-table .group-ca th{background:#dbeafe;border-bottom:2px solid #93c5fd}
.mr-table .group-exam th{background:#fde68a;border-bottom:2px solid #fbbf24}

/* CA columns */
.mr-table .ca-col{background:#f8fbff}
.mr-table .ca-total-col{background:#dbeafe;font-weight:700;color:#1e40af}

/* Exam columns */
.mr-table .exam-col{background:#fffdf5}
.mr-table .exam-total-col{background:#fde68a;font-weight:700;color:#92400e}

/* Result columns */
.mr-table .grand-total-col{background:#d1fae5;font-weight:800;color:#065f46}
.mr-table .grade-col{font-weight:800}
.mr-table .grade-col.g-a{color:#059669}
.mr-table .grade-col.g-b{color:#2563eb}
.mr-table .grade-col.g-c{color:#d97706}
.mr-table .grade-col.g-d{color:#ea580c}
.mr-table .grade-col.g-f{color:#dc2626}

/* Average row */
.mr-table .avg-row td{background:#f0f4ff!important;font-weight:700;color:#4338ca;border-top:2px solid #6366f1}
.mr-table .avg-row .stu-name{background:#f0f4ff!important;color:#4338ca;position:sticky;left:0;z-index:2}
.mr-table .avg-row .stu-serial{background:#f0f4ff!important;color:#4338ca;position:sticky;left:0;z-index:2}

/* No data */
.mr-empty{text-align:center;padding:3rem 1rem;color:#9ca3af}
.mr-empty i{font-size:2.5rem;margin-bottom:.75rem;display:block}
.mr-empty p{margin:0;font-size:.95rem}

/* Print styles — each subject on its own page */
@media print{
    .print-only { display: block !important; }
    .mr-header,.mr-filter-card,.mr-actions,.mr-btn{display:none!important}
    .mr-page{animation:none!important}
    .mr-subject-section{page-break-after:always;break-after:page}
    .mr-subject-section:last-child{page-break-after:auto;break-after:auto}
    .mr-subject-head{-webkit-print-color-adjust:exact;print-color-adjust:exact;border-radius:0!important}
    .mr-table{font-size:7pt}
    .mr-table th{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .mr-table .rot-th{height:70px;font-size:6pt}
    .group-ca th,.group-exam th{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .avg-row td{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .info-bar{-webkit-print-color-adjust:exact;print-color-adjust:exact}
}

/* Responsive */
@media(max-width:768px){.mr-grid{grid-template-columns:1fr 1fr}.mr-title{font-size:1.35rem}}
@media(max-width:480px){.mr-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="mr-page">
    <div class="mr-header">
        <div class="mr-header-left">
            <nav aria-label="breadcrumb" class="mr-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li class="active">Mark Roster</li></ol></nav>
            <h1 class="mr-title">Mark Roster</h1>
            <p class="mr-subtitle">Detailed mark roster with separate table per subject showing all CA and Exam entries</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="mr-card mr-filter-card">
        <div class="mr-card-head">
            <div class="mr-card-icon blue"><i class="fas fa-filter"></i></div>
            <div><h3 class="mr-card-title">Select Filters</h3><p class="mr-card-desc">Choose academic year, term, and class</p></div>
        </div>
        <form method="POST" action="{{ route('admin.mark-roster.generate') }}">
            @csrf
            <div class="mr-card-body">
                <div class="mr-grid">
                    <div class="mr-group">
                        <label class="mr-label">Academic Year <span style="color:#ef4444">*</span></label>
                        <select name="academic_year_id" class="mr-select" required {{ $isTeacher ?? false ? 'disabled' : '' }}>
                            <option value="">-- Select Year --</option>
                            @foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ old('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach
                        </select>
                        @if($isTeacher ?? false)<input type="hidden" name="academic_year_id" value="{{ $academicYears->first()->id ?? '' }}">@endif
                    </div>
                    <div class="mr-group">
                        <label class="mr-label">Term <span style="color:#ef4444">*</span></label>
                        <select name="term_id" class="mr-select" required {{ $isTeacher ?? false ? 'disabled' : '' }}>
                            <option value="">-- Select Term --</option>
                            @foreach($terms as $t)<option value="{{ $t->id }}" {{ old('term_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                        </select>
                        @if($isTeacher ?? false)<input type="hidden" name="term_id" value="{{ $terms->first()->id ?? '' }}">@endif
                    </div>
                    <div class="mr-group">
                        <label class="mr-label">Class <span style="color:#ef4444">*</span></label>
                        <select name="class_id" id="filterClass" class="mr-select" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mr-group">
                        <label class="mr-label">Section</label>
                        <select name="section_id" id="filterSection" class="mr-select">
                            <option value="">-- All Sections --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mr-actions">
                <button type="submit" class="mr-btn"><i class="fas fa-table"></i> Generate Roster</button>
            </div>
        </form>
    </div>

    {{-- Roster Results --}}
    @isset($subjectRosters)
    @if(count($subjectRosters) > 0)

    {{-- Print-only header --}}
    <div class="print-only" style="display:none;text-align:center;margin-bottom:1rem;padding:1rem 0;border-bottom:2px solid #333">
        <h2 style="margin:0;font-size:1.3rem;font-weight:800">School of Redemption</h2>
        <p style="margin:.25rem 0 0;font-size:.9rem;color:#666">Mark Roster - {{ $class->name ?? '' }} - {{ $term->name ?? '' }} - {{ $academicYear->name ?? '' }}</p>
    </div>

    {{-- Info bar --}}
    <div class="mr-card info-bar no-print" style="margin-bottom:1.5rem">
        <div style="display:flex;align-items:center;gap:1rem;padding:.75rem 1.5rem;background:linear-gradient(135deg,#1e3a5f,#264b73);color:#fff;flex-wrap:wrap">
            <span style="font-weight:800;font-size:1.05rem"><i class="fas fa-clipboard-list me-1"></i> Mark Roster</span>
            <span style="font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px">{{ $academicYear->name ?? '' }}</span>
            <span style="font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px">{{ $term->name ?? '' }}</span>
            <span style="font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px">{{ $class->name ?? '' }}</span>
            @if($section)<span style="font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px">{{ $section->name }}</span>@endif
            <span style="font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px">{{ count($subjectRosters) }} Subjects</span>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:.75rem;padding:.5rem 1.5rem;border-top:1px solid #f0f0f0;background:#fafbfc">
            <button onclick="window.print()" class="mr-btn mr-btn-outline"><i class="fas fa-print"></i> Print</button>
            <button onclick="exportRosterCSV()" class="mr-btn mr-btn-outline"><i class="fas fa-file-csv"></i> Export CSV</button>
        </div>
    </div>

    {{-- One table per subject — each subject on its own page when printing --}}
    @foreach($subjectRosters as $si => $sr)
    <?php
        $subj = $sr['subject'];
        $rows = $sr['rows'];
        $avgs = $sr['averages'];
        $colorIdx = $si % 10;

        // Helper: format raw mark field (1 decimal place)
        $fmt1 = function($v) {
            if ($v === null || $v === '') return '-';
            return number_format((float)$v, 1);
        };
        // Helper: format calculated field (2 decimal places)
        $fmt2 = function($v) {
            if ($v === null || $v === '') return '-';
            return number_format((float)$v, 2);
        };
    ?>
    <div class="mr-subject-section">
        <div class="mr-subject-head s{{ $colorIdx }}">
            <i class="fas fa-book subj-icon"></i>
            <span>{{ $subj->name }}</span>
            @if($subj->code)<span style="font-size:.75rem;opacity:.8">({{ $subj->code }})</span>@endif
            <span class="subj-badge">{{ count($rows) }} Students</span>
        </div>
        <div class="mr-table-wrap">
            <table class="mr-table">
                <thead>
                    {{-- Row 1: Group headers --}}
                    <tr>
                        <th rowspan="2" style="width:32px">#</th>
                        <th rowspan="2" style="text-align:left;min-width:130px">Student Name</th>
                        <th colspan="14" class="group-ca"><i class="fas fa-tasks me-1"></i>Continuous Assessment (Raw /70 &rarr; Scaled /30)</th>
                        <th colspan="5" class="group-exam"><i class="fas fa-pen-alt me-1"></i>Exam (/70)</th>
                        <th rowspan="2" class="grand-total-col" style="min-width:50px">Grand<br>Total</th>
                        <th rowspan="2" class="grade-col" style="min-width:40px">Grade</th>
                    </tr>
                    {{-- Row 2: Sub-field headers (rotated 90 degrees) --}}
                    <tr>
                        {{-- CA fields (rotated) --}}
                        <th class="rot-th ca-col">CA1 <small>/5</small></th>
                        <th class="rot-th ca-col">CA2 <small>/5</small></th>
                        <th class="rot-th ca-col">CA3 <small>/5</small></th>
                        <th class="rot-th ca-col">CA4 <small>/5</small></th>
                        <th class="rot-th ca-col">CA5 <small>/5</small></th>
                        <th class="rot-th ca-col">CA6 <small>/5</small></th>
                        <th class="rot-th ca-col">CA7 <small>/5</small></th>
                        <th class="rot-th ca-col">CA8 <small>/5</small></th>
                        <th class="rot-th ca-col">CA9 <small>/5</small></th>
                        <th class="rot-th ca-col">CA10 <small>/5</small></th>
                        <th class="rot-th ca-col">Conduct <small>/5</small></th>
                        <th class="rot-th ca-col">Handwriting <small>/5</small></th>
                        <th class="rot-th ca-col">Creativity <small>/10</small></th>
                        <th class="rot-th ca-total-col">CA Total <small>/30</small></th>
                        {{-- Exam fields (rotated) --}}
                        <th class="rot-th exam-col">Test 1 <small>/10</small></th>
                        <th class="rot-th exam-col">Test 2 <small>/10</small></th>
                        <th class="rot-th exam-col">Mid Term <small>/20</small></th>
                        <th class="rot-th exam-col">Final Exam <small>/30</small></th>
                        <th class="rot-th exam-total-col">Exam Total <small>/70</small></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                    <tr>
                        <td class="stu-serial">{{ $row['serial'] }}</td>
                        <td class="stu-name">{{ $row['student']->full_name ?? '' }}</td>
                        {{-- CA raw fields (1 decimal) --}}
                        <td class="ca-col">{{ $fmt1($row['ca1'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['ca2'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['ca3'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['ca4'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['ca5'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['ca6'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['ca7'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['ca8'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['ca9'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['ca10'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['conduct'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['handwriting'] ?? null) }}</td>
                        <td class="ca-col">{{ $fmt1($row['creativity'] ?? null) }}</td>
                        {{-- CA Total (2 decimals - calculated) --}}
                        <td class="ca-total-col">{{ $fmt2($row['ca_total'] ?? null) }}</td>
                        {{-- Exam raw fields (1 decimal) --}}
                        <td class="exam-col">{{ $fmt1($row['test1'] ?? null) }}</td>
                        <td class="exam-col">{{ $fmt1($row['test2'] ?? null) }}</td>
                        <td class="exam-col">{{ $fmt1($row['mid_term'] ?? null) }}</td>
                        <td class="exam-col">{{ $fmt1($row['final_exam'] ?? null) }}</td>
                        {{-- Exam Total (2 decimals - calculated) --}}
                        <td class="exam-total-col">{{ $fmt2($row['exam_total'] ?? null) }}</td>
                        {{-- Grand Total (2 decimals - calculated) --}}
                        <td class="grand-total-col">{{ $fmt2($row['grand_total'] ?? null) }}</td>
                        @php
                            $gClass = 'g-f';
                            if ($row['grade']) {
                                $g = $row['grade'];
                                if ($g === 'A') $gClass = 'g-a';
                                elseif ($g === 'B') $gClass = 'g-b';
                                elseif ($g === 'C') $gClass = 'g-c';
                                elseif ($g === 'D') $gClass = 'g-d';
                                elseif ($g === 'I') $gClass = 'g-i';
                            }
                        @endphp
                        <td class="grade-col {{ $gClass }}">{{ $row['grade'] ?? '-' }}</td>
                    </tr>
                    @endforeach

                    {{-- Average Row --}}
                    <tr class="avg-row">
                        <td class="stu-serial" colspan="2" style="text-align:center"><i class="fas fa-chart-bar me-1"></i>Class Average</td>
                        {{-- CA averages (1 decimal) --}}
                        <td>{{ $fmt1($avgs['ca1'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['ca2'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['ca3'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['ca4'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['ca5'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['ca6'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['ca7'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['ca8'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['ca9'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['ca10'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['conduct'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['handwriting'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['creativity'] ?? null) }}</td>
                        {{-- CA Total average (2 decimals) --}}
                        <td style="font-weight:800">{{ $fmt2($avgs['ca_total'] ?? null) }}</td>
                        {{-- Exam averages (1 decimal) --}}
                        <td>{{ $fmt1($avgs['test1'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['test2'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['mid_term'] ?? null) }}</td>
                        <td>{{ $fmt1($avgs['final_exam'] ?? null) }}</td>
                        {{-- Exam Total average (2 decimals) --}}
                        <td style="font-weight:800">{{ $fmt2($avgs['exam_total'] ?? null) }}</td>
                        {{-- Grand Total average (2 decimals) --}}
                        <td style="font-weight:800;font-size:.9rem">{{ $fmt2($avgs['grand_total'] ?? null) }}</td>
                        <td style="font-weight:800">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    @else
    <div class="mr-card">
        <div class="mr-empty">
            <i class="fas fa-clipboard-list"></i>
            <p>No marks found for the selected filters.</p>
            <p style="font-size:.82rem;margin-top:.5rem">Please make sure marks have been entered for the selected academic year, term, and class.</p>
        </div>
    </div>
    @endif
    @endisset
</div>
@endsection

@push('scripts')
<script>
(function(){
    var cls=document.getElementById('filterClass');
    var sec=document.getElementById('filterSection');
    if(cls){
        cls.addEventListener('change',function(){
            if(!this.value){sec.innerHTML='<option value="">-- All Sections --</option>';return;}
            fetch('{{ route("admin.mark-roster.sections") }}?class_id='+this.value,{credentials:'same-origin'})
            .then(function(r){return r.json();})
            .then(function(data){
                sec.innerHTML='<option value="">-- All Sections --</option>';
                data.forEach(function(s){sec.innerHTML+='<option value="'+s.id+'">'+s.name+'</option>';});
            });
        });
    }
})();

function exportRosterCSV(){
    var tables=document.querySelectorAll('.mr-table');
    if(!tables.length)return;
    var csv=[];
    tables.forEach(function(table){
        var sectionHead=table.closest('.mr-subject-section');
        if(sectionHead){
            var headDiv=sectionHead.querySelector('.mr-subject-head');
            if(headDiv){
                csv.push('');
                csv.push('=== '+headDiv.innerText.trim()+' ===');
                csv.push('');
            }
        }
        var rows=table.querySelectorAll('tr');
        rows.forEach(function(row){
            var cols=row.querySelectorAll('td,th');
            var rowData=[];
            cols.forEach(function(col){
                var text=col.innerText.replace(/"/g,'""').replace(/\n/g,' ');
                rowData.push('"'+text+'"');
            });
            csv.push(rowData.join(','));
        });
    });
    var blob=new Blob([csv.join('\n')],{type:'text/csv;charset=utf-8;'});
    var link=document.createElement('a');
    link.href=URL.createObjectURL(blob);
    link.download='mark_roster.csv';
    link.click();
}
</script>
@endpush
