@extends('student.layout')

@section('title', 'Graduate Dashboard')

@section('content')
<style>
.grad-banner{background:linear-gradient(135deg,#7c3aed,#4f46e5);color:#fff;border-radius:16px;padding:2rem;margin-bottom:1.5rem;position:relative;overflow:hidden}
.grad-banner::before{content:'';position:absolute;top:-50%;right:-10%;width:300px;height:300px;background:rgba(255,255,255,0.08);border-radius:50%}
.grad-banner h2{margin:0 0 0.5rem;font-size:1.75rem;font-weight:700;position:relative}
.grad-banner p{margin:0;opacity:0.9;position:relative}
.grad-banner .grad-icon{font-size:3rem;position:absolute;right:2rem;top:50%;transform:translateY(-50%);opacity:0.3}
.grad-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem}
.grad-stat-card{background:#fff;border-radius:12px;padding:1.25rem;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,0.04)}
.grad-stat-card .icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:0.5rem}
.grad-stat-card .num{font-size:1.5rem;font-weight:700;color:#1a1a2e}
.grad-stat-card .label{font-size:0.8rem;color:#6b7280;margin-top:2px}
.cert-card{background:#fff;border-radius:12px;padding:1rem 1.25rem;border:1px solid #e5e7eb;display:flex;align-items:center;gap:1rem;margin-bottom:0.75rem;transition:all .15s}
.cert-card:hover{border-color:#7c3aed;box-shadow:0 2px 8px rgba(124,58,237,0.1)}
.cert-icon{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
.cert-info{flex-grow:1}
.cert-info .title{font-weight:600;color:#1a1a2e}
.cert-info .meta{font-size:0.8rem;color:#6b7280;margin-top:2px}
.cert-actions{display:flex;gap:0.5rem}
.marks-table{width:100%;border-collapse:collapse;font-size:0.85rem}
.marks-table th{background:#f9fafb;padding:0.5rem 0.75rem;text-align:left;font-weight:600;color:#374151;border-bottom:2px solid #e5e7eb}
.marks-table td{padding:0.5rem 0.75rem;border-bottom:1px solid #f3f4f6}
.marks-table tr:hover td{background:#f9fafb}
.empty-state{text-align:center;padding:2rem;color:#9ca3af}
</style>

<div class="grad-banner">
    <i class="fas fa-graduation-cap grad-icon"></i>
    <h2><i class="fas fa-mortarboard me-2"></i>Congratulations, {{ $student->full_name }}!</h2>
    <p>You have successfully graduated from {{ $student->classroom?->name ?? 'Grade 12' }}@if($graduationAy) · Academic Year {{ $graduationAy->name }}@endif</p>
</div>

{{-- Stats --}}
<div class="grad-stats">
    <div class="grad-stat-card">
        <div class="icon" style="background:#dcfce7;color:#15803d"><i class="fas fa-check-circle"></i></div>
        <div class="num">{{ $certificates->count() }}</div>
        <div class="label">Certificate(s) Issued</div>
    </div>
    <div class="grad-stat-card">
        <div class="icon" style="background:#dbeafe;color:#1d4ed8"><i class="fas fa-book"></i></div>
        <div class="num">{{ $allMarks->count() }}</div>
        <div class="label">Total Mark Records</div>
    </div>
    <div class="grad-stat-card">
        <div class="icon" style="background:#fef3c7;color:#a16207"><i class="fas fa-calendar"></i></div>
        <div class="num">{{ $marksByAy->count() }}</div>
        <div class="label">Academic Year(s) Completed</div>
    </div>
    <div class="grad-stat-card">
        <div class="icon" style="background:#f3e8ff;color:#7e22ce"><i class="fas fa-star"></i></div>
        <div class="num">{{ $allMarks->count() > 0 ? round($allMarks->avg('grand_total'), 1) : 0 }}</div>
        <div class="label">Overall Average</div>
    </div>
</div>

{{-- Certificates --}}
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header" style="background:#f9fafb;padding:1rem 1.25rem">
        <h4 style="margin:0;font-weight:600"><i class="fas fa-award me-2 text-warning"></i>Your Certificates</h4>
    </div>
    <div class="card-body" style="padding:1rem 1.25rem">
        @if($certificates->isEmpty())
            <div class="empty-state">
                <i class="fas fa-folder-open" style="font-size:2.5rem;margin-bottom:0.5rem"></i>
                <p>No certificates have been issued yet.</p>
                <small>Contact the school office if you expected a certificate.</small>
            </div>
        @else
            @foreach($certificates as $cert)
                <div class="cert-card">
                    <div class="cert-icon"><i class="fas fa-certificate"></i></div>
                    <div class="cert-info">
                        <div class="title">{{ ucfirst($cert->type) }} Certificate</div>
                        <div class="meta">
                            <i class="fas fa-hashtag me-1"></i>{{ $cert->certificate_number }}
                            <span class="ms-3"><i class="fas fa-calendar me-1"></i>{{ $cert->issue_date?->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <div class="cert-actions">
                        @if($cert->type === 'transcript')
                            <a href="{{ route('admin.transcript.print', $cert->student_id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-print me-1"></i>View
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

{{-- Marks History --}}
<div class="card">
    <div class="card-header" style="background:#f9fafb;padding:1rem 1.25rem">
        <h4 style="margin:0;font-weight:600"><i class="fas fa-history me-2 text-primary"></i>Academic Record (All Years)</h4>
    </div>
    <div class="card-body" style="padding:1rem 1.25rem">
        @if($marksByAy->isEmpty())
            <div class="empty-state">
                <i class="fas fa-folder-open" style="font-size:2.5rem;margin-bottom:0.5rem"></i>
                <p>No marks records found.</p>
            </div>
        @else
            @foreach($marksByAy as $ayId => $ayMarks)
                @php $ayName = $ayMarks->first()->academicYear?->name ?? "AY #{$ayId}"; @endphp
                <h5 style="margin:1rem 0 0.5rem;color:#374151;font-size:0.95rem">
                    <i class="fas fa-calendar me-1 text-muted"></i>{{ $ayName }}
                </h5>
                <table class="marks-table" style="margin-bottom:1rem">
                    <thead>
                        <tr><th>Subject</th><th>Term</th><th>Class</th><th>Mark</th><th>Grade</th></tr>
                    </thead>
                    <tbody>
                        @foreach($ayMarks as $mark)
                            <tr>
                                <td>{{ $mark->subject?->name ?? '-' }}</td>
                                <td>{{ $mark->term?->name ?? '-' }}</td>
                                <td>{{ $mark->classRoom?->name ?? '-' }}</td>
                                <td><strong>{{ $mark->grand_total ?? '-' }}</strong></td>
                                <td>
                                    @if($mark->grade)
                                        <span class="badge bg-{{
                                            $mark->grade === 'A' ? 'success' :
                                            ($mark->grade === 'B' ? 'primary' :
                                            ($mark->grade === 'C' ? 'info' :
                                            ($mark->grade === 'D' ? 'warning' : 'danger')))
                                        }}">{{ $mark->grade }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    </div>
</div>

<div style="margin-top:1.5rem;padding:1rem;background:#f0f9ff;border-radius:10px;border-left:4px solid #3b82f6;font-size:0.85rem;color:#1e40af">
    <i class="fas fa-info-circle me-1"></i>
    <strong>Need help?</strong> Contact the school office to request a printed copy of your transcript, certificate, or to update your contact information.
</div>
@endsection
