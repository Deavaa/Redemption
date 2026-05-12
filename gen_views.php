<?php
echo "=== Clean CRUD View Generator ===\n";
 $M=[];
 $raw=require 'modules.php';
foreach($raw as $r){
  $M[$r[0]]=['route'=>$r[1],'fields'=>explode(',',$r[2])];
}
echo count($M)." modules loaded\n\n";
foreach($M as $model=>$info){
  $route=$info['route'];
  $fields=$info['fields'];
  $dir="resources/views/admin/$model";
  @mkdir($dir,0755,true);
  $th=''; $td=''; $ff=''; $ef=''; $sr='';
  foreach($fields as $f){
    $fn=ucwords(str_replace('_',' ',$f));
    $th.="<th>$fn</th>";
    $td.="<td>{!! \$item->$f ?? '-' !!}</td>";
    $ff.="<div class=\"mb-3\"><label class=\"form-label\">$fn</label><input type=\"text\" name=\"$f\" class=\"form-control\" value=\"{{old('$f')}}\" required></div>\n";
    $ef.="<div class=\"mb-3\"><label class=\"form-label\">$fn</label><input type=\"text\" name=\"$f\" class=\"form-control\" value=\"{{\$data->$f ?? ''}}\" required></div>\n";
    $sr.="<tr><th width=\"200\" class=\"table-light\">$fn</th><td>{!! \$data->$f ?? '-' !!}</td></tr>\n";
  }
  // INDEX
  $vi="@extends('layouts.admin')\n@section('title','$model')\n@section('content')\n<div class=\"d-flex justify-content-between align-items-center mb-3\">\n<h4 class=\"mb-0\">$model</h4>\n<a href=\"{{route('admin.$route.create')}}\" class=\"btn btn-primary btn-sm\"><i class=\"fas fa-plus me-1\"></i>Add New</a>\n</div>\n<div class=\"sc\">\n@if(session('success'))\n<div class=\"alert alert-success alert-dismissible fade show\">\n{{session('success')}}<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>\n</div>\n@endif\n<div class=\"table-responsive\">\n<table class=\"table table-hover table-bordered\">\n<thead class=\"table-dark\"><tr><th>#</th>$th<th>Actions</th></tr></thead>\n<tbody>\n@foreach(\$data as \$item)\n<tr><td>{{\$loop->iteration}}</td>$td\n<td class=\"nowrap\">\n<a href=\"{{route('admin.$route.edit',\$item->id)}}\" class=\"btn btn-sm btn-warning me-1\" title=\"Edit\"><i class=\"fas fa-edit\"></i></a>\n<form method=\"POST\" action=\"{{route('admin.$route.destroy',\$item->id)}}\" style=\"display:inline\" onsubmit=\"return confirm('Delete?')\">\n@csrf @method('DELETE')\n<button type=\"submit\" class=\"btn btn-sm btn-danger\" title=\"Delete\"><i class=\"fas fa-trash\"></i></button>\n</form></td></tr>\n@endforeach\n</tbody></table></div></div>\n@endsection\n";
  // CREATE
  $vc="@extends('layouts.admin')\n@section('title','Add $model')\n@section('content')\n<div class=\"d-flex justify-content-between align-items-center mb-3\">\n<h4 class=\"mb-0\">Add $model</h4>\n<a href=\"{{route('admin.$route.index')}}\" class=\"btn btn-secondary btn-sm\"><i class=\"fas fa-arrow-left me-1\"></i>Back</a>\n</div>\n<div class=\"sc\">\n<form method=\"POST\" action=\"{{route('admin.$route.store')}}\">\n@csrf\n$ff\n<button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-save me-1\"></i>Save</button>\n</form></div>\n@endsection\n";
  // EDIT
  $ve="@extends('layouts.admin')\n@section('title','Edit $model')\n@section('content')\n<div class=\"d-flex justify-content-between align-items-center mb-3\">\n<h4 class=\"mb-0\">Edit $model</h4>\n<a href=\"{{route('admin.$route.index')}}\" class=\"btn btn-secondary btn-sm\"><i class=\"fas fa-arrow-left me-1\"></i>Back</a>\n</div>\n<div class=\"sc\">\n<form method=\"POST\" action=\"{{route('admin.$route.update',\$data->id)}}\">\n@csrf @method('PUT')\n$ef\n<button type=\"submit\" class=\"btn btn-primary\"><i class=\"fas fa-save me-1\"></i>Update</button>\n</form></div>\n@endsection\n";
  // SHOW
  $vs="@extends('layouts.admin')\n@section('title','$model Details')\n@section('content')\n<div class=\"d-flex justify-content-between align-items-center mb-3\">\n<h4 class=\"mb-0\">$model Details</h4>\n<a href=\"{{route('admin.$route.index')}}\" class=\"btn btn-secondary btn-sm\"><i class=\"fas fa-arrow-left me-1\"></i>Back</a>\n</div>\n<div class=\"sc\">\n<table class=\"table table-bordered\">\n$sr</table>\n<a href=\"{{route('admin.$route.edit',\$data->id)}}\" class=\"btn btn-warning btn-sm mt-3\"><i class=\"fas fa-edit me-1\"></i>Edit</a>\n</div>\n@endsection\n";
  file_put_contents("$dir/index.blade.php",$vi);
  file_put_contents("$dir/create.blade.php",$vc);
  file_put_contents("$dir/edit.blade.php",$ve);
  file_put_contents("$dir/show.blade.php",$vs);
  echo "  $model OK\n";
}
echo "\nDONE: ".count($M)." x 4 = ".(count($M)*4)." views\n";
