@extends('layouts.admin')
@section('title', 'Classroom Details')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Classroom Details</h4>
        <a href="{{ route('admin.classrooms.index') }}" class="btn btn-secondary btn-sm"><i
                class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="sc">
        <table class="table table-bordered">
            <tr>
                <th width="200" class="table-light">Name</th>
                <td>{!! $data->name ?? '-' !!}</td>
            </tr>
            <tr>
                <th width="200" class="table-light">Section</th>
                <td>{!! $data->section ?? '-' !!}</td>
            </tr>
            <tr>
                <th width="200" class="table-light">Academic Year Id</th>
                <td>{!! $data->academic_year_id ?? '-' !!}</td>
            </tr>
            <tr>
                <th width="200" class="table-light">Teacher Id</th>
                <td>{!! $data->teacher_id ?? '-' !!}</td>
            </tr>
            <tr>
                <th width="200" class="table-light">Capacity</th>
                <td>{!! $data->capacity ?? '-' !!}</td>
            </tr>
        </table>
        <a href="{{ route('admin.classrooms.edit', $data->id) }}" class="btn btn-warning btn-sm mt-3"><i
                class="fas fa-edit me-1"></i>Edit</a>
    </div>
@endsection
