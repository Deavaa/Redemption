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

        /* ========== CERTIFICATE SHEET ========== */
        .cert-workspace {
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .cert-sheet {
            width: 210mm;
            height: 297mm;
            position: relative;
            background: #fff;
            box-shadow: 0 4px 24px rgba(0,0,0,.4);
            overflow: hidden;
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
            top: 10mm; left: 15mm; right: 15mm; bottom: 15mm;
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
            .cert-workspace { padding: 0; }
            .cert-sheet {
                box-shadow: none;
                width: 210mm;
                height: 297mm;
            }
            .cert-field, .cert-field-table {
                outline: none !important;
                background: transparent !important;
            }
            @page {
                size: A4;
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
        <span class="control-label">Field:</span>
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
            <button onclick="nudgeField(-1,0)" class="control-btn" title="← Left">◀</button>
            <button onclick="nudgeField(1,0)" class="control-btn" title="→ Right">▶</button>
            <button onclick="nudgeField(0,-1)" class="control-btn" title="↑ Up">▲</button>
            <button onclick="nudgeField(0,1)" class="control-btn" title="↓ Down">▼</button>
        </div>
    </div>
</div>

{{-- CERTIFICATE SHEET --}}
<div class="cert-workspace">
    <div class="cert-sheet" id="certSheet">
        {{-- Fields will be injected by JavaScript based on template type --}}
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
        {{-- Saved templates will be rendered here --}}
    </div>
</div>

<script>
(function() {
    // ========== CONFIGURATION ==========
    const TEMPLATE_TYPE = '{{ $templateType }}';
    const STORAGE_KEY = 'certPrint_positions_' + TEMPLATE_TYPE;
    const TEMPLATES_KEY = 'certPrint_templates_' + TEMPLATE_TYPE;

    // Sheet dimensions in px (A4 at 96dpi)
    const SHEET = document.getElementById('certSheet');
    let SHEET_W = SHEET.offsetWidth;
    let SHEET_H = SHEET.offsetHeight;

    // ========== FIELD DEFINITIONS PER TEMPLATE TYPE ==========
    const fieldConfigs = {
        kg: [
            { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($branchName) }}',       x: 105, y: 30,  fontSize: 16, weight: 700, align: 'center' },
            { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($branchAddress) }}',    x: 105, y: 50,  fontSize: 10, weight: 400, align: 'center' },
            { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}', x: 105, y: 120, fontSize: 18, weight: 700, align: 'center' },
            { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 105, y: 155, fontSize: 13, weight: 600, align: 'center' },
            { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 105, y: 175, fontSize: 13, weight: 600, align: 'center' },
            { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 105, y: 200, fontSize: 12, weight: 400, align: 'center' },
            { id: 'conduct',       label: 'Conduct',        text: '{{ $conduct ?? "—" }}',       x: 80,  y: 240, fontSize: 12, weight: 400, align: 'center' },
            { id: 'handwriting',   label: 'Handwriting',    text: '{{ $handwriting ?? "—" }}',   x: 130, y: 240, fontSize: 12, weight: 400, align: 'center' },
            { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}', x: 105, y: 280, fontSize: 11, weight: 400, align: 'center' },
        ],
        'g1-2': [
            { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($branchName) }}',          x: 105, y: 30,  fontSize: 16, weight: 700, align: 'center' },
            { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($branchAddress) }}',       x: 105, y: 50,  fontSize: 10, weight: 400, align: 'center' },
            { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}',  x: 105, y: 110, fontSize: 18, weight: 700, align: 'center' },
            { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 60, y: 145, fontSize: 12, weight: 400, align: 'left' },
            { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 150, y: 145, fontSize: 12, weight: 400, align: 'left' },
            { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 60, y: 170, fontSize: 13, weight: 600, align: 'left' },
            { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 150, y: 170, fontSize: 13, weight: 600, align: 'left' },
            { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 105, y: 195, fontSize: 12, weight: 400, align: 'center' },
            { id: 'avgMarks',      label: 'Average',        text: '{{ $average }}',       x: 60,  y: 230, fontSize: 14, weight: 700, align: 'left' },
            { id: 'rank',          label: 'Rank',           text: '{{ $rank ? $rank : "—" }}', x: 150, y: 230, fontSize: 14, weight: 700, align: 'left' },
            { id: 'conduct',       label: 'Conduct',        text: '{{ $conduct ?? "—" }}',       x: 60,  y: 260, fontSize: 12, weight: 400, align: 'left' },
            { id: 'handwriting',   label: 'Handwriting',    text: '{{ $handwriting ?? "—" }}',   x: 150, y: 260, fontSize: 12, weight: 400, align: 'left' },
            { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}', x: 105, y: 290, fontSize: 11, weight: 400, align: 'center' },
        ],
        'g3-6': [
            { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($branchName) }}',          x: 105, y: 25,  fontSize: 16, weight: 700, align: 'center' },
            { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($branchAddress) }}',       x: 105, y: 45,  fontSize: 10, weight: 400, align: 'center' },
            { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}',  x: 105, y: 90,  fontSize: 18, weight: 700, align: 'center' },
            { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 50, y: 120, fontSize: 11, weight: 400, align: 'left' },
            { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 155, y: 120, fontSize: 11, weight: 400, align: 'left' },
            { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 50, y: 145, fontSize: 12, weight: 600, align: 'left' },
            { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 155, y: 145, fontSize: 12, weight: 600, align: 'left' },
            { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 105, y: 170, fontSize: 11, weight: 400, align: 'center' },
            { id: 'subjectsTable', label: 'Subjects Table', type: 'table', x: 20, y: 200, fontSize: 9, weight: 400 },
            { id: 'totalMarks',    label: 'Total Marks',    text: '{{ $totalMarks }} / {{ $totalPossible }}', x: 50, y: 260, fontSize: 13, weight: 700, align: 'left' },
            { id: 'avgMarks',      label: 'Average',        text: '{{ $average }}',       x: 105, y: 260, fontSize: 13, weight: 700, align: 'center' },
            { id: 'rank',          label: 'Rank',           text: '{{ $rank ? $rank : "—" }}', x: 155, y: 260, fontSize: 13, weight: 700, align: 'left' },
            { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}', x: 105, y: 290, fontSize: 11, weight: 400, align: 'center' },
        ],
        'g7-8': [
            { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($branchName) }}',          x: 105, y: 25,  fontSize: 16, weight: 700, align: 'center' },
            { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($branchAddress) }}',       x: 105, y: 45,  fontSize: 10, weight: 400, align: 'center' },
            { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}',  x: 105, y: 85,  fontSize: 18, weight: 700, align: 'center' },
            { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 50, y: 115, fontSize: 11, weight: 400, align: 'left' },
            { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 155, y: 115, fontSize: 11, weight: 400, align: 'left' },
            { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 50, y: 140, fontSize: 12, weight: 600, align: 'left' },
            { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 155, y: 140, fontSize: 12, weight: 600, align: 'left' },
            { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 105, y: 165, fontSize: 11, weight: 400, align: 'center' },
            { id: 'subjectsTable', label: 'Subjects Table', type: 'table', x: 15, y: 195, fontSize: 9, weight: 400 },
            { id: 'totalMarks',    label: 'Total Marks',    text: '{{ $totalMarks }} / {{ $totalPossible }}', x: 40, y: 258, fontSize: 12, weight: 700, align: 'left' },
            { id: 'avgMarks',      label: 'Average',        text: '{{ $average }}',       x: 105, y: 258, fontSize: 12, weight: 700, align: 'center' },
            { id: 'rank',          label: 'Rank',           text: '{{ $rank ? $rank : "—" }}', x: 165, y: 258, fontSize: 12, weight: 700, align: 'left' },
            { id: 'conduct',       label: 'Conduct',        text: '{{ $conduct ?? "—" }}',       x: 40,  y: 278, fontSize: 11, weight: 400, align: 'left' },
            { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}', x: 105, y: 290, fontSize: 11, weight: 400, align: 'center' },
        ],
        'g9-10': [
            { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($branchName) }}',          x: 105, y: 25,  fontSize: 16, weight: 700, align: 'center' },
            { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($branchAddress) }}',       x: 105, y: 45,  fontSize: 10, weight: 400, align: 'center' },
            { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}',  x: 105, y: 80,  fontSize: 18, weight: 700, align: 'center' },
            { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 50, y: 110, fontSize: 11, weight: 400, align: 'left' },
            { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 155, y: 110, fontSize: 11, weight: 400, align: 'left' },
            { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 50, y: 135, fontSize: 12, weight: 600, align: 'left' },
            { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 155, y: 135, fontSize: 12, weight: 600, align: 'left' },
            { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 105, y: 160, fontSize: 11, weight: 400, align: 'center' },
            { id: 'subjectsTable', label: 'Subjects Table', type: 'table', x: 15, y: 190, fontSize: 8, weight: 400 },
            { id: 'totalMarks',    label: 'Total Marks',    text: '{{ $totalMarks }} / {{ $totalPossible }}', x: 40, y: 252, fontSize: 12, weight: 700, align: 'left' },
            { id: 'avgMarks',      label: 'Average',        text: '{{ $average }}',       x: 105, y: 252, fontSize: 12, weight: 700, align: 'center' },
            { id: 'rank',          label: 'Rank',           text: '{{ $rank ? $rank : "—" }}', x: 165, y: 252, fontSize: 12, weight: 700, align: 'left' },
            { id: 'conduct',       label: 'Conduct',        text: '{{ $conduct ?? "—" }}',       x: 40,  y: 272, fontSize: 11, weight: 400, align: 'left' },
            { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}', x: 105, y: 290, fontSize: 11, weight: 400, align: 'center' },
        ],
        'g11-12-nat': [
            { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($branchName) }}',          x: 105, y: 25,  fontSize: 16, weight: 700, align: 'center' },
            { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($branchAddress) }}',       x: 105, y: 45,  fontSize: 10, weight: 400, align: 'center' },
            { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}',  x: 105, y: 78,  fontSize: 18, weight: 700, align: 'center' },
            { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 50, y: 108, fontSize: 11, weight: 400, align: 'left' },
            { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 155, y: 108, fontSize: 11, weight: 400, align: 'left' },
            { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 50, y: 132, fontSize: 12, weight: 600, align: 'left' },
            { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 155, y: 132, fontSize: 12, weight: 600, align: 'left' },
            { id: 'streamName',    label: 'Stream',         text: 'Natural Science',                        x: 105, y: 152, fontSize: 12, weight: 600, align: 'center' },
            { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 105, y: 172, fontSize: 11, weight: 400, align: 'center' },
            { id: 'subjectsTable', label: 'Subjects Table', type: 'table', x: 15, y: 195, fontSize: 8, weight: 400 },
            { id: 'totalMarks',    label: 'Total Marks',    text: '{{ $totalMarks }} / {{ $totalPossible }}', x: 40, y: 250, fontSize: 12, weight: 700, align: 'left' },
            { id: 'avgMarks',      label: 'Average',        text: '{{ $average }}',       x: 105, y: 250, fontSize: 12, weight: 700, align: 'center' },
            { id: 'rank',          label: 'Rank',           text: '{{ $rank ? $rank : "—" }}', x: 165, y: 250, fontSize: 12, weight: 700, align: 'left' },
            { id: 'conduct',       label: 'Conduct',        text: '{{ $conduct ?? "—" }}',       x: 40,  y: 270, fontSize: 11, weight: 400, align: 'left' },
            { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}', x: 105, y: 290, fontSize: 11, weight: 400, align: 'center' },
        ],
        'g11-12-social': [
            { id: 'schoolName',    label: 'School Name',    text: '{{ addslashes($branchName) }}',          x: 105, y: 25,  fontSize: 16, weight: 700, align: 'center' },
            { id: 'schoolAddress', label: 'School Address', text: '{{ addslashes($branchAddress) }}',       x: 105, y: 45,  fontSize: 10, weight: 400, align: 'center' },
            { id: 'studentName',   label: 'Student Name',   text: '{{ addslashes($student->full_name) }}',  x: 105, y: 78,  fontSize: 18, weight: 700, align: 'center' },
            { id: 'admissionNo',   label: 'Admission No',   text: '{{ addslashes($student->admission_number ?? "") }}', x: 50, y: 108, fontSize: 11, weight: 400, align: 'left' },
            { id: 'rollNo',        label: 'Roll No',        text: '{{ addslashes($student->roll_number ?? "") }}', x: 155, y: 108, fontSize: 11, weight: 400, align: 'left' },
            { id: 'className',     label: 'Class',          text: '{{ addslashes($student->classroom?->name ?? "") }}', x: 50, y: 132, fontSize: 12, weight: 600, align: 'left' },
            { id: 'sectionName',   label: 'Section',        text: '{{ addslashes($student->section?->name ?? "") }}',   x: 155, y: 132, fontSize: 12, weight: 600, align: 'left' },
            { id: 'streamName',    label: 'Stream',         text: 'Social Science',                         x: 105, y: 152, fontSize: 12, weight: 600, align: 'center' },
            { id: 'academicYear',  label: 'Academic Year',  text: '{{ addslashes($academicYear?->name ?? "") }}',       x: 105, y: 172, fontSize: 11, weight: 400, align: 'center' },
            { id: 'subjectsTable', label: 'Subjects Table', type: 'table', x: 15, y: 195, fontSize: 8, weight: 400 },
            { id: 'totalMarks',    label: 'Total Marks',    text: '{{ $totalMarks }} / {{ $totalPossible }}', x: 40, y: 250, fontSize: 12, weight: 700, align: 'left' },
            { id: 'avgMarks',      label: 'Average',        text: '{{ $average }}',       x: 105, y: 250, fontSize: 12, weight: 700, align: 'center' },
            { id: 'rank',          label: 'Rank',           text: '{{ $rank ? $rank : "—" }}', x: 165, y: 250, fontSize: 12, weight: 700, align: 'left' },
            { id: 'conduct',       label: 'Conduct',        text: '{{ $conduct ?? "—" }}',       x: 40,  y: 270, fontSize: 11, weight: 400, align: 'left' },
            { id: 'dateField',     label: 'Date',           text: '{{ now()->format("d/m/Y") }}', x: 105, y: 290, fontSize: 11, weight: 400, align: 'center' },
        ],
    };

    // Subject table HTML per template
    const subjectTableHTML = {
        kg: null, // No subject table for KG
        'g1-2': null, // No subject table for G1-2
        'g3-6': buildSubjectTable(9),
        'g7-8': buildSubjectTable(9),
        'g9-10': buildSubjectTable(9),
        'g11-12-nat': buildSubjectTable(9),
        'g11-12-social': buildSubjectTable(9),
    };

    function buildSubjectTable(fontSize) {
        let html = '<table style="width:100%;font-size:' + fontSize + 'pt;">';
        html += '<tr style="background:#f0f0f0;font-weight:700;"><td style="width:5%;">#</td><td style="width:45%;">Subject</td><td style="width:15%;">CA</td><td style="width:15%;">Exam</td><td style="width:20%;">Total</td></tr>';
        @foreach($marks as $i => $m)
            html += '<tr>';
            html += '<td>{{ $i + 1 }}</td>';
            html += '<td>{{ addslashes($m->subject?->name ?? "Unknown") }}</td>';
            html += '<td>{{ $m->ca_total ?? "—" }}</td>';
            html += '<td>{{ $m->exam_total ?? "—" }}</td>';
            html += '<td style="font-weight:600;">{{ $m->grand_total ?? "—" }}</td>';
            html += '</tr>';
        @endforeach
        html += '</table>';
        return html;
    }

    // ========== STATE ==========
    let fields = {};
    let selectedFieldId = null;
    let isDragging = false;
    let dragOffset = { x: 0, y: 0 };

    // ========== LOAD SAVED POSITIONS OR USE DEFAULTS ==========
    function loadPositions() {
        const config = fieldConfigs[TEMPLATE_TYPE] || fieldConfigs['g3-6'];
        const saved = localStorage.getItem(STORAGE_KEY);

        if (saved) {
            try {
                const parsed = JSON.parse(saved);
                config.forEach(f => {
                    fields[f.id] = {
                        ...f,
                        x: parsed[f.id]?.x ?? f.x,
                        y: parsed[f.id]?.y ?? f.y,
                        fontSize: parsed[f.id]?.fontSize ?? f.fontSize,
                        weight: parsed[f.id]?.weight ?? f.weight,
                        align: parsed[f.id]?.align ?? f.align,
                    };
                });
                return;
            } catch (e) {}
        }

        // Use defaults
        config.forEach(f => {
            fields[f.id] = { ...f };
        });
    }

    // ========== RENDER FIELDS ==========
    function renderFields() {
        const sheet = document.getElementById('certSheet');
        // Remove existing fields
        sheet.querySelectorAll('.cert-field, .cert-field-table').forEach(el => el.remove());

        Object.values(fields).forEach(f => {
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
                sheet.appendChild(el);
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
                    el.style.width = '180mm';
                    el.style.transform = 'translateX(-50%)';
                    el.style.marginLeft = '15mm';
                }
                el.addEventListener('mousedown', startDrag);
                el.addEventListener('touchstart', startDragTouch, { passive: false });
                sheet.appendChild(el);
            }
        });

        populateFieldSelector();
    }

    // ========== FIELD SELECTOR ==========
    function populateFieldSelector() {
        const sel = document.getElementById('fieldSelector');
        sel.innerHTML = '<option value="">-- Select --</option>';
        Object.values(fields).forEach(f => {
            const opt = document.createElement('option');
            opt.value = f.id;
            opt.textContent = f.label;
            sel.appendChild(opt);
        });
    }

    // ========== DRAG ==========
    function startDrag(e) {
        e.preventDefault();
        const fieldId = e.currentTarget.dataset.fieldId;
        selectField(fieldId);

        isDragging = true;
        const el = e.currentTarget;
        const rect = el.getBoundingClientRect();
        dragOffset.x = e.clientX - rect.left;
        dragOffset.y = e.clientY - rect.top;

        function onMove(ev) {
            if (!isDragging) return;
            const sheetRect = document.getElementById('certSheet').getBoundingClientRect();
            let newX = ev.clientX - sheetRect.left - dragOffset.x;
            let newY = ev.clientY - sheetRect.top - dragOffset.y;

            // Convert px to mm
            const pxPerMm = sheetRect.width / 210;
            const xMm = Math.max(0, Math.min(210, newX / pxPerMm));
            const yMm = Math.max(0, Math.min(297, newY / pxPerMm));

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

        isDragging = true;
        const el = e.currentTarget;
        const rect = el.getBoundingClientRect();
        dragOffset.x = touch.clientX - rect.left;
        dragOffset.y = touch.clientY - rect.top;

        function onMove(ev) {
            if (!isDragging) return;
            const t = ev.touches[0];
            const sheetRect = document.getElementById('certSheet').getBoundingClientRect();
            let newX = t.clientX - sheetRect.left - dragOffset.x;
            let newY = t.clientY - sheetRect.top - dragOffset.y;

            const pxPerMm = sheetRect.width / 210;
            const xMm = Math.max(0, Math.min(210, newX / pxPerMm));
            const yMm = Math.max(0, Math.min(297, newY / pxPerMm));

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
        // Deselect previous
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
                el.style.width = '180mm';
                el.style.transform = 'translateX(-50%)';
                el.style.marginLeft = '15mm';
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

        const step = event?.shiftKey ? 10 : 1; // mm
        f.x = Math.max(0, Math.min(210, f.x + dx * step));
        f.y = Math.max(0, Math.min(297, f.y + dy * step));

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
        SHEET.classList.toggle('show-grid');
        document.getElementById('gridBtn').classList.toggle('active');
    };

    window.toggleMargins = function() {
        SHEET.classList.toggle('show-margins');
        document.getElementById('marginBtn').classList.toggle('active');
    };

    // ========== PERSISTENCE ==========
    function savePositions() {
        const data = {};
        Object.values(fields).forEach(f => {
            data[f.id] = { x: f.x, y: f.y, fontSize: f.fontSize, weight: f.weight, align: f.align };
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
            templates[name][f.id] = { x: f.x, y: f.y, fontSize: f.fontSize, weight: f.weight, align: f.align };
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

    // Make deleteTemplate available globally
    window.deleteTemplate = deleteTemplate;

    // ========== KEYBOARD SHORTCUTS ==========
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

    // Select first field
    const firstField = Object.values(fields)[0];
    if (firstField) selectField(firstField.id);

})();
</script>

</body>
</html>
