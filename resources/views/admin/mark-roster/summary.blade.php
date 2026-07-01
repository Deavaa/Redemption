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
.ms-table td{border:1px solid #e5e7eb;padding:1px 3px;text-align:center;}
.ms-table .stu-name{text-align:left;white-space:nowrap;font-weight:600;color:#1a1a2e;min-width:130px;max-width:200px;overflow:hidden;text-overflow:ellipsis;}
.ms-table .term-label{font-size:.65rem;font-weight:700;color:#6b7280;text-align:left;min-width:55px;white-space:nowrap;}
.ms-table .total-col{font-weight:700;background:#f0f4ff;color:#4361ee;}
.ms-table .avg-col{font-weight:700;background:#eef2ff;color:#6366f1;}
.ms-table .rank-col{font-weight:700;background:#f0fdf4;color:#059669;}
.ms-table .term1-row{background:#eff6ff;}
.ms-table .term2-row{background:#f5f3ff;}
.ms-table .annual-row{background:#f0fdf4;font-weight:600;}
.ms-table .mark-red{color:#dc2626;font-weight:700;}
.ms-table .mark-amber{color:#d97706;font-weight:700;}
.ms-table .mark-green{color:#059669;font-weight:700;}
@page{size:A4 landscape;margin:6mm;}
@media print{
    .admin-wrapper,.admin-sidebar,.admin-topbar,.sidebar-backdrop,.sidebar-footer,.sidebar-toggle,
    .no-print,.global-alert,.mobile-bottom-nav,.swipe-indicator,#adminAnnouncementBar{display:none!important;}
    .ms-page{width:100%!important;max-width:100%!important;padding:0!important;margin:0!important;}
    .ms-table{font-size:7pt!important;table-layout:fixed!important;width:100%!important;border-collapse:collapse!important;}
    .ms-table .stu-name{min-width:100px!important;width:120px!important;max-width:120px!important;position:static!important;white-space:nowrap!important;overflow:visible!important;}
    .ms-table thead{display:table-header-group!important;}
    .ms-table tbody tr{page-break-inside:auto!important;}
    .ms-card{box-shadow:none!important;border:none!important;}
    .ms-watermark{position:fixed!important;top:50%!important;left:50%!important;transform:translate(-50%,-50%)!important;width:300px!important;height:300px!important;opacity:0.06!important;z-index:-1!important;pointer-events:none!important;object-fit:contain!important;-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}
}
</style>
@endpush

@section('content')
<div class="ms-page">
    @if(!empty($logoUrl))
    <img src="{{ $logoUrl }}" alt="" class="ms-watermark no-print" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:350px;height:350px;opacity:0.05;z-index:0;pointer-events:none;object-fit:contain;" />
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
                        @foreach(['term1', 'term2', 'annual'] as $termKey)
                        <tr class="{{ $termKey }}-row">
                            @if($termKey === 'term1')
                            <td class="stu-name" rowspan="3">{{ $studentRows['term1']['student']->full_name ?? '' }}</td>
                            @endif
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
                                <td class="{{ $markClass }}">{{ $mark !== null ? $mark : '-' }}</td>
                            @endforeach
                            <td class="total-col">{{ $studentRows[$termKey]['total'] > 0 ? $studentRows[$termKey]['total'] : '-' }}</td>
                            <td class="avg-col">{{ $studentRows[$termKey]['average'] > 0 ? $studentRows[$termKey]['average'] : '-' }}</td>
                            <td class="rank-col">{{ $studentRows[$termKey]['rank'] }}</td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Signature Section --}}
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
