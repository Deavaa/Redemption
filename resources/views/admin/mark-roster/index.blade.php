@extends('layouts.admin')
@section('title', 'Mark List')

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
.mr-subject-section{margin-bottom:1rem}
.mr-subject-section:last-child{margin-bottom:0}
.mr-subject-head{display:flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:10px 10px 0 0;color:#fff;font-size:1.05rem;font-weight:800}
.mr-subject-head .subj-icon{font-size:1.2rem}
.mr-subject-head .subj-badge{font-size:.82rem;background:rgba(255,255,255,.2);padding:.1rem .5rem;border-radius:5px;margin-left:auto}

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
.mr-table{width:100%;border-collapse:collapse;font-size:.92rem}
.mr-table th{padding:.45rem .3rem;border:1px solid #e5e7eb;white-space:nowrap;text-align:center;font-weight:700;position:sticky;top:0}
.mr-table td{padding:.2rem .25rem;border:1px solid #e5e7eb;text-align:center}
.mr-table tbody tr:nth-child(even){background:#f9fafb}
.mr-table tbody tr:hover{background:#eef2ff}
.mr-table .stu-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e;min-width:180px;width:auto;max-width:250px;overflow:hidden;text-overflow:ellipsis}
.mr-table .stu-serial{font-weight:600;color:#6b7280;min-width:32px}

/* ── Rotated column headers ── */
.mr-table .rot-th{
    writing-mode:vertical-rl;
    transform:rotate(180deg);
    height:80px;
    min-width:26px;
    max-width:32px;
    padding:3px 1px;
    vertical-align:bottom;
    font-size:.82rem;
    line-height:1.1;
    letter-spacing:.3px;
    white-space:normal;
    word-break:break-word;
    overflow-wrap:break-word;
}
.mr-table .rot-th small{
    font-weight:400;
    opacity:.65;
    font-size:.72rem;
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
.mr-table .grand-total-col{font-weight:800}
.mr-table .grand-total-col.mark-red{background:#fef2f2!important;color:#dc2626!important}
.mr-table .grand-total-col.mark-amber{background:#fffbeb!important;color:#d97706!important}
.mr-table .grand-total-col.mark-green{background:#f0fdf4!important;color:#059669!important}
.mr-table .grade-col{font-weight:800}
.mr-table .grade-col.g-a{color:#059669;background:#f0fdf4}
.mr-table .grade-col.g-b{color:#2563eb;background:#eff6ff}
.mr-table .grade-col.g-c{color:#d97706;background:#fffbeb}
.mr-table .grade-col.g-d{color:#ea580c;background:#fff7ed}
.mr-table .grade-col.g-f{color:#dc2626;background:#fef2f2}
.mr-table .grade-col.g-i{color:#6b7280;background:#f3f4f6}

/* Color-coded individual mark cells (CA + Exam fields) */
.mr-table td.mark-red{background:#fef2f2!important;color:#dc2626!important;font-weight:700}
.mr-table td.mark-amber{background:#fffbeb!important;color:#d97706!important;font-weight:700}
.mr-table td.mark-green{background:#f0fdf4!important;color:#059669!important;font-weight:700}

/* Average row */
.mr-table .avg-row td{background:#f0f4ff!important;font-weight:700;color:#4338ca;border-top:2px solid #6366f1}
.mr-table .avg-row .stu-name{background:#f0f4ff!important;color:#4338ca;position:sticky;left:0;z-index:2}
.mr-table .avg-row .stu-serial{background:#f0f4ff!important;color:#4338ca;position:sticky;left:0;z-index:2}

/* No data */
.mr-empty{text-align:center;padding:3rem 1rem;color:#9ca3af}
.mr-empty i{font-size:2.5rem;margin-bottom:.75rem;display:block}
.mr-empty p{margin:0;font-size:.95rem}

/* Print styles — each subject on its own page */
@page{
    size:A4 landscape;
    margin:6mm;
}
@media print{
    /* Fit-to-page: scale content to fit A4 landscape width */
    html,body{
        zoom:1!important
    }
    .mr-page{
        width:100%!important;
        max-width:297mm!important;  /* A4 landscape width */
        margin:0!important;
        padding:0!important;
        overflow:visible!important
    }
    /* Reset all layout containers to full width - override sidebar offset */
    .admin-wrapper{
        margin:0!important;
        padding:0!important;
        display:block!important;
        box-sizing:border-box!important
    }
    .admin-main{
        margin:0!important;
        margin-left:0!important;
        padding:0!important;
        overflow:visible!important;
        max-width:100%!important;
        width:100%!important;
        display:block!important;
        box-sizing:border-box!important
    }
    .admin-content{
        margin:0!important;
        padding:0!important;
        overflow:visible!important;
        max-width:100%!important;
        width:100%!important;
        display:block!important;
        box-sizing:border-box!important
    }
    body,html{
        background:#fff!important;
        margin:0!important;
        padding:0!important;
        width:100%!important;
        overflow:visible!important
    }
    .mr-page{
        width:100%!important;
        max-width:100%!important;
        padding:0!important;
        margin:0!important;
        overflow:visible!important
    }
    .print-only { display: block !important; }
    /* Hide the screen-only report header on print (print-only header takes over) */
    .mr-report-header { display: none !important; }
    /* Print-only school header row in each table thead (repeats on each page) */
    .mr-print-school-header { display: table-row !important; }
    .mr-print-school-header th { background: #fff !important; color: #000 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    /* Watermark — logo centered on each page */
    .mr-watermark {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        width: 300px !important;
        height: 300px !important;
        opacity: 0.06 !important;
        z-index: -1 !important;
        pointer-events: none !important;
        object-fit: contain !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .mr-header,.mr-filter-card,.mr-actions,.mr-btn,
    .admin-sidebar,.sidebar-backdrop,.admin-topbar,.sidebar-footer,.sidebar-toggle,
    .no-print,.global-alert,.mobile-bottom-nav,.swipe-indicator,#adminAnnouncementBar{display:none!important}
    .mr-page{animation:none!important}
    .mr-subject-section{page-break-after:always;break-after:page}
    .mr-subject-section:last-child{page-break-after:auto;break-after:auto}
    .mr-subject-head{-webkit-print-color-adjust:exact;print-color-adjust:exact;border-radius:0!important;padding:4px 8px!important;font-size:11pt!important}
    .mr-table{font-size:9pt;width:100%!important;border-collapse:collapse!important}
    .mr-table-wrap{overflow:visible!important;width:100%!important;max-width:100%!important}
    .mr-table th{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    .mr-table .rot-th{height:65px;font-size:7.5pt;white-space:normal!important;word-break:break-word!important;overflow-wrap:break-word!important}
    .mr-table td{padding:2px 5px!important;font-size:9pt!important;white-space:nowrap!important}
    .mr-table .stu-name{font-size:9pt!important;white-space:nowrap!important;width:auto!important;min-width:150px!important;max-width:none!important;overflow:visible!important;padding:2px 8px!important;position:static!important}
    .mr-table .stu-serial{position:static!important;width:32px!important;min-width:32px!important}
    /* Each student row stays together — don't split across pages */
    .mr-table tbody tr{page-break-inside:avoid!important;break-inside:avoid!important;}
    .mr-table thead{display:table-header-group!important}
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
            <nav aria-label="breadcrumb" class="mr-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li class="active">Mark List</li></ol></nav>
            <h1 class="mr-title">Mark List</h1>
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

    {{-- Summary Mark List (3 rows per student) --}}
    <div class="mr-card mr-filter-card" style="margin-top:1rem;">
        <div class="mr-card-head">
            <div class="mr-card-icon green"><i class="fas fa-list-ol"></i></div>
            <div><h3 class="mr-card-title">Summary Mark List</h3><p class="mr-card-desc">3 rows per student (Term 1, Term 2, Annual) with subjects as columns</p></div>
        </div>
        <form method="POST" action="{{ route('admin.mark-roster.summary') }}">
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
                        <label class="mr-label">Class <span style="color:#ef4444">*</span></label>
                        <select name="class_id" id="filterClassSummary" class="mr-select" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="mr-group">
                        <label class="mr-label">Section</label>
                        <select name="section_id" id="filterSectionSummary" class="mr-select">
                            <option value="">-- All Sections --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mr-actions">
                <button type="submit" class="mr-btn" style="background:linear-gradient(135deg,#059669,#047857);"><i class="fas fa-list-ol"></i> Generate Summary List</button>
            </div>
        </form>
    </div>

    {{-- Roster Results --}}
    @isset($subjectRosters)
    @if(count($subjectRosters) > 0)

    {{-- ── Report Header (visible on screen AND print) ── --}}
    <div class="mr-report-header" style="text-align:center;margin-bottom:.75rem;padding:.75rem 1rem;background:#fff;border-radius:10px;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.04);position:relative;">
        @if(!empty($logoUrl))
        <img src="{{ $logoUrl }}" alt="School Logo" style="position:absolute;top:1rem;left:1.25rem;width:60px;height:60px;object-fit:contain;border-radius:8px;" />
        <img src="{{ $logoUrl }}" alt="School Logo" style="position:absolute;top:1rem;right:1.25rem;width:60px;height:60px;object-fit:contain;border-radius:8px;" />
        @endif
        <div style="{{ !empty($logoUrl) ? 'padding:0 80px;' : '' }}">
            <h1 style="margin:0 0 .25rem;font-size:1.5rem;font-weight:800;color:#1a1a2e;letter-spacing:-.3px;">{{ $schoolName ?? 'School of Redemption' }}</h1>
            @if($branch)<p style="margin:0 0 .25rem;font-size:.95rem;color:#374151;font-weight:600;"><i class="fas fa-code-branch" style="color:#6b7280;width:18px;"></i> {{ $branch->name ?? '' }} Branch</p>@endif
            <p style="margin:0 0 .15rem;font-size:.95rem;color:#374151;">
                @if($class)<span style="font-weight:600;"><i class="fas fa-users-class" style="color:#6b7280;width:18px;"></i> Class: {{ $class->name ?? '' }}</span>@endif
                @if($section)<span style="margin-left:1rem;font-weight:600;"><i class="fas fa-layer-group" style="color:#6b7280;width:18px;"></i> Section: {{ $section->name ?? '' }}</span>@endif
            </p>
            <p style="margin:.15rem 0 0;font-size:.9rem;color:#6b7280;">
                @if($academicYear)<span style="font-weight:600;"><i class="fas fa-calendar-alt" style="width:18px;"></i> Academic Year: {{ $academicYear->name ?? '' }}</span>@endif
                @if($term)<span style="margin-left:1rem;"><i class="fas fa-flag" style="width:18px;"></i> Term: {{ $term->name ?? '' }}</span>@endif
            </p>
            <p style="margin:.5rem 0 0;font-size:1.05rem;font-weight:700;color:#4361ee;border-top:2px solid #e5e7eb;padding-top:.5rem;display:inline-block;padding-left:2rem;padding-right:2rem;">
                <i class="fas fa-clipboard-list"></i> Mark List
            </p>
        </div>
    </div>

    {{-- Screen + Print watermark — logo centered behind content on each page --}}
    @if(!empty($logoUrl))
    <img src="{{ $logoUrl }}" alt="" class="mr-watermark" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:350px;height:350px;opacity:0.05;z-index:0;pointer-events:none;object-fit:contain;" />
    @endif

    {{-- Info bar --}}
    <div class="mr-card info-bar no-print" style="margin-bottom:1.5rem">
        <div style="display:flex;align-items:center;gap:1rem;padding:.75rem 1.5rem;background:linear-gradient(135deg,#1e3a5f,#264b73);color:#fff;flex-wrap:wrap">
            <span style="font-weight:800;font-size:1.05rem"><i class="fas fa-clipboard-list me-1"></i> Mark List</span>
            <span style="font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px">{{ $academicYear->name ?? '' }}</span>
            <span style="font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px">{{ $term->name ?? '' }}</span>
            <span style="font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px">{{ $class->name ?? '' }}</span>
            @if($section)<span style="font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px">{{ $section->name }}</span>@endif
            <span style="font-size:.78rem;background:rgba(255,255,255,.13);padding:.15rem .6rem;border-radius:6px">{{ count($subjectRosters) }} Subjects</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;padding:.5rem 1.5rem;border-top:1px solid #f0f0f0;background:#fafbfc;flex-wrap:wrap">
            {{-- Color legend --}}
            <div style="display:flex;align-items:center;gap:10px;font-size:.75rem;color:#6b7280;">
                <span style="font-weight:600;color:#1a1a2e;">Legend:</span>
                <span style="display:flex;align-items:center;gap:3px;"><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#fef2f2;border:1px solid #fecaca;"></span> <span style="color:#dc2626;font-weight:600;">&lt; 50%</span></span>
                <span style="display:flex;align-items:center;gap:3px;"><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#fffbeb;border:1px solid #fde68a;"></span> <span style="color:#d97706;font-weight:600;">50&ndash;69%</span></span>
                <span style="display:flex;align-items:center;gap:3px;"><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:#f0fdf4;border:1px solid #bbf7d0;"></span> <span style="color:#059669;font-weight:600;">&ge; 70%</span></span>
            </div>
            <div style="display:flex;gap:.75rem;">
                <button onclick="mrPrint()" class="mr-btn mr-btn-outline"><i class="fas fa-print"></i> Print</button>
                <button onclick="exportRosterCSV()" class="mr-btn mr-btn-outline"><i class="fas fa-file-csv"></i> Export CSV</button>
            </div>
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
        // Helper: color class based on value/max percentage
        // Returns 'mark-red' (<50%), 'mark-amber' (50-69%), 'mark-green' (>=70%), or '' (no value)
        $markClass = function($v, $max) {
            if ($v === null || $v === '' || $max <= 0) return '';
            $pct = (floatval($v) / floatval($max)) * 100;
            if ($pct < 50) return 'mark-red';
            if ($pct < 70) return 'mark-amber';
            return 'mark-green';
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
                    {{-- Print-only school header row (repeats on each printed page) --}}
                    <tr class="mr-print-school-header print-only" style="display:none;">
                        <th colspan="23" style="text-align:center;border:1px solid #333;padding:6px 8px;background:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;">
                            <table style="width:100%;border:none;">
                                <tr>
                                    <td style="width:36px;border:none;text-align:left;vertical-align:middle;">
                                        @if(!empty($logoUrl))<img src="{{ $logoUrl }}" alt="" style="width:32px;height:32px;object-fit:contain;" />@endif
                                    </td>
                                    <td style="border:none;text-align:center;vertical-align:middle;">
                                        <div style="font-size:1rem;font-weight:800;color:#000!important;">{{ $schoolName ?? 'School of Redemption' }}</div>
                                        <div style="font-size:.72rem;color:#444!important;margin-top:1px;">
                                            @if($branch){{ $branch->name }} Branch &middot; @endif
                                            Mark List &middot;
                                            @if($class){{ $class->name }}@if($section) - {{ $section->name }}@endif @endif
                                            &middot; @if($term){{ $term->name }}@endif
                                            &middot; @if($academicYear){{ $academicYear->name }}@endif
                                            &middot; {{ $subj->name ?? '' }}
                                        </div>
                                    </td>
                                    <td style="width:36px;border:none;text-align:right;vertical-align:middle;">
                                        @if(!empty($logoUrl))<img src="{{ $logoUrl }}" alt="" style="width:32px;height:32px;object-fit:contain;" />@endif
                                    </td>
                                </tr>
                            </table>
                        </th>
                    </tr>
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
                        {{-- CA raw fields (1 decimal) — color-coded by /5 --}}
                        <td class="ca-col {{ $markClass($row['ca1'] ?? null, 5) }}">{{ $fmt1($row['ca1'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['ca2'] ?? null, 5) }}">{{ $fmt1($row['ca2'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['ca3'] ?? null, 5) }}">{{ $fmt1($row['ca3'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['ca4'] ?? null, 5) }}">{{ $fmt1($row['ca4'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['ca5'] ?? null, 5) }}">{{ $fmt1($row['ca5'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['ca6'] ?? null, 5) }}">{{ $fmt1($row['ca6'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['ca7'] ?? null, 5) }}">{{ $fmt1($row['ca7'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['ca8'] ?? null, 5) }}">{{ $fmt1($row['ca8'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['ca9'] ?? null, 5) }}">{{ $fmt1($row['ca9'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['ca10'] ?? null, 5) }}">{{ $fmt1($row['ca10'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['conduct'] ?? null, 5) }}">{{ $fmt1($row['conduct'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['handwriting'] ?? null, 5) }}">{{ $fmt1($row['handwriting'] ?? null) }}</td>
                        <td class="ca-col {{ $markClass($row['creativity'] ?? null, 10) }}">{{ $fmt1($row['creativity'] ?? null) }}</td>
                        {{-- CA Total (2 decimals - calculated) — /30 --}}
                        <td class="ca-total-col {{ $markClass($row['ca_total'] ?? null, 30) }}">{{ $fmt2($row['ca_total'] ?? null) }}</td>
                        {{-- Exam raw fields (1 decimal) --}}
                        <td class="exam-col {{ $markClass($row['test1'] ?? null, 10) }}">{{ $fmt1($row['test1'] ?? null) }}</td>
                        <td class="exam-col {{ $markClass($row['test2'] ?? null, 10) }}">{{ $fmt1($row['test2'] ?? null) }}</td>
                        <td class="exam-col {{ $markClass($row['mid_term'] ?? null, 20) }}">{{ $fmt1($row['mid_term'] ?? null) }}</td>
                        <td class="exam-col {{ $markClass($row['final_exam'] ?? null, 30) }}">{{ $fmt1($row['final_exam'] ?? null) }}</td>
                        {{-- Exam Total (2 decimals - calculated) — /70 --}}
                        <td class="exam-total-col {{ $markClass($row['exam_total'] ?? null, 70) }}">{{ $fmt2($row['exam_total'] ?? null) }}</td>
                        {{-- Grand Total (2 decimals - calculated) — color-coded by percentage --}}
                        @php
                            $gt = $row['grand_total'] ?? null;
                            $gtClass = '';
                            if ($gt !== null && $gt !== '') {
                                $gtVal = floatval($gt);
                                if ($gtVal < 50) $gtClass = 'mark-red';
                                elseif ($gtVal < 70) $gtClass = 'mark-amber';
                                else $gtClass = 'mark-green';
                            }
                        @endphp
                        <td class="grand-total-col {{ $gtClass }}">{{ $fmt2($row['grand_total'] ?? null) }}</td>
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

    {{-- Signature Section --}}
    @isset($subjectRosters)
    @if(count($subjectRosters) > 0)
    <div style="display:flex;justify-content:space-around;margin-top:2rem;padding:1.5rem 2rem;background:#fff;border-radius:10px;border:1px solid #e5e7eb;gap:2rem;flex-wrap:wrap;">
        <div style="text-align:center;min-width:180px;">
            <div style="font-size:.75rem;color:#6b7280;margin-bottom:40px;">Subject Teacher</div>
            <div style="border-top:1px solid #333;padding-top:4px;font-size:.78rem;font-weight:600;color:#1a1a2e;">Name &amp; Signature</div>
            <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;">Date: _______________</div>
        </div>
        <div style="text-align:center;min-width:180px;">
            <div style="font-size:.75rem;color:#6b7280;margin-bottom:40px;">Homeroom Teacher</div>
            <div style="border-top:1px solid #333;padding-top:4px;font-size:.78rem;font-weight:600;color:#1a1a2e;">Name &amp; Signature</div>
            <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;">Date: _______________</div>
        </div>
        <div style="text-align:center;min-width:180px;">
            <div style="font-size:.75rem;color:#6b7280;margin-bottom:40px;">Branch Principal</div>
            <div style="border-top:1px solid #333;padding-top:4px;font-size:.78rem;font-weight:600;color:#1a1a2e;">Name &amp; Signature</div>
            <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;">Date: _______________</div>
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

    // Check if SheetJS (xlsx library) is loaded — if so, export as multi-sheet XLSX
    if(typeof XLSX !== 'undefined'){
        var wb = XLSX.utils.book_new();
        var sheetCount = 0;
        tables.forEach(function(table){
            var sectionHead=table.closest('.mr-subject-section');
            var sheetName='Subject'+(++sheetCount);
            if(sectionHead){
                var headDiv=sectionHead.querySelector('.mr-subject-head');
                if(headDiv){
                    var name=headDiv.innerText.trim().replace(/[\\\/\?\*\[\]]/g,'').substring(0,31);
                    if(name) sheetName=name;
                }
            }
            var ws = XLSX.utils.table_to_sheet(table);
            XLSX.utils.book_append_sheet(wb, ws, sheetName);
        });
        XLSX.writeFile(wb, 'mark_roster.xlsx');
        return;
    }

    // Fallback: CSV with BOM
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
    // Prepend UTF-8 BOM (\uFEFF) so Excel opens with correct encoding for Amharic
    var blob=new Blob(['\uFEFF'+csv.join('\n')],{type:'text/csv;charset=utf-8;'});
    var link=document.createElement('a');
    link.href=URL.createObjectURL(blob);
    link.download='mark_roster.csv';
    link.click();
}

// Print with A4 landscape pre-selected
// The @page CSS rule (size:A4 landscape) tells the browser to use A4.
function mrPrint(){
    document.body.classList.add('printing-a4');
    setTimeout(function(){
        window.print();
        setTimeout(function(){ document.body.classList.remove('printing-a4'); }, 500);
    }, 50);
}
</script>
@endpush
