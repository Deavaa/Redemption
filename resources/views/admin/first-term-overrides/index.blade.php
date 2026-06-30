@extends('layouts.admin')
@section('title', 'First Term Override Marks (Mid-Year Entrants)')

@push('styles')
<style>
.fto-page { max-width: 1400px; margin: 0 auto; }
.fto-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.06); border: 1px solid #f0f0f0; margin-bottom: 1.5rem; }
.fto-card-head { display: flex; align-items: center; gap: .75rem; padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; background: #fafbfc; border-radius: 12px 12px 0 0; }
.fto-card-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.fto-card-icon.blue { background: #eef2ff; color: #4361ee; }
.fto-card-title { font-size: 1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.fto-card-desc { font-size: .82rem; color: #9ca3af; margin: .1rem 0 0; }
.fto-card-body { padding: 1.25rem 1.5rem; }
.fto-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.fto-group { display: flex; flex-direction: column; }
.fto-label { font-weight: 600; color: #374151; margin-bottom: .4rem; font-size: .85rem; }
.fto-select { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 8px; padding: .5rem .8rem; font-size: .88rem; color: #1a1a2e; background: #fff; }
.fto-btn { display: inline-flex; align-items: center; gap: .5rem; padding: .5rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: .88rem; border: none; cursor: pointer; color: #fff; background: linear-gradient(135deg, #4361ee, #3a0ca3); box-shadow: 0 2px 8px rgba(67,97,238,.3); }
.fto-table-wrap { overflow-x: auto; max-height: calc(100vh - 350px); overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 8px; }
.fto-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.fto-table th { border: 1px solid #e5e7eb; font-weight: 700; padding: .4rem .5rem; text-align: center; background: #f9fafb; position: sticky; top: 0; z-index: 5; }
.fto-table td { border: 1px solid #e5e7eb; padding: .25rem .3rem; text-align: center; }
.fto-table .stu-name { text-align: left; white-space: nowrap; font-weight: 600; min-width: 140px; position: sticky; left: 0; background: #fff; z-index: 2; }
.fto-input { width: 60px; text-align: center; border: 1px solid #e5e7eb; border-radius: 4px; padding: 3px 4px; font-size: .78rem; font-weight: 600; }
.fto-input:focus { border-color: #4361ee; outline: none; box-shadow: 0 0 0 2px rgba(67,97,238,.1); }
.fto-badge { font-size: .6rem; background: #d97706; color: #fff; padding: 1px 4px; border-radius: 3px; margin-left: 4px; }
.fto-legend { display: flex; gap: 1rem; margin-top: .5rem; font-size: .78rem; color: #6b7280; }
@media(max-width:768px) { .fto-grid { grid-template-columns: 1fr 1fr; } }
</style>
@endpush

@section('content')
<div class="fto-page">
    <div style="margin-bottom:1.5rem;">
        <h1 style="font-size:1.5rem;font-weight:800;color:#1a1a2e;margin:0;">First Term Override Marks</h1>
        <p style="font-size:.85rem;color:#6b7280;margin:.25rem 0 0;">For students who joined in Term 2 — enter their first-term subject marks from their previous school. These marks are display-only and do NOT affect other students' rankings.</p>
    </div>

    @if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-weight:600;font-size:.85rem;border:1px solid #6ee7b7;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#fee2e2;color:#dc2626;padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-weight:600;font-size:.85rem;border:1px solid #fca5a5;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Filter Card --}}
    <div class="fto-card">
        <div class="fto-card-head">
            <div class="fto-card-icon blue"><i class="fas fa-filter"></i></div>
            <div>
                <h3 class="fto-card-title">Select Class & Section</h3>
                <p class="fto-card-desc">Choose the academic year, class, and section to load students</p>
            </div>
        </div>
        <div class="fto-card-body">
            <form method="GET" action="{{ route('admin.first-term-overrides.index') }}">
                <div class="fto-grid">
                    <div class="fto-group">
                        <label class="fto-label">Academic Year</label>
                        <select name="academic_year_id" class="fto-select" required>
                            <option value="">-- Select --</option>
                            @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" @if((string)$selectedAy === (string)$ay->id) selected @endif>{{ $ay->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fto-group">
                        <label class="fto-label">Class</label>
                        <select name="class_id" class="fto-select" required onchange="this.form.submit()">
                            <option value="">-- Select --</option>
                            @foreach($classes as $c)
                            <option value="{{ $c->id }}" @if((string)$selectedClass === (string)$c->id) selected @endif>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fto-group">
                        <label class="fto-label">Section</label>
                        <select name="section_id" class="fto-select" required onchange="this.form.submit()">
                            <option value="">-- Select --</option>
                            @foreach($sections as $s)
                            <option value="{{ $s->id }}" @if((string)$selectedSection === (string)$s->id) selected @endif>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fto-group" style="justify-content:flex-end;">
                        <button type="submit" class="fto-btn"><i class="fas fa-search"></i> Load Students</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($students->isNotEmpty() && $subjects->isNotEmpty())
    {{-- Override Entry Form --}}
    <div class="fto-card">
        <div class="fto-card-head">
            <div class="fto-card-icon blue"><i class="fas fa-edit"></i></div>
            <div>
                <h3 class="fto-card-title">Enter First Term Marks (per subject)</h3>
                <p class="fto-card-desc">{{ $students->count() }} students · {{ $subjects->count() }} subjects · Leave blank if no data</p>
            </div>
        </div>
        <div class="fto-card-body" style="padding:0;">
            <form method="POST" action="{{ route('admin.first-term-overrides.store') }}">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $selectedAy }}">
                <input type="hidden" name="class_id" value="{{ $selectedClass }}">
                <input type="hidden" name="section_id" value="{{ $selectedSection }}">

                <div class="fto-table-wrap">
                    <table class="fto-table">
                        <thead>
                            <tr>
                                <th style="text-align:left;min-width:140px;position:sticky;left:0;z-index:6;background:#f9fafb;">Student Name</th>
                                <th style="width:60px;">Mid-Year</th>
                                @foreach($subjects as $subj)
                                <th style="min-width:70px;">{{ $subj->name }}<br><small style="font-weight:400;font-size:.7rem;color:#9ca3af;">/100</small></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr>
                                <td class="stu-name">
                                    {{ $student->full_name }}
                                    @if((int)($student->joined_term ?? 1) === 2)
                                    <span class="fto-badge">T2</span>
                                    @endif
                                </td>
                                <td>
                                    @if((int)($student->joined_term ?? 1) === 2)
                                    <i class="fas fa-check" style="color:#059669;"></i>
                                    @else
                                    <span style="color:#d1d5db;">—</span>
                                    @endif
                                </td>
                                @foreach($subjects as $subj)
                                @php
                                    $key = $student->id . '_' . $subj->id;
                                    $override = $overrides[$key] ?? null;
                                    $existingVal = $override ? $override->grand_total : '';
                                    $existingRank = $override ? $override->rank_override : '';
                                @endphp
                                <td>
                                    <input type="number" step="0.01" class="fto-input"
                                        name="marks[{{ $student->id }}_{{ $subj->id }}][grand_total]"
                                        value="{{ old("marks.{$student->id}_{$subj->id}.grand_total", $existingVal) }}"
                                        placeholder="-" title="{{ $subj->name }} mark for {{ $student->full_name }}">
                                    <input type="hidden" name="marks[{{ $student->id }}_{{ $subj->id }}][student_id]" value="{{ $student->id }}">
                                    <input type="hidden" name="marks[{{ $student->id }}_{{ $subj->id }}][subject_id]" value="{{ $subj->id }}">
                                </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;border-top:1px solid #f0f0f0;background:#fafbfc;border-radius:0 0 12px 12px;">
                    <div class="fto-legend">
                        <span><i class="fas fa-info-circle" style="color:#4361ee;"></i> Enter the student's first-term mark (out of 100) for each subject. Leave blank if no data.</span>
                    </div>
                    <button type="submit" class="fto-btn"><i class="fas fa-save"></i> Save Override Marks</button>
                </div>
            </form>
        </div>
    </div>
    @elseif($selectedAy && $selectedClass && $selectedSection)
    <div class="fto-card">
        <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
            <i class="fas fa-users-slash" style="font-size:2.5rem;margin-bottom:.5rem;"></i>
            <p>No students found for the selected class and section.</p>
        </div>
    </div>
    @else
    <div class="fto-card">
        <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
            <i class="fas fa-filter" style="font-size:2.5rem;margin-bottom:.5rem;"></i>
            <p>Select academic year, class, and section to load students.</p>
        </div>
    </div>
    @endif
</div>
@endsection
