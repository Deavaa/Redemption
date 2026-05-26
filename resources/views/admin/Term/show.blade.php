@extends('layouts.admin')
@section('title','Term Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
<h4 class="mb-0">Term Details</h4>
<a href="{{route('admin.terms.index')}}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<div class="sc">
<table class="table table-bordered">
<tr><th width="200" class="table-light">Name</th><td>{!! $data->name ?? '-' !!}</td></tr>
<tr><th width="200" class="table-light">Academic Year</th><td>{{ $data->academicYear->name ?? '-' }}</td></tr>
<tr><th width="200" class="table-light">Start Date</th>
    <td>
        {{ $data->start_date ? \Carbon\Carbon::parse($data->start_date)->format('M d, Y') : '-' }}
        @if($data->start_date)
            <span style="font-size:0.8rem;color:#b45309;background:#fef3c7;border-radius:4px;padding:1px 6px;margin-left:6px;font-weight:500;">
                {{ \App\Helpers\EthiopianDate::format(\Carbon\Carbon::parse($data->start_date)->format('Y-m-d')) }} EC
            </span>
        @endif
    </td>
</tr>
<tr><th width="200" class="table-light">End Date</th>
    <td>
        {{ $data->end_date ? \Carbon\Carbon::parse($data->end_date)->format('M d, Y') : '-' }}
        @if($data->end_date)
            <span style="font-size:0.8rem;color:#b45309;background:#fef3c7;border-radius:4px;padding:1px 6px;margin-left:6px;font-weight:500;">
                {{ \App\Helpers\EthiopianDate::format(\Carbon\Carbon::parse($data->end_date)->format('Y-m-d')) }} EC
            </span>
        @endif
    </td>
</tr>
@if($data->exam_start_date)
<tr><th width="200" class="table-light">Exam Start Date</th>
    <td>
        {{ \Carbon\Carbon::parse($data->exam_start_date)->format('M d, Y') }}
        <span style="font-size:0.8rem;color:#b45309;background:#fef3c7;border-radius:4px;padding:1px 6px;margin-left:6px;font-weight:500;">
            {{ \App\Helpers\EthiopianDate::format(\Carbon\Carbon::parse($data->exam_start_date)->format('Y-m-d')) }} EC
        </span>
    </td>
</tr>
@endif
@if($data->exam_end_date)
<tr><th width="200" class="table-light">Exam End Date</th>
    <td>
        {{ \Carbon\Carbon::parse($data->exam_end_date)->format('M d, Y') }}
        <span style="font-size:0.8rem;color:#b45309;background:#fef3c7;border-radius:4px;padding:1px 6px;margin-left:6px;font-weight:500;">
            {{ \App\Helpers\EthiopianDate::format(\Carbon\Carbon::parse($data->exam_end_date)->format('Y-m-d')) }} EC
        </span>
    </td>
</tr>
@endif
<tr><th width="200" class="table-light">Active</th><td>{{ $data->is_active ? 'Yes' : 'No' }}</td></tr>
</table>
<a href="{{route('admin.terms.edit',$data->id)}}" class="btn btn-warning btn-sm mt-3"><i class="fas fa-edit me-1"></i>Edit</a>
</div>
@endsection
