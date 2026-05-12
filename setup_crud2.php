<?php
echo "=== Fixed CRUD View Generator ===\n";
 $M=[];
foreach(file('crud_defs.txt',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){
 $p=explode('|',$line);
if(count($p)<3)continue;
 $M[$p[0]]=['route'=>$p[1],'fields'=>explode(',',$p[2])];
}
echo count($M)." modules loaded\n\n";
 $ti=<<<'TPL'
@extends('layouts.admin')
@section('title','__M__')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">__M__</h4><a href="{{route('admin.__R__.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add New</a></div>
<div class="sc">
@if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{session('success')}}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
<div class="table-responsive"><table class="table table-hover table-bordered"><thead class="table-dark"><tr><th>#</th>__TH__<th>Actions</th></tr></thead><tbody>@foreach($data as $item)<tr><td>{{$loop->iteration}}</td>__TD__<td class="nowrap"><a href="{{route('admin.__R__.edit',$item->id)}}" class="btn btn-sm btn-warning me-1"><i class="fas fa-edit"></i></a><form method="POST" action="{{route('admin.__R__.destroy',$item->id)}}" style="display:inline" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form></td></tr>@endforeach</tbody></table></div></div>
@endsection
TPL;
 $tc=<<<'TPL'
@extends('layouts.admin')
@section('title','Add __M__')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">Add __M__</h4><a href="{{route('admin.__R__.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a></div>
<div class="sc"><form method="POST" action="{{route('admin.__R__.store')}}">@csrf
__FF__
<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button></form></div>
@endsection
TPL;
 $te=<<<'TPL'
@extends('layouts.admin')
@section('title','Edit __M__')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">Edit __M__</h4><a href="{{route('admin.__R__.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a></div>
<div class="sc"><form method="POST" action="{{route('admin.__R__.update',$data->id)}}">@csrf @method('PUT')
__EF__
<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update</button></form></div>
@endsection
TPL;
 $ts=<<<'TPL'
@extends('layouts.admin')
@section('title','__M__ Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="mb-0">__M__ Details</h4><a href="{{route('admin.__R__.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a></div>
<div class="sc"><table class="table table-bordered">
__SR__
</table><a href="{{route('admin.__R__.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a></div>
@endsection
TPL;
 $n=0;
foreach($M as $model=>$info){
list($route,$fields)=$info;
 $dir="resources/views/admin/$model";
@mkdir($dir,0755,true);
 $th=$td=$ff=$ef=$sr='';
foreach($fields as $f){
 $fn=ucwords(str_replace('_',' ',$f));
 $th.="<th>$fn</th>";
 $td.="<td>{{\$item->$f ?? '-'}}</td>";
 $ff.="<div class=\"mb-3\"><label class=\"form-label\">$fn</label><input type=\"text\" name=\"$f\" class=\"form-control\" value=\"{{old('$f')}}\" required></div>";
 $ef.="<div class=\"mb-3\"><label class=\"form-label\">$fn</label><input type=\"text\" name=\"$f\" class=\"form-control\" value=\"{{\$data->$f ?? ''}}\" required></div>";
 $sr.="<tr><th width=\"200\" class=\"table-light\">$fn</th><td>{{\$data->$f ?? '-'}}</td></tr>";
}
 $r=['__M__'=>$model,'__R__'=>$route,'__TH__'=>$th,'__TD__'=>$td,'__FF__'=>$ff,'__EF__'=>$ef,'__SR__'=>$sr];
file_put_contents("$dir/index.blade.php",str_replace(array_keys($r),$r,$ti));
file_put_contents("$dir/create.blade.php",str_replace(array_keys($r),$r,$tc));
file_put_contents("$dir/edit.blade.php",str_replace(array_keys($r),$r,$te));
file_put_contents("$dir/show.blade.php",str_replace(array_keys($r),$r,$ts));
 $n++;echo "  $model OK\n";
}
echo "\nDONE: $n x 4 = ".($n*4)." views (no warnings)\n";
