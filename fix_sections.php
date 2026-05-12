<?php
 $dir = 'resources/views/admin/Classroom';
@mkdir($dir,0755,true);

 $edit = <<<'EDIT'
@extends('layouts.admin')
@section('title','Edit Class')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Edit Class - {{$data->name}}</h4>
<a href="{{route('admin.classes.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<ul class="nav nav-tabs mb-3" role="tablist">
<li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-info"><i class="fas fa-info-circle me-1"></i>Class Info</a></li>
<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-sections"><i class="fas fa-th-large me-1"></i>Sections ({{\App\Models\Section::where('class_id',$data->id)->count()}})</a></li>
<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-assets"><i class="fas fa-box me-1"></i>Assets ({{\App\Models\ClassAsset::where('class_id',$data->id)->count()}})</a></li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="tab-info">
<form method="POST" action="{{route('admin.classes.update',$data->id)}}">@csrf @method('PUT')
<div class="row"><div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Class Name *</label><input type="text" name="name" class="form-control" value="{{$data->name}}" required></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Max Students</label><input type="number" name="capacity" class="form-control" value="{{$data->capacity}}" min="1" max="500"></div></div></div>
<div class="row"><div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Academic Year *</label><select name="academic_year_id" class="form-select" required><option value="">-- Select --</option>
@foreach(\App\Models\AcademicYear::all() as $ay)<option value="{{$ay->id}}" {{$data->academic_year_id==$ay->id?'selected':''}}>{{$ay->name}}</option>@endforeach</select></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label fw-bold">Branch</label><select name="branch_id" class="form-select"><option value="">-- Select --</option>
@foreach(\App\Models\Branch::all() as $b)<option value="{{$b->id}}" {{$data->branch_id==$b->id?'selected':''}}>{{$b->name}}</option>@endforeach</select></div></div></div>
<div class="mb-3"><label class="form-label fw-bold">Homeroom Teacher</label>
<div class="input-group mb-2"><span class="input-group-text"><i class="fas fa-search"></i></span>
<input type="text" id="teacherSearch" class="form-control" placeholder="Type name or department..." oninput="filterTeachers()">
<span class="input-group-text"><button type="button" class="btn btn-sm btn-success p-0 px-2" data-bs-toggle="modal" data-bs-target="#addTeacherModal"><i class="fas fa-user-plus"></i></button></span></div>
<select name="teacher_id" id="teacherSelect" class="form-select" size="3" style="max-height:110px"><option value="">-- No Teacher --</option>
@foreach(\App\Models\Teacher::orderBy('first_name')->get() as $t)
<option value="{{$t->id}}" data-name="{{$t->first_name}} {{$t->last_name}}" data-dept="{{$t->department}}" data-email="{{$t->email}}" {{$data->teacher_id==$t->id?'selected':''}}>{{$t->first_name}} {{$t->last_name}} | {{$t->department ?? 'N/A'}} | {{$t->email}}</option>
@endforeach</select>
<small class="text-muted" id="teacherCount">{{\App\Models\Teacher::count()}} teacher(s)</small>
</div>
<div class="mb-4"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Class</button></div>
</form>
</div>

<div class="tab-pane fade" id="tab-sections">
<div class="d-flex justify-content-between align-items-center mb-3">
<h6 class="mb-0">Sections for {{$data->name}}</h6>
<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSectionModal"><i class="fas fa-plus me-1"></i>Add Section</button>
</div>
@if(session('section_success'))
<div class="alert alert-success alert-dismissible fade show py-2"><small><i class="fas fa-check-circle me-1"></i>{{session('section_success')}}</small><button type="button" class="btn-close py-0" data-bs-dismiss="alert"></button></div>
@endif
<div class="row g-3">
@foreach(\App\Models\Section::where('class_id',$data->id)->get() as $sec)
<div class="col-md-6">
<div class="card border-top border-4 @if($loop->first) border-primary @elseif($loop->index==1) border-success @elseif($loop->index==2) border-warning @else border-info @endif">
<div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
<h6 class="mb-0"><i class="fas fa-th-large me-1"></i>Section <strong>{{$sec->name}}</strong></h6>
<div>
<button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editSection({{$sec->id}},'{{$sec->name}}',{{$sec->capacity ?? 0}},{{$sec->teacher_id ?? 'null'}})" title="Edit"><i class="fas fa-edit"></i></button>
<form method="POST" action="{{route('admin.sections.destroy',$sec->id)}}" style="display:inline" onsubmit="return confirm('Delete section {{$sec->name}}?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button></form>
</div>
</div>
<div class="card-body p-3">
<div class="row g-2">
<div class="col-6">
<div class="p-2 rounded text-center" style="background:#e3f2fd">
<i class="fas fa-users text-primary"></i>
<div class="fw-bold fs-5">{{$sec->capacity ?? 0}}</div>
<small class="text-muted">Max Students</small>
</div>
</div>
<div class="col-6">
<div class="p-2 rounded text-center" style="background:#fff3e0">
<i class="fas fa-user-tie text-warning"></i>
<div class="fw-bold small mt-1">{{$sec->teacher_id ? \App\Models\Teacher::find($sec->teacher_id)->first_name.' '.\App\Models\Teacher::find($sec->teacher_id)->last_name : 'Not Assigned'}}</div>
<small class="text-muted">{{$sec->teacher_id ? \App\Models\Teacher::find($sec->teacher_id)->department ?? '' : 'No teacher'}}</small>
</div>
</div>
<div class="col-12 mt-2">
<div class="d-flex justify-content-between align-items-center p-2 rounded" style="background:#f8f9fa">
<small class="text-muted"><i class="fas fa-box me-1"></i>{{\App\Models\ClassAsset::where('class_id',$data->id)->where('section_id',$sec->id)->count()}} asset(s)</small>
<small class="text-muted"><i class="fas fa-user-graduate me-1"></i>Students TBD</small>
</div>
</div>
</div>
</div>
</div>
@endforeach
@if(\App\Models\Section::where('class_id',$data->id)->count()==0)
<div class="col-12 text-center text-muted py-4"><i class="fas fa-th-large fa-2x d-block mb-2"></i>No sections yet. Click "Add Section" to create A, B, C...</div>
@endif
</div>
<div class="modal fade" id="addSectionModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header bg-dark text-white"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Section</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<form method="POST" action="{{route('admin.sections.store')}}">@csrf<input type="hidden" name="class_id" value="{{$data->id}}">
<div class="modal-body">
<div class="mb-3"><label class="form-label fw-bold">Section Name *</label><input type="text" name="name" class="form-control" placeholder="e.g. A, B, C" required></div>
<div class="row"><div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Max Students *</label><input type="number" name="capacity" class="form-control" value="40" min="1" max="200" required></div></div>
<div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Homeroom Teacher</label><select name="teacher_id" class="form-select"><option value="">-- Not Assigned --</option>
@foreach(\App\Models\Teacher::orderBy('first_name')->get() as $t)<option value="{{$t->id}}">{{$t->first_name}} {{$t->last_name}} ({{$t->department ?? 'N/A'}})</option>@endforeach</select></div></div></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Section</button></div>
</form></div></div></div>
<div class="modal fade" id="editSectionModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header bg-primary text-white"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Section</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<form id="editSectionForm" method="POST"><input type="hidden" id="es_id" name="id"><input type="hidden" name="_token" value="{{csrf_token()}}">
<input type="hidden" name="_method" value="PUT">
<div class="modal-body">
<div class="mb-3"><label class="form-label fw-bold">Section Name *</label><input type="text" name="name" id="es_name" class="form-control" required></div>
<div class="row"><div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Max Students *</label><input type="number" name="capacity" id="es_cap" class="form-control" min="1" max="200" required></div></div>
<div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Homeroom Teacher</label><select name="teacher_id" id="es_teacher" class="form-select"><option value="">-- Not Assigned --</option>
@foreach(\App\Models\Teacher::orderBy('first_name')->get() as $t)<option value="{{$t->id}}">{{$t->first_name}} {{$t->last_name}} ({{$t->department ?? 'N/A'}})</option>@endforeach</select></div></div></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Section</button></div>
</form></div></div></div>
</div>

<div class="tab-pane fade" id="tab-assets">
<div class="d-flex justify-content-between align-items-center mb-3">
<h6 class="mb-0">Assets for {{$data->name}}</h6>
<button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addAssetModal"><i class="fas fa-plus me-1"></i>Add Asset</button>
</div>
<div class="mb-3"><div class="input-group"><span class="input-group-text"><i class="fas fa-filter"></i></span>
<select id="sectionFilter" class="form-select" onchange="filterAssets()"><option value="all">All Sections</option>
@foreach(\App\Models\Section::where('class_id',$data->id)->get() as $sec)<option value="{{$sec->id}}">Section {{$sec->name}}</option>@endforeach</select></div></div>
@if(session('asset_success'))
<div class="alert alert-success alert-dismissible fade show py-2"><small><i class="fas fa-check-circle me-1"></i>{{session('asset_success')}}</small><button type="button" class="btn-close py-0" data-bs-dismiss="alert"></button></div>
@endif
<div class="table-responsive"><table class="table table-sm table-bordered table-hover" id="assetsTable">
<thead class="table-light"><tr><th>#</th><th>Asset</th><th>Section</th><th>Qty</th><th>Condition</th><th>Actions</th></tr></thead>
<tbody>
@foreach(\App\Models\ClassAsset::where('class_id',$data->id)->get() as $ast)
<tr data-section="{{$ast->section_id ?? ''}}">
<td>{{$loop->iteration}}</td><td><strong>{{$ast->name}}</strong></td>
<td>{{$ast->section_id ? \App\Models\Section::find($ast->section_id)->name ?? '-' : '<span class="badge bg-secondary">General</span>'}}</td>
<td>{{$ast->quantity}}</td>
<td><span class="badge @($ast->condition=='Good'?'bg-success':($ast->condition=='Fair'?'bg-warning':'bg-danger'))">{{$ast->condition}}</span></td>
<td><form method="POST" action="{{route('admin.class-assets.destroy',$ast->id)}}" style="display:inline" onsubmit="return confirm('Remove?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form></td></tr>
@endforeach
@if(\App\Models\ClassAsset::where('class_id',$data->id)->count()==0)
<tr><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-box-open fa-2x d-block mb-2"></i>No assets yet</td></tr>
@endif
</tbody></table></div>
<div class="modal fade" id="addAssetModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header bg-dark text-white"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Asset</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<form method="POST" action="{{route('admin.class-assets.store')}}">@csrf<input type="hidden" name="class_id" value="{{$data->id}}">
<div class="modal-body">
<div class="mb-3"><label class="form-label fw-bold">Asset Name *</label><input type="text" name="name" class="form-control" required></div>
<div class="mb-3"><label class="form-label fw-bold">Assign to Section</label><select name="section_id" class="form-select"><option value="">General (All Sections)</option>
@foreach(\App\Models\Section::where('class_id',$data->id)->get() as $sec)<option value="{{$sec->id}}">Section {{$sec->name}}</option>@endforeach</select></div>
<div class="row"><div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Quantity *</label><input type="number" name="quantity" class="form-control" value="1" min="1" required></div></div>
<div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Condition *</label><select name="condition" class="form-select"><option value="Good">Good</option><option value="Fair">Fair</option><option value="Poor">Poor</option></select></div></div></div>
<div class="row"><div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Purchase Date</label><input type="date" name="purchase_date" class="form-control"></div></div>
<div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Description</label><input type="text" name="description" class="form-control"></div></div></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button></div>
</form></div></div></div>
</div>
</div>
</div>

<div class="modal fade" id="addTeacherModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
<div class="modal-header bg-dark text-white"><h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New Teacher</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<form id="ajaxTeacherForm">@csrf
<div class="modal-body">
<div class="row"><div class="col-6"><div class="mb-3"><label class="form-label fw-bold">First Name *</label><input type="text" name="first_name" class="form-control" required></div></div>
<div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Last Name *</label><input type="text" name="last_name" class="form-control" required></div></div></div>
<div class="row"><div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Email *</label><input type="email" name="email" class="form-control" required></div></div>
<div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Phone</label><input type="text" name="phone" class="form-control"></div></div></div>
<div class="row"><div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Department *</label><input type="text" name="department" class="form-control" required></div></div>
<div class="col-6"><div class="mb-3"><label class="form-label fw-bold">Qualification</label><input type="text" name="qualification" class="form-control"></div></div></div>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-primary" id="saveTeacherBtn"><i class="fas fa-save me-1"></i>Save & Select</button></div>
</form></div></div></div>
@endsection
@push('scripts')
<script>
function filterTeachers(){var q=document.getElementById('teacherSearch').value.toLowerCase().trim();var sel=document.getElementById('teacherSelect');var c=0;for(var i=1;i<sel.options.length;i++){var t=(sel.options[i].getAttribute('data-name')||'').toLowerCase()+' '+(sel.options[i].getAttribute('data-dept')||'').toLowerCase()+' '+(sel.options[i].getAttribute('data-email')||'').toLowerCase();if(q===''||t.indexOf(q)!==-1){sel.options[i].hidden=false;sel.options[i].style.display='';c++;}else{sel.options[i].hidden=true;sel.options[i].style.display='none';}}document.getElementById('teacherCount').textContent=c+' teacher(s)';}
function filterAssets(){var v=document.getElementById('sectionFilter').value;document.querySelectorAll('#assetsTable tbody tr[data-section]').forEach(function(r){r.style.display=(v==='all'||r.getAttribute('data-section')===v)?'':'none';});}
function editSection(id,name,cap,teacherId){document.getElementById('es_id').value=id;document.getElementById('es_name').value=name;document.getElementById('es_cap').value=cap;document.getElementById('es_teacher').value=teacherId||'';var route='{{url("admin/sections/")}}'+id;document.getElementById('editSectionForm').action=route;new bootstrap.Modal(document.getElementById('editSectionModal')).show();}
document.getElementById('editSectionForm').addEventListener('submit',function(e){e.preventDefault();var fd=new FormData(this);var id=document.getElementById('es_id').value;fetch(this.action,{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){return r.json()}).then(function(d){if(d.success){window.location.reload();}else{alert(d.error||'Failed');}}).catch(function(e){alert('Error: '+e);});});
document.getElementById('ajaxTeacherForm').addEventListener('submit',function(e){e.preventDefault();var btn=document.getElementById('saveTeacherBtn');btn.disabled=true;btn.innerHTML='<i class="fas fa-spinner fa-spin me-1"></i>Saving...';var fd=new FormData(this);fetch('{{url("admin/teachers")}}',{method:'POST',body:fd,headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){return r.json()}).then(function(data){if(data.id){var sel=document.getElementById('teacherSelect');var opt=document.createElement('option');opt.value=data.id;opt.selected=true;opt.setAttribute('data-name',data.first_name+' '+data.last_name);opt.setAttribute('data-dept',data.department||'');opt.setAttribute('data-email',data.email);opt.textContent=data.first_name+' '+data.last_name+' | '+(data.department||'N/A')+' | '+data.email;sel.appendChild(opt);sel.value=data.id;document.getElementById('teacherSearch').value='';filterTeachers();var m=bootstrap.Modal.getInstance(document.getElementById('addTeacherModal'));if(m)m.hide();document.getElementById('ajaxTeacherForm').reset();}else{alert(data.error||'Failed');}btn.disabled=false;btn.innerHTML='<i class="fas fa-save me-1"></i>Save & Select';}).catch(function(err){alert('Error');btn.disabled=false;btn.innerHTML='<i class="fas fa-save me-1"></i>Save & Select';});});
</script>
@endpush
EDIT;

file_put_contents("$dir/edit.blade.php",$edit);
echo "DONE: Sections with capacity + homeroom teacher\n";
