@extends('layouts.admin')
@section('title','Subject Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">{{ __('Subject Details') }}</h4>
<a href="{{route('admin.subjects.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>{{ __('Back') }}</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">{{ __('Name') }}</th><td>{{ $subject->name ?? '-' }}</td></tr>
<tr><th width="200" class="table-light">{{ __('Code') }}</th><td>{{ $subject->code ?? '-' }}</td></tr>
<tr><th width="200" class="table-light">{{ __('Type') }}</th><td><span class="badge bg-{{ $subject->type == 'compulsory' ? 'primary' : ($subject->type == 'elective' ? 'success' : 'info') }}">{{ ucfirst($subject->type ?? '-') }}</span></td></tr>
<tr><th width="200" class="table-light">{{ __('Priority') }}</th><td>{{ $subject->priority ?? '0' }}</td></tr>
<tr><th width="200" class="table-light">{{ __('Status') }}</th><td><span class="badge bg-{{ $subject->is_active ? 'success' : 'secondary' }}">{{ $subject->is_active ? __('Active') : __('Inactive') }}</span></td></tr>
<tr><th width="200" class="table-light">{{ __('Description') }}</th><td>{{ $subject->description ?? '-' }}</td></tr>
</table>

@if($subject->relationLoaded('assignments') && $subject->assignments->count() > 0)
<h5 class="mt-4 mb-2">{{ __('Assigned Classes') }}</h5>
<table class="table table-bordered table-sm">
<thead class="table-light">
<tr><th>{{ __('Class') }}</th><th>{{ __('Section') }}</th><th>{{ __('Teacher') }}</th></tr>
</thead>
<tbody>
@foreach($subject->assignments as $assignment)
<tr>
<td>{{ $assignment->classroom->name ?? '-' }}</td>
<td>{{ $assignment->section->name ?? '-' }}</td>
<td>{{ $assignment->teacher->name ?? '-' }}</td>
</tr>
@endforeach
</tbody>
</table>
@endif

<a href="{{route('admin.subjects.edit', $subject->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>{{ __('Edit') }}</a>
</div>
@endsection
