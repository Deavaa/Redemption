<?php
echo "Creating improved Term forms...\n";
 $dir = 'resources/views/admin/Term';
@mkdir($dir,0755,true);

 $create = <<<'CREATE'
@extends('layouts.admin')
@section('title','Add Term')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Add Term</h4>
<a href="{{route('admin.terms.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.terms.store')}}">
@csrf
<div class="mb-3">
<label class="form-label fw-bold">Term Name *</label>
<input type="text" name="name" class="form-control" placeholder="e.g. First Semester" value="{{old('name')}}" required>
</div>
<div class="mb-3">
<label class="form-label fw-bold">Academic Year *</label>
<select name="academic_year_id" class="form-select" required>
<option value="">-- Select Academic Year --</option>
@foreach(\App\Models\AcademicYear::all() as $ay)
<option value="{{$ay->id}}" {{old('academic_year_id')==$ay->id?'selected':''}}>{{$ay->name}}</option>
@endforeach
</select>
</div>
<div class="row">
<div class="col-md-6">
<div class="mb-3">
<label class="form-label fw-bold">Start Date *</label>
<div class="input-group mb-2">
<span class="input-group-text"><i class="fas fa-calendar"></i></span>
<input type="date" name="start_date" id="start_date" class="form-control" value="{{old('start_date')}}" onchange="gregToEth('start')" required>
</div>
<div class="mt-2 p-2 rounded" style="background:#f8f4e8;border-left:4px solid #c9a84c">
<small class="text-muted d-block">Ethiopian Date:</small>
<div class="row g-2 mt-1">
<div class="col-4"><select id="start_et_month" class="form-select form-select-sm" onchange="ethToGreg('start')"><option value="">Month</option></select></div>
<div class="col-3"><input type="number" id="start_et_day" class="form-control form-control-sm" min="1" max="30" placeholder="Day" onchange="ethToGreg('start')"></div>
<div class="col-5"><input type="number" id="start_et_year" class="form-control form-control-sm" placeholder="Year" onchange="ethToGreg('start')"></div>
</div>
</div>
</div>
</div>
<div class="col-md-6">
<div class="mb-3">
<label class="form-label fw-bold">End Date *</label>
<div class="input-group mb-2">
<span class="input-group-text"><i class="fas fa-calendar"></i></span>
<input type="date" name="end_date" id="end_date" class="form-control" value="{{old('end_date')}}" onchange="gregToEth('end')" required>
</div>
<div class="mt-2 p-2 rounded" style="background:#f8f4e8;border-left:4px solid #c9a84c">
<small class="text-muted d-block">Ethiopian Date:</small>
<div class="row g-2 mt-1">
<div class="col-4"><select id="end_et_month" class="form-select form-select-sm" onchange="ethToGreg('end')"><option value="">Month</option></select></div>
<div class="col-3"><input type="number" id="end_et_day" class="form-control form-control-sm" min="1" max="30" placeholder="Day" onchange="ethToGreg('end')"></div>
<div class="col-5"><input type="number" id="end_et_year" class="form-control form-control-sm" placeholder="Year" onchange="ethToGreg('end')"></div>
</div>
</div>
</div>
</div>
</div>
<div class="mb-4">
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" name="is_current" id="is_current" value="1" {{old('is_current')=='1'?'checked':''}}>
<label class="form-check-label fw-bold" for="is_current">Set as Current Semester</label>
</div>
</div>
<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Term</button>
</form>
</div>
@endsection
@push('scripts')
<script>
var EM=['Meskerem','Tikimt','Hidar','Tahsas','Tir','Yekatit','Megabit','Miazia','Ginbot','Sene','Hamle','Nehase','Pagume'];
function initMonths(){
var s1=document.getElementById('start_et_month');
var s2=document.getElementById('end_et_month');
for(var i=0;i<13;i++){
var o1=document.createElement('option');o1.value=i+1;o1.text=EM[i];s1.appendChild(o1);
var o2=document.createElement('option');o2.value=i+1;o2.text=EM[i];s2.appendChild(o2);
}
}
function gregToJDN(y,m,d){
var a=Math.floor((14-m)/12);var yy=y+4800-a;var mm=m+12*a-3;
return d+Math.floor((153*mm+2)/5)+365*yy+Math.floor(yy/4)-Math.floor(yy/100)+Math.floor(yy/400)-32045;
}
function jdnToGreg(j){
var a=j+32044;var b=Math.floor((4*a+3)/146097);var c=a-Math.floor(146097*b/4);
var dd=Math.floor((4*c+3)/1461);var e=c-Math.floor(1461*dd/4);var m=Math.floor((5*e+2)/153);
var day=e-Math.floor((153*m+2)/5)+1;var month=m+3-12*Math.floor(m/10);
var year=100*b+dd-4800+Math.floor(m/10);
return year+'-'+String(month).padStart(2,'0')+'-'+String(day).padStart(2,'0');
}
function gregToEth(p){
var v=document.getElementById(p+'_date').value;if(!v)return;
var pp=v.split('-');var j=gregToJDN(parseInt(pp[0]),parseInt(pp[1]),parseInt(pp[2]));
var r=j-1724221;var n=Math.floor(r/1461);var d=r%1461;
var ey,ed;
if(d<365){ey=4*n+1;ed=d;}else if(d<730){ey=4*n+2;ed=d-365;}
else if(d<1096){ey=4*n+3;ed=d-730;}else{ey=4*n+4;ed=d-1096;}
var em=Math.floor(ed/30)+1;var edn=(ed%30)+1;
document.getElementById(p+'_et_month').value=em;
document.getElementById(p+'_et_day').value=edn;
document.getElementById(p+'_et_year').value=ey;
}
function ethToGreg(p){
var em=document.getElementById(p+'_et_month').value;
var ed=document.getElementById(p+'_et_day').value;
var ey=document.getElementById(p+'_et_year').value;
if(!em||!ed||!ey)return;
em=parseInt(em);ed=parseInt(ed);ey=parseInt(ey);
var doy=(em-1)*30+(ed-1);var n=Math.floor((ey-1)/4);var yic=(ey-1)%4;
var cs=n*1461;var ys;
if(yic===0)ys=0;else if(yic===1)ys=365;else if(yic===2)ys=730;else ys=1096;
var j=cs+ys+doy+1724221;
document.getElementById(p+'_date').value=jdnToGreg(j);
}
initMonths();
</script>
@endpush
CREATE;

 $edit = <<<'EDIT'
@extends('layouts.admin')
@section('title','Edit Term')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Edit Term</h4>
<a href="{{route('admin.terms.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<form method="POST" action="{{route('admin.terms.update',$data->id)}}">
@csrf @method('PUT')
<div class="mb-3">
<label class="form-label fw-bold">Term Name *</label>
<input type="text" name="name" class="form-control" value="{{$data->name}}" required>
</div>
<div class="mb-3">
<label class="form-label fw-bold">Academic Year *</label>
<select name="academic_year_id" class="form-select" required>
<option value="">-- Select Academic Year --</option>
@foreach(\App\Models\AcademicYear::all() as $ay)
<option value="{{$ay->id}}" {{$data->academic_year_id==$ay->id?'selected':''}}>{{$ay->name}}</option>
@endforeach
</select>
</div>
<div class="row">
<div class="col-md-6">
<div class="mb-3">
<label class="form-label fw-bold">Start Date *</label>
<div class="input-group mb-2">
<span class="input-group-text"><i class="fas fa-calendar"></i></span>
<input type="date" name="start_date" id="start_date" class="form-control" value="{{$data->start_date}}" onchange="gregToEth('start')" required>
</div>
<div class="mt-2 p-2 rounded" style="background:#f8f4e8;border-left:4px solid #c9a84c">
<small class="text-muted d-block">Ethiopian Date:</small>
<div class="row g-2 mt-1">
<div class="col-4"><select id="start_et_month" class="form-select form-select-sm" onchange="ethToGreg('start')"><option value="">Month</option></select></div>
<div class="col-3"><input type="number" id="start_et_day" class="form-control form-control-sm" min="1" max="30" placeholder="Day" onchange="ethToGreg('start')"></div>
<div class="col-5"><input type="number" id="start_et_year" class="form-control form-control-sm" placeholder="Year" onchange="ethToGreg('start')"></div>
</div>
</div>
</div>
</div>
<div class="col-md-6">
<div class="mb-3">
<label class="form-label fw-bold">End Date *</label>
<div class="input-group mb-2">
<span class="input-group-text"><i class="fas fa-calendar"></i></span>
<input type="date" name="end_date" id="end_date" class="form-control" value="{{$data->end_date}}" onchange="gregToEth('end')" required>
</div>
<div class="mt-2 p-2 rounded" style="background:#f8f4e8;border-left:4px solid #c9a84c">
<small class="text-muted d-block">Ethiopian Date:</small>
<div class="row g-2 mt-1">
<div class="col-4"><select id="end_et_month" class="form-select form-select-sm" onchange="ethToGreg('end')"><option value="">Month</option></select></div>
<div class="col-3"><input type="number" id="end_et_day" class="form-control form-control-sm" min="1" max="30" placeholder="Day" onchange="ethToGreg('end')"></div>
<div class="col-5"><input type="number" id="end_et_year" class="form-control form-control-sm" placeholder="Year" onchange="ethToGreg('end')"></div>
</div>
</div>
</div>
</div>
</div>
<div class="mb-4">
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" name="is_current" id="is_current" value="1" {{$data->is_current=='1'?'checked':''}}>
<label class="form-check-label fw-bold" for="is_current">Set as Current Semester</label>
</div>
</div>
<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button>
</form>
</div>
@endsection
@push('scripts')
<script>
var EM=['Meskerem','Tikimt','Hidar','Tahsas','Tir','Yekatit','Megabit','Miazia','Ginbot','Sene','Hamle','Nehase','Pagume'];
function initMonths(){
var s1=document.getElementById('start_et_month');
var s2=document.getElementById('end_et_month');
for(var i=0;i<13;i++){
var o1=document.createElement('option');o1.value=i+1;o1.text=EM[i];s1.appendChild(o1);
var o2=document.createElement('option');o2.value=i+1;o2.text=EM[i];s2.appendChild(o2);
}
}
function gregToJDN(y,m,d){
var a=Math.floor((14-m)/12);var yy=y+4800-a;var mm=m+12*a-3;
return d+Math.floor((153*mm+2)/5)+365*yy+Math.floor(yy/4)-Math.floor(yy/100)+Math.floor(yy/400)-32045;
}
function jdnToGreg(j){
var a=j+32044;var b=Math.floor((4*a+3)/146097);var c=a-Math.floor(146097*b/4);
var dd=Math.floor((4*c+3)/1461);var e=c-Math.floor(1461*dd/4);var m=Math.floor((5*e+2)/153);
var day=e-Math.floor((153*m+2)/5)+1;var month=m+3-12*Math.floor(m/10);
var year=100*b+dd-4800+Math.floor(m/10);
return year+'-'+String(month).padStart(2,'0')+'-'+String(day).padStart(2,'0');
}
function gregToEth(p){
var v=document.getElementById(p+'_date').value;if(!v)return;
var pp=v.split('-');var j=gregToJDN(parseInt(pp[0]),parseInt(pp[1]),parseInt(pp[2]));
var r=j-1724221;var n=Math.floor(r/1461);var d=r%1461;
var ey,ed;
if(d<365){ey=4*n+1;ed=d;}else if(d<730){ey=4*n+2;ed=d-365;}
else if(d<1096){ey=4*n+3;ed=d-730;}else{ey=4*n+4;ed=d-1096;}
var em=Math.floor(ed/30)+1;var edn=(ed%30)+1;
document.getElementById(p+'_et_month').value=em;
document.getElementById(p+'_et_day').value=edn;
document.getElementById(p+'_et_year').value=ey;
}
function ethToGreg(p){
var em=document.getElementById(p+'_et_month').value;
var ed=document.getElementById(p+'_et_day').value;
var ey=document.getElementById(p+'_et_year').value;
if(!em||!ed||!ey)return;
em=parseInt(em);ed=parseInt(ed);ey=parseInt(ey);
var doy=(em-1)*30+(ed-1);var n=Math.floor((ey-1)/4);var yic=(ey-1)%4;
var cs=n*1461;var ys;
if(yic===0)ys=0;else if(yic===1)ys=365;else if(yic===2)ys=730;else ys=1096;
var j=cs+ys+doy+1724221;
document.getElementById(p+'_date').value=jdnToGreg(j);
}
initMonths();
window.onload=function(){gregToEth('start');gregToEth('end');}
</script>
@endpush
EDIT;

 $index = <<<'INDEX'
@extends('layouts.admin')
@section('title','Terms')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Terms / Semesters</h4>
<a href="{{route('admin.terms.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add New</a>
</div>
<div class="sc">
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
<i class="fas fa-check-circle me-2"></i>{{session('success')}}
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
<div class="table-responsive">
<table class="table table-hover table-bordered">
<thead class="table-dark">
<tr><th>#</th><th>Term Name</th><th>Academic Year</th><th>Start Date</th><th>End Date</th><th>Status</th><th>Actions</th></tr>
</thead>
<tbody>
@foreach($data as $item)
<tr>
<td>{{$loop->iteration}}</td>
<td><strong>{{$item->name}}</strong></td>
<td>{{$item->academic_year_id ? \App\Models\AcademicYear::find($item->academic_year_id)->name ?? '-' : '-'}}</td>
<td>{{$item->start_date}}</td>
<td>{{$item->end_date}}</td>
<td>
@if($item->is_current == 1)
<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Current</span>
@else
<span class="badge bg-secondary">Inactive</span>
@endif
</td>
<td class="nowrap">
<a href="{{route('admin.terms.edit',$item->id)}}" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a>
<form method="POST" action="{{route('admin.terms.destroy',$item->id)}}" style="display:inline" onsubmit="return confirm('Delete this term?')">
@csrf @method('DELETE')
<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
{{ $data->links() }}
</div>
@endsection
INDEX;

file_put_contents("$dir/create.blade.php",$create);
file_put_contents("$dir/edit.blade.php",$edit);
file_put_contents("$dir/index.blade.php",$index);
echo "DONE: Term forms improved\n";
