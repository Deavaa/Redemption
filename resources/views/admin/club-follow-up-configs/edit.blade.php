@extends('layouts.admin')
@section('title', 'Edit Club Follow-up Configuration')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.club-follow-up-configs.index') }}">Club Follow-up Config</a></li>
                <li class="active">Edit</li>
            </ol></nav>
            <h1 class="modern-page-title">Edit Follow-up Configuration</h1>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-form-section">
            <div class="modern-form-section-body">
                <form method="POST" action="{{ route('admin.club-follow-up-configs.update', $clubFollowUpConfig->id) }}">
                    @csrf @method('PUT')
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Configuration Name *</label>
                            <input type="text" name="name" class="modern-input" style="padding-left:0.75rem" value="{{ $clubFollowUpConfig->name }}" required>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Follow-up Type *</label>
                            <select name="follow_up_type" class="modern-input modern-select" style="padding-left:0.75rem" required>
                                @foreach(\App\Models\ClubFollowUpConfig::followUpTypeOptions() as $key => $label)
                                <option value="{{ $key }}" {{ $clubFollowUpConfig->follow_up_type === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Club</label>
                            <select name="club_id" class="modern-input modern-select" style="padding-left:0.75rem">
                                <option value="">All Clubs</option>
                                @foreach($clubs as $club)
                                <option value="{{ $club->id }}" {{ $clubFollowUpConfig->club_id == $club->id ? 'selected' : '' }}>{{ $club->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Branch</label>
                            <select name="branch_id" class="modern-input modern-select" style="padding-left:0.75rem">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $clubFollowUpConfig->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Days After Activity *</label>
                            <input type="number" name="days_after_activity" class="modern-input" style="padding-left:0.75rem" value="{{ $clubFollowUpConfig->days_after_activity }}" min="1" required>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Active</label>
                            <select name="is_active" class="modern-input modern-select" style="padding-left:0.75rem">
                                <option value="1" {{ $clubFollowUpConfig->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$clubFollowUpConfig->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Auto Reminder</label>
                            <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.88rem;margin-top:0.5rem;">
                                <input type="checkbox" name="is_auto_reminder" value="1" {{ $clubFollowUpConfig->is_auto_reminder ? 'checked' : '' }}> Enable
                            </label>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Reminder Days Before</label>
                            <input type="number" name="reminder_days_before" class="modern-input" style="padding-left:0.75rem" value="{{ $clubFollowUpConfig->reminder_days_before }}" min="0">
                        </div>
                    </div>

                    <div class="modern-form-group" style="margin-top:1rem;">
                        <label class="modern-form-label">Description</label>
                        <textarea name="description" class="modern-input" style="padding-left:0.75rem;min-height:80px;">{{ $clubFollowUpConfig->description }}</textarea>
                    </div>

                    {{-- Checklist --}}
                    <div style="margin-top:1.5rem;">
                        <label class="modern-form-label">Checklist Items</label>
                        <div id="checklistContainer">
                            @foreach($clubFollowUpConfig->checklist_items ?? [] as $item)
                            <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;">
                                <input type="text" name="checklist_items[]" class="modern-input" style="padding-left:0.75rem;flex:1;" value="{{ $item }}">
                                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;"><i class="fas fa-times"></i></button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addChecklistItem()" class="btn-modern btn-modern-outline" style="font-size:0.82rem;padding:0.4rem 0.8rem;margin-top:0.5rem;">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>

                    {{-- Rating Criteria --}}
                    <div style="margin-top:1.5rem;">
                        <label class="modern-form-label">Rating Criteria</label>
                        <div id="criteriaContainer">
                            @foreach($clubFollowUpConfig->rating_criteria ?? [] as $key => $label)
                            <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;">
                                <input type="text" name="rating_criteria[{{ $key }}]" class="modern-input" style="padding-left:0.75rem;flex:1;" value="{{ $label }}">
                                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;"><i class="fas fa-times"></i></button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addCriteriaItem()" class="btn-modern btn-modern-outline" style="font-size:0.82rem;padding:0.4rem 0.8rem;margin-top:0.5rem;">
                            <i class="fas fa-plus"></i> Add Criteria
                        </button>
                    </div>

                    <div style="margin-top:1.5rem;">
                        <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-save"></i> Update</button>
                        <a href="{{ route('admin.club-follow-up-configs.index') }}" class="btn-modern btn-modern-outline" style="margin-left:0.5rem;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function addChecklistItem() {
        const container = document.getElementById('checklistContainer');
        const div = document.createElement('div');
        div.style.cssText = 'display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;';
        div.innerHTML = `<input type="text" name="checklist_items[]" class="modern-input" style="padding-left:0.75rem;flex:1;" placeholder="Enter checklist item...">
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;"><i class="fas fa-times"></i></button>`;
        container.appendChild(div);
    }
    function addCriteriaItem() {
        const container = document.getElementById('criteriaContainer');
        const key = 'custom_' + Date.now();
        const div = document.createElement('div');
        div.style.cssText = 'display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;';
        div.innerHTML = `<input type="text" name="rating_criteria[${key}]" class="modern-input" style="padding-left:0.75rem;flex:1;" placeholder="Enter criteria...">
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;"><i class="fas fa-times"></i></button>`;
        container.appendChild(div);
    }
</script>
@endpush
@endsection
