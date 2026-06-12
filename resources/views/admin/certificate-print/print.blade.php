<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Print — {{ $student->full_name }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1e293b;
            color: #e2e8f0;
            min-height: 100vh;
        }

        /* ========== CONTROL PANEL (hidden during print) ========== */
        .control-panel {
            background: #0f172a;
            border-bottom: 1px solid #334155;
            padding: 10px 16px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .control-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 6px;
        }
        .control-row:last-child { margin-bottom: 0; }

        .control-label {
            font-size: 0.7rem;
            color: #94a3b8;
            font-weight: 600;
            min-width: 60px;
        }

        .control-select, .control-input {
            background: #1e293b;
            border: 1px solid #475569;
            color: #e2e8f0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.72rem;
            outline: none;
        }
        .control-select:focus, .control-input:focus { border-color: #3b82f6; }
        .control-input { width: 60px; text-align: center; }

        .control-btn {
            background: #1e293b;
            border: 1px solid #475569;
            color: #e2e8f0;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all .15s;
        }
        .control-btn:hover { background: #334155; border-color: #3b82f6; }
        .control-btn.active { background: #3b82f6; border-color: #3b82f6; color: #fff; }
        .control-btn.primary { background: #3b82f6; border-color: #3b82f6; color: #fff; }
        .control-btn.primary:hover { background: #2563eb; }
        .control-btn.danger { background: #dc2626; border-color: #dc2626; color: #fff; }
        .control-btn.danger:hover { background: #b91c1c; }

        .template-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: rgba(59,130,246,0.2);
            color: #60a5fa;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .page-tab {
            padding: 4px 14px;
            border-radius: 4px;
            font-size: 0.72rem;
            cursor: pointer;
            border: 1px solid #475569;
            background: #1e293b;
            color: #94a3b8;
            transition: all .15s;
        }
        .page-tab.active { background: #3b82f6; border-color: #3b82f6; color: #fff; }

        /* ========== CERTIFICATE SHEET ========== */
        .cert-workspace {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            gap: 30px;
        }

        .cert-sheet {
            width: 297mm;
            height: 210mm;
            position: relative;
            background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,.4);
            overflow: hidden;
            flex-shrink: 0;
        }

        .page-label {
            position: absolute;
            top: -22px;
            left: 0;
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 600;
        }

        /* ========== DRAGGABLE FIELDS ========== */
        .cert-field {
            position: absolute;
            cursor: move;
            user-select: none;
            white-space: nowrap;
            transition: outline 0.1s;
            outline: 1px dashed rgba(59,130,246,0.3);
            padding: 1px 3px;
        }
        .cert-field:hover { outline: 2px dashed rgba(59,130,246,0.6); }
        .cert-field.selected { outline: 2px solid #3b82f6; background: rgba(59,130,246,0.05); }

        .cert-field-table {
            position: absolute;
            cursor: move;
            user-select: none;
            transition: outline 0.1s;
            outline: 1px dashed rgba(59,130,246,0.3);
        }
        .cert-field-table:hover { outline: 2px dashed rgba(59,130,246,0.6); }
        .cert-field-table.selected { outline: 2px solid #3b82f6; }
        .cert-field-table table { border-collapse: collapse; }
        .cert-field-table td {
            border: 1px solid #999;
            padding: 2px 5px;
            font-size: inherit;
            color: #111;
        }
        .cert-field-table th {
            border: 1px solid #999;
            padding: 2px 5px;
            font-size: inherit;
            color: #111;
            background: #f0f0f0;
            font-weight: 700;
        }

        /* ========== GRID / MARGINS ========== */
        .cert-sheet.show-grid {
            background-image:
                linear-gradient(to right, rgba(255,0,0,.15) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,0,0,.15) 1px, transparent 1px);
            background-size: 10mm 10mm;
        }
        .cert-sheet.show-margins::before {
            content: '';
            position: absolute;
            top: 10mm; left: 15mm; right: 15mm; bottom: 10mm;
            border: 1px dashed rgba(0,150,0,.3);
            pointer-events: none;
            z-index: 0;
        }

        /* ========== LAYOUT TEMPLATE PANEL ========== */
        .template-panel {
            background: #0f172a;
            border-top: 1px solid #334155;
            padding: 10px 16px;
            position: sticky;
            bottom: 0;
            z-index: 1000;
        }
        .template-list {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }
        .template-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #1e293b;
            border: 1px solid #475569;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            cursor: pointer;
            transition: all .15s;
        }
        .template-chip:hover { border-color: #3b82f6; }
        .template-chip .chip-delete {
            color: #94a3b8;
            cursor: pointer;
            font-size: 0.65rem;
        }
        .template-chip .chip-delete:hover { color: #f87171; }

        /* ========== PRINT STYLES ========== */
        @media print {
            body { background: #fff; }
            .control-panel, .template-panel, .no-print { display: none !important; }
            .cert-workspace { padding: 0; gap: 0; }
            .cert-sheet {
                box-shadow: none;
                width: 297mm;
                height: 210mm;
                page-break-after: always;
            }
            .cert-sheet:last-child { page-break-after: avoid; }
            .cert-field, .cert-field-table {
                outline: none !important;
                background: transparent !important;
            }
            .page-label { display: none !important; }
            @page {
                size: A4 landscape;
                margin: 0;
            }
        }
    </style>
</head>
<body>

{{-- TOP CONTROL PANEL --}}
<div class="control-panel no-print">
    <div class="control-row">
        <button onclick="window.close()" class="control-btn" style="margin-right:8px;">← Back</button>
        <span class="template-badge">
            <i class="fas fa-print"></i> {{ $templateLabel }}
        </span>
        <span style="font-size:0.7rem;color:#94a3b8;margin-left:8px;">
            {{ $student->full_name }} &middot; {{ $student->classroom?->name }} {{ $student->section?->name }}
        </span>
        <div style="margin-left:auto;display:flex;gap:6px;">
            <button onclick="toggleGrid()" class="control-btn" id="gridBtn">Grid</button>
            <button onclick="toggleMargins()" class="control-btn" id="marginBtn">Margins</button>
            <button onclick="window.print()" class="control-btn primary"><i class="fas fa-print"></i> Print</button>
        </div>
    </div>
    <div class="control-row">
        <span class="control-label">Page:</span>
        <button class="page-tab active" data-page="1" onclick="switchPage(1)">Page 1</button>
        <button class="page-tab" data-page="2" onclick="switchPage(2)">Page 2</button>
        <span class="control-label" style="margin-left:12px;">Field:</span>
        <select id="fieldSelector" class="control-select" onchange="selectField(this.value)">
            <option value="">-- Select --</option>
        </select>
        <span class="control-label" style="margin-left:8px;">X:</span>
        <input type="number" id="fieldX" class="control-input" onchange="updateFieldPos()">
        <span class="control-label">Y:</span>
        <input type="number" id="fieldY" class="control-input" onchange="updateFieldPos()">
        <span class="control-label" style="margin-left:8px;">Font:</span>
        <input type="number" id="fieldFontSize" class="control-input" min="6" max="32" value="12" onchange="updateFieldStyle()">
        <span class="control-label">pt</span>
        <select id="fieldWeight" class="control-select" onchange="updateFieldStyle()">
            <option value="400">Normal</option>
            <option value="600">Semi-Bold</option>
            <option value="700">Bold</option>
        </select>
        <div style="margin-left:8px;display:flex;gap:3px;">
            <button onclick="setAlign('left')" class="control-btn" title="Align Left">⬅</button>
            <button onclick="setAlign('center')" class="control-btn" title="Align Center">⬌</button>
            <button onclick="setAlign('right')" class="control-btn" title="Align Right">➡</button>
        </div>
        <div style="margin-left:8px;display:flex;gap:3px;">
            <button onclick="nudgeField(-1,0)" class="control-btn" title="← Left (Shift=10mm)">◀</button>
            <button onclick="nudgeField(1,0)" class="control-btn" title="→ Right (Shift=10mm)">▶</button>
            <button onclick="nudgeField(0,-1)" class="control-btn" title="↑ Up (Shift=10mm)">▲</button>
            <button onclick="nudgeField(0,1)" class="control-btn" title="↓ Down (Shift=10mm)">▼</button>
        </div>
    </div>
</div>

{{-- CERTIFICATE SHEETS — TWO LANDSCAPE PAGES --}}
<div class="cert-workspace">
    <div style="position:relative;">
        <span class="page-label no-print">Page 1 — Student Info</span>
        <div class="cert-sheet" id="certSheet1">
            {{-- Page 1 fields injected by JS --}}
        </div>
    </div>
    <div style="position:relative;">
        <span class="page-label no-print">Page 2 — Results</span>
        <div class="cert-sheet" id="certSheet2">
            {{-- Page 2 fields injected by JS --}}
        </div>
    </div>
</div>

{{-- BOTTOM TEMPLATE PANEL --}}
<div class="template-panel no-print">
    <div class="control-row">
        <span class="control-label">Layouts:</span>
        <input type="text" id="templateName" class="control-input" style="width:140px;" placeholder="Template name...">
        <button onclick="saveTemplate()" class="control-btn primary">Save Layout</button>
        <button onclick="resetPositions()" class="control-btn danger">Reset Default</button>
    </div>
    <div class="template-list" id="templateList">
        {{-- Saved templates rendered here --}}
    </div>
</div>

<script>
(function() {
    // ========== CONFIGURATION ==========
    const TEMPLATE_TYPE = '{{ $templateType }}';
    const STORAGE_KEY = 'certPrint_positions_' + TEMPLATE_TYPE;
    const TEMPLATES_KEY = 'certPrint_templates_' + TEMPLATE_TYPE;

    const SHEET1 = document.getElementById('certSheet1');
    const SHEET2 = document.getElementById('certSheet2');

    // Landscape A4: 297mm wide × 210mm tall
    const SHEET_W_MM = 297;
    const SHEET_H_MM = 210;

    // ========== TERM-KEY DATA FROM SERVER ==========
    const termKeys = {{ json_encode($termKeys) }} || [];
    const termNames = {{ json_encode($termNames) }} || {};
    const subjectRows = {{ json_encode($subjectRows) }} || [];
    const termSummaries = {{ json_encode($termSummaries) }} || {};
    const annualSummary = {{ json_encode($annualSummary) }} || {};

    // ========== SUBJECT TABLE HTML ==========
    function buildSubjectTable(fontSize) {
        // Build dynamic column headers based on available terms
        // Fallback: if no terms, use a single "Total" column
        let numTermCols = termKeys.length > 0 ? termKeys.length : 1;
        let colCount = 2 + numTermCols + 1; // # + Subject + terms + Annual Avg
        let subjectWidth = Math.max(25, 50 - numTermCols * 8);
        let termColWidth = numTermCols > 0 ? Math.floor((65 - subjectWidth) / (numTermCols + 1)) : 15;

        let html = '<table style="width:100%;font-size:' + fontSize + 'pt;">';
        // Header row
        html += '<tr><th style="width:5%;">#</th><th style="width:' + subjectWidth + '%;">Subject</th>';
        if (termKeys.length > 0) {
            termKeys.forEach(function(key) {
                html += '<th style="width:' + termColWidth + '%;">' + (termNames[key] || key) + '</th>';
            });
        } else {
            html += '<th style="width:' + termColWidth + '%;">Total</th>';
        }
        html += '<th style="width:' + termColWidth + '%;">Annual Avg</th>';
        html += '</tr>';

        // Subject rows
        if (subjectRows.length > 0) {
            subjectRows.forEach(function(row, i) {
                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td>' + (row.subject || 'Unknown') + '</td>';
                if (termKeys.length > 0) {
                    termKeys.forEach(function(key) {
                        let val = row[key];
                        html += '<td style="text-align:center;">' + (val !== null && val !== undefined ? val : '—') + '</td>';
                    });
                } else {
                    // No terms — show grand_total from annualAvg
                    let val = row.annualAvg;
                    html += '<td style="text-align:center;">' + (val !== null && val !== undefined ? val : '—') + '</td>';
                }
                html += '<td style="text-align:center;font-weight:600;">' + (row.annualAvg !== null && row.annualAvg !== undefined ? row.annualAvg : '—') + '</td>';
                html += '</tr>';
            });
        } else {
            // No subject data — show placeholder row
            html += '<tr><td>1</td><td colspan="' + (numTermCols + 2) + '" style="text-align:center;color:#999;">No marks recorded</td></tr>';
        }

        // Summary rows after subjects
        // Conduct row
        html += '<tr style="font-weight:600;background:#f0f0f0;">';
        html += '<td colspan="2">Conduct</td>';
        if (termKeys.length > 0) {
            termKeys.forEach(function(key) {
                let val = termSummaries[key] ? termSummaries[key].conduct : null;
                html += '<td style="text-align:center;">' + (val !== null && val !== undefined ? val : '—') + '</td>';
            });
        } else {
            html += '<td style="text-align:center;">' + (annualSummary.conduct !== null && annualSummary.conduct !== undefined ? annualSummary.conduct : '—') + '</td>';
        }
        html += '<td style="text-align:center;">' + (annualSummary.conduct !== null && annualSummary.conduct !== undefined ? annualSummary.conduct : '—') + '</td>';
        html += '</tr>';

        // Total row
        html += '<tr style="font-weight:600;background:#f0f0f0;">';
        html += '<td colspan="2">Total</td>';
        if (termKeys.length > 0) {
            termKeys.forEach(function(key) {
                let val = termSummaries[key] ? termSummaries[key].total : null;
                html += '<td style="text-align:center;">' + (val !== null && val !== undefined ? val : '—') + '</td>';
            });
        } else {
            html += '<td style="text-align:center;">' + (annualSummary.total !== null && annualSummary.total !== undefined ? annualSummary.total : '—') + '</td>';
        }
        html += '<td style="text-align:center;">' + (annualSummary.total !== null && annualSummary.total !== undefined ? annualSummary.total : '—') + '</td>';
        html += '</tr>';

        // Average row
        html += '<tr style="font-weight:600;background:#f0f0f0;">';
        html += '<td colspan="2">Average</td>';
        if (termKeys.length > 0) {
            termKeys.forEach(function(key) {
                let val = termSummaries[key] ? termSummaries[key].average : null;
                html += '<td style="text-align:center;">' + (val !== null && val !== undefined ? val : '—') + '</td>';
            });
        } else {
            html += '<td style="text-align:center;">' + (annualSummary.average !== null && annualSummary.average !== undefined ? annualSummary.average : '—') + '</td>';
        }
        html += '<td style="text-align:center;">' + (annualSummary.average !== null && annualSummary.average !== undefined ? annualSummary.average : '—') + '</td>';
        html += '</tr>';

        // Rank row
        html += '<tr style="font-weight:600;background:#f0f0f0;">';
        html += '<td colspan="2">Rank</td>';
        if (termKeys.length > 0) {
            termKeys.forEach(function(key) {
                let val = termSummaries[key] ? termSummaries[key].rank : null;
                html += '<td style="text-align:center;">' + (val !== null && val !== undefined ? val : '—') + '</td>';
            });
        } else {
            html += '<td style="text-align:center;">' + (annualSummary.rank !== null && annualSummary.rank !== undefined ? annualSummary.rank : '—') + '</td>';
        }
        html += '<td style="text-align:center;">' + (annualSummary.rank !== null && annualSummary.rank !== undefined ? annualSummary.rank : '—') + '</td>';
        html += '</tr>';

        html += '</table>';
        return html;
    }

    const subjectTableHTML = {
        kg: buildSubjectTable(9),
        'g1-2': buildSubjectTable(9),
        'g3-6': buildSubjectTable(9),
        'g7-8': buildSubjectTable(9),
        'g9-10': buildSubjectTable(9),
        'g11-12-nat': buildSubjectTable(8),
        'g11-12-social': buildSubjectTable(8),
    };

    // ========== FIELD DEFINITIONS — PAGE 1 & PAGE 2 PER TEMPLATE ==========
    // Coordinates in mm (landscape: 297mm × 210mm)
    // Page 2 subjectsTable now includes Conduct/Total/Average/Rank summary rows inside the table
    const fieldConfigs = {
        kg: {
            page1: [
                { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($schoolName) }}',       x: 148.5, y: 18,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($schoolAddress) }}',    x: 148.5, y: 33,  fontSize: 10, weight: 400, align: 'center' },
                { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}', x: 148.5, y: 58,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 60, y: 82, fontSize: 11, weight: 400, align: 'left' },
                { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 210, y: 82, fontSize: 11, weight: 400, align: 'left' },
                { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 60, y: 100, fontSize: 12, weight: 600, align: 'left' },
                { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 210, y: 100, fontSize: 12, weight: 600, align: 'left' },
                { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 148.5, y: 120, fontSize: 11, weight: 400, align: 'center' },
                { id: 'handwriting',   label: 'Handwriting',    text: 'Handwriting: {{ $handwriting ?? "—" }}', x: 210, y: 140, fontSize: 11, weight: 400, align: 'left' },
                { id: 'homeroomTeacher', label: 'Homeroom Teacher', text: 'Homeroom Teacher: {{ addslashes($homeroomTeacherName) }}', x: 148.5, y: 165, fontSize: 11, weight: 600, align: 'center' },
            ],
            page2: [
                { id: 'subjectsTable', label: 'Subjects Table (incl. Conduct/Total/Avg/Rank)', type: 'table', x: 20, y: 10, fontSize: 9, weight: 400 },
                { id: 'homeroomComment', label: 'Homeroom Comment', text: '{{ addslashes($homeroomComment) }}', x: 148.5, y: 175, fontSize: 10, weight: 400, align: 'center' },
                { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}',  x: 60,  y: 195, fontSize: 11, weight: 400, align: 'left' },
                { id: 'principalSignature', label: 'Principal Signature', text: '_______________________', x: 210, y: 195, fontSize: 11, weight: 400, align: 'left' },
            ]
        },
        'g1-2': {
            page1: [
                { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($schoolName) }}',       x: 148.5, y: 18,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($schoolAddress) }}',    x: 148.5, y: 33,  fontSize: 10, weight: 400, align: 'center' },
                { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}', x: 148.5, y: 58,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 60, y: 82, fontSize: 11, weight: 400, align: 'left' },
                { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 210, y: 82, fontSize: 11, weight: 400, align: 'left' },
                { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 60, y: 100, fontSize: 12, weight: 600, align: 'left' },
                { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 210, y: 100, fontSize: 12, weight: 600, align: 'left' },
                { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 148.5, y: 120, fontSize: 11, weight: 400, align: 'center' },
                { id: 'handwriting',   label: 'Handwriting',    text: 'Handwriting: {{ $handwriting ?? "—" }}', x: 210, y: 140, fontSize: 11, weight: 400, align: 'left' },
                { id: 'homeroomTeacher', label: 'Homeroom Teacher', text: 'Homeroom Teacher: {{ addslashes($homeroomTeacherName) }}', x: 148.5, y: 165, fontSize: 11, weight: 600, align: 'center' },
            ],
            page2: [
                { id: 'subjectsTable', label: 'Subjects Table (incl. Conduct/Total/Avg/Rank)', type: 'table', x: 20, y: 10, fontSize: 9, weight: 400 },
                { id: 'homeroomComment', label: 'Homeroom Comment', text: '{{ addslashes($homeroomComment) }}', x: 148.5, y: 175, fontSize: 10, weight: 400, align: 'center' },
                { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}',  x: 60,  y: 195, fontSize: 11, weight: 400, align: 'left' },
                { id: 'principalSignature', label: 'Principal Signature', text: '_______________________', x: 210, y: 195, fontSize: 11, weight: 400, align: 'left' },
            ]
        },
        'g3-6': {
            page1: [
                { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($schoolName) }}',       x: 148.5, y: 18,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($schoolAddress) }}',    x: 148.5, y: 33,  fontSize: 10, weight: 400, align: 'center' },
                { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}', x: 148.5, y: 58,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 60, y: 82, fontSize: 11, weight: 400, align: 'left' },
                { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 210, y: 82, fontSize: 11, weight: 400, align: 'left' },
                { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 60, y: 100, fontSize: 12, weight: 600, align: 'left' },
                { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 210, y: 100, fontSize: 12, weight: 600, align: 'left' },
                { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 148.5, y: 120, fontSize: 11, weight: 400, align: 'center' },
                { id: 'homeroomTeacher', label: 'Homeroom Teacher', text: 'Homeroom Teacher: {{ addslashes($homeroomTeacherName) }}', x: 148.5, y: 160, fontSize: 11, weight: 600, align: 'center' },
            ],
            page2: [
                { id: 'subjectsTable', label: 'Subjects Table (incl. Conduct/Total/Avg/Rank)', type: 'table', x: 20, y: 10, fontSize: 9, weight: 400 },
                { id: 'homeroomComment', label: 'Homeroom Comment', text: '{{ addslashes($homeroomComment) }}', x: 148.5, y: 170, fontSize: 10, weight: 400, align: 'center' },
                { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}',  x: 60,  y: 190, fontSize: 11, weight: 400, align: 'left' },
                { id: 'principalSignature', label: 'Principal Signature', text: '_______________________', x: 210, y: 190, fontSize: 11, weight: 400, align: 'left' },
            ]
        },
        'g7-8': {
            page1: [
                { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($schoolName) }}',       x: 148.5, y: 18,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($schoolAddress) }}',    x: 148.5, y: 33,  fontSize: 10, weight: 400, align: 'center' },
                { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}', x: 148.5, y: 55,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 60, y: 78, fontSize: 11, weight: 400, align: 'left' },
                { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 210, y: 78, fontSize: 11, weight: 400, align: 'left' },
                { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 60, y: 96, fontSize: 12, weight: 600, align: 'left' },
                { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 210, y: 96, fontSize: 12, weight: 600, align: 'left' },
                { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 148.5, y: 115, fontSize: 11, weight: 400, align: 'center' },
                { id: 'homeroomTeacher', label: 'Homeroom Teacher', text: 'Homeroom Teacher: {{ addslashes($homeroomTeacherName) }}', x: 148.5, y: 155, fontSize: 11, weight: 600, align: 'center' },
            ],
            page2: [
                { id: 'subjectsTable', label: 'Subjects Table (incl. Conduct/Total/Avg/Rank)', type: 'table', x: 15, y: 10, fontSize: 9, weight: 400 },
                { id: 'homeroomComment', label: 'Homeroom Comment', text: '{{ addslashes($homeroomComment) }}', x: 148.5, y: 165, fontSize: 10, weight: 400, align: 'center' },
                { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}',  x: 60,  y: 185, fontSize: 11, weight: 400, align: 'left' },
                { id: 'principalSignature', label: 'Principal Signature', text: '_______________________', x: 210, y: 185, fontSize: 11, weight: 400, align: 'left' },
            ]
        },
        'g9-10': {
            page1: [
                { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($schoolName) }}',       x: 148.5, y: 18,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($schoolAddress) }}',    x: 148.5, y: 33,  fontSize: 10, weight: 400, align: 'center' },
                { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}', x: 148.5, y: 55,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 60, y: 78, fontSize: 11, weight: 400, align: 'left' },
                { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 210, y: 78, fontSize: 11, weight: 400, align: 'left' },
                { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 60, y: 96, fontSize: 12, weight: 600, align: 'left' },
                { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 210, y: 96, fontSize: 12, weight: 600, align: 'left' },
                { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 148.5, y: 115, fontSize: 11, weight: 400, align: 'center' },
                { id: 'homeroomTeacher', label: 'Homeroom Teacher', text: 'Homeroom Teacher: {{ addslashes($homeroomTeacherName) }}', x: 148.5, y: 155, fontSize: 11, weight: 600, align: 'center' },
            ],
            page2: [
                { id: 'subjectsTable', label: 'Subjects Table (incl. Conduct/Total/Avg/Rank)', type: 'table', x: 10, y: 10, fontSize: 8, weight: 400 },
                { id: 'homeroomComment', label: 'Homeroom Comment', text: '{{ addslashes($homeroomComment) }}', x: 148.5, y: 165, fontSize: 10, weight: 400, align: 'center' },
                { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}',  x: 60,  y: 185, fontSize: 11, weight: 400, align: 'left' },
                { id: 'principalSignature', label: 'Principal Signature', text: '_______________________', x: 210, y: 185, fontSize: 11, weight: 400, align: 'left' },
            ]
        },
        'g11-12-nat': {
            page1: [
                { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($schoolName) }}',       x: 148.5, y: 18,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($schoolAddress) }}',    x: 148.5, y: 33,  fontSize: 10, weight: 400, align: 'center' },
                { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}', x: 148.5, y: 55,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 60, y: 75, fontSize: 11, weight: 400, align: 'left' },
                { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 210, y: 75, fontSize: 11, weight: 400, align: 'left' },
                { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 60, y: 93, fontSize: 12, weight: 600, align: 'left' },
                { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 210, y: 93, fontSize: 12, weight: 600, align: 'left' },
                { id: 'streamName',    label: 'Stream',         text: 'Natural Science',                     x: 148.5, y: 111, fontSize: 13, weight: 700, align: 'center' },
                { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 148.5, y: 128, fontSize: 11, weight: 400, align: 'center' },
                { id: 'homeroomTeacher', label: 'Homeroom Teacher', text: 'Homeroom Teacher: {{ addslashes($homeroomTeacherName) }}', x: 148.5, y: 168, fontSize: 11, weight: 600, align: 'center' },
            ],
            page2: [
                { id: 'subjectsTable', label: 'Subjects Table (incl. Conduct/Total/Avg/Rank)', type: 'table', x: 10, y: 10, fontSize: 8, weight: 400 },
                { id: 'homeroomComment', label: 'Homeroom Comment', text: '{{ addslashes($homeroomComment) }}', x: 148.5, y: 165, fontSize: 10, weight: 400, align: 'center' },
                { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}',  x: 60,  y: 185, fontSize: 11, weight: 400, align: 'left' },
                { id: 'principalSignature', label: 'Principal Signature', text: '_______________________', x: 210, y: 185, fontSize: 11, weight: 400, align: 'left' },
            ]
        },
        'g11-12-social': {
            page1: [
                { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($schoolName) }}',       x: 148.5, y: 18,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($schoolAddress) }}',    x: 148.5, y: 33,  fontSize: 10, weight: 400, align: 'center' },
                { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}', x: 148.5, y: 55,  fontSize: 18, weight: 700, align: 'center' },
                { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 60, y: 75, fontSize: 11, weight: 400, align: 'left' },
                { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 210, y: 75, fontSize: 11, weight: 400, align: 'left' },
                { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 60, y: 93, fontSize: 12, weight: 600, align: 'left' },
                { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 210, y: 93, fontSize: 12, weight: 600, align: 'left' },
                { id: 'streamName',    label: 'Stream',         text: 'Social Science',                      x: 148.5, y: 111, fontSize: 13, weight: 700, align: 'center' },
                { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 148.5, y: 128, fontSize: 11, weight: 400, align: 'center' },
                { id: 'homeroomTeacher', label: 'Homeroom Teacher', text: 'Homeroom Teacher: {{ addslashes($homeroomTeacherName) }}', x: 148.5, y: 168, fontSize: 11, weight: 600, align: 'center' },
            ],
            page2: [
                { id: 'subjectsTable', label: 'Subjects Table (incl. Conduct/Total/Avg/Rank)', type: 'table', x: 10, y: 10, fontSize: 8, weight: 400 },
                { id: 'homeroomComment', label: 'Homeroom Comment', text: '{{ addslashes($homeroomComment) }}', x: 148.5, y: 165, fontSize: 10, weight: 400, align: 'center' },
                { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}',  x: 60,  y: 185, fontSize: 11, weight: 400, align: 'left' },
                { id: 'principalSignature', label: 'Principal Signature', text: '_______________________', x: 210, y: 185, fontSize: 11, weight: 400, align: 'left' },
            ]
        },
    };

    // ========== STATE ==========
    let fields = {};       // { fieldId: { ...fieldConfig, page: 1|2 } }
    let selectedFieldId = null;
    let isDragging = false;
    let dragOffset = { x: 0, y: 0 };
    let activePage = 1;

    // ========== LOAD POSITIONS ==========
    function loadPositions() {
        const config = fieldConfigs[TEMPLATE_TYPE] || fieldConfigs['g3-6'];
        const saved = localStorage.getItem(STORAGE_KEY);

        let savedData = null;
        if (saved) {
            try { savedData = JSON.parse(saved); } catch(e) {}
        }

        ['page1', 'page2'].forEach(pageKey => {
            const pageNum = pageKey === 'page1' ? 1 : 2;
            (config[pageKey] || []).forEach(f => {
                const savedField = savedData ? savedData[f.id] : null;
                fields[f.id] = {
                    ...f,
                    page: pageNum,
                    x: savedField?.x ?? f.x,
                    y: savedField?.y ?? f.y,
                    fontSize: savedField?.fontSize ?? f.fontSize,
                    weight: savedField?.weight ?? f.weight,
                    align: savedField?.align ?? f.align,
                };
            });
        });
    }

    // ========== RENDER FIELDS ==========
    function renderFields() {
        SHEET1.querySelectorAll('.cert-field, .cert-field-table').forEach(el => el.remove());
        SHEET2.querySelectorAll('.cert-field, .cert-field-table').forEach(el => el.remove());

        Object.values(fields).forEach(f => {
            const targetSheet = f.page === 1 ? SHEET1 : SHEET2;

            if (f.type === 'table') {
                const tableHTML = subjectTableHTML[TEMPLATE_TYPE];
                if (!tableHTML) return;

                const el = document.createElement('div');
                el.className = 'cert-field-table';
                el.id = 'field-' + f.id;
                el.dataset.fieldId = f.id;
                el.style.left = f.x + 'mm';
                el.style.top = f.y + 'mm';
                el.style.fontSize = f.fontSize + 'pt';
                el.innerHTML = tableHTML;
                el.addEventListener('mousedown', startDrag);
                el.addEventListener('touchstart', startDragTouch, { passive: false });
                targetSheet.appendChild(el);
            } else {
                const el = document.createElement('div');
                el.className = 'cert-field';
                el.id = 'field-' + f.id;
                el.dataset.fieldId = f.id;
                el.style.left = f.x + 'mm';
                el.style.top = f.y + 'mm';
                el.style.fontSize = f.fontSize + 'pt';
                el.style.fontWeight = f.weight;
                el.style.textAlign = f.align;
                el.style.color = '#111';
                el.textContent = f.text;
                if (f.align === 'center') {
                    el.style.width = '260mm';
                    el.style.transform = 'translateX(-50%)';
                    el.style.marginLeft = '18.5mm';
                }
                el.addEventListener('mousedown', startDrag);
                el.addEventListener('touchstart', startDragTouch, { passive: false });
                targetSheet.appendChild(el);
            }
        });

        populateFieldSelector();
    }

    // ========== PAGE SWITCHING ==========
    window.switchPage = function(pageNum) {
        activePage = pageNum;
        document.querySelectorAll('.page-tab').forEach(t => {
            t.classList.toggle('active', parseInt(t.dataset.page) === pageNum);
        });
        // Scroll to the active page
        const target = pageNum === 1 ? SHEET1 : SHEET2;
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    // ========== FIELD SELECTOR ==========
    function populateFieldSelector() {
        const sel = document.getElementById('fieldSelector');
        const prevVal = sel.value;
        sel.innerHTML = '<option value="">-- Select --</option>';

        const optGroup1 = document.createElement('optgroup');
        optGroup1.label = 'Page 1 — Student Info';
        const optGroup2 = document.createElement('optgroup');
        optGroup2.label = 'Page 2 — Results';

        Object.values(fields).forEach(f => {
            const opt = document.createElement('option');
            opt.value = f.id;
            opt.textContent = f.label;
            (f.page === 1 ? optGroup1 : optGroup2).appendChild(opt);
        });

        sel.appendChild(optGroup1);
        sel.appendChild(optGroup2);

        if (prevVal) sel.value = prevVal;
    }

    // ========== DRAG ==========
    function getSheetForField(fieldId) {
        return fields[fieldId]?.page === 1 ? SHEET1 : SHEET2;
    }

    function startDrag(e) {
        e.preventDefault();
        const fieldId = e.currentTarget.dataset.fieldId;
        selectField(fieldId);

        // Switch to the page this field is on
        const f = fields[fieldId];
        if (f) switchPage(f.page);

        isDragging = true;
        const el = e.currentTarget;
        const rect = el.getBoundingClientRect();
        dragOffset.x = e.clientX - rect.left;
        dragOffset.y = e.clientY - rect.top;

        function onMove(ev) {
            if (!isDragging) return;
            const sheet = getSheetForField(fieldId);
            const sheetRect = sheet.getBoundingClientRect();
            let newX = ev.clientX - sheetRect.left - dragOffset.x;
            let newY = ev.clientY - sheetRect.top - dragOffset.y;

            const pxPerMm = sheetRect.width / SHEET_W_MM;
            const xMm = Math.max(0, Math.min(SHEET_W_MM, newX / pxPerMm));
            const yMm = Math.max(0, Math.min(SHEET_H_MM, newY / pxPerMm));

            fields[fieldId].x = Math.round(xMm * 10) / 10;
            fields[fieldId].y = Math.round(yMm * 10) / 10;

            el.style.left = fields[fieldId].x + 'mm';
            el.style.top = fields[fieldId].y + 'mm';

            if (selectedFieldId === fieldId) {
                document.getElementById('fieldX').value = fields[fieldId].x;
                document.getElementById('fieldY').value = fields[fieldId].y;
            }
        }

        function onUp() {
            isDragging = false;
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            savePositions();
        }

        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    }

    function startDragTouch(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const fieldId = e.currentTarget.dataset.fieldId;
        selectField(fieldId);

        const f = fields[fieldId];
        if (f) switchPage(f.page);

        isDragging = true;
        const el = e.currentTarget;
        const rect = el.getBoundingClientRect();
        dragOffset.x = touch.clientX - rect.left;
        dragOffset.y = touch.clientY - rect.top;

        function onMove(ev) {
            if (!isDragging) return;
            const t = ev.touches[0];
            const sheet = getSheetForField(fieldId);
            const sheetRect = sheet.getBoundingClientRect();
            let newX = t.clientX - sheetRect.left - dragOffset.x;
            let newY = t.clientY - sheetRect.top - dragOffset.y;

            const pxPerMm = sheetRect.width / SHEET_W_MM;
            const xMm = Math.max(0, Math.min(SHEET_W_MM, newX / pxPerMm));
            const yMm = Math.max(0, Math.min(SHEET_H_MM, newY / pxPerMm));

            fields[fieldId].x = Math.round(xMm * 10) / 10;
            fields[fieldId].y = Math.round(yMm * 10) / 10;

            el.style.left = fields[fieldId].x + 'mm';
            el.style.top = fields[fieldId].y + 'mm';

            if (selectedFieldId === fieldId) {
                document.getElementById('fieldX').value = fields[fieldId].x;
                document.getElementById('fieldY').value = fields[fieldId].y;
            }
        }

        function onUp() {
            isDragging = false;
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend', onUp);
            savePositions();
        }

        document.addEventListener('touchmove', onMove, { passive: false });
        document.addEventListener('touchend', onUp);
    }

    // ========== SELECT FIELD ==========
    window.selectField = function(fieldId) {
        document.querySelectorAll('.cert-field.selected, .cert-field-table.selected').forEach(el => el.classList.remove('selected'));

        selectedFieldId = fieldId;
        document.getElementById('fieldSelector').value = fieldId;

        const el = document.getElementById('field-' + fieldId);
        if (el) el.classList.add('selected');

        const f = fields[fieldId];
        if (f) {
            document.getElementById('fieldX').value = f.x;
            document.getElementById('fieldY').value = f.y;
            document.getElementById('fieldFontSize').value = f.fontSize;
            document.getElementById('fieldWeight').value = f.weight;
            switchPage(f.page);
        }
    };

    // ========== UPDATE FIELD POSITION ==========
    window.updateFieldPos = function() {
        if (!selectedFieldId) return;
        const f = fields[selectedFieldId];
        if (!f) return;

        f.x = parseFloat(document.getElementById('fieldX').value) || 0;
        f.y = parseFloat(document.getElementById('fieldY').value) || 0;

        const el = document.getElementById('field-' + selectedFieldId);
        if (el) {
            el.style.left = f.x + 'mm';
            el.style.top = f.y + 'mm';
        }
        savePositions();
    };

    // ========== UPDATE FIELD STYLE ==========
    window.updateFieldStyle = function() {
        if (!selectedFieldId) return;
        const f = fields[selectedFieldId];
        if (!f) return;

        f.fontSize = parseInt(document.getElementById('fieldFontSize').value) || 12;
        f.weight = document.getElementById('fieldWeight').value;

        const el = document.getElementById('field-' + selectedFieldId);
        if (el) {
            el.style.fontSize = f.fontSize + 'pt';
            el.style.fontWeight = f.weight;
        }
        savePositions();
    };

    // ========== ALIGNMENT ==========
    window.setAlign = function(align) {
        if (!selectedFieldId) return;
        const f = fields[selectedFieldId];
        if (!f) return;

        f.align = align;
        const el = document.getElementById('field-' + selectedFieldId);
        if (el) {
            el.style.textAlign = align;
            if (align === 'center') {
                el.style.width = '260mm';
                el.style.transform = 'translateX(-50%)';
                el.style.marginLeft = '18.5mm';
            } else {
                el.style.width = '';
                el.style.transform = '';
                el.style.marginLeft = '';
            }
        }
        savePositions();
    };

    // ========== NUDGE ==========
    window.nudgeField = function(dx, dy) {
        if (!selectedFieldId) return;
        const f = fields[selectedFieldId];
        if (!f) return;

        const step = event?.shiftKey ? 10 : 1;
        f.x = Math.max(0, Math.min(SHEET_W_MM, f.x + dx * step));
        f.y = Math.max(0, Math.min(SHEET_H_MM, f.y + dy * step));

        const el = document.getElementById('field-' + selectedFieldId);
        if (el) {
            el.style.left = f.x + 'mm';
            el.style.top = f.y + 'mm';
        }
        document.getElementById('fieldX').value = f.x;
        document.getElementById('fieldY').value = f.y;
        savePositions();
    };

    // ========== GRID / MARGINS ==========
    window.toggleGrid = function() {
        SHEET1.classList.toggle('show-grid');
        SHEET2.classList.toggle('show-grid');
        document.getElementById('gridBtn').classList.toggle('active');
    };
    window.toggleMargins = function() {
        SHEET1.classList.toggle('show-margins');
        SHEET2.classList.toggle('show-margins');
        document.getElementById('marginBtn').classList.toggle('active');
    };

    // ========== PERSISTENCE ==========
    function savePositions() {
        const data = {};
        Object.values(fields).forEach(f => {
            data[f.id] = { x: f.x, y: f.y, fontSize: f.fontSize, weight: f.weight, align: f.align, page: f.page };
        });
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    window.resetPositions = function() {
        if (!confirm('Reset all field positions to default?')) return;
        localStorage.removeItem(STORAGE_KEY);
        fields = {};
        loadPositions();
        renderFields();
    };

    // ========== LAYOUT TEMPLATES ==========
    window.saveTemplate = function() {
        const name = document.getElementById('templateName').value.trim();
        if (!name) { alert('Enter a template name'); return; }

        const templates = JSON.parse(localStorage.getItem(TEMPLATES_KEY) || '{}');
        templates[name] = {};
        Object.values(fields).forEach(f => {
            templates[name][f.id] = { x: f.x, y: f.y, fontSize: f.fontSize, weight: f.weight, align: f.align, page: f.page };
        });
        localStorage.setItem(TEMPLATES_KEY, JSON.stringify(templates));
        document.getElementById('templateName').value = '';
        renderTemplates();
    };

    function loadTemplate(name) {
        const templates = JSON.parse(localStorage.getItem(TEMPLATES_KEY) || '{}');
        const tpl = templates[name];
        if (!tpl) return;

        Object.keys(tpl).forEach(id => {
            if (fields[id]) {
                fields[id].x = tpl[id].x;
                fields[id].y = tpl[id].y;
                fields[id].fontSize = tpl[id].fontSize;
                fields[id].weight = tpl[id].weight;
                fields[id].align = tpl[id].align;
                if (tpl[id].page) fields[id].page = tpl[id].page;
            }
        });
        renderFields();
        savePositions();
    }

    function deleteTemplate(name) {
        if (!confirm('Delete template "' + name + '"?')) return;
        const templates = JSON.parse(localStorage.getItem(TEMPLATES_KEY) || '{}');
        delete templates[name];
        localStorage.setItem(TEMPLATES_KEY, JSON.stringify(templates));
        renderTemplates();
    }

    function renderTemplates() {
        const templates = JSON.parse(localStorage.getItem(TEMPLATES_KEY) || '{}');
        const container = document.getElementById('templateList');
        container.innerHTML = '';

        if (Object.keys(templates).length === 0) {
            container.innerHTML = '<span style="font-size:0.7rem;color:#64748b;">No saved layouts yet</span>';
            return;
        }

        Object.keys(templates).forEach(name => {
            const chip = document.createElement('span');
            chip.className = 'template-chip';
            chip.innerHTML = '<i class="fas fa-layer-group" style="font-size:0.6rem;"></i> ' + name +
                ' <span class="chip-delete" onclick="event.stopPropagation();deleteTemplate(\'' + name.replace(/'/g, "\\'") + '\')"><i class="fas fa-times"></i></span>';
            chip.addEventListener('click', () => loadTemplate(name));
            container.appendChild(chip);
        });
    }

    window.deleteTemplate = deleteTemplate;

    // ========== KEYBOARD ==========
    document.addEventListener('keydown', function(e) {
        if (!selectedFieldId) return;
        if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'SELECT') return;

        switch (e.key) {
            case 'ArrowLeft':  e.preventDefault(); nudgeField(-1, 0); break;
            case 'ArrowRight': e.preventDefault(); nudgeField(1, 0); break;
            case 'ArrowUp':    e.preventDefault(); nudgeField(0, -1); break;
            case 'ArrowDown':  e.preventDefault(); nudgeField(0, 1); break;
        }
    });

    // ========== INIT ==========
    loadPositions();
    renderFields();
    renderTemplates();

    const firstField = Object.values(fields).find(f => f.page === 1);
    if (firstField) selectField(firstField.id);

})();
</script>

</body>
</html>
