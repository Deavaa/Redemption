<?php
 $dir = 'resources/views/admin/Classroom';
@mkdir($dir,0755,true);

 $create = <<<'CREATE'
@extends('layouts.admin')
@section('title','Add Class')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0"><i class="fas fa-chalkboard me-2 text-primary"></i>Add New Class</h4>
<a href="{{route('admin.classes.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.classes.store')}}">
@csrf
<div class="row">
<div class="col-lg-8">
<h5 class="border-bottom pb-2 mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Class Information</h5>
<div class="row">
<div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Class Name *</label><input type="text" name="name" class="form-control" placeholder="e.g. Grade 1" required></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>
@foreach(\App\Models\AcademicYear::all() as $ay)<option value="{{$ay->id}}">{{$ay->name}}</option>@endforeach</select></div></div>
</div>
<div class="row">
<div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Branch</label><select name="branch_id" class="form-select"><option value="">-- Select --</option>
@foreach(\App\Models\Branch::all() as $b)<option value="{{$b->id}}">{{$b->name}}</option>@endforeach</select></div></div>
</div>
<hr class="my-4">
<h5 class="border-bottom pb-2 mb-3"><i class="fas fa-th-large text-success me-2"></i>Sections <button type="button" class="btn btn-success btn-sm ms-2" onclick="addSectionRow()"><i class="fas fa-plus me-1"></i>Add Section</button></h5>
<div class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i>Each section has its own max students and homeroom teacher. Click "Add Section" to add more.</div>
<div id="sectionRows">
<div class="row mb-3 align-items-end p-2 rounded border bg-light">
<input type="hidden" name="section_id[]" value="">
<div class="col-2"><label class="form-label fw-bold small">Name *</label><input type="text" name="section_name[]" class="form-control form-control-sm" placeholder="e.g. A" required></div>
<div class="col-2"><label class="form-label fw-bold small">Max Students</label><input type="number" name="section_capacity[]" class="form-control form-control-sm" value="40" min="1"></div>
<div class="col-5"><label class="form-label fw-bold small">Homeroom Teacher</label><select name="section_teacher_id[]" class="form-select form-select-sm"><option value="">-- Not Assigned --</option>
@foreach(\App\Models\Teacher::orderBy('first_name')->get() as $t)<option value="{{$t->id}}">{{$t->first_name}} {{$t->last_name}} ({{$t->department ?? 'N/A'}})</option>@endforeach</select></div>
<div class="col-3"><button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest('.row').remove()"><i class="fas fa-trash me-1"></i>Remove</button></div>
</div>
</div>
</div>
<div class="col-lg-4">
<h5 class="border-bottom pb-2 mb-3"><i class="fas fa-box text-warning me-2"></i>Class Assets</h5>
<div class="alert alert-info small py-2">Save the class first, then add assets from the edit page.</div>
</div>
</div>
<div class="mb-4 mt-3">
<button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Save Class & Sections</button>
</div>
</form>
</div>
@push('scripts')
<script>
function addSectionRow(){var d=document.getElementById('sectionRows');var r=document.createElement('div');r.className='row mb-3 align-items-end p-2 rounded border bg-light';r.innerHTML='<input type="hidden" name="section_id[]" value=""><div class="col-2"><label class="form-label fw-bold small">Name *</label><input type="text" name="section_name[]" class="form-control form-control-sm" placeholder="e.g. B" required></div><div class="col-2"><label class="form-label fw-bold small">Max Students</label><input type="number" name="section_capacity[]" class="form-control form-control-sm" value="40" min="1"></div><div class="col-5"><label class="form-label fw-bold small">Homeroom Teacher</label><select name="section_teacher_id[]" class="form-select form-select-sm"></select></div><div class="col-3"><button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="this.closest(\'.row\').remove()"><i class=\"fas fa-trash me-1\"></i>Remove</button></div>';d.appendChild(r);rebuildTeacherDropdown(r.querySelector('select'));window.scrollTo(0,document.body.scrollHeight);}
function rebuildTeacherDropdown(sel){if(!sel)return;sel.innerHTML='<option value="">-- Not Assigned --</option>';var teachers=[];@foreach(\App\Models\Teacher::orderBy('first_name')->get() as $t)teachers.push({id:{{$t->id}},n:'{{$t->first_name}} {{$t->last_name}} ({{$t->department ?? "N/A"}})'});@endforeach teachers.forEach(function(t){var o=document.createElement('option');o.value=t.id;o.textContent=t.n;sel.appendChild(o);});}
</script>
@endpush
@endsection
CREATE;

file_put_contents("$dir/create.blade.php",$create);
echo "DONE: create form updated\n";
