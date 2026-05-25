@extends('layouts.admin')
@section('title', 'Create Report Document')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1"><i class="fas fa-plus me-2"></i>Create Report Document</h4>
            <p class="text-muted mb-0">Submit reports and documents for review and exchange</p>
        </div>
        <a href="{{ route('admin.report-exchange.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.report-exchange.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Document Details</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Enter document title">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Enter document description or summary">{{ old('description') }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Document Type <span class="text-danger">*</span></label>
                            <select name="document_type" class="form-select" required>
                                <option value="report" {{ old('document_type') === 'report' ? 'selected' : '' }}>General Report</option>
                                <option value="memo" {{ old('document_type') === 'memo' ? 'selected' : '' }}>Memo</option>
                                <option value="proposal" {{ old('document_type') === 'proposal' ? 'selected' : '' }}>Proposal</option>
                                <option value="financial" {{ old('document_type') === 'financial' ? 'selected' : '' }}>Financial Report</option>
                                <option value="academic" {{ old('document_type') === 'academic' ? 'selected' : '' }}>Academic Report</option>
                                <option value="inspection" {{ old('document_type') === 'inspection' ? 'selected' : '' }}>Inspection Report</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Attach File</label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.png,.zip">
                            <small class="text-muted">Max 25MB. PDF, Word, Excel, PowerPoint, Images, ZIP</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-route me-2"></i>Routing Information</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">From Branch</label>
                            <select name="from_branch_id" class="form-select">
                                <option value="">Headquarters</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ (old('from_branch_id') ?? $defaultFromBranch) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">To Branch</label>
                            <select name="to_branch_id" class="form-select">
                                <option value="">Headquarters</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Academic Year</label>
                            <select name="academic_year_id" class="form-select" id="academicYearSelect">
                                <option value="">Select Year</option>
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Term</label>
                            <select name="term_id" class="form-select" id="termSelect">
                                <option value="">Select Term</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Report Period</h6></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Reports are grouped by time period: monthly, quarterly, half-year, or yearly.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Report Grouping <span class="text-danger">*</span></label>
                            <select name="report_grouping" class="form-select" id="reportGroupingSelect" required>
                                @foreach($reportGroupings as $key => $label)
                                <option value="{{ $key }}" {{ old('report_grouping') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Report Period <span class="text-danger">*</span></label>
                            <select name="report_period" class="form-select" id="reportPeriodSelect" required>
                                @foreach($periodOptions as $val => $label)
                                <option value="{{ $val }}" {{ old('report_period') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-users me-2"></i>Recipients</h6></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Select users who should receive and review this document. @if(auth()->user()->role === 'teacher') Reports are automatically routed to your branch principal. @elseif(auth()->user()->isBranchPrincipal()) Reports are automatically routed to the General Manager. @endif</p>
                    <div class="row g-2">
                        @foreach($recipientCandidates as $rcptUser)
                        @if($rcptUser->id !== auth()->id())
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check">
                                <input type="checkbox" name="recipients[]" value="{{ $rcptUser->id }}" id="user_{{ $rcptUser->id }}" class="form-check-input"
                                    {{ in_array($rcptUser->id, old('recipients', [])) ? 'checked' : '' }}>
                                <label for="user_{{ $rcptUser->id }}" class="form-check-label small">
                                    {{ $rcptUser->name }}<br><span class="text-muted">{{ ucfirst($rcptUser->role) }}</span>
                                </label>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="action" value="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-1"></i> Submit Report
                </button>
                <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">
                    <i class="fas fa-save me-1"></i> Save as Draft
                </button>
                <a href="{{ route('admin.report-exchange.index') }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('academicYearSelect')?.addEventListener('change', function() {
    const yearId = this.value;
    const termSelect = document.getElementById('termSelect');
    termSelect.innerHTML = '<option value="">Loading...</option>';
    if (!yearId) { termSelect.innerHTML = '<option value="">Select Term</option>'; return; }
    fetch('{{ route('admin.report-exchange.terms') }}?academic_year_id=' + yearId)
        .then(r => r.json())
        .then(terms => {
            termSelect.innerHTML = '<option value="">Select Term</option>';
            terms.forEach(t => {
                termSelect.innerHTML += `<option value="${t.id}">${t.name}</option>`;
            });
        })
        .catch(() => { termSelect.innerHTML = '<option value="">Error loading</option>'; });
});

// Dynamic period options based on grouping type
document.getElementById('reportGroupingSelect')?.addEventListener('change', function() {
    const grouping = this.value;
    const periodSelect = document.getElementById('reportPeriodSelect');
    periodSelect.innerHTML = '<option value="">Loading...</option>';

    fetch('{{ route('admin.report-exchange.period-options') }}?grouping=' + grouping)
        .then(r => r.json())
        .then(options => {
            periodSelect.innerHTML = '';
            Object.entries(options).forEach(([val, label]) => {
                periodSelect.innerHTML += `<option value="${val}">${label}</option>`;
            });
        })
        .catch(() => { periodSelect.innerHTML = '<option value="">Error loading</option>'; });
});
</script>
@endsection
