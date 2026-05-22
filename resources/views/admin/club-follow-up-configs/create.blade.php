@extends('layouts.admin')
@section('title', 'Create Club Follow-up Configuration')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li><a href="{{ route('admin.club-follow-up-configs.index') }}">Club Follow-up Config</a></li>
                <li class="active">Create</li>
            </ol></nav>
            <h1 class="modern-page-title">Create Follow-up Configuration</h1>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-form-section">
            <div class="modern-form-section-body">
                <form method="POST" action="{{ route('admin.club-follow-up-configs.store') }}">
                    @csrf
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label">Configuration Name *</label>
                            <input type="text" name="name" class="modern-input" style="padding-left:0.75rem" required placeholder="e.g., Weekly Activity Check-in">
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Follow-up Type *</label>
                            <select name="follow_up_type" class="modern-input modern-select" style="padding-left:0.75rem" required>
                                @foreach(\App\Models\ClubFollowUpConfig::followUpTypeOptions() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Club (leave blank for all clubs)</label>
                            <select name="club_id" class="modern-input modern-select" style="padding-left:0.75rem">
                                <option value="">All Clubs</option>
                                @foreach($clubs as $club)
                                <option value="{{ $club->id }}">{{ $club->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Branch (leave blank for all branches)</label>
                            <select name="branch_id" class="modern-input modern-select" style="padding-left:0.75rem">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Days After Activity *</label>
                            <input type="number" name="days_after_activity" class="modern-input" style="padding-left:0.75rem" value="7" min="1" required>
                            <span class="modern-form-hint">How many days after the activity should follow-up occur?</span>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Auto Reminder</label>
                            <div style="display:flex;align-items:center;gap:1rem;margin-top:0.5rem;">
                                <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.88rem;">
                                    <input type="checkbox" name="is_auto_reminder" value="1" checked> Enable automatic reminders
                                </label>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label">Reminder Days Before Deadline</label>
                            <input type="number" name="reminder_days_before" class="modern-input" style="padding-left:0.75rem" value="1" min="0">
                        </div>
                    </div>

                    <div class="modern-form-group" style="margin-top:1rem;">
                        <label class="modern-form-label">Description</label>
                        <textarea name="description" class="modern-input" style="padding-left:0.75rem;min-height:80px;" placeholder="Describe when and why this follow-up should be performed..."></textarea>
                    </div>

                    {{-- Checklist Items --}}
                    <div style="margin-top:1.5rem;">
                        <label class="modern-form-label">Checklist Items</label>
                        <div id="checklistContainer">
                            @foreach($defaultChecklist as $index => $item)
                            <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;">
                                <input type="text" name="checklist_items[]" class="modern-input" style="padding-left:0.75rem;flex:1;" value="{{ $item }}">
                                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;"><i class="fas fa-times"></i></button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addChecklistItem()" class="btn-modern btn-modern-outline" style="font-size:0.82rem;padding:0.4rem 0.8rem;margin-top:0.5rem;">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>

                    {{-- Rating Criteria --}}
                    <div style="margin-top:1.5rem;">
                        <label class="modern-form-label">Rating Criteria (1-5 scale)</label>
                        <div id="criteriaContainer">
                            @foreach($defaultCriteria as $key => $label)
                            <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;">
                                <input type="text" name="rating_criteria[{{ $key }}]" class="modern-input" style="padding-left:0.75rem;flex:1;" value="{{ $label }}">
                                <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;"><i class="fas fa-times"></i></button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addCriteriaItem()" class="btn-modern btn-modern-outline" style="font-size:0.82rem;padding:0.4rem 0.8rem;margin-top:0.5rem;">
                            <i class="fas fa-plus"></i> Add Criteria
                        </button>
                    </div>

                    <div style="margin-top:1.5rem;">
                        <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-save"></i> Save Configuration</button>
                        <a href="{{ route('admin.club-follow-up-configs.index') }}" class="btn-modern btn-modern-outline" style="margin-left:0.5rem;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let checklistIndex = {{ count($defaultChecklist) }};
    let criteriaIndex = {{ count($defaultCriteria) }};

    function addChecklistItem() {
        const container = document.getElementById('checklistContainer');
        const div = document.createElement('div');
        div.style.cssText = 'display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;';
        div.innerHTML = `<input type="text" name="checklist_items[]" class="modern-input" style="padding-left:0.75rem;flex:1;" placeholder="Enter checklist item...">
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;"><i class="fas fa-times"></i></button>`;
        container.appendChild(div);
    }

    function addCriteriaItem() {
        const container = document.getElementById('criteriaContainer');
        const key = 'custom_' + Date.now();
        const div = document.createElement('div');
        div.style.cssText = 'display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;';
        div.innerHTML = `<input type="text" name="rating_criteria[${key}]" class="modern-input" style="padding-left:0.75rem;flex:1;" placeholder="Enter rating criteria...">
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;"><i class="fas fa-times"></i></button>`;
        container.appendChild(div);
    }
</script>
@endpush
@endsection
