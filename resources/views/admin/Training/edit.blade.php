@extends('layouts.admin')
@section('title', 'Edit Training')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">HR</a></li>
                    <li><a href="{{ route('admin.trainings.index') }}">Capacity Building</a></li>
                    <li class="active">Edit</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Edit Training Program</h1>
            <p class="modern-page-subtitle">{{ $item->title }}</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.trainings.show', $item->id) }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ route('admin.trainings.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.trainings.update', $item->id) }}">
            @csrf @method('PUT')

            {{-- Program Details --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Program Details</h3>
                        <p class="modern-form-section-desc">Enter the training program title, type, and category</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="title">Training Title <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-book modern-input-icon"></i>
                                <input type="text" name="title" id="title" class="modern-input {{ $errors->has('title') ? 'is-invalid' : '' }}" value="{{ old('title', $item->title) }}" required>
                            </div>
                            @error('title') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="type">Training Type <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-tag modern-input-icon"></i>
                                <select name="type" id="type" class="modern-select {{ $errors->has('type') ? 'is-invalid' : '' }}" required>
                                    <option value="">Select type...</option>
                                    @foreach(['workshop'=>'Workshop','seminar'=>'Seminar','online_course'=>'Online Course','on_the_job'=>'On-the-Job','certification'=>'Certification','conference'=>'Conference','mentorship'=>'Mentorship','induction'=>'Induction'] as $k=>$v)
                                    <option value="{{ $k }}" {{ old('type', $item->type) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('type') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="category">Category <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-folder modern-input-icon"></i>
                                <select name="category" id="category" class="modern-select {{ $errors->has('category') ? 'is-invalid' : '' }}" required>
                                    <option value="">Select category...</option>
                                    @foreach(['pedagogical'=>'Pedagogical','administrative'=>'Administrative','technical'=>'Technical / ICT','leadership'=>'Leadership','safety'=>'Safety & Compliance','curriculum'=>'Curriculum','pastoral'=>'Pastoral Care','general'=>'General'] as $k=>$v)
                                    <option value="{{ $k }}" {{ old('category', $item->category) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('category') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="target_audience">Target Audience <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-users modern-input-icon"></i>
                                <select name="target_audience" id="target_audience" class="modern-select {{ $errors->has('target_audience') ? 'is-invalid' : '' }}" required>
                                    @foreach(['all'=>'All Staff','teachers'=>'Teachers','admins'=>'Administrators','staff'=>'Support Staff','specific'=>'Specific'] as $k=>$v)
                                    <option value="{{ $k }}" {{ old('target_audience', $item->target_audience) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('target_audience') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="max_participants">Max Participants</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user-plus modern-input-icon"></i>
                                <input type="number" name="max_participants" id="max_participants" class="modern-input {{ $errors->has('max_participants') ? 'is-invalid' : '' }}" value="{{ old('max_participants', $item->max_participants) }}" min="0">
                            </div>
                            @error('max_participants') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Provider & Schedule --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-gold">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Provider & Schedule</h3>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="provider">Provider / Institution</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <input type="text" name="provider" id="provider" class="modern-input" value="{{ old('provider', $item->provider) }}">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="facilitator">Facilitator / Trainer</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-chalkboard-teacher modern-input-icon"></i>
                                <input type="text" name="facilitator" id="facilitator" class="modern-input" value="{{ old('facilitator', $item->facilitator) }}">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="venue">Venue / Location</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-map-marker-alt modern-input-icon"></i>
                                <input type="text" name="venue" id="venue" class="modern-input" value="{{ old('venue', $item->venue) }}">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="duration_hours">Duration (Hours)</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-hourglass-half modern-input-icon"></i>
                                <input type="number" name="duration_hours" id="duration_hours" class="modern-input" value="{{ old('duration_hours', $item->duration_hours) }}" min="0">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="start_date">Start Date</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-plus modern-input-icon"></i>
                                <input type="date" name="start_date" id="start_date" class="modern-input" value="{{ old('start_date', $item->start_date?->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="end_date">End Date</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-calendar-minus modern-input-icon"></i>
                                <input type="date" name="end_date" id="end_date" class="modern-input" value="{{ old('end_date', $item->end_date?->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Budget & Status --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Budget & Status</h3>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="cost">Cost / Budget</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-dollar-sign modern-input-icon"></i>
                                <input type="number" name="cost" id="cost" class="modern-input" value="{{ old('cost', $item->cost) }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="budget_source">Budget Source</label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-piggy-bank modern-input-icon"></i>
                                <input type="text" name="budget_source" id="budget_source" class="modern-input" value="{{ old('budget_source', $item->budget_source) }}">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="status">Status <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-flag modern-input-icon"></i>
                                <select name="status" id="status" class="modern-select" required>
                                    @foreach(['planned'=>'Planned','ongoing'=>'Ongoing','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$v)
                                    <option value="{{ $k }}" {{ old('status', $item->status) == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Objectives & Outcomes --}}
            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-purple">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Objectives & Outcomes</h3>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="objectives">Training Objectives</label>
                            <textarea name="objectives" id="objectives" class="modern-textarea" rows="3">{{ old('objectives', $item->objectives) }}</textarea>
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="outcomes">Expected Outcomes</label>
                            <textarea name="outcomes" id="outcomes" class="modern-textarea" rows="3">{{ old('outcomes', $item->outcomes) }}</textarea>
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="description">Description</label>
                            <textarea name="description" id="description" class="modern-textarea" rows="3">{{ old('description', $item->description) }}</textarea>
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="notes">Notes</label>
                            <textarea name="notes" id="notes" class="modern-textarea" rows="2">{{ old('notes', $item->notes) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-form-actions">
                <a href="{{ route('admin.trainings.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <i class="fas fa-save"></i> Update Training
                </button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.modern-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.modern-page-header-left { flex: 1; }
.modern-page-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; }
.modern-page-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }
.modern-page-header-right { display: flex; gap: 0.75rem; }
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
.modern-form-section { border-bottom: 1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom: none; }
.modern-form-section-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem 2rem 0.75rem; }
.modern-form-section-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }
.modern-form-section-icon-gold { background: #fff7ed; color: #f59e0b; }
.modern-form-section-icon-purple { background: #f5f3ff; color: #7c3aed; }
.modern-form-section-title { font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-form-section-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.15rem 0 0; }
.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }
.modern-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
.modern-form-span-2 { grid-column: span 2; }
.modern-form-group { display: flex; flex-direction: column; }
.modern-form-label { font-weight: 600; color: #374151; margin-bottom: 0.45rem; font-size: 0.88rem; }
.modern-required { color: #ef4444; font-weight: 700; }
.modern-input-wrapper { position: relative; }
.modern-input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.85rem; pointer-events: none; z-index: 1; }
.modern-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.7rem 0.9rem 0.7rem 2.5rem; font-size: 0.9rem; color: #1a1a2e; background: #fff; }
.modern-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.modern-textarea { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.7rem 0.9rem; font-size: 0.9rem; color: #1a1a2e; background: #fff; resize: vertical; font-family: inherit; }
.modern-textarea:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.modern-select { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.7rem 0.9rem 0.7rem 2.5rem; font-size: 0.9rem; color: #1a1a2e; background: #fff; appearance: none; cursor: pointer; }
.modern-select:focus { outline: none; border-color: #4361ee; }
.modern-form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.5rem 2rem; border-top: 1px solid #f0f0f0; background: #fafbfc; }
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.btn-modern-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color: #4361ee; color: #4361ee; }
.btn-modern-ghost { background: transparent; color: #6b7280; }
.btn-modern-ghost:hover { color: #1a1a2e; background: #f3f4f6; }
@media (max-width: 768px) {
    .modern-form-grid { grid-template-columns: 1fr; }
    .modern-form-span-2 { grid-column: span 1; }
    .modern-form-section-body { padding: 1rem 1.25rem; }
    .modern-form-actions { flex-direction: column; }
}
</style>
@endpush
@endsection
