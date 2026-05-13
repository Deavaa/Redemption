@extends('layouts.admin')
@section('title', 'Edit Exam')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.exams.index') }}">Exams</a></li>
                    <li class="active">Edit</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Edit Exam</h1>
            <p class="modern-page-subtitle">Update exam schedule for <strong>{{ $exam->name }}</strong></p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.exams.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="modern-card">
        <form method="POST" action="{{ route('admin.exams.update', $exam->id) }}">
            @csrf @method('PUT')

            {{-- Exam Information --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Exam Information</h3>
                        <p class="modern-form-section-desc">Update exam name, type, and total marks</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="name">
                                Exam Name <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-pen modern-input-icon"></i>
                                <input type="text"
                                    name="name"
                                    id="name"
                                    class="modern-input {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                    value="{{ old('name', $exam->name) }}"
                                    placeholder="e.g. End of Term 2 Exam"
                                    required
                                    autofocus>
                            </div>
                            @error('name')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="type">
                                Exam Type <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-tag modern-input-icon"></i>
                                <select name="type" id="type" class="modern-input modern-select {{ $errors->has('type') ? 'is-invalid' : '' }}" required>
                                                                        <option value="">-- Select Type --</option>
                                    <option value="exam" {{ old('type', $exam->type) == 'exam' ? 'selected' : '' }}>Exam</option>
                                    <option value="quiz" {{ old('type', $exam->type) == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                    <option value="test" {{ old('type', $exam->type) == 'test' ? 'selected' : '' }}>Test</option>
                                    <option value="midterm" {{ old('type', $exam->type) == 'midterm' ? 'selected' : '' }}>Mid-Term Exam</option>
                                    <option value="final" {{ old('type', $exam->type) == 'final' ? 'selected' : '' }}>Final Exam</option>
                                    <option value="assignment" {{ old('type', $exam->type) == 'assignment' ? 'selected' : '' }}>Assignment</option>
                                    <option value="project" {{ old('type', $exam->type) == 'project' ? 'selected' : '' }}>Project</option>
                                </select>
                            </div>
                            @error('type')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="total_marks">
                                Total Marks <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-star modern-input-icon"></i>
                                <input type="number"
                                    name="total_marks"
                                    id="total_marks"
                                    class="modern-input {{ $errors->has('total_marks') ? 'is-invalid' : '' }}"
                                    value="{{ old('total_marks', $exam->total_marks) }}"
                                    min="0"
                                    placeholder="100"
                                    required>
                            </div>
                            @error('total_marks')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Academic Period --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Academic Period</h3>
                        <p class="modern-form-section-desc">Update the academic year and term for this exam</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="academic_year_id">
                                Academic Year <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar modern-input-icon"></i>
                                <select name="academic_year_id" id="exam_ay" class="modern-input modern-select {{ $errors->has('academic_year_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Select Year --</option>
                                    @foreach($academicYears as $ay)
                                    <option value="{{ $ay->id }}" {{ old('academic_year_id', $exam->academic_year_id) == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('academic_year_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="term_id">
                                Term <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-list-ol modern-input-icon"></i>
                                <select name="term_id" id="exam_term" class="modern-input modern-select {{ $errors->has('term_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">-- Loading --</option>
                                </select>
                            </div>
                            @error('term_id')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Exam Schedule --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-gold">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Exam Schedule</h3>
                        <p class="modern-form-section-desc">Update the date range and daily time for the exam</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="start_date">
                                Start Date <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-play modern-input-icon"></i>
                                <input type="date"
                                    name="start_date"
                                    id="start_date"
                                    class="modern-input {{ $errors->has('start_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('start_date', $exam->start_date ? $exam->start_date->format('Y-m-d') : '') }}"
                                    required>
                            </div>
                            @error('start_date')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="end_date">
                                End Date <span class="modern-required">*</span>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-stop modern-input-icon"></i>
                                <input type="date"
                                    name="end_date"
                                    id="end_date"
                                    class="modern-input {{ $errors->has('end_date') ? 'is-invalid' : '' }}"
                                    value="{{ old('end_date', $exam->end_date ? $exam->end_date->format('Y-m-d') : '') }}"
                                    required>
                            </div>
                            @error('end_date')
                                <span class="modern-form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="start_time">
                                Start Time <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-clock modern-input-icon"></i>
                                <input type="time"
                                    name="start_time"
                                    id="start_time"
                                    class="modern-input"
                                    value="{{ old('start_time', $exam->start_time) }}">
                            </div>
                        </div>

                        <div class="modern-form-group">
                            <label class="modern-form-label" for="end_time">
                                End Time <small>(optional)</small>
                            </label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-clock modern-input-icon"></i>
                                <input type="time"
                                    name="end_time"
                                    id="end_time"
                                    class="modern-input"
                                    value="{{ old('end_time', $exam->end_time) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple">
                        <i class="fas fa-align-left"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Description</h3>
                        <p class="modern-form-section-desc">Update special instructions or notes for this exam</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-group">
                        <label class="modern-form-label" for="description">
                            Description / Instructions <small>(optional)</small>
                        </label>
                        <div class="modern-input-wrapper">
                            <i class="fas fa-comment-dots modern-input-icon modern-input-icon-textarea"></i>
                            <textarea
                                name="description"
                                id="description"
                                class="modern-input modern-textarea {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                placeholder="Any special instructions for students..."
                                rows="3">{{ old('description', $exam->description) }}</textarea>
                        </div>
                        @error('description')
                            <span class="modern-form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="modern-form-actions">
                <a href="{{ route('admin.exams.index') }}" class="btn-modern btn-modern-ghost">
                    Cancel
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-save"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
/* Modern Page Layout */
.modern-page { animation: fadeSlideIn 0.4s ease-out; }

@keyframes fadeSlideIn {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

.modern-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.modern-page-header-left { flex: 1; }

.modern-page-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0;
    letter-spacing: -0.5px;
}

.modern-page-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0.25rem 0 0;
}

.modern-page-subtitle strong { color: #4361ee; }

/* Breadcrumb */
.modern-breadcrumb ol {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0 0 0.5rem;
    gap: 0.5rem;
    font-size: 0.8rem;
    align-items: center;
}

.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }

/* Card */
.modern-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    border: 1px solid #f0f0f0;
    overflow: hidden;
}

/* Form Section */
.modern-form-section { border-bottom: 1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom: none; }

.modern-form-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 2rem 0.75rem;
}

.modern-form-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }
.modern-form-section-icon-gold { background: #fefce8; color: #d97706; }
.modern-form-section-icon-purple { background: #f5f3ff; color: #7c3aed; }

.modern-form-section-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}

.modern-form-section-desc {
    font-size: 0.82rem;
    color: #9ca3af;
    margin: 0.15rem 0 0;
}

.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }

/* Form Grid */
.modern-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.modern-form-span-2 { grid-column: span 2; }

/* Form Group */
.modern-form-group { display: flex; flex-direction: column; }

.modern-form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.45rem;
    font-size: 0.88rem;
}

.modern-form-label small {
    font-weight: 400;
    color: #9ca3af;
    font-size: 0.78rem;
}

.modern-required { color: #ef4444; font-weight: 700; }

/* Input */
.modern-input-wrapper { position: relative; }

.modern-input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 0.85rem;
    pointer-events: none;
    z-index: 1;
}

.modern-input-icon-textarea { top: 1.1rem; transform: none; }

.modern-input {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.7rem 0.9rem 0.7rem 2.5rem;
    font-size: 0.9rem;
    color: #1a1a2e;
    background: #fff;
    transition: all 0.2s;
}

.modern-input:focus {
    outline: none;
    border-color: #4361ee;
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modern-input::placeholder { color: #c5c9d2; }

.modern-input.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.modern-textarea { resize: vertical; min-height: 80px; }

.modern-select {
    appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25rem;
    padding-right: 2.5rem;
}

.modern-form-error {
    display: block;
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.35rem;
    font-weight: 500;
}

/* Form Actions */
.modern-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.5rem 2rem;
    border-top: 1px solid #f0f0f0;
    background: #fafbfc;
}

/* Modern Button */
.btn-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.35rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.25s;
}

.btn-modern-primary {
    background: linear-gradient(135deg, #4361ee, #3a0ca3);
    color: #fff;
    box-shadow: 0 2px 8px rgba(67, 97, 238, 0.3);
}

.btn-modern-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
    color: #fff;
}

.btn-modern-outline {
    background: transparent;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
}

.btn-modern-outline:hover {
    border-color: #4361ee;
    color: #4361ee;
    background: #f8f9ff;
}

.btn-modern-ghost {
    background: transparent;
    color: #6b7280;
    padding: 0.65rem 1rem;
}

.btn-modern-ghost:hover {
    color: #1a1a2e;
    background: #f3f4f6;
}

/* Responsive */
@media (max-width: 768px) {
    .modern-page-header { flex-direction: column; align-items: stretch; }
    .modern-page-title { font-size: 1.35rem; }
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-span-2 { grid-column: span 1; }
    .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; }
    .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; }
    .modern-form-actions { padding: 1rem 1.25rem; flex-direction: column; }
    .btn-modern { justify-content: center; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
(function(){
    var allTerms = {!! $allTerms->toJson() !!};
    var selAY = document.getElementById('exam_ay');
    var selTerm = document.getElementById('exam_term');

    function filterTerms(ayId) {
        selTerm.innerHTML = '<option value="">-- Select Term --</option>';
        if (!ayId) return;
        for (var i = 0; i < allTerms.length; i++) {
            if (allTerms[i].academic_year_id == ayId) {
                var opt = document.createElement('option');
                opt.value = allTerms[i].id;
                opt.textContent = allTerms[i].name;
                selTerm.appendChild(opt);
            }
        }
    }

    selAY.addEventListener('change', function(){ filterTerms(this.value); });

    // Pre-select current values
    selAY.value = '{{ $exam->academic_year_id }}';
    filterTerms(selAY.value);
    selTerm.value = '{{ $exam->term_id }}';
})();
</script>
@endpush
@endsection
