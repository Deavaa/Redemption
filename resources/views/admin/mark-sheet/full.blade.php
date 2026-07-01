@extends('layouts.admin')
@section('title', 'Full Mark Sheet')

@push('styles')
<style>
.fms-page{animation:fmsIn .4s ease-out}
@keyframes fmsIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.fms-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.fms-header-left{flex:1}
.fms-title{font-size:1.75rem;font-weight:800;color:#1a1a2e;margin:0;letter-spacing:-.5px}
.fms-subtitle{font-size:.9rem;color:#6c757d;margin:.25rem 0 0}
.fms-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}
.fms-breadcrumb li{color:#adb5bd}
.fms-breadcrumb li a{color:#6c757d;text-decoration:none;transition:color .2s}
.fms-breadcrumb li a:hover{color:#4361ee}
.fms-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}
.fms-breadcrumb li.active{color:#4361ee;font-weight:500}

.fms-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.5rem}
.fms-card-head{display:flex;align-items:center;gap:.75rem;padding:1rem 1.5rem;border-bottom:1px solid #f0f0f0;background:#fafbfc}
.fms-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.fms-card-icon.blue{background:#eef2ff;color:#4361ee}
.fms-card-title{font-size:1rem;font-weight:700;color:#1a1a2e;margin:0}
.fms-card-desc{font-size:.82rem;color:#9ca3af;margin:.1rem 0 0}
.fms-card-body{padding:1.25rem 1.5rem}
.fms-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem}
.fms-group{display:flex;flex-direction:column}
.fms-label{font-weight:600;color:#374151;margin-bottom:.4rem;font-size:.85rem}
.fms-select{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.6rem 2.2rem .6rem .8rem;font-size:.88rem;color:#1a1a2e;background:#fff;appearance:none;cursor:pointer;transition:all .2s;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .6rem center;background-repeat:no-repeat;background-size:1.15rem}
.fms-select:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}
.fms-btn{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:10px;font-weight:600;font-size:.88rem;border:none;cursor:pointer;transition:all .25s;color:#fff;background:linear-gradient(135deg,#4361ee,#3a0ca3);box-shadow:0 2px 8px rgba(67,97,238,.3)}
.fms-btn:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4)}
.fms-btn-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb;box-shadow:none}
.fms-btn-outline:hover{border-color:#4361ee;color:#4361ee;background:#f8f9ff;transform:none;box-shadow:none}
.fms-actions{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid #f0f0f0;background:#fafbfc}

/* Term section headers */
.fms-term-section{margin-bottom:0}
.fms-term-section:last-child{margin-bottom:0}
.fms-term-head{display:flex;align-items:center;gap:.4rem;padding:0 .5rem;margin:0;border-radius:0;color:#fff;font-size:1rem;font-weight:800;line-height:1.4;min-height:28px}
.fms-term-head.term1{background:linear-gradient(135deg,#3b82f6,#2563eb)}
.fms-term-head.term2{background:linear-gradient(135deg,#8b5cf6,#7c3aed)}
.fms-term-head.annual{background:linear-gradient(135deg,#10b981,#059669)}
.fms-term-head .term-icon{font-size:1rem}
.fms-term-head .term-label{font-size:.82rem;background:rgba(255,255,255,.2);padding:0 .4rem;border-radius:3px;margin-left:auto}

/* Rotated header table */
.fms-seq-table-wrap{overflow-x:auto}
.fms-seq-table{width:100%;border-collapse:collapse;font-size:.95rem}
.fms-seq-table th{border:1px solid #e5e7eb;font-weight:700;position:sticky;top:0;vertical-align:bottom;padding:0}
.fms-seq-table td{padding:1px 3px;border:1px solid #e5e7eb;text-align:center;line-height:1.2}
.fms-seq-table tbody tr:nth-child(even){background:#f9fafb}
.fms-seq-table tbody tr:hover{background:#eef2ff}

/* Rotated subject headers - using writing-mode for reliable 90° rotation */
.fms-seq-table .th-rotated{height:75px;padding:0;border:1px solid #e5e7eb;vertical-align:bottom}
.fms-seq-table .th-rotated .th-rotate-inner{width:100%;height:100%;display:flex;align-items:flex-end;justify-content:center}
.fms-seq-table .th-rotated .th-rotate-text{writing-mode:vertical-rl;transform:rotate(180deg);font-size:.88rem;font-weight:700;padding:3px 2px;white-space:normal;word-break:break-word;overflow-wrap:break-word;letter-spacing:.2px;line-height:1.05;max-height:75px}

/* Fixed header columns */
.fms-seq-table .th-fixed{padding:1px 4px;white-space:nowrap;text-align:center}
.fms-seq-table .stu-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e;padding:1px 6px;overflow:visible}
.fms-seq-table .mark-val{font-weight:600}
.fms-seq-table .total-col{font-weight:700;background:#f0f4ff;color:#4361ee}
.fms-seq-table .avg-col{font-weight:600;background:#eef2ff;color:#6366f1;font-size:.9rem}
.fms-seq-table .rank-col{font-weight:700}

/* Color-coded marks by percentage (red <50%, amber 50-69%, green >=70%) */
.fms-seq-table td.mark-red{background:#fef2f2!important;color:#dc2626!important;font-weight:700}
.fms-seq-table td.mark-amber{background:#fffbeb!important;color:#d97706!important;font-weight:700}
.fms-seq-table td.mark-green{background:#f0fdf4!important;color:#059669!important;font-weight:700}
.fms-seq-table .total-col.mark-red{background:#fef2f2!important;color:#dc2626!important}
.fms-seq-table .total-col.mark-amber{background:#fffbeb!important;color:#d97706!important}
.fms-seq-table .total-col.mark-green{background:#f0fdf4!important;color:#059669!important}
.fms-seq-table .avg-col.mark-red{background:#fef2f2!important;color:#dc2626!important}
.fms-seq-table .avg-col.mark-amber{background:#fffbeb!important;color:#d97706!important}
.fms-seq-table .avg-col.mark-green{background:#f0fdf4!important;color:#059669!important}

/* Term-specific summary row colors */
.fms-seq-table .avg-row td{font-weight:700;border-top:2px solid #6366f1}
.fms-seq-table .avg-row.term1 td{background:#dbeafe!important;color:#1e40af}
.fms-seq-table .avg-row.term2 td{background:#ede9fe!important;color:#5b21b6}
.fms-seq-table .avg-row.annual td{background:#d1fae5!important;color:#065f46}
.fms-seq-table .avg-row .stu-name{font-weight:700}

.fms-seq-table .highest-row td{font-weight:700;border-top:2px solid #eab308;background:#fef9c3!important;color:#854d0e}
.fms-seq-table .highest-row .stu-name{font-weight:700}

.fms-seq-table .lowest-row td{font-weight:700;border-top:2px solid #ef4444;background:#fee2e2!important;color:#991b1b}
.fms-seq-table .lowest-row .stu-name{font-weight:700}

/* Term 1 header */
.fms-seq-table.term1-table .th-fixed{background:#eff6ff;color:#1e3a8a}
.fms-seq-table.term1-table .th-rotated{background:#eff6ff;color:#1e3a8a}
.fms-seq-table.term1-table .rank-col{color:#2563eb;background:#dbeafe}
/* Term 2 header */
.fms-seq-table.term2-table .th-fixed{background:#f5f3ff;color:#5b21b6}
.fms-seq-table.term2-table .th-rotated{background:#f5f3ff;color:#5b21b6}
.fms-seq-table.term2-table .rank-col{color:#7c3aed;background:#ede9fe}
/* Annual header */
.fms-seq-table.annual-table .th-fixed{background:#ecfdf5;color:#065f46}
.fms-seq-table.annual-table .th-rotated{background:#ecfdf5;color:#065f46}
.fms-seq-table.annual-table .rank-col{color:#059669;background:#d1fae5}

/* No data */
.fms-empty{text-align:center;padding:3rem 1rem;color:#9ca3af}
.fms-empty i{font-size:2.5rem;margin-bottom:.75rem;display:block}
.fms-empty p{margin:0;font-size:.95rem}

/* Summary stats row under each table */
.fms-term-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:2px;padding:2px 4px;border-top:1px solid #f0f0f0;background:#fafbfc}
.fms-stat-card{background:#fff;border-radius:4px;padding:1px 6px;border:1px solid #e5e7eb}
.fms-stat-card .stat-label{font-size:.72rem;color:#9ca3af;font-weight:600;text-transform:uppercase;letter-spacing:.3px;line-height:1.1}
.fms-stat-card .stat-value{font-size:1rem;font-weight:800;margin-top:0;line-height:1.2}
.fms-stat-card.term1 .stat-value{color:#2563eb}
.fms-stat-card.term2 .stat-value{color:#7c3aed}
.fms-stat-card.annual .stat-value{color:#059669}

/* Print styles */
@page{
    size:A4 landscape;
    margin:6mm;
}
/* When printing, apply fit-to-page scaling */
@media print{
    body.printing-a4 .fms-page{
        zoom:1;  /* Full 100% — no scaling, fit to A4 landscape width */
    }
    /* Firefox doesn't support zoom, use transform as fallback */
    @-moz-document url-prefix(){
        body.printing-a4 .fms-page{
            transform:scale(1);
            transform-origin:top left;
            width:100%;
        }
    }
}
@media print{
    /* Fit-to-page: full A4 landscape width, no scaling */
    html,body{
        zoom:1!important
    }
    .fms-page{
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
    /* Hide non-print elements */
    .fms-header,.fms-card,.fms-actions,.fms-term-stats,
    .admin-sidebar,.sidebar-backdrop,.admin-topbar,.sidebar-footer,.sidebar-toggle,
    .no-print,.global-alert,.mobile-bottom-nav,.swipe-indicator,#adminAnnouncementBar{
        display:none!important
    }
    /* Show print-only elements */
    .print-only{display:block!important}
    /* Hide the screen-only report header on print (print-only header takes over) */
    .fms-report-header{display:none!important}
    /* Print header repeats on each page via thead */
    .fms-print-header{display:table-header-group!important}
    .fms-print-header-table{display:table!important;width:100%!important}
    /* Print-only school header row in each table thead (repeats on each page) */
    .fms-print-school-header{display:table-row!important}
    .fms-print-school-header th{background:#fff!important;color:#000!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
    /* Watermark — logo centered on each page */
    .fms-watermark{
        position:fixed!important;
        top:50%!important;
        left:50%!important;
        transform:translate(-50%,-50%)!important;
        width:300px!important;
        height:300px!important;
        opacity:0.06!important;
        z-index:-1!important;
        pointer-events:none!important;
        object-fit:contain!important;
        -webkit-print-color-adjust:exact!important;
        print-color-adjust:exact!important;
    }
    /* Remove box shadows and decorative borders */
    *{
        box-sizing:border-box!important
    }
    body,html{
        background:#fff!important;
        margin:0!important;
        padding:0!important;
        overflow:visible!important
    }
    .fms-page{
        width:100%!important;
        max-width:100%!important;
        padding:4mm!important;
        margin:0!important;
        box-shadow:none!important;
        border:none!important;
        overflow:visible!important
    }
    .fms-term-head{
        border-radius:0!important;
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;
        margin:0!important;
        padding:1px 6px!important;
        font-size:10pt!important;
        line-height:1.3!important;
        min-height:0!important
    }
    /* Table container must not clip content */
    .fms-seq-table-wrap{
        overflow:visible!important;
        width:100%!important;
        max-width:100%!important;
        margin:0!important;
        padding:0!important
    }
    .fms-seq-table{
        font-size:9pt!important;
        width:100%!important;
        table-layout:auto!important;
        border-collapse:collapse!important
    }
    .fms-seq-table th{
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact
    }
    .fms-term-section{
        page-break-inside:avoid;
        margin-bottom:4px!important;
        overflow:visible!important
    }
    .avg-row td,.highest-row td,.lowest-row td{
        -webkit-print-color-adjust:exact;
        print-color-adjust:exact
    }
    .fms-seq-table .th-rotated{
        height:75px!important
    }
    .fms-seq-table .th-rotated .th-rotate-text{
        font-size:8pt!important;
        white-space:normal!important;
        word-break:break-word!important;
        overflow-wrap:break-word!important
    }
    .fms-seq-table td{
        padding:2px 4px!important;
        font-size:9pt!important;
        overflow:hidden!important;
        text-overflow:ellipsis!important;
        word-wrap:break-word!important
    }
    /* Name column: full width, no truncation, no wrapping */
    .fms-seq-table .stu-name{
        font-size:9pt!important;
        white-space:nowrap!important;
        width:auto!important;
        max-width:none!important;
        overflow:visible!important;
        padding:2px 8px!important
    }
    .fms-seq-table .th-fixed{
        font-size:8.5pt!important;
        padding:3px 4px!important
    }
    .fms-seq-table .total-col,
    .fms-seq-table .avg-col,
    .fms-seq-table .rank-col{
        min-width:40px!important
    }
}

/* Responsive */
@media(max-width:768px){.fms-grid{grid-template-columns:1fr 1fr}.fms-title{font-size:1.35rem}}
@media(max-width:480px){.fms-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="fms-page">
    <div class="fms-header">
        <div class="fms-header-left">
            <nav aria-label="breadcrumb" class="fms-breadcrumb"><ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li class="active">Full Mark Sheet</li></ol></nav>
            <h1 class="fms-title">Full Mark Sheet</h1>
            <p class="fms-subtitle">Complete mark sheet with Term 1, Term 2, and Annual results in sequence</p>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="fms-card no-print">
        <div class="fms-card-head">
            <div class="fms-card-icon blue"><i class="fas fa-filter"></i></div>
            <div><h3 class="fms-card-title">Select Filters</h3><p class="fms-card-desc">Choose academic year and class to generate the full mark sheet</p></div>
        </div>
        <form method="GET" action="{{ route('admin.mark-sheet-full.generate') }}">
            <div class="fms-card-body">
                <div class="fms-grid">
                    <div class="fms-group">
                        <label class="fms-label">Academic Year <span style="color:#ef4444">*</span></label>
                        <select name="academic_year_id" class="fms-select" required {{ $isTeacher ?? false ? 'disabled' : '' }}>
                            <option value="">-- Select Year --</option>
                            @foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ old('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach
                        </select>
                        @if($isTeacher ?? false)<input type="hidden" name="academic_year_id" value="{{ $academicYears->first()->id ?? '' }}">@endif
                    </div>
                    <div class="fms-group">
                        <label class="fms-label">Class <span style="color:#ef4444">*</span></label>
                        <select name="class_id" id="filterClass" class="fms-select" required>
                            <option value="">-- Select Class --</option>
                            @foreach($classes as $c)<option value="{{ $c->id }}" {{ old('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="fms-group">
                        <label class="fms-label">Section</label>
                        <select name="section_id" id="filterSection" class="fms-select">
                            <option value="">-- All Sections --</option>
                        </select>
                    </div>
                    <div class="fms-group" style="align-self:flex-end">
                        <button type="submit" class="fms-btn"><i class="fas fa-table"></i> Generate Sheet</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Results --}}
    @isset($roster)
    @if(count($roster) > 0)

    @php
        // Helper: color class based on grand_total value (out of 100)
        // Returns 'mark-red' (<50), 'mark-amber' (50-69), 'mark-green' (>=70), or '' (no value)
        $markClass = function($v) {
            if ($v === null || $v === '') return '';
            $val = floatval($v);
            if ($val < 50) return 'mark-red';
            if ($val < 70) return 'mark-amber';
            return 'mark-green';
        };
    @endphp

    {{-- Screen + Print watermark — logo centered behind content on each page --}}
    @if(!empty($logoUrl))
    <img src="{{ $logoUrl }}" alt="" class="fms-watermark" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:350px;height:350px;opacity:0.05;z-index:0;pointer-events:none;object-fit:contain;" />
    @endif

    {{-- ── Report Header (compact, single block) ── --}}
    <div class="fms-report-header" style="text-align:center;margin-bottom:.25rem;padding:.25rem .5rem;background:#fff;border-radius:6px;border:1px solid #e5e7eb;position:relative;">
        @if(!empty($logoUrl))
        <img src="{{ $logoUrl }}" alt="School Logo" style="position:absolute;top:2px;left:4px;width:36px;height:36px;object-fit:contain;" />
        <img src="{{ $logoUrl }}" alt="School Logo" style="position:absolute;top:2px;right:4px;width:36px;height:36px;object-fit:contain;" />
        @endif
        <div style="{{ !empty($logoUrl) ? 'padding:0 44px;' : '' }}">
            <div style="font-size:1rem;font-weight:800;color:#1a1a2e;line-height:1.2;">{{ $schoolName ?? 'School of Redemption' }}</div>
            <div style="font-size:.72rem;color:#374151;line-height:1.3;margin-top:1px;">
                @if($branch)<strong>{{ $branch->name ?? '' }}</strong> &middot; @endif
                @if($class)<strong>{{ $class->name ?? '' }}</strong>@if($section) - {{ $section->name }}@endif @endif
                &middot; @if($academicYear){{ $academicYear->name }}@endif
                @if($term1) &middot; T1: {{ $term1->name }}@endif
                @if($term2) &middot; T2: {{ $term2->name }}@endif
            </div>
        </div>
    </div>

    {{-- Print & Export Actions --}}
    <div class="fms-card no-print" style="margin-bottom:1rem">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:.75rem;padding:.75rem 1.5rem;flex-wrap:wrap">
            {{-- Color legend --}}
            <div style="display:flex;align-items:center;gap:12px;font-size:.78rem;color:#6b7280;">
                <span style="font-weight:600;color:#1a1a2e;">Color Legend:</span>
                <span style="display:flex;align-items:center;gap:4px;"><span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:#fef2f2;border:1px solid #fecaca;"></span> <span style="color:#dc2626;font-weight:600;">&lt; 50 (Fail)</span></span>
                <span style="display:flex;align-items:center;gap:4px;"><span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:#fffbeb;border:1px solid #fde68a;"></span> <span style="color:#d97706;font-weight:600;">50&ndash;69 (Below Avg)</span></span>
                <span style="display:flex;align-items:center;gap:4px;"><span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:#f0fdf4;border:1px solid #bbf7d0;"></span> <span style="color:#059669;font-weight:600;">&ge; 70 (Pass)</span></span>
            </div>
            <div style="display:flex;gap:.75rem;">
                <button onclick="fmsPrint()" class="fms-btn fms-btn-outline"><i class="fas fa-print"></i> Print All</button>
                <button onclick="exportCSV()" class="fms-btn fms-btn-outline"><i class="fas fa-file-csv"></i> Export CSV</button>
            </div>
        </div>
    </div>

    {{-- Compute highest and lowest marks for all terms --}}
    @php
        $highest = ['term1' => [], 'term2' => [], 'annual' => [], 'term1_total' => 0, 'term2_total' => 0, 'annual_total' => 0];
        $lowest  = ['term1' => [], 'term2' => [], 'annual' => [], 'term1_total' => 0, 'term2_total' => 0, 'annual_total' => 0];
        foreach ($subjects as $subj) {
            $t1Vals = []; $t2Vals = []; $aVals = [];
            foreach ($roster as $row) {
                $t1 = $row['term1'][$subj->id] ?? null;
                if ($t1 && $t1['grand_total'] !== null) $t1Vals[] = floatval($t1['grand_total']);
                $t2 = $row['term2'][$subj->id] ?? null;
                if ($t2 && $t2['grand_total'] !== null) $t2Vals[] = floatval($t2['grand_total']);
                $ann = $row['annual'][$subj->id] ?? null;
                if ($ann && $ann['grand_total'] !== null) $aVals[] = floatval($ann['grand_total']);
            }
            $highest['term1'][$subj->id]  = count($t1Vals) ? max($t1Vals) : null;
            $lowest['term1'][$subj->id]   = count($t1Vals) ? min($t1Vals) : null;
            $highest['term2'][$subj->id]  = count($t2Vals) ? max($t2Vals) : null;
            $lowest['term2'][$subj->id]   = count($t2Vals) ? min($t2Vals) : null;
            $highest['annual'][$subj->id] = count($aVals) ? max($aVals) : null;
            $lowest['annual'][$subj->id]  = count($aVals) ? min($aVals) : null;
        }
        $t1Totals = array_filter(array_column($roster, 'term1_total'));
        $t2Totals = array_filter(array_column($roster, 'term2_total'));
        $aTotals  = array_filter(array_column($roster, 'annual_total'));
        $highest['term1_total']  = count($t1Totals) ? max($t1Totals) : 0;
        $lowest['term1_total']   = count($t1Totals) ? min($t1Totals) : 0;
        $highest['term2_total']  = count($t2Totals) ? max($t2Totals) : 0;
        $lowest['term2_total']   = count($t2Totals) ? min($t2Totals) : 0;
        $highest['annual_total'] = count($aTotals) ? max($aTotals) : 0;
        $lowest['annual_total']  = count($aTotals) ? min($aTotals) : 0;
    @endphp


    {{-- ============================================================ --}}
    {{-- TERM 1 --}}
    {{-- ============================================================ --}}
    <div class="fms-term-section">
        <div class="fms-term-head term1">
            <i class="fas fa-clipboard-list term-icon"></i>
            <span>Term 1{{ $term1 ? ': ' . $term1->name : '' }}</span>
            <span class="term-label">{{ count($roster) }} Students &middot; {{ $subjects->count() }} Subjects</span>
        </div>
        <div class="fms-seq-table-wrap">
            <table class="fms-seq-table term1-table">
                <thead>
                    {{-- Print-only school header row (repeats on each printed page) --}}
                    <tr class="fms-print-school-header print-only" style="display:none;">
                        <th colspan="{{ 2 + $subjects->count() + 3 }}" style="text-align:center;border:1px solid #333;padding:6px 8px;background:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;">
                            <table style="width:100%;border:none;">
                                <tr>
                                    <td style="width:36px;border:none;text-align:left;vertical-align:middle;">
                                        @if(!empty($logoUrl))<img src="{{ $logoUrl }}" alt="" style="width:32px;height:32px;object-fit:contain;" />@endif
                                    </td>
                                    <td style="border:none;text-align:center;vertical-align:middle;">
                                        <div style="font-size:1rem;font-weight:800;color:#000!important;">{{ $schoolName ?? 'School of Redemption' }}</div>
                                        <div style="font-size:.72rem;color:#444!important;margin-top:1px;">
                                            @if($branch){{ $branch->name }} Branch &middot; @endif
                                            @if($class){{ $class->name }}@if($section) - {{ $section->name }}@endif @endif
                                            &middot; @if($academicYear){{ $academicYear->name }}@endif
                                            &middot; Term 1: {{ $term1->name ?? '' }}
                                        </div>
                                    </td>
                                    <td style="width:36px;border:none;text-align:right;vertical-align:middle;">
                                        @if(!empty($logoUrl))<img src="{{ $logoUrl }}" alt="" style="width:32px;height:32px;object-fit:contain;" />@endif
                                    </td>
                                </tr>
                            </table>
                        </th>
                    </tr>
                    <tr>
                        <th class="th-fixed" style="width:40px">#</th>
                        <th class="th-fixed" style="text-align:left;min-width:160px">Student Name</th>
                        @foreach($subjects as $subj)
                        <th class="th-rotated">
                            <div class="th-rotate-inner">
                                <span class="th-rotate-text">{{ $subj->name }}</span>
                            </div>
                        </th>
                        @endforeach
                        <th class="th-fixed">Total</th>
                        <th class="th-fixed">Average</th>
                        <th class="th-fixed">Rank</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roster as $i => $row)
                    <tr @if(!empty($row['is_mid_year'])) style="background:#fffbeb;" @endif>
                        <td>{{ $i + 1 }}</td>
                        <td class="stu-name">{{ $row['student']->full_name ?? '' }}@if(!empty($row['is_mid_year'])) <span style="font-size:.6rem;background:#d97706;color:#fff;padding:1px 4px;border-radius:3px;margin-left:2px;" title="Joined in Term 2">T2</span>@endif</td>
                        @foreach($subjects as $subj)
                            @php $t1 = $row['term1'][$subj->id] ?? null @endphp
                            <td class="{{ $t1 && $t1['grand_total'] !== null ? $markClass($t1['grand_total']) : '' }}">
                                @if($t1 && $t1['grand_total'] !== null)
                                    <span class="mark-val">{{ $t1['grand_total'] }}</span>
                                @else
                                    <span style="color:#d1d5db">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="total-col {{ $markClass($row['term1_total'] ?? null) }}">{{ $row['term1_total'] ?: '-' }}</td>
                        <td class="avg-col {{ $markClass($row['term1_avg'] ?? null) }}">{{ $row['term1_avg'] ?: '-' }}</td>
                        <td class="rank-col">{{ $row['term1_rank'] ?? '-' }}</td>
                    </tr>
                    @endforeach

                    {{-- Class Average --}}
                    <tr class="avg-row term1">
                        <td colspan="2" class="stu-name"><i class="fas fa-chart-bar"></i> Class Average</td>
                        @foreach($subjects as $subj)
                            <td>{{ $averages['term1'][$subj->id] ?? '-' }}</td>
                        @endforeach
                        <td>{{ $averages['term1_total_avg'] ?? '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>

                    {{-- Highest Mark --}}
                    <tr class="highest-row">
                        <td colspan="2" class="stu-name"><i class="fas fa-arrow-up"></i> Highest Mark</td>
                        @foreach($subjects as $subj)
                            <td>{{ $highest['term1'][$subj->id] ?? '-' }}</td>
                        @endforeach
                        <td>{{ $highest['term1_total'] ?: '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>

                    {{-- Lowest Mark --}}
                    <tr class="lowest-row">
                        <td colspan="2" class="stu-name"><i class="fas fa-arrow-down"></i> Lowest Mark</td>
                        @foreach($subjects as $subj)
                            <td>{{ $lowest['term1'][$subj->id] ?? '-' }}</td>
                        @endforeach
                        <td>{{ $lowest['term1_total'] ?: '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
        </div>
        {{-- Term 1 Stats --}}
        <div class="fms-term-stats">
            <div class="fms-stat-card term1">
                <div class="stat-label">Students</div>
                <div class="stat-value">{{ count($roster) }}</div>
            </div>
            <div class="fms-stat-card term1">
                <div class="stat-label">Class Average</div>
                <div class="stat-value">{{ $averages['term1_total_avg'] ?? '-' }}</div>
            </div>
            <div class="fms-stat-card term1">
                <div class="stat-label">Highest Total</div>
                <div class="stat-value">{{ $highest['term1_total'] ?: '-' }}</div>
            </div>
            <div class="fms-stat-card term1">
                <div class="stat-label">Lowest Total</div>
                <div class="stat-value">{{ $lowest['term1_total'] ?: '-' }}</div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TERM 2 --}}
    {{-- ============================================================ --}}
    @if($term2)
    <div class="fms-term-section">
        <div class="fms-term-head term2">
            <i class="fas fa-clipboard-list term-icon"></i>
            <span>Term 2: {{ $term2->name }}</span>
            <span class="term-label">{{ count($roster) }} Students &middot; {{ $subjects->count() }} Subjects</span>
        </div>
        <div class="fms-seq-table-wrap">
            <table class="fms-seq-table term2-table">
                <thead>
                    {{-- Print-only school header row (repeats on each printed page) --}}
                    <tr class="fms-print-school-header print-only" style="display:none;">
                        <th colspan="{{ 2 + $subjects->count() + 3 }}" style="text-align:center;border:1px solid #333;padding:6px 8px;background:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;">
                            <table style="width:100%;border:none;">
                                <tr>
                                    <td style="width:36px;border:none;text-align:left;vertical-align:middle;">
                                        @if(!empty($logoUrl))<img src="{{ $logoUrl }}" alt="" style="width:32px;height:32px;object-fit:contain;" />@endif
                                    </td>
                                    <td style="border:none;text-align:center;vertical-align:middle;">
                                        <div style="font-size:1rem;font-weight:800;color:#000!important;">{{ $schoolName ?? 'School of Redemption' }}</div>
                                        <div style="font-size:.72rem;color:#444!important;margin-top:1px;">
                                            @if($branch){{ $branch->name }} Branch &middot; @endif
                                            @if($class){{ $class->name }}@if($section) - {{ $section->name }}@endif @endif
                                            &middot; @if($academicYear){{ $academicYear->name }}@endif
                                            &middot; Term 2: {{ $term2->name ?? '' }}
                                        </div>
                                    </td>
                                    <td style="width:36px;border:none;text-align:right;vertical-align:middle;">
                                        @if(!empty($logoUrl))<img src="{{ $logoUrl }}" alt="" style="width:32px;height:32px;object-fit:contain;" />@endif
                                    </td>
                                </tr>
                            </table>
                        </th>
                    </tr>
                    <tr>
                        <th class="th-fixed" style="width:40px">#</th>
                        <th class="th-fixed" style="text-align:left;min-width:160px">Student Name</th>
                        @foreach($subjects as $subj)
                        <th class="th-rotated">
                            <div class="th-rotate-inner">
                                <span class="th-rotate-text">{{ $subj->name }}</span>
                            </div>
                        </th>
                        @endforeach
                        <th class="th-fixed">Total</th>
                        <th class="th-fixed">Average</th>
                        <th class="th-fixed">Rank</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roster as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="stu-name">{{ $row['student']->full_name ?? '' }}</td>
                        @foreach($subjects as $subj)
                            @php $t2 = $row['term2'][$subj->id] ?? null @endphp
                            <td class="{{ $t2 && $t2['grand_total'] !== null ? $markClass($t2['grand_total']) : '' }}">
                                @if($t2 && $t2['grand_total'] !== null)
                                    <span class="mark-val">{{ $t2['grand_total'] }}</span>
                                @else
                                    <span style="color:#d1d5db">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="total-col {{ $markClass($row['term2_total'] ?? null) }}">{{ $row['term2_total'] ?: '-' }}</td>
                        <td class="avg-col {{ $markClass($row['term2_avg'] ?? null) }}">{{ $row['term2_avg'] ?: '-' }}</td>
                        <td class="rank-col">{{ $row['term2_rank'] ?? '-' }}</td>
                    </tr>
                    @endforeach

                    <tr class="avg-row term2">
                        <td colspan="2" class="stu-name"><i class="fas fa-chart-bar"></i> Class Average</td>
                        @foreach($subjects as $subj)
                            <td>{{ $averages['term2'][$subj->id] ?? '-' }}</td>
                        @endforeach
                        <td>{{ $averages['term2_total_avg'] ?? '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>

                    <tr class="highest-row">
                        <td colspan="2" class="stu-name"><i class="fas fa-arrow-up"></i> Highest Mark</td>
                        @foreach($subjects as $subj)
                            <td>{{ $highest['term2'][$subj->id] ?? '-' }}</td>
                        @endforeach
                        <td>{{ $highest['term2_total'] ?: '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>

                    <tr class="lowest-row">
                        <td colspan="2" class="stu-name"><i class="fas fa-arrow-down"></i> Lowest Mark</td>
                        @foreach($subjects as $subj)
                            <td>{{ $lowest['term2'][$subj->id] ?? '-' }}</td>
                        @endforeach
                        <td>{{ $lowest['term2_total'] ?: '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="fms-term-stats">
            <div class="fms-stat-card term2">
                <div class="stat-label">Students</div>
                <div class="stat-value">{{ count($roster) }}</div>
            </div>
            <div class="fms-stat-card term2">
                <div class="stat-label">Class Average</div>
                <div class="stat-value">{{ $averages['term2_total_avg'] ?? '-' }}</div>
            </div>
            <div class="fms-stat-card term2">
                <div class="stat-label">Highest Total</div>
                <div class="stat-value">{{ $highest['term2_total'] ?: '-' }}</div>
            </div>
            <div class="fms-stat-card term2">
                <div class="stat-label">Lowest Total</div>
                <div class="stat-value">{{ $lowest['term2_total'] ?: '-' }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- ANNUAL --}}
    {{-- ============================================================ --}}
    <div class="fms-term-section">
        <div class="fms-term-head annual">
            <i class="fas fa-award term-icon"></i>
            <span>Annual Result (Average of Term 1 & Term 2)</span>
            <span class="term-label">{{ count($roster) }} Students &middot; {{ $subjects->count() }} Subjects</span>
        </div>
        <div class="fms-seq-table-wrap">
            <table class="fms-seq-table annual-table">
                <thead>
                    {{-- Print-only school header row (repeats on each printed page) --}}
                    <tr class="fms-print-school-header print-only" style="display:none;">
                        <th colspan="{{ 2 + $subjects->count() + 3 }}" style="text-align:center;border:1px solid #333;padding:6px 8px;background:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact;">
                            <table style="width:100%;border:none;">
                                <tr>
                                    <td style="width:36px;border:none;text-align:left;vertical-align:middle;">
                                        @if(!empty($logoUrl))<img src="{{ $logoUrl }}" alt="" style="width:32px;height:32px;object-fit:contain;" />@endif
                                    </td>
                                    <td style="border:none;text-align:center;vertical-align:middle;">
                                        <div style="font-size:1rem;font-weight:800;color:#000!important;">{{ $schoolName ?? 'School of Redemption' }}</div>
                                        <div style="font-size:.72rem;color:#444!important;margin-top:1px;">
                                            @if($branch){{ $branch->name }} Branch &middot; @endif
                                            @if($class){{ $class->name }}@if($section) - {{ $section->name }}@endif @endif
                                            &middot; @if($academicYear){{ $academicYear->name }}@endif
                                            &middot; Annual Result
                                        </div>
                                    </td>
                                    <td style="width:36px;border:none;text-align:right;vertical-align:middle;">
                                        @if(!empty($logoUrl))<img src="{{ $logoUrl }}" alt="" style="width:32px;height:32px;object-fit:contain;" />@endif
                                    </td>
                                </tr>
                            </table>
                        </th>
                    </tr>
                    <tr>
                        <th class="th-fixed" style="width:40px">#</th>
                        <th class="th-fixed" style="text-align:left;min-width:160px">Student Name</th>
                        @foreach($subjects as $subj)
                        <th class="th-rotated">
                            <div class="th-rotate-inner">
                                <span class="th-rotate-text">{{ $subj->name }}</span>
                            </div>
                        </th>
                        @endforeach
                        <th class="th-fixed">Total</th>
                        <th class="th-fixed">Average</th>
                        <th class="th-fixed">Rank</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roster as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="stu-name">{{ $row['student']->full_name ?? '' }}</td>
                        @foreach($subjects as $subj)
                            @php $ann = $row['annual'][$subj->id] ?? null @endphp
                            <td class="{{ $ann && $ann['grand_total'] !== null ? $markClass($ann['grand_total']) : '' }}">
                                @if($ann && $ann['grand_total'] !== null)
                                    <span class="mark-val">{{ $ann['grand_total'] }}</span>
                                @else
                                    <span style="color:#d1d5db">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="total-col {{ $markClass($row['annual_total'] ?? null) }}">{{ $row['annual_total'] ?: '-' }}</td>
                        <td class="avg-col {{ $markClass($row['annual_avg'] ?? null) }}">{{ $row['annual_avg'] ?: '-' }}</td>
                        <td class="rank-col">{{ $row['annual_rank'] ?? '-' }}</td>
                    </tr>
                    @endforeach

                    <tr class="avg-row annual">
                        <td colspan="2" class="stu-name"><i class="fas fa-chart-bar"></i> Class Average</td>
                        @foreach($subjects as $subj)
                            <td>{{ $averages['annual'][$subj->id] ?? '-' }}</td>
                        @endforeach
                        <td>{{ $averages['annual_total_avg'] ?? '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>

                    <tr class="highest-row">
                        <td colspan="2" class="stu-name"><i class="fas fa-arrow-up"></i> Highest Mark</td>
                        @foreach($subjects as $subj)
                            <td>{{ $highest['annual'][$subj->id] ?? '-' }}</td>
                        @endforeach
                        <td>{{ $highest['annual_total'] ?: '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>

                    <tr class="lowest-row">
                        <td colspan="2" class="stu-name"><i class="fas fa-arrow-down"></i> Lowest Mark</td>
                        @foreach($subjects as $subj)
                            <td>{{ $lowest['annual'][$subj->id] ?? '-' }}</td>
                        @endforeach
                        <td>{{ $lowest['annual_total'] ?: '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="fms-term-stats">
            <div class="fms-stat-card annual">
                <div class="stat-label">Students</div>
                <div class="stat-value">{{ count($roster) }}</div>
            </div>
            <div class="fms-stat-card annual">
                <div class="stat-label">Class Average</div>
                <div class="stat-value">{{ $averages['annual_total_avg'] ?? '-' }}</div>
            </div>
            <div class="fms-stat-card annual">
                <div class="stat-label">Highest Total</div>
                <div class="stat-value">{{ $highest['annual_total'] ?: '-' }}</div>
            </div>
            <div class="fms-stat-card annual">
                <div class="stat-label">Lowest Total</div>
                <div class="stat-value">{{ $lowest['annual_total'] ?: '-' }}</div>
            </div>
        </div>
    </div>

    @else
    <div class="fms-card">
        <div class="fms-empty">
            <i class="fas fa-clipboard-list"></i>
            <p>No marks found for the selected filters.</p>
            <p style="font-size:.82rem;margin-top:.5rem">Please make sure marks have been entered for the selected academic year and class.</p>
        </div>
    </div>
    @endif
    @endisset

    {{-- Signature Section --}}
    @isset($roster)
    <div class="fms-signatures" style="display:flex;justify-content:space-around;margin-top:2rem;padding:1.5rem 2rem;background:#fff;border-radius:10px;border:1px solid #e5e7eb;gap:2rem;flex-wrap:wrap;">
        <div style="text-align:center;min-width:180px;">
            <div style="font-size:.75rem;color:#6b7280;margin-bottom:40px;">Subject Teacher</div>
            <div style="border-top:1px solid #333;padding-top:4px;font-size:.78rem;font-weight:600;color:#1a1a2e;">Name &amp; Signature</div>
            <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;">Date: _______________</div>
        </div>
        <div style="text-align:center;min-width:180px;">
            <div style="font-size:.75rem;color:#6b7280;margin-bottom:40px;">Homeroom Teacher</div>
            <div style="border-top:1px solid #333;padding-top:4px;font-size:.78rem;font-weight:600;color:#1a1a2e;">{{ $homeroomTeacherName ?? '_________________' }}</div>
            <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;">Date: _______________</div>
        </div>
        <div style="text-align:center;min-width:180px;">
            <div style="font-size:.75rem;color:#6b7280;margin-bottom:40px;">Branch Principal</div>
            <div style="border-top:1px solid #333;padding-top:4px;font-size:.78rem;font-weight:600;color:#1a1a2e;">Name &amp; Signature</div>
            <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;">Date: _______________</div>
        </div>
    </div>
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
            fetch('{{ route("admin.mark-sheet-full.sections") }}?class_id='+this.value,{credentials:'same-origin'})
            .then(function(r){return r.json();})
            .then(function(data){
                sec.innerHTML='<option value="">-- All Sections --</option>';
                data.forEach(function(s){sec.innerHTML+='<option value="'+s.id+'">'+s.name+'</option>';});
            });
        });
    }
})();

// ── Student data for XLSX export (gender, age, class, section, comments) ──
var FMS_STUDENT_DATA = {};
@isset($roster)
@if(isset($class) && isset($section))
@php
    $className = $class->name ?? '';
    $sectionName = $section->name ?? '';
@endphp
@foreach($roster as $row)
    @php
        $s = $row['student'];
        $age = '';
        try { $age = $s->date_of_birth ? \Carbon\Carbon::parse($s->date_of_birth)->age . '' : ''; } catch(\Throwable $e) {}
    @endphp
    FMS_STUDENT_DATA[{{ $s->id }}] = {
        gender: {{ json_encode($s->gender ?? '') }},
        age: {{ json_encode($age) }},
        class_name: {{ json_encode($className) }},
        section_name: {{ json_encode($sectionName) }},
    };
@endforeach
@endif
@endisset

// ── Mark-based comment configuration ──
// Returns a comment based on the student's average mark.
// Configured via FMS_COMMENT_RANGES (editable below).
var FMS_COMMENT_RANGES = [
    { min: 90, comment: 'Outstanding performance! Keep up the excellent work.' },
    { min: 80, comment: 'Excellent performance. Your dedication is commendable.' },
    { min: 70, comment: 'Very good performance. Continue striving for excellence.' },
    { min: 60, comment: 'Good performance. With more effort you can achieve more.' },
    { min: 50, comment: 'Satisfactory. Put more effort to improve your results.' },
    { min: 40, comment: 'Below average. You need to work harder and seek help.' },
    { min: 0,  comment: 'Poor performance. Please seek additional support.' },
];
function fmsGetCommentForMark(avg) {
    if (!avg || avg <= 0) return '';
    for (var i = 0; i < FMS_COMMENT_RANGES.length; i++) {
        if (avg >= FMS_COMMENT_RANGES[i].min) return FMS_COMMENT_RANGES[i].comment;
    }
    return '';
}

function exportCSV(){
    var tables=document.querySelectorAll('.fms-seq-table');
    if(!tables.length)return;

    // ── Single-sheet XLSX export: all tables in sequence on one sheet ──
    if(typeof XLSX !== 'undefined'){
        var wsData = [];
        var studentKeys = Object.keys(FMS_STUDENT_DATA);

        tables.forEach(function(table, tableIdx){
            var sectionHead=table.closest('.fms-term-section');
            // Add section header as a title row
            if(sectionHead){
                var headDiv=sectionHead.querySelector('.fms-term-head');
                if(headDiv){
                    var titleText=headDiv.innerText.trim();
                    wsData.push([titleText]);  // Section title row
                }
            }

            var rows = table.querySelectorAll('tr');
            var dataRowCount = 0;
            rows.forEach(function(row, rowIdx) {
                var cols = row.querySelectorAll('td,th');
                var rowData = [];
                cols.forEach(function(col) {
                    var clone = col.cloneNode(true);
                    var badges = clone.querySelectorAll('span');
                    badges.forEach(function(b) { b.remove(); });
                    rowData.push(clone.innerText.trim().replace(/\n/g,' '));
                });

                if (rowIdx === 0) {
                    // Header row: insert Gender, Age, Class, Section after Student Name
                    // and Comment at the end
                    var newHeader = [rowData[0], 'Gender', 'Age', 'Class', 'Section'];
                    for (var i = 2; i < rowData.length; i++) newHeader.push(rowData[i]);
                    newHeader.push('Comment');
                    wsData.push(newHeader);
                } else {
                    // Skip summary rows (Class Average, Highest, Lowest)
                    var firstCell = (rowData[0] || '').toLowerCase();
                    if (firstCell.indexOf('class average') !== -1 || firstCell.indexOf('highest') !== -1 || firstCell.indexOf('lowest') !== -1) {
                        // Still add them but without extra columns
                        wsData.push(rowData);
                        return;
                    }

                    // Data row: insert student info after Student Name
                    var newRow = [rowData[0]]; // #
                    newRow.push(rowData[1]);   // Student Name

                    // Match student by index
                    if (studentKeys[dataRowCount]) {
                        var info = FMS_STUDENT_DATA[studentKeys[dataRowCount]];
                        newRow.push(info.gender || '');
                        newRow.push(info.age || '');
                        newRow.push(info.class_name || '');
                        newRow.push(info.section_name || '');
                    } else {
                        newRow.push('', '', '', '');
                    }

                    // Add remaining columns (subjects, Total, Average, Rank)
                    for (var j = 2; j < rowData.length; j++) newRow.push(rowData[j]);

                    // Add comment based on average (second-to-last column)
                    var avgIdx = rowData.length - 2;
                    var avgVal = parseFloat(rowData[avgIdx]) || 0;
                    newRow.push(fmsGetCommentForMark(avgVal));

                    wsData.push(newRow);
                    dataRowCount++;
                }
            });

            // Add empty row between sections
            wsData.push([]);
        });

        var wb = XLSX.utils.book_new();
        var ws = XLSX.utils.aoa_to_sheet(wsData);
        XLSX.utils.book_append_sheet(wb, ws, 'Full Mark Sheet');
        XLSX.writeFile(wb, 'mark_sheet_full.xlsx');
        return;
    }

    // Fallback: CSV with BOM (single file, all tables concatenated)
    var csv=[];
    tables.forEach(function(table){
        var sectionHead=table.closest('.fms-term-section');
        if(sectionHead){
            var headDiv=sectionHead.querySelector('.fms-term-head');
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
    link.download='mark_sheet_full.csv';
    link.click();
}

// Print with A4 landscape pre-selected
// The @page CSS rule (size:A4 landscape) tells the browser to use A4.
// We also add a body class that triggers fit-to-page scaling.
function fmsPrint(){
    document.body.classList.add('printing-a4');
    // Small delay to let CSS apply before print dialog opens
    setTimeout(function(){
        window.print();
        // Clean up after print dialog closes
        setTimeout(function(){ document.body.classList.remove('printing-a4'); }, 500);
    }, 50);
}
</script>
@endpush
