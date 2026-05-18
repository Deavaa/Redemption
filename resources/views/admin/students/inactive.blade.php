@extends('layouts.admin')
@section('title', 'Inactive / Left Students')

@push('styles')
<style>
.stu-page { animation: stuFadeIn 0.4s ease-out; }
@keyframes stuFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.stu-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem; }
.stu-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin: 0; }
.stu-subtitle { font-size: 0.88rem; color: var(--text-muted); margin: 0.25rem 0 0; }
.stu-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.stu-breadcrumb li a { color: var(--text-muted); text-decoration: none; }
.stu-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.stu-breadcrumb li.active { color: #4361ee; font-weight: 500; }
</style>
@endpush

@section('content')
<div class="stu-page">
    <div class="stu-header">
        <div>
            <nav class="stu-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                <li class="active">Inactive / Left</li>
            </ol></nav>
            <h1 class="stu-title">Inactive / Left Students</h1>
            <p class="stu-subtitle">Students who have left the school and may be eligible for readmission</p>
        </div>
        <a href="{{ route('admin.students.index') }}" class="btn-modern btn-modern-ghost" style="font-size:0.82rem;padding:0.5rem 1.1rem;">
            <i class="fas fa-arrow-left"></i> Back to Active Students
        </a>
    </div>

    <div class="modern-card">
        <div class="modern-card-header">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:36px;height:36px;border-radius:10px;background:#fef2f2;color:#ef4444;display:flex;align-items:center;justify-content:center;"><i class="fas fa-user-clock"></i></div>
                <h3 class="modern-card-title">Left Students</h3>
            </div>
            @if($students->count() > 0)
            <div style="font-size:0.78rem;color:var(--text-muted);">Total: {{ $students->total() }}</div>
            @endif
        </div>
        <div class="modern-card-body" style="padding:0;overflow-x:auto;">
            @if($students->count() > 0)
            <table class="promo-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Admission #</th>
                        <th>Previous Class</th>
                        <th>Leave Date</th>
                        <th>Leave Reason</th>
                        <th>Readmitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $index => $student)
                    <tr>
                        <td style="font-weight:600;color:var(--text-muted);">{{ $students->firstItem() + $index }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#ef4444,#f87171);color:#fff;display:flex;align-items:center;justify-content:center;font-size:0.72rem;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($student->full_name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:600;color:var(--text-dark);">{{ $student->full_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-weight:600;">{{ $student->admission_number }}</td>
                        <td>{{ $student->previousClassroom->name ?? $student->classroom->name ?? '-' }}</td>
                        <td>{{ $student->leave_date ? $student->leave_date->format('M d, Y') : '-' }}</td>
                        <td>{{ $student->leave_reason ?? '-' }}</td>
                        <td>
                            @if($student->readmission_count > 0)
                            <span class="modern-badge modern-badge-info">{{ $student->readmission_count }} time(s)</span>
                            @else
                            <span class="modern-badge modern-badge-light">Never</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;align-items:center;">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="promo-action-btn" title="View"><i class="fas fa-eye"></i></a>
                                @if($student->canBeReadmitted())
                                <a href="{{ route('admin.students.readmit', $student->id) }}" class="promo-action-btn" title="Readmit" style="border-color:#10b981;color:#10b981;"><i class="fas fa-redo"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div style="text-align:center;padding:3rem 1.5rem;">
                <i class="fas fa-user-check" style="font-size:3rem;color:#d1d5db;margin-bottom:1rem;display:block;"></i>
                <p style="color:var(--text-muted);font-size:0.95rem;">No inactive or transferred students found.</p>
            </div>
            @endif
        </div>
    </div>

    @if($students->count() > 0)
    <div style="display:flex;justify-content:center;margin-top:1rem;">
        {{ $students->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
