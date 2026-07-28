@extends('layouts.admin')
@section('title', 'Bulk Fix Class/Section')

@push('styles')
<style>
.bfc-page{max-width:1200px;margin:0 auto;font-family:'Inter',sans-serif;}
.bfc-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;}
.bfc-header h2{margin:0;font-size:1.5rem;font-weight:700;color:#0f172a;}
.bfc-header h2 i{color:#047857;margin-right:8px;}
.bfc-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,0.04);overflow:hidden;margin-bottom:1.5rem;}
.bfc-card-head{display:flex;align-items:center;gap:12px;padding:16px 20px;border-bottom:1px solid #f1f5f9;background:#fafbfc;}
.bfc-card-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;}
.bfc-card-icon.blue{background:#e0e7ff;color:#4361ee;}
.bfc-card-icon.green{background:#d1fae5;color:#059669;}
.bfc-card-icon.amber{background:#fef3c7;color:#d97706;}
.bfc-card-title{font-size:1rem;font-weight:700;color:#0f172a;margin:0;}
.bfc-card-desc{font-size:0.78rem;color:#94a3b8;margin:2px 0 0;}
.bfc-card-body{padding:20px;}
.bfc-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;}
@media(max-width:768px){.bfc-grid{grid-template-columns:1fr 1fr;}}
@media(max-width:480px){.bfc-grid{grid-template-columns:1fr;}}
.bfc-field{display:flex;flex-direction:column;}
.bfc-label{font-size:0.8rem;font-weight:600;color:#374151;margin-bottom:6px;}
.bfc-label .req{color:#ef4444;}
.bfc-select{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:0.6rem 0.8rem;font-size:0.88rem;color:#1f2937;background:#fff;appearance:none;cursor:pointer;transition:all 0.2s;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right 0.6rem center;background-repeat:no-repeat;background-size:1.15rem;}
.bfc-select:focus{outline:none;border-color:#047857;box-shadow:0 0 0 3px rgba(4,120,87,0.10);}
.bfc-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:10px;font-size:0.9rem;font-weight:600;border:none;cursor:pointer;transition:all 0.25s;color:#fff;background:linear-gradient(135deg,#047857,#0d9488);box-shadow:0 4px 14px rgba(4,120,87,0.30);}
.bfc-btn:hover{transform:translateY(-2px);box-shadow:0 8px 22px rgba(4,120,87,0.40);}
.bfc-btn-outline{background:transparent;color:#64748b;border:1.5px solid #e5e7eb;box-shadow:none;}
.bfc-btn-outline:hover{border-color:#047857;color:#047857;background:#f0fdf4;transform:none;box-shadow:none;}
.bfc-actions{display:flex;justify-content:flex-end;gap:10px;padding:16px 20px;border-top:1px solid #f1f5f9;background:#fafbfc;}
.bfc-table{width:100%;border-collapse:collapse;font-size:0.85rem;}
.bfc-table th{padding:8px 12px;text-align:left;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:#64748b;background:#f9fafb;border-bottom:2px solid #e5e7eb;}
.bfc-table td{padding:8px 12px;border-bottom:1px solid #f3f4f6;color:#1e293b;}
.bfc-table tbody tr:hover{background:#f8fafc;}
.bfc-alert{padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:0.85rem;font-weight:600;}
.bfc-alert-success{background:#d1fae5;border:1px solid #a7f3d0;color:#065f46;}
.bfc-alert-error{background:#fee2e2;border:1px solid #fecaca;color:#991b1b;}
.bfc-alert-info{background:#dbeafe;border:1px solid #93c5fd;color:#1e40af;}
.bfc-empty{text-align:center;padding:32px;color:#94a3b8;}
.bfc-empty i{font-size:2rem;opacity:0.3;display:block;margin-bottom:8px;}
.bfc-stats{display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap;}
.bfc-stat{background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:12px 16px;text-align:center;min-width:120px;}
.bfc-stat-val{font-size:1.5rem;font-weight:800;color:#0f172a;}
.bfc-stat-lbl{font-size:0.72rem;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-top:2px;}
.bfc-warning{background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;color:#92400e;font-size:0.82rem;margin-bottom:16px;display:flex;align-items:flex-start;gap:8px;}
</style>
@endpush

@section('content')
<div class="bfc-page">
    <div class="bfc-header">
        <h2><i class="fas fa-wrench"></i>Bulk Fix Class/Section</h2>
        <a href="{{ route('admin.enrollments.index') }}" class="bfc-btn bfc-btn-outline"><i class="fas fa-arrow-left"></i> Back to Enrollments</a>
    </div>

    @if(session('success'))
    <div class="bfc-alert bfc-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bfc-alert bfc-alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="bfc-warning">
        <i class="fas fa-exclamation-triangle" style="margin-top:2px;"></i>
        <div>
            <strong>What this does:</strong> Moves ALL active students from the "From Class" to the "To Class".
            Updates both the student's main class_id AND their enrollment record for the selected academic year.
            Use this to fix students who are stuck in their old grade after bulk enrollment or promotion.
        </div>
    </div>

    {{-- Selection Form --}}
    <div class="bfc-card">
        <div class="bfc-card-head">
            <div class="bfc-card-icon blue"><i class="fas fa-filter"></i></div>
            <div>
                <h3 class="bfc-card-title">Select Classes</h3>
                <p class="bfc-card-desc">Choose the old class (current) and the new class (target) to move students</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.enrollments.bulk-fix-class') }}">
            <div class="bfc-card-body">
                <div class="bfc-grid">
                    <div class="bfc-field">
                        <label class="bfc-label">Academic Year <span class="req">*</span></label>
                        <select name="academic_year_id" class="bfc-select" required onchange="this.form.submit()">
                            <option value="">-- Select Year --</option>
                            @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" @if($selectedAy && $selectedAy->id == $ay->id) selected @endif>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bfc-field">
                        <label class="bfc-label">From Class (Old Grade) <span class="req">*</span></label>
                        <select name="from_class_id" class="bfc-select" required onchange="this.form.submit()">
                            <option value="">-- Select Old Class --</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" @if((string)$fromClassId === (string)$c->id) selected @endif>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bfc-field">
                        <label class="bfc-label">To Class (New Grade) <span class="req">*</span></label>
                        <select name="to_class_id" class="bfc-select" required onchange="this.form.submit()">
                            <option value="">-- Select New Class --</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" @if((string)$toClassId === (string)$c->id) selected @endif>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="bfc-field">
                        <label class="bfc-label">Section (optional)</label>
                        <select name="section_id" class="bfc-select">
                            <option value="">-- Auto-assign --</option>
                            @if($toSections->count() > 0)
                                @foreach($toSections as $s)
                                <option value="{{ $s->id }}" @if((string)$toSectionId === (string)$s->id) selected @endif>{{ $s->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="bfc-actions">
                <button type="submit" class="bfc-btn bfc-btn-outline"><i class="fas fa-eye"></i> Preview Students</button>
            </div>
        </form>
    </div>

    {{-- Preview Students --}}
    @if($previewStudents->count() > 0)
    <div class="bfc-card">
        <div class="bfc-card-head">
            <div class="bfc-card-icon amber"><i class="fas fa-users"></i></div>
            <div>
                <h3 class="bfc-card-title">Preview: {{ $previewStudents->count() }} Students</h3>
                <p class="bfc-card-desc">These students will be moved from <strong>{{ $fromClassName }}</strong> to <strong>{{ $toClassName }}</strong></p>
            </div>
        </div>
        <div class="bfc-card-body" style="padding:0;">
            <table class="bfc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Current Class</th>
                        <th>Current Section</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($previewStudents as $i => $student)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td style="font-weight:600;">{{ $student->full_name }}</td>
                        <td>{{ $fromClassName }}</td>
                        <td>{{ $student->section?->name ?? '—' }}</td>
                        <td><span style="padding:2px 8px;border-radius:50px;background:#d1fae5;color:#065f46;font-size:0.72rem;font-weight:700;">{{ ucfirst($student->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="bfc-actions">
            <form method="POST" action="{{ route('admin.enrollments.process-bulk-fix-class') }}" onsubmit="return confirm('Move {{ $previewStudents->count() }} student(s) from {{ $fromClassName }} to {{ $toClassName }}? This will update both student records and enrollment records.')">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $selectedAy->id }}">
                <input type="hidden" name="from_class_id" value="{{ $fromClassId }}">
                <input type="hidden" name="to_class_id" value="{{ $toClassId }}">
                <input type="hidden" name="section_id" value="{{ $toSectionId }}">
                <button type="submit" class="bfc-btn"><i class="fas fa-check"></i> Confirm: Move {{ $previewStudents->count() }} Students</button>
            </form>
        </div>
    </div>
    @elseif($fromClassId && $toClassId)
    <div class="bfc-card">
        <div class="bfc-card-body">
            <div class="bfc-empty">
                <i class="fas fa-check-circle"></i>
                <p>No active students found in <strong>{{ $fromClassName }}</strong>.</p>
                <p style="font-size:0.8rem;margin-top:4px;">All students may have already been moved, or there are no active students in this class.</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
