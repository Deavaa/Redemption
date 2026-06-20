<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\MarkEntry;
use App\Models\Attendance;
use App\Models\FeePayment;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Exam;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * Report Export Controller
 *
 * Provides PDF (print-friendly HTML) and Excel (CSV) export endpoints
 * for the mobile app and the web admin panel.
 *
 * PDF: Returns a print-optimized HTML page that auto-triggers window.print().
 *      The user can then "Save as PDF" from the browser/webview print dialog.
 *      This avoids the need for a server-side PDF library (dompdf/mpdf).
 *
 * Excel: Returns a CSV file with the proper Content-Type and
 *        Content-Disposition headers. CSV opens natively in Excel,
 *        Google Sheets, and Apple Numbers.
 *
 * For native server-side PDF generation, install barryvdh/laravel-dompdf:
 *   composer require barryvdh/laravel-dompdf
 * Then replace the printHtml() calls with Pdf::loadHTML(...)->download().
 */
class ReportExportController extends Controller
{
    /**
     * Export student marks report.
     * GET /api/export/marks?academic_year_id=1&term_id=2&exam_id=3&class_id=4&section_id=5&subject_id=6&format=pdf|csv
     */
    public function exportMarks(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'exam_id' => 'nullable|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'format' => 'required|in:pdf,csv',
        ]);

        $ay = AcademicYear::findOrFail($request->academic_year_id);
        $term = Term::findOrFail($request->term_id);
        $class = ClassRoom::findOrFail($request->class_id);
        $section = $request->section_id ? Section::find($request->section_id) : null;
        $subject = $request->subject_id ? Subject::find($request->subject_id) : null;
        $exam = $request->exam_id ? Exam::find($request->exam_id) : null;

        // Build query for mark entries
        $query = MarkEntry::with(['student', 'subject', 'exam'])
            ->where('academic_year_id', $ay->id)
            ->where('term_id', $term->id)
            ->whereHas('student', function ($q) use ($class, $section) {
                $q->where('class_id', $class->id);
                if ($section) $q->where('section_id', $section->id);
            });

        if ($subject) $query->where('subject_id', $subject->id);
        if ($exam) $query->where('exam_id', $exam->id);

        $marks = $query->orderBy('student_id')->orderBy('subject_id')->get();

        $title = "Marks Report — {$class->name}" .
            ($section ? " / {$section->name}" : '') .
            " — {$term->name} — {$ay->name}";

        if ($request->format === 'csv') {
            return $this->downloadCsv($title, $this->marksToCsvRows($marks));
        }

        return $this->printHtml($title, $this->marksToHtml($marks, $ay, $term, $class, $section, $subject, $exam));
    }

    /**
     * Export attendance report.
     * GET /api/export/attendance?date=2025-01-15&class_id=4&section_id=5&format=pdf|csv
     */
    public function exportAttendance(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'format' => 'required|in:pdf,csv',
        ]);

        $date = $request->date;
        $class = ClassRoom::findOrFail($request->class_id);
        $section = $request->section_id ? Section::find($request->section_id) : null;

        $query = Attendance::with(['student'])
            ->where('date', $date)
            ->whereHas('student', function ($q) use ($class, $section) {
                $q->where('class_id', $class->id);
                if ($section) $q->where('section_id', $section->id);
            });

        $records = $query->orderBy('student_id')->get();

        $title = "Attendance Report — {$class->name}" .
            ($section ? " / {$section->name}" : '') .
            " — {$date}";

        if ($request->format === 'csv') {
            return $this->downloadCsv($title, $this->attendanceToCsvRows($records));
        }

        return $this->printHtml($title, $this->attendanceToHtml($records, $date, $class, $section));
    }

    /**
     * Export fee payments report.
     * GET /api/export/fees?from=2025-01-01&to=2025-01-31&class_id=4&format=pdf|csv
     */
    public function exportFees(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'class_id' => 'nullable|exists:classes,id',
            'format' => 'required|in:pdf,csv',
        ]);

        $query = FeePayment::with(['student', 'fee'])
            ->orderBy('payment_date', 'desc');

        if ($request->from) $query->where('payment_date', '>=', $request->from);
        if ($request->to) $query->where('payment_date', '<=', $request->to);
        if ($request->class_id) {
            $query->whereHas('student', fn($q) => $q->where('class_id', $request->class_id));
        }

        $payments = $query->limit(1000)->get();

        $title = "Fee Payments Report" .
            ($request->from ? " — From {$request->from}" : '') .
            ($request->to ? " to {$request->to}" : '');

        if ($request->format === 'csv') {
            return $this->downloadCsv($title, $this->feesToCsvRows($payments));
        }

        return $this->printHtml($title, $this->feesToHtml($payments, $request->from, $request->to));
    }

    /**
     * Export student list.
     * GET /api/export/students?class_id=4&section_id=5&branch_id=1&format=pdf|csv
     */
    public function exportStudents(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'branch_id' => 'nullable|exists:branches,id',
            'format' => 'required|in:pdf,csv',
        ]);

        $query = Student::with(['classRoom', 'section', 'branch'])
            ->where('status', 'active');

        if ($request->class_id) $query->where('class_id', $request->class_id);
        if ($request->section_id) $query->where('section_id', $request->section_id);
        if ($request->branch_id) $query->where('branch_id', $request->branch_id);

        $students = $query->orderBy('class_id')->orderBy('section_id')->orderBy('full_name')->limit(2000)->get();

        $title = "Student List Report";

        if ($request->format === 'csv') {
            return $this->downloadCsv($title, $this->studentsToCsvRows($students));
        }

        return $this->printHtml($title, $this->studentsToHtml($students));
    }

    // ─── PRIVATE HELPERS ────────────────────────────────────────────────

    /**
     * Return a CSV file download response.
     */
    private function downloadCsv(string $title, array $rows): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $this->sanitizeFilename($title) . '.csv';

        return Response::stream(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Return a print-friendly HTML page that auto-triggers window.print().
     * The user can then "Save as PDF" from the print dialog.
     */
    private function printHtml(string $title, string $bodyHtml)
    {
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        color: #1a1a2e; background: #fff; padding: 20px;
        font-size: 12px; line-height: 1.5;
    }
    .report-header { text-align: center; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #047857; }
    .report-header h1 { font-size: 20px; color: #047857; margin-bottom: 4px; }
    .report-header .subtitle { font-size: 13px; color: #6b7280; }
    .report-meta { display: flex; justify-content: space-between; font-size: 11px; color: #6b7280; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; margin-top: 8px; }
    th { background: #047857; color: #fff; padding: 8px 6px; text-align: left; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
    td { padding: 6px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
    tr:nth-child(even) td { background: #f9fafb; }
    .report-footer { margin-top: 20px; padding-top: 12px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }
    .no-print { display: none; }
    @media print {
        body { padding: 0; }
        .no-print { display: none !important; }
        @page { margin: 1cm; }
    }
    @media screen {
        .print-bar {
            position: fixed; top: 0; left: 0; right: 0;
            background: #047857; color: #fff; padding: 10px 20px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15); z-index: 1000;
        }
        .print-bar button {
            background: #fff; color: #047857; border: none;
            padding: 6px 16px; border-radius: 6px; font-weight: 600;
            cursor: pointer; font-size: 13px;
        }
        .print-bar button:hover { background: #f0fdf4; }
        body { padding-top: 60px; }
    }
</style>
</head>
<body>
    <div class="print-bar no-print">
        <strong>{$title}</strong>
        <div>
            <button onclick="window.print()">Print / Save as PDF</button>
        </div>
    </div>
    <div class="report-header">
        <h1>School of Redemption</h1>
        <div class="subtitle">{$title}</div>
    </div>
    <div class="report-meta">
        <span>Generated: {$this->nowFormatted()}</span>
        <span>By: {$this->userName()}</span>
    </div>
    {$bodyHtml}
    <div class="report-footer">
        School of Redemption — Confidential — Generated on {$this->nowFormatted()}
    </div>
    <script>
        // Auto-trigger print dialog after a short delay (lets the page render)
        if (window.matchMedia && window.matchMedia('print').matches === false) {
            setTimeout(function() { window.print(); }, 500);
        }
    </script>
</body>
</html>
HTML;

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    private function sanitizeFilename(string $title): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]+/', '_', $title);
    }

    private function nowFormatted(): string
    {
        return now()->format('M j, Y g:i A');
    }

    private function userName(): string
    {
        return auth()->user()?->name ?? 'System';
    }

    // ─── CSV / HTML row builders ────────────────────────────────────────

    private function marksToCsvRows($marks): array
    {
        $rows = [['Student ID', 'Student Name', 'Class', 'Section', 'Subject', 'Exam', 'CA Total', 'Exam Total', 'Grand Total', 'Grade']];
        foreach ($marks as $m) {
            $rows[] = [
                $m->student?->admission_number ?? '',
                $m->student?->full_name ?? '',
                $m->student?->classRoom?->name ?? '',
                $m->student?->section?->name ?? '',
                $m->subject?->name ?? '',
                $m->exam?->name ?? '',
                $m->ca_total ?? '',
                $m->exam_total ?? '',
                $m->grand_total ?? '',
                $m->grade ?? '',
            ];
        }
        return $rows;
    }

    private function marksToHtml($marks, $ay, $term, $class, $section, $subject, $exam): string
    {
        $html = '<table><thead><tr><th>#</th><th>Student ID</th><th>Name</th><th>Subject</th><th>CA</th><th>Exam</th><th>Total</th><th>Grade</th></tr></thead><tbody>';
        foreach ($marks as $i => $m) {
            $html .= '<tr>';
            $html .= '<td>' . ($i + 1) . '</td>';
            $html .= '<td>' . e($m->student?->admission_number ?? '') . '</td>';
            $html .= '<td>' . e($m->student?->full_name ?? '') . '</td>';
            $html .= '<td>' . e($m->subject?->name ?? '') . '</td>';
            $html .= '<td>' . e($m->ca_total ?? '-') . '</td>';
            $html .= '<td>' . e($m->exam_total ?? '-') . '</td>';
            $html .= '<td><strong>' . e($m->grand_total ?? '-') . '</strong></td>';
            $html .= '<td>' . e($m->grade ?? '-') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        if ($marks->isEmpty()) {
            $html = '<p style="text-align:center;color:#9ca3af;padding:20px;">No marks found for the selected criteria.</p>';
        }
        return $html;
    }

    private function attendanceToCsvRows($records): array
    {
        $rows = [['Student ID', 'Student Name', 'Class', 'Section', 'Date', 'Status', 'Notes']];
        foreach ($records as $a) {
            $rows[] = [
                $a->student?->admission_number ?? '',
                $a->student?->full_name ?? '',
                $a->student?->classRoom?->name ?? '',
                $a->student?->section?->name ?? '',
                $a->date ?? '',
                ucfirst($a->status ?? ''),
                $a->notes ?? '',
            ];
        }
        return $rows;
    }

    private function attendanceToHtml($records, $date, $class, $section): string
    {
        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $late = $records->where('status', 'late')->count();
        $html = '<div style="display:flex;gap:16px;margin-bottom:12px;">';
        $html .= '<span style="background:#d1fae5;color:#065f46;padding:4px 12px;border-radius:6px;font-weight:600;">Present: ' . $present . '</span>';
        $html .= '<span style="background:#fef2f2;color:#991b1b;padding:4px 12px;border-radius:6px;font-weight:600;">Absent: ' . $absent . '</span>';
        $html .= '<span style="background:#fef3c7;color:#92400e;padding:4px 12px;border-radius:6px;font-weight:600;">Late: ' . $late . '</span>';
        $html .= '<span style="background:#f3f4f6;color:#374151;padding:4px 12px;border-radius:6px;font-weight:600;">Total: ' . $records->count() . '</span>';
        $html .= '</div>';
        $html .= '<table><thead><tr><th>#</th><th>Student ID</th><th>Name</th><th>Status</th><th>Notes</th></tr></thead><tbody>';
        foreach ($records as $i => $a) {
            $statusColor = match($a->status) {
                'present' => '#d1fae5',
                'absent' => '#fef2f2',
                'late' => '#fef3c7',
                default => '#f3f4f6',
            };
            $html .= '<tr>';
            $html .= '<td>' . ($i + 1) . '</td>';
            $html .= '<td>' . e($a->student?->admission_number ?? '') . '</td>';
            $html .= '<td>' . e($a->student?->full_name ?? '') . '</td>';
            $html .= '<td><span style="background:' . $statusColor . ';padding:2px 8px;border-radius:4px;font-weight:600;">' . ucfirst($a->status ?? '') . '</span></td>';
            $html .= '<td>' . e($a->notes ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        if ($records->isEmpty()) {
            $html = '<p style="text-align:center;color:#9ca3af;padding:20px;">No attendance records found for this date.</p>';
        }
        return $html;
    }

    private function feesToCsvRows($payments): array
    {
        $rows = [['Receipt #', 'Student ID', 'Student Name', 'Class', 'Fee Type', 'Amount Paid', 'Payment Date', 'Method']];
        foreach ($payments as $p) {
            $rows[] = [
                $p->receipt_number ?? '',
                $p->student?->admission_number ?? '',
                $p->student?->full_name ?? '',
                $p->student?->classRoom?->name ?? '',
                $p->fee?->name ?? '',
                $p->amount_paid ?? '',
                $p->payment_date ?? '',
                ucfirst($p->payment_method ?? ''),
            ];
        }
        return $rows;
    }

    private function feesToHtml($payments, $from, $to): string
    {
        $total = $payments->sum('amount_paid');
        $html = '<div style="display:flex;gap:16px;margin-bottom:12px;">';
        $html .= '<span style="background:#d1fae5;color:#065f46;padding:4px 12px;border-radius:6px;font-weight:600;">Total Collected: ' . number_format($total, 2) . '</span>';
        $html .= '<span style="background:#f3f4f6;color:#374151;padding:4px 12px;border-radius:6px;font-weight:600;">Transactions: ' . $payments->count() . '</span>';
        $html .= '</div>';
        $html .= '<table><thead><tr><th>#</th><th>Receipt #</th><th>Student</th><th>Fee Type</th><th>Amount</th><th>Date</th><th>Method</th></tr></thead><tbody>';
        foreach ($payments as $i => $p) {
            $html .= '<tr>';
            $html .= '<td>' . ($i + 1) . '</td>';
            $html .= '<td>' . e($p->receipt_number ?? '') . '</td>';
            $html .= '<td>' . e($p->student?->full_name ?? '') . '</td>';
            $html .= '<td>' . e($p->fee?->name ?? '') . '</td>';
            $html .= '<td><strong>' . number_format($p->amount_paid ?? 0, 2) . '</strong></td>';
            $html .= '<td>' . e($p->payment_date ?? '') . '</td>';
            $html .= '<td>' . ucfirst($p->payment_method ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        if ($payments->isEmpty()) {
            $html = '<p style="text-align:center;color:#9ca3af;padding:20px;">No fee payments found for the selected period.</p>';
        }
        return $html;
    }

    private function studentsToCsvRows($students): array
    {
        $rows = [['Admission #', 'Name', 'Class', 'Section', 'Gender', 'Date of Birth', 'Parent Name', 'Parent Phone', 'Branch']];
        foreach ($students as $s) {
            $rows[] = [
                $s->admission_number ?? '',
                $s->full_name ?? '',
                $s->classRoom?->name ?? '',
                $s->section?->name ?? '',
                ucfirst($s->gender ?? ''),
                $s->date_of_birth ?? '',
                $s->father_name ?? $s->mother_name ?? '',
                $s->father_phone ?? $s->mother_phone ?? '',
                $s->branch?->name ?? '',
            ];
        }
        return $rows;
    }

    private function studentsToHtml($students): string
    {
        $html = '<div style="display:flex;gap:16px;margin-bottom:12px;">';
        $html .= '<span style="background:#dbeafe;color:#1e40af;padding:4px 12px;border-radius:6px;font-weight:600;">Total Students: ' . $students->count() . '</span>';
        $html .= '</div>';
        $html .= '<table><thead><tr><th>#</th><th>Admission #</th><th>Name</th><th>Class</th><th>Section</th><th>Gender</th><th>Parent Phone</th></tr></thead><tbody>';
        foreach ($students as $i => $s) {
            $html .= '<tr>';
            $html .= '<td>' . ($i + 1) . '</td>';
            $html .= '<td>' . e($s->admission_number ?? '') . '</td>';
            $html .= '<td>' . e($s->full_name ?? '') . '</td>';
            $html .= '<td>' . e($s->classRoom?->name ?? '') . '</td>';
            $html .= '<td>' . e($s->section?->name ?? '') . '</td>';
            $html .= '<td>' . ucfirst($s->gender ?? '') . '</td>';
            $html .= '<td>' . e($s->father_phone ?? $s->mother_phone ?? '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        if ($students->isEmpty()) {
            $html = '<p style="text-align:center;color:#9ca3af;padding:20px;">No students found.</p>';
        }
        return $html;
    }
}
