<?php
 $dir = 'resources/views/admin/Classroom';

 $edit = <<<'EDIT'
@extends('layouts.admin')
@section('title','Edit Class')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0"><i class="fas fa-chalkboard me-2 text-primary"></i>Edit Class - {{$data->name}}</h4>
<a href="{{route('admin.classes.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{session('success')}}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
<div class="sc">
<form method="POST" action="{{route('admin.classes.update',$data->id)}}">
@csrf @method('PUT')
<div class="row">
<div class="col-lg-8">
<h5 class="border-bottom pb-2 mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Class Information</h5>
<div class="row">
<div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Class Name *</label><input type="text" name="name" class="form-control" value="{{$data->name}}" required></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>
@foreach(\App\Models\AcademicYear::all() as $ay)<option value="{{$ay->id}}" {{$data->academic_year_id==$ay->id?'selected':''}}>{{$ay->name}}</option>@endforeach</select></div></div>
</div>
<div class="row">
<div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Branch</label><select name="branch_id" class="form-select"><option value="">-- Select --</option>
@foreach(\App\Models\Branch::all() as $b)<option value="{{$b->id}}" {{$data->branch_id==$b->id?'selected':''}}>{{$b->name}}</option>@endforeach</select></div></div>
</div>
<hr class="my-4">
<h5 class="border-bottom pb-2 mb-3"><i class="fas fa-th-large text-success me-2"></i>Sections <button type="button" class="btn btn-success btn-sm ms-2" onclick="addSectionRow()"><i class="fas fa-plus me-1"></i>Add Section</button></h5>
<div class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i>Each section has its own max students and homeroom teacher.</div>
<div id="sectionRows">
@foreach(\App\Models\Section::where('class_id',$data->id)->get() as $sec)
<div class="row mb-3 align-items-end p-2 rounded border bg-light">
<input type="hidden" name="section_id[]" value="{{$sec->id}}">
<div class="col-2"><label class="form-label fw-bold small">Name *</label><input type="text" name="section_name[]" class="form-control form-control-sm" value="{{$sec->name}}" required></div>
<div class="col-2"><label class="form-label fw-bold small">Max Students</label><input type="number" name="section_capacity[]" class="form-control form-control-sm" value="{{$sec->capacity ?? 40}}" min="1"></div>
<div class="col-5"><label class="form-label fw-bold small">Homeroom Teacher</label><select name="section_teacher_id[]" class="form-select form-select-sm"><option value="">-- Not Assigned --</option>
@foreach(\App\Models\Teacher::orderBy('first_name')->get() as $t)<option value="{{$t->id}}" {{$sec->teacher_id==$t->id?'selected':''}}>{{$t->first_name}} {{$t->last_name}} ({{$t->department ?? 'N/A'}})</option>@endforeach</select></div>
<div class="col-3"><button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="markRemove(this)"><i class="fas fa-trash me-1"></i>Remove</button></div>
</div>
@endforeach
</div>
</div>
<div class="col-lg-4">
<h5 class="border-bottom pb-2 mb-3"><i class="fas fa-box text-warning me-2"></i>Assets ({{\App\Models\ClassAsset::where('class_id',$data->id)->count()}})</h5>
<div class="d-flex justify-content-between align-items-center mb-2">
<div class="input-group input-group-sm"><span class="input-group-text"><i class="fas fa-filter"></i></span>
<select id="sectionFilter" class="form-select form-select-sm" onchange="filterAssets()"><option value="all">All</option>
@foreach(\App\Models\Section::where('class_id',$data->id)->get() as $sec)<option value="{{$sec->id}}">{{$sec->name}}</option>@endforeach</select></div>
<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addAssetModal"><i class="fas fa-plus"></i></button>
</div>
<div id="assetsList" style="max-height:450px;overflow-y:auto">
@foreach(\App\Models\ClassAsset::where('class_id',$data->id)->get() as $ast)
<div class="card card-body py-2 px-3 mb-2 border" data-section="{{$ast->section_id ?? ''}}" style="font-size:13px">
<div class="d-flex justify-content-between align-items-center">
<div><strong>{{$ast->name}}</strong> <span class="badge bg-secondary">{{$ast->quantity}}</span>
<span class="badge @($ast->condition=='Good'?'bg-success':($ast->condition=='Fair'?'bg-warning':'bg-danger'))">{{$ast->condition}}</span>
<span class="text-muted">{{$ast->section_id ? \App\Models\Section::find($ast->section_id)->name ?? '' : 'General'}}</span></div>
<form method="POST" action="{{route('admin.class-assets.destroy',$ast->id)}}" style="display:inline" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1"><i class="fas fa-times"></i></button></form>
</div></div>
@endforeach
@if(\App\Models\ClassAsset::where('class_id',$data->id)->count()==0)
<div class="text-center text-muted py-3 small"><i class="fas fa-box-open d-block fa-lg mb-1"></i>No assets</div>
@endif
</div>
</div>
</div>
<div class="mb-4 mt-3">
<button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Save All Changes</button>
</div>
</form>
</div>
<div class="modal fade" id="addAssetModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header bg-dark text-white"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Asset</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<form method="POST" action="{{route('admin.class-assets.store')}}">@csrf<input type="hidden" name="class_id" value="{{$data->id}}">
<div class="modal-body">
<div class="mb-3"><label class="form-label fw-bold">Asset Name *</label><input type="text" name="name" class="form-control" required></div>
<div class="mb-3"><label class="form-label fw-bold">Section</label><select name="section_id" class="form-select"><option value="">General</option>
@foreach(\App\Models\Section::where('class_id',$data->id)->get() as $sec)<option value="{{$sec->id}}">{{$sec->name}}</option>@endforeach</select></div>
<div class="row"><div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Quantity *</label><input type="number" name="quantity" class="form-control" value="1" min="1" required></div></div>
<div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Condition *</label><select name="condition" class="form-select"><option value="Good">Good</option><option value="Fair">Fair</option><option value="Poor">Poor</option></select></div></div></div>
<div class="row"><div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Purchase Date</label><input type="date" name="purchase_date" class="form-control"></div></div>
<div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Description</label><input type="text" name="description" class="form-control"></div></div></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button></div>
</form></div></div></div>
@endsection
@push('scripts')
<script>
function addSectionRow(){var d=document.getElementById('sectionRows');var r=document.createElement('div');r.className='row mb-3 align-items-end p-2 rounded border bg-light';r.innerHTML='<input type="hidden" name="section_id[]" value=""><div class="col-2"><label class="form-label fw-bold small">Name *</label><input type="text" name="section_name[]" class="form-control form-control-sm" placeholder="e.g. B" required></div><div class="col-2"><label class="form-label fw-bold small">Max Students</label><input type="number" name="section_capacity[]" class="form-control form-control-sm" value="40" min="1"></div><div class="col-5"><label class="form-label fw-bold small">Homeroom Teacher</label><select name="section_teacher_id[]" class="form-select form-select-sm"></select></div><div class="col-3"><button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="markRemove(this)"><i class=\"fas fa-trash me-1\"></i>Remove</button></div>';d.appendChild(r);rebuildTeacherDropdown(r.querySelector('select'));window.scrollTo(0,document.body.scrollHeight);}
function rebuildTeacherDropdown(sel){if(!sel)return;sel.innerHTML='<option value="">-- Not Assigned --</option>';var teachers=[];@foreach(\App\Models\Teacher::orderBy('first_name')->get() as $t)teachers.push({id:{{$t->id}},n:'{{$t->first_name}} {{$t->last_name}} ({{$t->department ?? "N/A"}})'});@endforeach teachers.forEach(function(t){var o=document.createElement('option');o.value=t.id;o.textContent=t.n;sel.appendChild(o);});}
function markRemove(btn){var row=btn.closest('.row');var hid=row.querySelector('input[type=hidden][name="section_id[]"]');if(hid&&hid.value){var inp=document.createElement('input');inp.type='hidden';inp.name='remove_section[]';inp.value=hid.value;document.querySelector('form').appendChild(inp);}row.remove();}
function filterAssets(){var v=document.getElementById('sectionFilter').value;document.querySelectorAll('#assetsList [data-section]').forEach(function(r){r.style.display=(v==='all'||r.getAttribute('data-section')===v)?'':'none';});}
</script>
@endpush
EDIT;

file_put_contents("$dir/edit.blade.php",$edit);
echo "DONE: edit form updated\n";
