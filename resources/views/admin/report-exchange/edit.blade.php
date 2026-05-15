@extends('layouts.admin')
@section('title', 'Edit Report Document')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1"><i class="fas fa-edit me-2"></i>Edit Report Document</h4>
            <p class="text-muted mb-0">{{ $report_exchange->title }}</p>
        </div>
        <a href="{{ route('admin.report-exchange.show', $report_exchange->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.report-exchange.update', $report_exchange->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Document Details</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $report_exchange->title) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $report_exchange->description) }}</textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Document Type <span class="text-danger">*</span></label>
                            <select name="document_type" class="form-select" required>
                                @foreach(['report'=>'General Report','memo'=>'Memo','proposal'=>'Proposal','financial'=>'Financial Report','academic'=>'Academic Report','inspection'=>'Inspection Report'] as $val => $label)
                                <option value="{{ $val }}" {{ old('document_type', $report_exchange->document_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                @foreach(['low'=>'Low','normal'=>'Normal','high'=>'High','urgent'=>'Urgent'] as $val => $label)
                                <option value="{{ $val }}" {{ old('priority', $report_exchange->priority) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Replace File</label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.png,.zip">
                            @if($report_exchange->file_name)
                            <small class="text-muted">Current: {{ $report_exchange->file_name }}</small>
                            @endif
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
                                <option value="{{ $branch->id }}" {{ old('from_branch_id', $report_exchange->from_branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">To Branch</label>
                            <select name="to_branch_id" class="form-select">
                                <option value="">Headquarters</option>
                                @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('to_branch_id', $report_exchange->to_branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Academic Year</label>
                            <select name="academic_year_id" class="form-select">
                                <option value="">Select Year</option>
                                @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id', $report_exchange->academic_year_id) == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Term</label>
                            <select name="term_id" class="form-select">
                                <option value="">Select Term</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-users me-2"></i>Recipients</h6></div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($users as $user)
                        @if($user->id !== auth()->id())
                        <div class="col-md-4 col-lg-3">
                            <div class="form-check">
                                <input type="checkbox" name="recipients[]" value="{{ $user->id }}" id="user_{{ $user->id }}" class="form-check-input"
                                    {{ in_array($user->id, $selectedRecipients) ? 'checked' : '' }}>
                                <label for="user_{{ $user->id }}" class="form-check-label small">
                                    {{ $user->name }}
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
                <a href="{{ route('admin.report-exchange.show', $report_exchange->id) }}" class="btn btn-light">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
