@extends('layouts.admin')
@section('title', 'Assign Subjects to Class')
@push('styles')
<style>
.assignment-type-card{cursor:pointer;transition:all .2s;border:2px solid transparent}
.assignment-type-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.1)}
.assignment-type-card.active-core{border-color:#0d6efd!important;background:rgba(13,110,253,.08)}
.assignment-type-card.active-elective{border-color:#fd7e14!important;background:rgba(253,126,20,.08)}
.subject-check-item{padding:10px 12px;border:1px solid #dee2e6;border-radius:8px;cursor:pointer;transition:all .15s}
.subject-check-item:hover{background:#f0f8ff;border-color:#0d6efd}
.subject-check-item.selected{background:#e7f1ff;border-color:#0d6efd}
.subject-check-item.disabled{opacity:.5;cursor:not-allowed;background:#f8f9fa}
.section-check-item{padding:6px 12px;border:1px solid #dee2e6;border-radius:8px;cursor:pointer}
.section-check-item:hover{background:#fff3e0;border-color:#fd7e14}
.subject-type-badge{font-size:.7rem;padding:2px 6px;border-radius:6px;font-weight:600}
.subject-type-compulsory{background:#dcfce7;color:#15803d}
.subject-type-elective{background:#ffedd5;color:#c2410c}
.subject-type-optional{background:#e0e7ff;color:#4338ca}
.already-assigned{font-size:.7rem;color:#16a34a;font-weight:600}
</style>
@endpush
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Assign Subjects to Class</h4><p class="text-muted mb-0">Pick a class, then choose multiple subjects with checkboxes.</p></div>
        <a href="{{ route('admin.subject-assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>
    <form method="POST" action="{{ route('admin.subject-assignments.store') }}">@csrf
    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Step 1: Academic Year --}}
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-1-circle-fill"></i></span> Academic Year</div><div class="card-body">
                <select name="academic_year_id" id="academicYear" class="form-select" required><option value="">-- Select Academic Year --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach</select>
            </div></div>

            {{-- Step 2: Class --}}
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-2-circle-fill"></i></span> Class</div><div class="card-body">
                <select name="class_id" id="classSelect" class="form-select" required><option value="">-- Select Class --</option>@foreach($classes as $class)<option value="{{ $class->id }}" data-has-sections="{{ $class->sections->count() }}">{{ $class->name }} ({{ $class->branch->name }})</option>@endforeach</select>
                <div class="form-text"><i class="bi bi-info-circle me-1"></i>Subjects will be assigned to this single class. Sections load automatically.</div>
            </div></div>

            {{-- Step 3: Assignment Type --}}
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-3-circle-fill"></i></span> Assignment Type</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="assignment-type-card card text-center p-3 active-core" id="typeCore" data-type="core"><i class="bi bi-star-fill text-primary fs-2"></i><div class="fw-semibold mt-1">Core Subject</div><div class="text-muted small mt-1">Applies to ALL sections of the class</div></div></div>
                    <div class="col-md-6"><div class="assignment-type-card card text-center p-3" id="typeElective" data-type="elective"><i class="bi bi-star-half text-warning fs-2"></i><div class="fw-semibold mt-1">Elective / Other</div><div class="text-muted small mt-1">Applies to specific sections only</div></div></div>
                </div>
                <input type="hidden" name="assignment_type" id="assignmentType" value="core">
            </div></div>

            {{-- Step 4: Subjects (multi-select checkboxes) --}}
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2 d-flex align-items-center">
                <span class="text-primary me-2"><i class="bi bi-4-circle-fill"></i></span> Select Subjects
                <div class="ms-auto d-flex gap-2">
                    <input type="text" id="subjectSearch" class="form-control form-control-sm" placeholder="Search subjects..." style="width:200px">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="toggleAllSubjects"><i class="bi bi-check2-all me-1"></i>Select All</button>
                </div>
            </div><div class="card-body">
                <div id="subjectListContainer" style="max-height:400px;overflow-y:auto;border:1px solid #dee2e6;border-radius:8px;padding:8px;">
                    <p class="text-muted mb-0 text-center py-3" id="noClassWarning"><i class="bi bi-arrow-up-circle me-1"></i>Please select a class above first.</p>
                    <div class="row g-2" id="subjectList" style="display:none;">@foreach($subjects as $subject)
                        <div class="col-md-6 col-sm-12">
                            <div class="subject-check-item" data-subject-id="{{ $subject->id }}" data-subject-name="{{ strtolower($subject->name) }}" data-subject-code="{{ strtolower($subject->code ?? '') }}">
                                <div class="d-flex align-items-start gap-2">
                                    <input type="checkbox" class="form-check-input subject-check mt-1" name="subject_ids[]" value="{{ $subject->id }}" id="subj_{{ $subject->id }}">
                                    <label for="subj_{{ $subject->id }}" class="flex-grow-1 user-select-none cursor-pointer">
                                        <div class="fw-medium">{{ $subject->name }}@if($subject->code) <span class="text-muted small">({{ $subject->code }})</span>@endif</div>
                                        <div class="mt-1">
                                            <span class="subject-type-badge subject-type-{{ $subject->type ?? 'compulsory' }}">{{ ucfirst($subject->type ?? 'compulsory') }}</span>
                                            @if($subject->priority) <span class="text-muted small ms-1">P:{{ $subject->priority }}</span>@endif
                                            @if(!$subject->is_active) <span class="badge bg-danger ms-1">Inactive</span>@endif
                                        </div>
                                        <div class="already-assigned mt-1" style="display:none;" id="already_{{ $subject->id }}"><i class="bi bi-check-circle-fill me-1"></i>Already assigned</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach</div>
                </div>
                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>Only subjects not already assigned to this class/year will be created. Duplicates are skipped automatically.</div>
            </div></div>

            {{-- Step 5: Sections (only for Elective) --}}
            <div class="card mb-3" id="sectionCard" style="display:none;"><div class="card-header bg-light fw-semibold py-2"><span class="text-warning me-2"><i class="bi bi-5-circle-fill"></i></span> Select Sections <span class="badge bg-warning text-dark ms-2">Elective Only</span> <button type="button" class="btn btn-sm btn-outline-warning float-end" id="toggleAllSections"><i class="bi bi-check2-all me-1"></i>Select All</button></div><div class="card-body">
                <div id="sectionList"><p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i>Select a class above first.</p></div>
            </div></div>

            {{-- Step 6: Teacher (optional, applied to all selected subjects) --}}
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-secondary me-2"><i class="bi bi-person-check-fill"></i></span> Teacher <span class="text-muted small fw-normal">(optional, applied to all selected subjects)</span></div><div class="card-body">
                <select name="teacher_id" class="form-select"><option value="">-- No Teacher (assign later) --</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>@endforeach</select>
            </div></div>
        </div>

        {{-- Summary sidebar --}}
        <div class="col-lg-4">
            <div class="card border-primary mb-3 sticky-top" style="top:80px;"><div class="card-header bg-primary text-white py-2"><i class="bi bi-clipboard-check me-1"></i> Summary</div><div class="card-body" id="summaryBody"><div class="text-center text-muted py-4"><i class="bi bi-arrow-left-circle fs-1"></i><p class="mt-2">Select a class and subjects.</p></div></div></div>
            <div class="d-grid gap-2"><button type="submit" class="btn btn-primary btn-lg" id="submitBtn"><i class="bi bi-check-circle me-1"></i> Save Assignment(s)</button><a href="{{ route('admin.subject-assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i> Cancel</a></div>
        </div>
    </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
const API_BASE='{{ url("/admin/subject-assignments/api") }}';
let currentType='core';
let selectedClass=null;
let allSectionsCache={};
let existingAssignments=new Set(); // subject_ids already assigned for this class+year

// --- Step 3: Type toggle ---
document.getElementById('typeCore').addEventListener('click',function(){
    currentType='core';
    document.getElementById('assignmentType').value='core';
    this.classList.add('active-core');
    document.getElementById('typeElective').classList.remove('active-elective');
    document.getElementById('sectionCard').style.display='none';
    updateSummary();
});
document.getElementById('typeElective').addEventListener('click',function(){
    currentType='elective';
    document.getElementById('assignmentType').value='elective';
    this.classList.add('active-elective');
    document.getElementById('typeCore').classList.remove('active-core');
    document.getElementById('sectionCard').style.display='block';
    if(selectedClass) loadSectionsForClass(selectedClass);
    updateSummary();
});

// --- Step 2: Class change ---
document.getElementById('classSelect').addEventListener('change',function(){
    selectedClass=this.value||null;
    if(selectedClass){
        document.getElementById('noClassWarning').style.display='none';
        document.getElementById('subjectList').style.display='flex';
        loadExistingAssignments();
        if(currentType==='elective') loadSectionsForClass(selectedClass);
    }else{
        document.getElementById('noClassWarning').style.display='block';
        document.getElementById('subjectList').style.display='none';
    }
    updateSummary();
});

// --- Step 4: Subject checkbox handling ---
document.querySelectorAll('.subject-check-item').forEach(item=>{
    item.addEventListener('click',function(e){
        if(e.target.tagName==='INPUT')return;
        const cb=this.querySelector('.subject-check');
        cb.checked=!cb.checked;
        cb.dispatchEvent(new Event('change'));
    });
});
document.querySelectorAll('.subject-check').forEach(cb=>{
    cb.addEventListener('change',function(){
        const item=this.closest('.subject-check-item');
        if(this.checked) item.classList.add('selected');
        else item.classList.remove('selected');
        updateSummary();
    });
});

// Subject search filter
document.getElementById('subjectSearch').addEventListener('input',function(){
    const q=this.value.toLowerCase().trim();
    document.querySelectorAll('.subject-check-item').forEach(item=>{
        const name=item.dataset.subjectName||'';
        const code=item.dataset.subjectCode||'';
        const match=!q||name.includes(q)||code.includes(q);
        item.style.display=match?'':'none';
    });
});

// Select all subjects
document.getElementById('toggleAllSubjects').addEventListener('click',function(){
    const visible=Array.from(document.querySelectorAll('.subject-check-item')).filter(i=>i.style.display!=='none');
    const allChecked=visible.every(i=>i.querySelector('.subject-check').checked);
    visible.forEach(item=>{
        const cb=item.querySelector('.subject-check');
        cb.checked=!allChecked;
        item.classList.toggle('selected',cb.checked);
    });
    this.innerHTML=allChecked?'<i class="bi bi-check2-all me-1"></i>Select All':'<i class="bi bi-x-circle me-1"></i>Deselect All';
    updateSummary();
});

// --- Step 5: Sections (Elective) ---
document.getElementById('toggleAllSections').addEventListener('click',function(){
    const all=document.querySelectorAll('.section-check');
    if(all.length===0)return;
    const allChecked=Array.from(all).every(cb=>cb.checked);
    all.forEach(cb=>{cb.checked=!allChecked;});
    this.innerHTML=allChecked?'<i class="bi bi-check2-all me-1"></i>Select All':'<i class="bi bi-x-circle me-1"></i>Deselect All';
    updateSummary();
});

function loadSectionsForClass(classId){
    if(allSectionsCache[classId]){renderSections();return;}
    fetch(API_BASE+'/sections?class_id='+classId)
        .then(r=>r.json())
        .then(s=>{allSectionsCache[classId]=s;renderSections();})
        .catch(()=>{allSectionsCache[classId]=[];renderSections();});
}

function renderSections(){
    const container=document.getElementById('sectionList');
    if(!selectedClass){container.innerHTML='<p class="text-muted mb-0">Select a class above first.</p>';return;}
    const className=document.querySelector('#classSelect option[value="'+selectedClass+'"]')?.textContent||'Class';
    const secs=allSectionsCache[selectedClass]||[];
    if(secs.length===0){
        container.innerHTML='<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-triangle me-1"></i>No sections defined for '+className+'. Add sections first, or use Core type.</div>';
        return;
    }
    let html='<div class="fw-semibold mb-2 text-primary"><i class="bi bi-collection me-1"></i>'+className+'</div><div class="d-flex flex-wrap gap-2">';
    secs.forEach(sec=>{
        html+='<div class="section-check-item"><input type="checkbox" class="form-check-input section-check" name="section_ids[]" value="'+sec.id+'" id="sec_'+sec.id+'" checked><label for="sec_'+sec.id+'" class="ms-1 user-select-none cursor-pointer small">'+sec.name+'</label></div>';
    });
    html+='</div>';
    container.innerHTML=html;
    document.querySelectorAll('.section-check').forEach(cb=>cb.addEventListener('change',updateSummary));
}

// Load existing assignments for this class+year to mark already-assigned subjects
function loadExistingAssignments(){
    const ayId=document.getElementById('academicYear').value;
    if(!ayId||!selectedClass){
        document.querySelectorAll('.already-assigned').forEach(el=>el.style.display='none');
        document.querySelectorAll('.subject-check-item').forEach(i=>i.classList.remove('disabled'));
        existingAssignments.clear();
        return;
    }
    // Use existing assignments passed from controller (window.existingData) if available,
    // otherwise fetch via API
    fetch(API_BASE+'/existing?class_id='+selectedClass+'&academic_year_id='+ayId)
        .then(r=>r.json())
        .then(data=>{
            existingAssignments=new Set(data.existing_subject_ids||[]);
            document.querySelectorAll('.subject-check-item').forEach(item=>{
                const sid=item.dataset.subjectId;
                const badge=document.getElementById('already_'+sid);
                if(existingAssignments.has(parseInt(sid))){
                    if(badge) badge.style.display='block';
                    item.classList.add('disabled');
                }else{
                    if(badge) badge.style.display='none';
                    item.classList.remove('disabled');
                }
            });
        })
        .catch(()=>{existingAssignments.clear();});
}

// Also reload existing assignments when academic year changes
document.getElementById('academicYear').addEventListener('change',function(){
    if(selectedClass) loadExistingAssignments();
    updateSummary();
});

// --- Summary panel ---
function updateSummary(){
    const ay=document.getElementById('academicYear');
    const cls=document.getElementById('classSelect');
    const ayName=ay.options[ay.selectedIndex]?.text||'Not selected';
    const clsName=cls.options[cls.selectedIndex]?.text||'Not selected';
    const selectedSubjects=Array.from(document.querySelectorAll('.subject-check:checked')).map(cb=>cb.value);
    const newCount=selectedSubjects.filter(id=>!existingAssignments.has(parseInt(id))).length;
    const skipCount=selectedSubjects.length-newCount;
    const sectionCount=document.querySelectorAll('.section-check:checked').length;
    const sb=document.getElementById('summaryBody');
    if(selectedSubjects.length===0){
        sb.innerHTML='<div class="text-center text-muted py-4"><i class="bi bi-clipboard fs-1"></i><p class="mt-2">Select subjects to assign.</p></div>';
        return;
    }
    let si=currentType==='core'?'<span class="text-primary">All Sections</span>':'<span class="text-warning">'+sectionCount+' section(s)</span>';
    let totalRecords=currentType==='core'?newCount:newCount*sectionCount;
    let html='<table class="table table-sm mb-0">';
    html+='<tr><td class="text-muted">Academic Year</td><td class="fw-semibold">'+ayName+'</td></tr>';
    html+='<tr><td class="text-muted">Class</td><td class="fw-semibold">'+clsName+'</td></tr>';
    html+='<tr><td class="text-muted">Type</td><td><span class="badge '+(currentType==='core'?'bg-primary':'bg-warning text-dark')+'">'+(currentType==='core'?'Core':'Elective')+'</span></td></tr>';
    html+='<tr><td class="text-muted">Subjects Selected</td><td class="fw-semibold">'+selectedSubjects.length+'</td></tr>';
    if(skipCount>0) html+='<tr><td class="text-muted">Already Assigned (skip)</td><td class="text-success">'+skipCount+'</td></tr>';
    html+='<tr><td class="text-muted">Sections</td><td>'+si+'</td></tr>';
    html+='<tr class="border-top"><td class="text-muted fw-semibold">New Records</td><td class="fw-bold fs-5 text-primary">'+totalRecords+'</td></tr>';
    html+='</table>';
    if(currentType==='elective'&&sectionCount===0){
        html+='<div class="alert alert-warning mt-2 mb-0 py-2"><i class="bi bi-exclamation-triangle me-1"></i>Select at least one section.</div>';
    }
    if(newCount===0){
        html+='<div class="alert alert-info mt-2 mb-0 py-2"><i class="bi bi-info-circle me-1"></i>All selected subjects are already assigned.</div>';
    }
    sb.innerHTML=html;
}

updateSummary();
</script>
@endpush
