@extends('layouts.admin')
@section('title', 'Mark List — Summary (3 Rows Per Student)')

@push('styles')
<style>
.ms-page{max-width:1400px;margin:0 auto;}
.ms-card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;margin-bottom:1rem;}
.ms-table-wrap{overflow-x:auto;border:1px solid #e5e7eb;border-radius:8px;}
.ms-table{width:100%;border-collapse:collapse;font-size:.75rem;}
.ms-table th{border:1px solid #e5e7eb;font-weight:700;padding:4px 6px;text-align:center;background:#f9fafb;white-space:nowrap;}
.ms-table td{border:1px solid #e5e7eb;padding:2px 4px;text-align:right;font-variant-numeric:tabular-nums;}
.ms-table .stu-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e;min-width:180px;max-width:250px;overflow:hidden;text-overflow:ellipsis;}
.ms-table .term-label{font-size:.65rem;font-weight:700;color:#6b7280;text-align:left;min-width:55px;white-space:nowrap;}
.ms-table .total-col{font-weight:700;background:#f0f4ff;color:#4361ee;text-align:right;}
.ms-table .avg-col{font-weight:700;background:#eef2ff;color:#6366f1;text-align:right;}
.ms-table .rank-col{font-weight:700;background:#f0fdf4;color:#059669;text-align:center;}
.ms-table .term1-row{background:#eff6ff;}
.ms-table .term2-row{background:#f5f3ff;}
.ms-table .annual-row{background:#f0fdf4;font-weight:600;}
.ms-table .mark-red{color:#dc2626;font-weight:700;}
.ms-table .mark-amber{color:#d97706;font-weight:700;}
.ms-table .mark-green{color:#059669;font-weight:700;}
.ms-table .page-break-row{page-break-after:always;break-after:page;}
.ms-stats-inline td{border:none!important;background:transparent!important;padding:8px 0 4px 0!important;}

/* PRINT */
@page{size:A4 landscape;margin:10mm;}

@media print{
    body *{visibility:hidden;}
    .ms-page,.ms-page *{visibility:visible;}
    .ms-page{position:absolute;left:0;top:0;width:100%;}

    html,body{margin:0!important;padding:0!important;width:100%!important;background:#fff!important;font-size:9pt!important;}
    .admin-wrapper,.admin-sidebar,.sidebar-backdrop,.admin-topbar,.sidebar-footer,.sidebar-toggle,
    .no-print,.global-alert,.mobile-bottom-nav,.swipe-indicator,#adminAnnouncementBar,.navbar,.footer,
    .mobile-drawer,.mobile-drawer-overlay,.cursor-dot,.cursor-ring,.ms-signatures-screen{display:none!important;}
    .admin-wrapper{display:block!important;margin:0!important;padding:0!important;}
    .admin-main,.admin-content{margin:0!important;padding:0!important;width:100%!important;max-width:100%!important;display:block!important;overflow:visible!important;}
    .ms-page{width:100%!important;max-width:none!important;padding:0!important;margin:0!important;}
    .ms-card{box-shadow:none!important;border:none!important;margin:0!important;padding:0!important;border-radius:0!important;}

    .ms-table-wrap{overflow:visible!important;width:100%!important;border:none!important;}
    .ms-table{font-size:7.5pt!important;width:100%!important;border-collapse:collapse!important;}
    .ms-table th{padding:3px 4px!important;border:0.75pt solid #333!important;background:#e8eef5!important;font-size:7.35pt!important;font-weight:700!important;color:#1a1a2e!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table td{padding:2px 4px!important;font-size:7.875pt!important;border:0.75pt solid #333!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table .stu-name{min-width:120px!important;white-space:nowrap!important;}
    .ms-table .term1-row{background:#eff6ff!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table .term2-row{background:#f5f3ff!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table .annual-row{background:#f0fdf4!important;font-weight:700!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table .total-col{background:#dbeafe!important;color:#1e40af!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table .avg-col{background:#e0e7ff!important;color:#4338ca!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table .rank-col{background:#d1fae5!important;color:#065f46!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table .mark-red{color:#dc2626!important;font-weight:700!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table .mark-amber{color:#d97706!important;font-weight:700!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table .mark-green{color:#059669!important;font-weight:700!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table thead{display:table-header-group!important;}
    .ms-table tfoot{display:table-footer-group!important;}

    /* Page break */
    .ms-table .page-break-row{page-break-after:always!important;break-after:page!important;}

    /* Watermark — logo centered behind content on every page */
    .ms-watermark{position:fixed!important;top:50%!important;left:50%!important;transform:translate(-50%,-50%)!important;width:400px!important;height:400px!important;opacity:0.08!important;z-index:0!important;pointer-events:none!important;object-fit:contain!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;visibility:visible!important;display:block!important;}

    /* Page number — fixed at bottom-right of every page */
    .ms-page-num{position:fixed!important;bottom:5mm!important;right:10mm!important;font-size:7pt!important;color:#666!important;visibility:visible!important;z-index:9999!important;}
}
</style>
@endpush

@section('content')
<div class="ms-page">
    @if(!empty($logoUrl))
    <img src="{{ $logoUrl }}" alt="" class="ms-watermark" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:350px;height:350px;opacity:0.05;z-index:0;pointer-events:none;object-fit:contain;" />
    @endif

    {{-- Page number — shows on every printed page (bottom-right) --}}
    <div class="ms-page-num" style="display:none;">Page <span id="pageNum"></span></div>

    {{-- Report Header --}}
    <div style="text-align:center;margin-bottom:.5rem;padding:.5rem;background:#fff;border-radius:6px;border:1px solid #e5e7eb;position:relative;">
        @if(!empty($logoUrl))
        <img src="{{ $logoUrl }}" alt="Logo" style="position:absolute;top:4px;left:8px;width:36px;height:36px;object-fit:contain;" />
        <img src="{{ $logoUrl }}" alt="Logo" style="position:absolute;top:4px;right:8px;width:36px;height:36px;object-fit:contain;" />
        @endif
        <div style="{{ !empty($logoUrl) ? 'padding:0 44px;' : '' }}">
            <div style="font-size:1rem;font-weight:800;color:#1a1a2e;">{{ $schoolName ?? 'School of Redemption' }}</div>
            <div style="font-size:.72rem;color:#374151;line-height:1.3;margin-top:1px;">
                @if($branch)<strong>{{ $branch->name ?? '' }}</strong> &middot; @endif
                <strong>Mark List (Summary)</strong> &middot;
                @if($class)<strong>{{ $class->name ?? '' }}</strong>@if($section) - {{ $section->name }}@endif @endif
                &middot; @if($academicYear){{ $academicYear->name }}@endif
            </div>
        </div>
    </div>

    {{-- Actions (screen only) --}}
    <div class="no-print" style="display:flex;justify-content:flex-end;gap:.5rem;margin-bottom:.5rem;">
        <a href="{{ route('admin.mark-roster.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <button onclick="window.print()" class="btn btn-sm btn-outline-primary"><i class="fas fa-print"></i> Print</button>
        <button onclick="exportSummaryXLSX()" class="btn btn-sm btn-outline-success"><i class="fas fa-file-csv"></i> Export XLSX</button>
    </div>

    @php
        $studentNum = 0;
        $totalStudents = count($roster);
        $stats = ['above50' => ['M' => 0, 'F' => 0, 'total' => 0], 'below50' => ['M' => 0, 'F' => 0, 'total' => 0]];
        foreach ($roster as $studentRows) {
            $gender = strtoupper(substr($studentRows['term1']['student']->gender ?? 'M', 0, 1));
            if ($gender !== 'M' && $gender !== 'F') $gender = 'M';
            $avg = $studentRows['annual']['average'] ?? 0;
            if ($avg > 0) {
                if ($avg >= 50) { $stats['above50'][$gender]++; $stats['above50']['total']++; }
                else { $stats['below50'][$gender]++; $stats['below50']['total']++; }
            }
        }
        $colspan = 3 + count($subjects) + 3;
    @endphp

    {{-- Main Table — uses thead (repeats on every page) + tfoot (signatures repeat on every page) --}}
    <div class="ms-card">
        <div class="ms-table-wrap">
            <table class="ms-table" id="summaryTable">
                {{-- Column headers — repeats on every printed page --}}
                <thead>
                    <tr>
                        <th style="text-align:left;min-width:130px;">Student Name</th>
                        <th style="min-width:55px;">Term</th>
                        @foreach($subjects as $subj)
                        <th style="min-width:50px;">{{ $subj->name }}</th>
                        @endforeach
                        <th class="total-col">Total</th>
                        <th class="avg-col">Average</th>
                        <th class="rank-col">Rank</th>
                    </tr>
                </thead>

                {{-- Signature footer — tfoot repeats on EVERY printed page automatically --}}
                <tfoot>
                    <tr>
                        <td colspan="{{ $colspan }}" style="border:none!important;padding:20px 0 0 0!important;background:transparent!important;">
                            <div style="display:flex;justify-content:space-around;gap:2rem;flex-wrap:wrap;">
                                <div style="text-align:center;min-width:180px;">
                                    <div style="font-size:7pt;color:#666;margin-bottom:25px;">Homeroom Teacher</div>
                                    <div style="border-top:0.5pt solid #333;padding-top:2px;font-size:7pt;font-weight:600;color:#1a1a2e;">Name &amp; Signature</div>
                                    <div style="font-size:6pt;color:#999;margin-top:1px;">Date: _______________</div>
                                </div>
                                <div style="text-align:center;min-width:180px;">
                                    <div style="font-size:7pt;color:#666;margin-bottom:25px;">Branch Principal</div>
                                    <div style="border-top:0.5pt solid #333;padding-top:2px;font-size:7pt;font-weight:600;color:#1a1a2e;">Name &amp; Signature</div>
                                    <div style="font-size:6pt;color:#999;margin-top:1px;">Date: _______________</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tfoot>

                {{-- Student data rows --}}
                <tbody>
                    @foreach($roster as $studentRows)
                        @php $studentNum++; @endphp

                        @foreach(['term1', 'term2', 'annual'] as $termKey)
                        @php
                            $isAnnualRow = ($termKey === 'annual');
                            $isLastStudent = ($studentNum == $totalStudents);
                            // Page break logic:
                            // FIRST PAGE: 5 students + stats table, then break
                            //   → NO break on student 5's annual row (stats goes after, then break)
                            // SUBSEQUENT PAGES: 6 students, break after annual row
                            //   → Break after students 11, 17, 23...
                            // Last student: NO break (prevents blank page)
                            $shouldBreak = false;
                            if ($isAnnualRow && !$isLastStudent) {
                                // Skip student 5 (break is on stats row instead)
                                // Break after students 11, 17, 23, 29...
                                if ($studentNum > 5 && ($studentNum - 5) % 6 == 0) {
                                    $shouldBreak = true;
                                }
                            }
                            $rowClass = $termKey . '-row';
                            if ($shouldBreak) {
                                $rowClass .= ' page-break-row';
                            }
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="stu-name">{{ $termKey === 'term1' ? ($studentRows['term1']['student']->full_name ?? '') : '' }}</td>
                            <td class="term-label">{{ $studentRows[$termKey]['term_label'] }}</td>
                            @foreach($subjects as $subj)
                                @php
                                    $mark = $studentRows[$termKey]['subjects'][$subj->id] ?? null;
                                    $markClass = '';
                                    if ($mark !== null && $mark !== '') {
                                        if ($mark < 50) $markClass = 'mark-red';
                                        elseif ($mark < 70) $markClass = 'mark-amber';
                                        else $markClass = 'mark-green';
                                    }
                                @endphp
                                <td class="{{ $markClass }}">{{ $mark !== null ? number_format($mark, 2) : '-' }}</td>
                            @endforeach
                            <td class="total-col">{{ $studentRows[$termKey]['total'] > 0 ? number_format($studentRows[$termKey]['total'], 2) : '-' }}</td>
                            <td class="avg-col">{{ $studentRows[$termKey]['average'] > 0 ? number_format($studentRows[$termKey]['average'], 2) : '-' }}</td>
                            <td class="rank-col">{{ $studentRows[$termKey]['rank'] }}</td>
                        </tr>
                        @endforeach

                        {{-- Stats table after the 5th student (first page bottom) --}}
                        {{-- Also show after last student if total <= 5 --}}
                        {{-- The stats row gets page-break-after so it stays on page 1 and page 2 starts fresh --}}
                        @if($studentNum == 5 || ($isLastStudent && $studentNum < 5))
                        @php $statsBreakClass = ($studentNum == 5 && !$isLastStudent) ? ' page-break-row' : ''; @endphp
                        <tr class="ms-stats-inline{{ $statsBreakClass }}">
                            <td colspan="{{ $colspan }}" style="border:none;padding:8px 0 4px 0;">
                                <table style="border-collapse:collapse;font-size:.72rem;border:1px solid #333;">
                                    <thead>
                                        <tr style="background:#f9fafb;">
                                            <th style="border:1px solid #333;padding:3px 8px;text-align:left;">Annual Result Summary</th>
                                            <th style="border:1px solid #333;padding:3px 8px;">Male</th>
                                            <th style="border:1px solid #333;padding:3px 8px;">Female</th>
                                            <th style="border:1px solid #333;padding:3px 8px;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td style="border:1px solid #333;padding:2px 8px;color:#dc2626;">Below 50% (Fail)</td>
                                            <td style="border:1px solid #333;padding:2px 8px;text-align:center;">{{ $stats['below50']['M'] }}</td>
                                            <td style="border:1px solid #333;padding:2px 8px;text-align:center;">{{ $stats['below50']['F'] }}</td>
                                            <td style="border:1px solid #333;padding:2px 8px;text-align:center;font-weight:700;">{{ $stats['below50']['total'] }}</td>
                                        </tr>
                                        <tr>
                                            <td style="border:1px solid #333;padding:2px 8px;color:#059669;">Above 50% (Pass)</td>
                                            <td style="border:1px solid #333;padding:2px 8px;text-align:center;">{{ $stats['above50']['M'] }}</td>
                                            <td style="border:1px solid #333;padding:2px 8px;text-align:center;">{{ $stats['above50']['F'] }}</td>
                                            <td style="border:1px solid #333;padding:2px 8px;text-align:center;font-weight:700;">{{ $stats['above50']['total'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Screen-only stats table (for screen view — hidden in print) --}}
    <div class="no-print" style="margin-top:1rem;">
        <table style="border-collapse:collapse;font-size:.72rem;border:1px solid #333;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="border:1px solid #333;padding:3px 8px;text-align:left;">Annual Result Summary</th>
                    <th style="border:1px solid #333;padding:3px 8px;">Male</th>
                    <th style="border:1px solid #333;padding:3px 8px;">Female</th>
                    <th style="border:1px solid #333;padding:3px 8px;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border:1px solid #333;padding:2px 8px;color:#dc2626;">Below 50% (Fail)</td>
                    <td style="border:1px solid #333;padding:2px 8px;text-align:center;">{{ $stats['below50']['M'] }}</td>
                    <td style="border:1px solid #333;padding:2px 8px;text-align:center;">{{ $stats['below50']['F'] }}</td>
                    <td style="border:1px solid #333;padding:2px 8px;text-align:center;font-weight:700;">{{ $stats['below50']['total'] }}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #333;padding:2px 8px;color:#059669;">Above 50% (Pass)</td>
                    <td style="border:1px solid #333;padding:2px 8px;text-align:center;">{{ $stats['above50']['M'] }}</td>
                    <td style="border:1px solid #333;padding:2px 8px;text-align:center;">{{ $stats['above50']['F'] }}</td>
                    <td style="border:1px solid #333;padding:2px 8px;text-align:center;font-weight:700;">{{ $stats['above50']['total'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Screen-only signatures (print uses tfoot above) --}}
    <div class="ms-signatures-screen no-print" style="display:flex;justify-content:space-around;margin-top:2rem;padding:1.5rem 2rem;background:#fff;border-radius:10px;border:1px solid #e5e7eb;gap:2rem;flex-wrap:wrap;">
        <div style="text-align:center;min-width:200px;">
            <div style="font-size:.75rem;color:#6b7280;margin-bottom:40px;">Homeroom Teacher</div>
            <div style="border-top:1px solid #333;padding-top:4px;font-size:.78rem;font-weight:600;color:#1a1a2e;">Name &amp; Signature</div>
            <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;">Date: _______________</div>
        </div>
        <div style="text-align:center;min-width:200px;">
            <div style="font-size:.75rem;color:#6b7280;margin-bottom:40px;">Branch Principal</div>
            <div style="border-top:1px solid #333;padding-top:4px;font-size:.78rem;font-weight:600;color:#1a1a2e;">Name &amp; Signature</div>
            <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;">Date: _______________</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportSummaryXLSX() {
    var table = document.getElementById('summaryTable');
    if (!table || typeof XLSX === 'undefined') {
        var csv = ['\uFEFF'];
        table.querySelectorAll('tr').forEach(function(row) {
            var cells = row.querySelectorAll('td,th');
            var rowData = [];
            cells.forEach(function(col) { rowData.push('"' + col.innerText.trim().replace(/"/g, '""') + '"'); });
            csv.push(rowData.join(','));
        });
        var blob = new Blob([csv.join('\n')], {type: 'text/csv;charset=utf-8;'});
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'mark_list_summary.csv';
        link.click();
        return;
    }
    var ws = XLSX.utils.table_to_sheet(table);
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Mark List Summary');
    XLSX.writeFile(wb, 'mark_list_summary.xlsx');
}
</script>
@endpush
@endsection
