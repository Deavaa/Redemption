@extends('layouts.admin')
@section('title', 'New Teacher Efficiency Assessment')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol><li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li><li><a href="{{ route('admin.teacher-efficiency.index') }}">Teacher Efficiency</a></li><li class="active">New Assessment</li></ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.teacher-efficiency.index') }}" class="btn-modern btn-modern-outline"><i class="fas fa-arrow-left"></i><span>Back</span></a>
        </div>
    </div>

    @if($errors->any())
    <div class="modern-alert modern-alert-error" style="margin-bottom:1rem;"><i class="fas fa-exclamation-circle"></i><span>Please fix the errors below.</span></div>
    @endif

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.teacher-efficiency.store') }}" id="assessmentForm">
            @csrf

            {{-- Section 1: Context --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue"><i class="fas fa-user-check"></i></div>
                    <div><h3 class="modern-form-section-title">Assessment Context</h3><p class="modern-form-section-desc">Select teacher, academic year, term and branch</p></div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Branch <span class="modern-required">*</span></label>
                            <select name="branch_id" id="branchSelect" class="modern-input modern-select" required>
                                <option value="">-- Select Branch --</option>
                                @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id', $selectedBranchId) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Teacher <span class="modern-required">*</span></label>
                            <select name="teacher_id" id="teacherSelect" class="modern-input modern-select" required>
                                <option value="">-- Select Branch First --</option>
                                @foreach($teachers as $t)
                                <option value="{{ $t->id }}" data-branch="{{ $t->branch_id }}" {{ old('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Academic Year <span class="modern-required">*</span></label>
                            <select name="academic_year_id" class="modern-input modern-select" required>
                                <option value="">-- Select --</option>
                                @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}" {{ old('academic_year_id') == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                                @endforeach
                            </select>
                            @error('academic_year_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Term <span class="modern-required">*</span></label>
                            <select name="term_id" class="modern-input modern-select" required>
                                <option value="">-- Select --</option>
                                @foreach($allTerms as $t)
                                <option value="{{ $t->id }}" {{ old('term_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                            @error('term_id')<span class="modern-form-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Efficiency Criteria --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green"><i class="fas fa-star"></i></div>
                    <div><h3 class="modern-form-section-title">Efficiency Criteria (1-5 Scale)</h3><p class="modern-form-section-desc">Rate each criterion from 1 (Poor) to 5 (Excellent)</p></div>
                </div>
                <div class="modern-form-section-body" style="padding:1.25rem 2rem 1.75rem;">
                    @php
                        $criteriaLabels = \App\Models\TeacherEfficiencyAssessment::CRITERIA;
                        $scaleLabels = [1 => 'Poor', 2 => 'Below Avg', 3 => 'Satisfactory', 4 => 'Good', 5 => 'Excellent'];
                    @endphp
                    @foreach($criteriaLabels as $field => $label)
                    <div class="eq-criteria-row">
                        <div class="eq-criteria-label">{{ $label }}</div>
                        <div class="eq-criteria-radios">
                            @for($i = 1; $i <= 5; $i++)
                            <label class="eq-radio-label" title="{{ $scaleLabels[$i] }}">
                                <input type="radio" name="{{ $field }}" value="{{ $i }}" class="eq-criteria-input" {{ old($field) == (string)$i ? 'checked' : '' }} required>
                                <span class="eq-radio-dot">{{ $i }}</span>
                            </label>
                            @endfor
                        </div>
                    </div>
                    @error($field)
                    <div class="modern-form-error" style="margin-top:-.5rem;margin-bottom:.5rem;">{{ $message }}</div>
                    @enderror
                    @endforeach

                    {{-- Live Score Display --}}
                    <div class="eq-score-display" id="liveScoreDisplay">
                        <div class="eq-score-left">
                            <span class="eq-score-label">Calculated Score</span>
                            <span class="eq-score-value" id="liveScore">0</span>
                            <span class="eq-score-max">/100</span>
                        </div>
                        <div class="eq-score-right">
                            <span class="eq-grade-badge" id="liveGrade">-</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Comments --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple"><i class="fas fa-comment-dots"></i></div>
                    <div><h3 class="modern-form-section-title">Comments & Feedback</h3></div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Strengths</label>
                            <textarea name="strengths" class="modern-input modern-textarea" rows="3" placeholder="What the teacher does well...">{{ old('strengths') }}</textarea>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Areas for Improvement</label>
                            <textarea name="areas_for_improvement" class="modern-input modern-textarea" rows="3" placeholder="Where the teacher can improve...">{{ old('areas_for_improvement') }}</textarea>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Action Plan</label>
                            <textarea name="action_plan" class="modern-input modern-textarea" rows="3" placeholder="Recommended actions for improvement...">{{ old('action_plan') }}</textarea>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">General Comments</label>
                            <textarea name="comments" class="modern-input modern-textarea" rows="3" placeholder="Any additional notes...">{{ old('comments') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-form-actions">
                <a href="{{ route('admin.teacher-efficiency.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" name="status" value="draft" class="btn-modern btn-modern-outline"><i class="fas fa-save"></i> Save Draft</button>
                <button type="submit" name="status" value="completed" class="btn-modern btn-modern-primary"><i class="fas fa-check"></i> Submit Assessment</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.modern-page{animation:fadeSlideIn .4s ease-out}@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem}.modern-page-header-left{flex:1}.modern-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}.modern-breadcrumb li{color:#adb5bd}.modern-breadcrumb li a{color:#6c757d;text-decoration:none}.modern-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.modern-breadcrumb li.active{color:#4361ee;font-weight:500}.modern-alert{display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;border-radius:10px;font-size:.88rem;font-weight:500}.modern-alert-error{background:#fef2f2;color:#dc2626;border:1px solid #fecaca}.modern-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden}.modern-form-section{border-bottom:1px solid #f0f0f0}.modern-form-section:last-of-type{border-bottom:none}.modern-form-section-header{display:flex;align-items:center;gap:1rem;padding:1.5rem 2rem .75rem}.modern-form-section-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}.modern-form-section-icon-blue{background:#eef2ff;color:#4361ee}.modern-form-section-icon-green{background:#ecfdf5;color:#10b981}.modern-form-section-icon-purple{background:#f5f3ff;color:#7c3aed}.modern-form-section-title{font-size:1.05rem;font-weight:700;color:#1a1a2e;margin:0}.modern-form-section-desc{font-size:.82rem;color:#9ca3af;margin:.15rem 0 0}.modern-form-section-body{padding:1.25rem 2rem 1.75rem}.modern-form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:1.25rem}.modern-form-group{display:flex;flex-direction:column}.modern-form-label{font-weight:600;color:#374151;margin-bottom:.45rem;font-size:.88rem}.modern-required{color:#ef4444;font-weight:700}.modern-input{width:100%;border:1.5px solid #e5e7eb;border-radius:10px;padding:.7rem .9rem;font-size:.9rem;color:#1a1a2e;background:#fff;transition:all .2s}.modern-input:focus{outline:none;border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1)}.modern-input::placeholder{color:#c5c9d2}.modern-textarea{resize:vertical;min-height:80px}.modern-select{appearance:none;cursor:pointer;background-image:url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");background-position:right .75rem center;background-repeat:no-repeat;background-size:1.25rem;padding-right:2.5rem}.modern-form-error{display:block;color:#ef4444;font-size:.8rem;margin-top:.35rem;font-weight:500}.modern-form-actions{display:flex;justify-content:flex-end;gap:.75rem;padding:1.5rem 2rem;border-top:1px solid #f0f0f0;background:#fafbfc}.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.35rem;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;border:none;cursor:pointer;transition:all .25s}.btn-modern-primary{background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;box-shadow:0 2px 8px rgba(67,97,238,.3)}.btn-modern-primary:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}.btn-modern-outline{background:transparent;color:#6b7280;border:1.5px solid #e5e7eb}.btn-modern-outline:hover{border-color:#4361ee;color:#4361ee}.btn-modern-ghost{background:transparent;color:#6b7280;padding:.65rem 1rem}.btn-modern-ghost:hover{color:#1a1a2e;background:#f3f4f6}
.eq-criteria-row{display:flex;align-items:center;gap:1rem;padding:.75rem 0;border-bottom:1px solid #f3f4f6}.eq-criteria-row:last-of-type{border-bottom:none}.eq-criteria-label{flex:1;font-size:.88rem;font-weight:500;color:#374151}.eq-criteria-radios{display:flex;gap:.25rem}.eq-radio-label{cursor:pointer}.eq-radio-label input{display:none}.eq-radio-dot{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;border:1.5px solid #e5e7eb;font-size:.85rem;font-weight:600;color:#6b7280;transition:all .2s}.eq-radio-label:hover .eq-radio-dot{border-color:#4361ee;color:#4361ee;background:#eef2ff}.eq-radio-label input:checked+.eq-radio-dot{background:#4361ee;color:#fff;border-color:#4361ee;box-shadow:0 2px 6px rgba(67,97,238,.3)}.eq-score-display{margin-top:1.5rem;padding:1.25rem;background:linear-gradient(135deg,#f8f9ff,#eef2ff);border-radius:12px;display:flex;justify-content:space-between;align-items:center;border:1px solid #e0e7ff}.eq-score-label{font-size:.85rem;color:#6b7280;font-weight:500;display:block}.eq-score-value{font-size:2.5rem;font-weight:900;color:#4361ee;line-height:1}.eq-score-max{font-size:1rem;color:#9ca3af;font-weight:500}.eq-grade-badge{display:inline-flex;align-items:center;padding:.5rem 1.25rem;border-radius:50px;font-size:1rem;font-weight:700}.eq-grade-excellent{background:#ecfdf5;color:#059669}.eq-grade-good{background:#eff6ff;color:#2563eb}.eq-grade-satisfactory{background:#f3f4f6;color:#6b7280}.eq-grade-needs_improvement{background:#fffbeb;color:#d97706}.eq-grade-unsatisfactory{background:#fef2f2;color:#dc2626}@media(max-width:768px){.modern-form-grid{grid-template-columns:1fr}.modern-form-actions{flex-direction:column}.btn-modern{justify-content:center;width:100%}.eq-criteria-row{flex-direction:column;align-items:flex-start;gap:.5rem}}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Branch → Teacher filtering
    const branchSelect = document.getElementById('branchSelect');
    const teacherSelect = document.getElementById('teacherSelect');
    const teacherOptions = teacherSelect.querySelectorAll('option[data-branch]');

    function filterTeachers() {
        const branchId = branchSelect.value;
        let hasVisible = false;
        teacherOptions.forEach(function(opt) {
            if (!branchId || opt.dataset.branch === branchId) {
                opt.style.display = '';
                hasVisible = true;
            } else {
                opt.style.display = 'none';
            }
        });
        const firstOpt = teacherSelect.querySelector('option[value=""]');
        if (firstOpt) {
            firstOpt.textContent = branchId ? '-- Select Teacher --' : '-- Select Branch First --';
        }
        if (!branchId) {
            teacherOptions.forEach(function(opt) { opt.style.display = ''; });
        }
    }

    branchSelect.addEventListener('change', filterTeachers);
    filterTeachers();

    // Live score calculation
    const criteriaFields = [
        'lesson_delivery','student_assessment','curriculum_coverage','classroom_environment',
        'student_participation','professional_development','communication','time_management',
        'collaboration','result_achievement'
    ];

    function calculateScore() {
        let total = 0, count = 0;
        criteriaFields.forEach(function(field) {
            const checked = document.querySelector('input[name="' + field + '"]:checked');
            if (checked) { total += parseInt(checked.value); count++; }
        });
        const score = count > 0 ? Math.round((total / count) * 20 * 100) / 100 : 0;
        document.getElementById('liveScore').textContent = score.toFixed(1);

        let gradeText = '-', gradeClass = '';
        if (score >= 90) { gradeText = 'Excellent'; gradeClass = 'eq-grade-excellent'; }
        else if (score >= 75) { gradeText = 'Good'; gradeClass = 'eq-grade-good'; }
        else if (score >= 60) { gradeText = 'Satisfactory'; gradeClass = 'eq-grade-satisfactory'; }
        else if (score >= 40) { gradeText = 'Needs Improvement'; gradeClass = 'eq-grade-needs_improvement'; }
        else if (count > 0) { gradeText = 'Unsatisfactory'; gradeClass = 'eq-grade-unsatisfactory'; }

        const badge = document.getElementById('liveGrade');
        badge.textContent = gradeText;
        badge.className = 'eq-grade-badge ' + gradeClass;
    }

    document.querySelectorAll('.eq-criteria-input').forEach(function(input) {
        input.addEventListener('change', calculateScore);
    });

    // Initial calculation
    calculateScore();
});
</script>
@endpush
@endsection
