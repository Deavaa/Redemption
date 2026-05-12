@extends('layouts.admin')
@section('title', 'Edit Exam')
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h4 class="mb-1 fw-bold">Edit Exam</h4></div><a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a></div>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('admin.exams.update', $exam) }}">@csrf @method('PUT')
            <div class="row g-3 mb-3">
                <div class="col-md-6"><label class="form-label fw-semibold">Exam Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="{{ old('name', $exam->name) }}" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Type</label><input type="text" name="type" class="form-control" value="{{ old('type', $exam->type) }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label><select name="academic_year_id" id="examAy" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}" {{ $exam->academic_year_id == $ay->id ? 'selected' : '' }}>{{ $ay->name }}</option>@endforeach</select></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-4"><label class="form-label fw-semibold">Term <span class="text-danger">*</span></label><select name="term_id" id="examTerm" class="form-select" required></select></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Total Marks</label><input type="number" name="total_marks" class="form-control" value="{{ old('total_marks', $exam->total_marks) }}" min="0"></div>
                <div class="col-md-4"><label class="form-label fw-semibold">Passing Marks</label><input type="number" name="passing_marks" class="form-control" value="{{ old('passing_marks', $exam->passing_marks) }}" min="0"></div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-3"><label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label><input type="date" name="start_date" class="form-control" value="{{ old('start_date', $exam->start_date ? $exam->start_date->format('Y-m-d') : '') }}" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label><input type="date" name="end_date" class="form-control" value="{{ old('end_date', $exam->end_date ? $exam->end_date->format('Y-m-d') : '') }}" required></div>
                <div class="col-md-3"><label class="form-label fw-semibold">Start Time</label><input type="time" name="start_time" class="form-control" value="{{ old('start_time', $exam->start_time) }}"></div>
                <div class="col-md-3"><label class="form-label fw-semibold">End Time</label><input type="time" name="end_time" class="form-control" value="{{ old('end_time', $exam->end_time) }}"></div>
            </div>
            <div class="row g-3 mb-4"><div class="col-12"><label class="form-label fw-semibold">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $exam->description) }}</textarea></div></div>
            <div class="d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update</button><a href="{{ route('admin.exams.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection
@push('scripts')
<script>
var allTerms={{ $allTerms->toJson() }};var examAySelect=document.getElementById('examAy'),examTermSelect=document.getElementById('examTerm');var currentTermId={{ $exam->term_id }};
function populateTerms(ayId){examTermSelect.innerHTML='';if(!ayId){examTermSelect.innerHTML='<option value="">-- Select AY first --</option>';return;}
var filtered=allTerms.filter(function(t){return t.academic_year_id==ayId;});
if(filtered.length===0){examTermSelect.innerHTML='<option value="">-- No terms --</option>';}
else{examTermSelect.innerHTML='<option value="">-- Select Term --</option>';filtered.forEach(function(t){var opt=document.createElement('option');opt.value=t.id;opt.textContent=t.name;if(t.id==currentTermId)opt.selected=true;examTermSelect.appendChild(opt);});}}
examAySelect.addEventListener('change',function(){populateTerms(this.value);});
populateTerms(examAySelect.value);
</script>
@endpush