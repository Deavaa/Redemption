@extends('layouts.admin')
@section('title', 'Assign Subject')
@push('styles')
<style>.assignment-type-card{cursor:pointer;transition:all .2s;border:2px solid transparent}.assignment-type-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.1)}.assignment-type-card.active-core{border-color:#0d6efd!important;background:rgba(13,110,253,.08)}.assignment-type-card.active-elective{border-color:#fd7e14!important;background:rgba(253,126,20,.08)}.class-check-item{padding:8px 12px;border:1px solid #dee2e6;border-radius:8px;cursor:pointer}.class-check-item:hover{background:#f0f8ff;border-color:#0d6efd}.class-check-item.selected{background:#e7f1ff;border-color:#0d6efd}.section-check-item{padding:6px 12px;border:1px solid #dee2e6;border-radius:8px;cursor:pointer}.section-check-item:hover{background:#fff3e0;border-color:#fd7e14}</style>
@endpush
@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1 fw-bold">Assign Subject to Classes</h4><p class="text-muted mb-0">Core = all sections. Elective = specific sections.</p></div>
        <a href="{{ route('admin.subject-assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>
    <form method="POST" action="{{ route('admin.subject-assignments.store') }}">@csrf
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-1-circle-fill"></i></span> Academic Year</div><div class="card-body">
                <select name="academic_year_id" id="academicYear" class="form-select" required><option value="">-- Select --</option>@foreach($academicYears as $ay)<option value="{{ $ay->id }}">{{ $ay->name }}</option>@endforeach</select>
            </div></div>
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-2-circle-fill"></i></span> Subject</div><div class="card-body">
                <select name="subject_id" id="subjectSelect" class="form-select" required><option value="">-- Select --</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" data-type="{{ strtolower($subject->type ?? '') }}">{{ $subject->name }} @if($subject->code)({{ $subject->code }})@endif @if($subject->type)- {{ $subject->type }}@endif</option>@endforeach</select>
            </div></div>
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-3-circle-fill"></i></span> Assignment Type</div><div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="assignment-type-card card text-center p-3 active-core" id="typeCore" data-type="core"><i class="bi bi-star-fill text-primary fs-2"></i><div class="fw-semibold mt-1">Core Subject</div><div class="text-muted small mt-1">All sections of selected classes</div></div></div>
                    <div class="col-md-6"><div class="assignment-type-card card text-center p-3" id="typeElective" data-type="elective"><i class="bi bi-star-half text-warning fs-2"></i><div class="fw-semibold mt-1">Elective / Other</div><div class="text-muted small mt-1">Specific sections only</div></div></div>
                </div>
                <input type="hidden" name="assignment_type" id="assignmentType" value="core">
            </div></div>
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-primary me-2"><i class="bi bi-4-circle-fill"></i></span> Select Classes <button type="button" class="btn btn-sm btn-outline-primary float-end" id="toggleAllClasses"><i class="bi bi-check2-all me-1"></i>Select All</button></div><div class="card-body">
                <div class="row g-2" id="classList">@foreach($classes as $class)<div class="col-md-4 col-sm-6"><div class="class-check-item" data-class-id="{{ $class->id }}"><input type="checkbox" class="form-check-input class-check" name="class_ids[]" value="{{ $class->id }}" id="class_{{ $class->id }}"><label for="class_{{ $class->id }}" class="ms-2 user-select-none cursor-pointer"><span class="fw-medium">{{ $class->name }} ({{ $class->branch->name }})</span></label></div></div>@endforeach</div>
            </div></div>
            <div class="card mb-3" id="sectionCard" style="display:none;"><div class="card-header bg-light fw-semibold py-2"><span class="text-warning me-2"><i class="bi bi-5-circle-fill"></i></span> Select Sections <span class="badge bg-warning text-dark ms-2">Elective Only</span> <button type="button" class="btn btn-sm btn-outline-warning float-end" id="toggleAllSections"><i class="bi bi-check2-all me-1"></i>Select All</button></div><div class="card-body">
                <div id="sectionList"><p class="text-muted mb-0"><i class="bi bi-info-circle me-1"></i>Select classes above first.</p></div>
            </div></div>
            <div class="card mb-3"><div class="card-header bg-light fw-semibold py-2"><span class="text-secondary me-2"><i class="bi bi-person-check-fill"></i></span> Teacher</div><div class="card-body">
                <select name="teacher_id" class="form-select"><option value="">-- Select Teacher (Optional) --</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>@endforeach</select>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card border-primary mb-3"><div class="card-header bg-primary text-white py-2"><i class="bi bi-clipboard-check me-1"></i> Summary</div><div class="card-body" id="summaryBody"><div class="text-center text-muted py-4"><i class="bi bi-arrow-left-circle fs-1"></i><p class="mt-2">Select a subject and classes.</p></div></div></div>
            <div class="d-grid gap-2"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-circle me-1"></i> Save Assignment(s)</button><a href="{{ route('admin.subject-assignments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Cancel</a></div>
        </div>
    </div>
    </form>
</div>
@endsection
@push('scripts')
<script>
const API_BASE='{{ url("/admin/subject-assignments/api") }}';let currentType='core';let selectedClasses=new Set();let allSectionsCache={};
document.getElementById('typeCore').addEventListener('click',function(){currentType='core';document.getElementById('assignmentType').value='core';this.classList.add('active-core');document.getElementById('typeElective').classList.remove('active-elective');document.getElementById('sectionCard').style.display='none';updateSummary();});
document.getElementById('typeElective').addEventListener('click',function(){currentType='elective';document.getElementById('assignmentType').value='elective';this.classList.add('active-elective');document.getElementById('typeCore').classList.remove('active-core');document.getElementById('sectionCard').style.display='block';updateSummary();});
document.querySelectorAll('.class-check').forEach(cb=>{cb.addEventListener('change',function(){const cid=this.value;const item=this.closest('.class-check-item');if(this.checked){selectedClasses.add(cid);item.classList.add('selected');loadSectionsForClass(cid);}else{selectedClasses.delete(cid);item.classList.remove('selected');}updateSummary();});});
document.querySelectorAll('.class-check-item').forEach(item=>{item.addEventListener('click',function(e){if(e.target.tagName==='INPUT')return;const cb=this.querySelector('.class-check');cb.checked=!cb.checked;cb.dispatchEvent(new Event('change'));});});
document.getElementById('toggleAllClasses').addEventListener('click',function(){const all=document.querySelectorAll('.class-check');const allChecked=Array.from(all).every(cb=>cb.checked);all.forEach(cb=>{cb.checked=!allChecked;cb.dispatchEvent(new Event('change'));});this.innerHTML=allChecked?'<i class="bi bi-check2-all me-1"></i>Select All':'<i class="bi bi-x-circle me-1"></i>Deselect All';});
document.getElementById('toggleAllSections').addEventListener('click',function(){const all=document.querySelectorAll('.section-check');if(all.length===0)return;const allChecked=Array.from(all).every(cb=>cb.checked);all.forEach(cb=>{cb.checked=!allChecked;cb.dispatchEvent(new Event('change'));});this.innerHTML=allChecked?'<i class="bi bi-check2-all me-1"></i>Select All':'<i class="bi bi-x-circle me-1"></i>Deselect All';});
function loadSectionsForClass(classId){if(allSectionsCache[classId]){renderSections();return;}fetch(API_BASE+'/sections?class_id='+classId).then(r=>r.json()).then(s=>{allSectionsCache[classId]=s;renderSections();}).catch(()=>{allSectionsCache[classId]=[];});}
function renderSections(){const container=document.getElementById('sectionList');if(selectedClasses.size===0){container.innerHTML='<p class="text-muted mb-0">Select classes above first.</p>';return;}let html='';selectedClasses.forEach(cid=>{const cn=document.querySelector('.class-check-item[data-class-id="'+cid+'"]')?.querySelector('label span')?.textContent||'Class '+cid;const secs=allSectionsCache[cid]||[];html+='<div class="mb-3"><div class="fw-semibold mb-2 text-primary"><i class="bi bi-collection me-1"></i>'+cn+'</div><div class="d-flex flex-wrap gap-2">';secs.forEach(sec=>{html+='<div class="section-check-item"><input type="checkbox" class="form-check-input section-check" name="section_ids[]" value="'+sec.id+'" id="sec_'+cid+'_'+sec.id+'" '+(currentType==='elective'?'checked':'')+'><label for="sec_'+cid+'_'+sec.id+'" class="ms-1 user-select-none cursor-pointer small">'+sec.name+'</label></div>';});if(secs.length===0)html+='<small class="text-muted">No sections</small>';html+='</div></div>';});container.innerHTML=html;document.querySelectorAll('.section-check').forEach(cb=>{cb.addEventListener('change',updateSummary);});}
document.getElementById('subjectSelect').addEventListener('change',function(){const t=this.options[this.selectedIndex].getAttribute('data-type')||'';if(t==='core')document.getElementById('typeCore').click();else if(t==='elective')document.getElementById('typeElective').click();updateSummary();});
function updateSummary(){const sel=document.getElementById('subjectSelect'),ay=document.getElementById('academicYear');const sn=sel.options[sel.selectedIndex]?.text||'Not selected';const an=ay.options[ay.selectedIndex]?.text||'Not selected';const cc=selectedClasses.size;const sb=document.getElementById('summaryBody');if(cc===0){sb.innerHTML='<div class="text-center text-muted py-4"><p>Select a subject and classes.</p></div>';return;}let si=currentType==='core'?'<span class="text-primary">All Sections</span>':'<span class="text-warning">'+document.querySelectorAll('.section-check:checked').length+' section(s)</span>';let tc=currentType==='core'?cc:document.querySelectorAll('.section-check:checked').length;sb.innerHTML='<table class="table table-sm mb-0"><tr><td class="text-muted">Subject</td><td class="fw-semibold">'+sn+'</td></tr><tr><td class="text-muted">AY</td><td>'+an+'</td></tr><tr><td class="text-muted">Type</td><td><span class="badge '+(currentType==='core'?'bg-primary':'bg-warning text-dark')+'">'+(currentType==='core'?'Core':'Elective')+'</span></td></tr><tr><td class="text-muted">Classes</td><td>'+cc+'</td></tr><tr><td class="text-muted">Sections</td><td>'+si+'</td></tr><tr class="border-top"><td class="text-muted fw-semibold">Records</td><td class="fw-bold fs-5 text-primary">'+tc+'</td></tr></table>';}
updateSummary();
</script>
@endpush