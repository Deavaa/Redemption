@extends('layouts.admin')
@section('title', 'Mark List — Summary (3 Rows Per Student)')

@push('styles')
<style>
.ms-page{animation:msIn .4s ease-out;max-width:1400px;margin:0 auto;}
@keyframes msIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.ms-card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;margin-bottom:1rem;}
.ms-card-head{display:flex;align-items:center;gap:.75rem;padding:.75rem 1.25rem;border-bottom:1px solid #f0f0f0;background:#fafbfc;border-radius:12px 12px 0 0;}
.ms-card-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;background:#eef2ff;color:#4361ee;}
.ms-card-title{font-size:.95rem;font-weight:700;color:#1a1a2e;margin:0;}
.ms-card-desc{font-size:.78rem;color:#9ca3af;margin:.1rem 0 0;}
.ms-card-body{padding:1rem 1.25rem;}
.ms-table-wrap{overflow-x:auto;max-height:calc(100vh - 300px);overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;}
.ms-table{width:100%;border-collapse:collapse;font-size:.75rem;}
.ms-table th{border:1px solid #e5e7eb;font-weight:700;padding:2px 4px;text-align:center;background:#f9fafb;position:sticky;top:0;z-index:5;white-space:nowrap;}
.ms-table td{border:1px solid #e5e7eb;padding:1px 3px;text-align:right;font-variant-numeric:tabular-nums;}
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
@page{size:A4 landscape;margin:6mm;}
@media print{
    html,body{margin:0!important;padding:0!important;width:100%!important;font-size:105%!important;}
    .admin-wrapper,.admin-sidebar,.admin-topbar,.sidebar-backdrop,.sidebar-footer,.sidebar-toggle,
    .no-print,.global-alert,.mobile-bottom-nav,.swipe-indicator,#adminAnnouncementBar{display:none!important;}
    .admin-wrapper{display:block!important;margin:0!important;padding:0!important;}
    .admin-main{margin:0!important;padding:0!important;width:100%!important;max-width:100%!important;display:block!important;}
    .admin-content{margin:0!important;padding:0!important;width:100%!important;max-width:100%!important;display:block!important;overflow:visible!important;}
    .ms-page{width:100%!important;max-width:none!important;padding:0!important;margin:0!important;}
    .ms-card{box-shadow:none!important;border:none!important;margin:0!important;padding:0!important;}
    .ms-card-head,.ms-card-body{padding:2mm!important;}
    .ms-table-wrap{overflow:visible!important;width:100%!important;max-width:100%!important;border:none!important;max-height:none!important;}
    .ms-table{font-size:8pt!important;width:100%!important;border-collapse:collapse!important;}
    .ms-table th{padding:3px 5px!important;white-space:nowrap!important;border:1px solid #333!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table td{padding:2px 5px!important;font-size:8pt!important;white-space:nowrap!important;border:1px solid #333!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
    .ms-table .stu-name{white-space:nowrap!important;width:auto!important;min-width:150px!important;max-width:none!important;overflow:visible!important;position:static!important;}
    .ms-table .term-label{white-space:nowrap!important;width:auto!important;}
    .ms-table thead{display:table-header-group!important;}
    .ms-table .student-group{page-break-inside:avoid!important;break-inside:avoid!important;}
    .ms-table .student-group.page-break-after{page-break-after:always!important;break-after:page!important;}
    /* Stats table: show on first page only, hide on subsequent pages */
    .ms-stats-table{page-break-inside:avoid!important;break-inside:avoid!important;}
    /* Hide web-only elements in print */
    .ms-watermark{position:fixed!important;top:50%!important;left:50%!important;transform:translate(-50%,-50%)!important;width:300px!important;height:300px!important;opacity:0.06!important;z-index:-1!important;pointer-events:none!important;object-fit:contain!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
}
</style>
@endpush

@section('content')
<div class="ms-page">
    @if(!empty($logoUrl))
    <img src="{{ $logoUrl }}" alt="" class="ms-watermark" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:350px;height:350px;opacity:0.05;z-index:0;pointer-events:none;object-fit:contain;" />
    {{-- Print-only watermark — force visible in print --}}
    <style>@media print{.ms-watermark{display:block!important;opacity:0.06!important;}}</style>
    @endif

    {{-- Report Header --}}
    <div style="text-align:center;margin-bottom:.5rem;padding:.25rem .5rem;background:#fff;border-radius:6px;border:1px solid #e5e7eb;position:relative;">
        @if(!empty($logoUrl))
        <img src="{{ $logoUrl }}" alt="Logo" style="position:absolute;top:2px;left:4px;width:36px;height:36px;object-fit:contain;" />
        <img src="{{ $logoUrl }}" alt="Logo" style="position:absolute;top:2px;right:4px;width:36px;height:36px;object-fit:contain;" />
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

    {{-- Actions --}}
    <div class="ms-card no-print" style="margin-bottom:.5rem;">
        <div style="display:flex;justify-content:flex-end;gap:.5rem;padding:.4rem 1rem;">
            <a href="{{ route('admin.mark-roster.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
            <button onclick="window.print()" class="btn btn-sm btn-outline-primary"><i class="fas fa-print"></i> Print</button>
            <button onclick="exportSummaryXLSX()" class="btn btn-sm btn-outline-success"><i class="fas fa-file-csv"></i> Export XLSX</button>
        </div>
    </div>

    {{-- Summary Table --}}
    <div class="ms-card">
        <div class="ms-table-wrap">
            <table class="ms-table" id="summaryTable">
                <thead>
                    <tr>
                        <th style="text-align:left;min-width:130px;position:sticky;left:0;z-index:6;background:#f9fafb;">Student Name</th>
                        <th style="min-width:55px;">Term</th>
                        @foreach($subjects as $subj)
                        <th style="min-width:50px;">{{ $subj->name }}</th>
                        @endforeach
                        <th class="total-col">Total</th>
                        <th class="avg-col">Average</th>
                        <th class="rank-col">Rank</th>
                    </tr>
                </thead>
                <tbody>
                    @php $studentNum = 0; @endphp
                    @foreach($roster as $studentRows)
                        @php $studentNum++; @endphp
                        <tbody class="student-group{{ $studentNum % 5 == 0 ? ' page-break-after' : '' }}">
                        @foreach(['term1', 'term2', 'annual'] as $termKey)
                        <tr class="{{ $termKey }}-row">
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
                        </tbody>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Summary Statistics: pass/fail breakdown by gender (ANNUAL ONLY) --}}
    {{-- Print only on first page (no-print class hides on screen, page-break-after forces to first page) --}}
    @php
        $stats = ['above50' => ['M' => 0, 'F' => 0, 'total' => 0], 'below50' => ['M' => 0, 'F' => 0, 'total' => 0]];
        foreach ($roster as $studentRows) {
            $gender = strtoupper(substr($studentRows['term1']['student']->gender ?? 'M', 0, 1));
            if ($gender !== 'M' && $gender !== 'F') $gender = 'M';
            $avg = $studentRows['annual']['average'] ?? 0;
            if ($avg > 0) {
                if ($avg >= 50) {
                    $stats['above50'][$gender]++;
                    $stats['above50']['total']++;
                } else {
                    $stats['below50'][$gender]++;
                    $stats['below50']['total']++;
                }
            }
        }
    @endphp
    <div class="ms-stats-table" style="display:flex;justify-content:flex-start;margin-top:1rem;">
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

    {{-- Signature Section: Homeroom + Principal only (no Subject Teacher) --}}
    <div style="display:flex;justify-content:space-around;margin-top:2rem;padding:1.5rem 2rem;background:#fff;border-radius:10px;border:1px solid #e5e7eb;gap:2rem;flex-wrap:wrap;">
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
        // Fallback: CSV
        var csv = ['\uFEFF'];
        table.querySelectorAll('tr').forEach(function(row) {
            var cells = row.querySelectorAll('td,th');
            var rowData = [];
            cells.forEach(function(col) {
                rowData.push('"' + col.innerText.trim().replace(/"/g, '""') + '"');
            });
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
