@extends('layouts.admin')
@section('page-title', 'Student Management')
@section('body-class', 'page-admin-students')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bi bi-mortarboard me-2"></i>Students</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-gold">Students</li>
            </ol></nav>
        </div>
        <a href="{{ route('admin.students.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Add Student</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #c9a84c !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-primary mb-0">{{ $totalStudents ?? 0 }}</h3>
                    <small class="text-muted">Total Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #28a745 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-success mb-0">{{ $activeStudents ?? 0 }}</h3>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #17a2b8 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-info mb-0">{{ $inactiveStudents ?? 0 }}</h3>
                    <small class="text-muted">Inactive</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-top:3px solid #dc3545 !important">
                <div class="card-body text-center">
                    <h3 class="fw-bold text-danger mb-0">{{ $students->total() }}</h3>
                    <small class="text-muted">Showing</small>
                </div>
            </div>
        </div>
    </div>

    @if($students->count() > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-semibold mb-0"><i class="bi bi-people me-2"></i>All Students</h6>
                <span class="badge bg-light text-dark">{{ $students->count() }} student(s)</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:40px">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Roll No</th>
                            <th>Status</th>
                            <th style="width:100px" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td class="ps-3 text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold">{{ $student->first_name }} {{ $student->last_name }}</span></td>
                            <td><span class="text-muted">{{ $student->email ?? '-' }}</span></td>
                            <td>{{ $student->phone ?? '-' }}</td>
                            <td>{{ $student->classroom?->name ?? '-' }}</td>
                            <td>{{ $student->section?->name ?? '-' }}</td>
                            <td>{{ $student->roll_number ?? '-' }}</td>
                            <td>
                                @php
                                    $statusColor = match($student->status ?? '') {
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'graduated' => 'info',
                                        'transferred' => 'warning',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($student->status ?? 'N/A') }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit"><i class="bi bi-pencil-square fs-5"></i></a>
                                <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" style="display:inline" onsubmit="return confirm('Delete this student?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Delete"><i class="bi bi-trash fs-5"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($students->hasPages())
        <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} of {{ $students->total() }}</small>
            {{ $students->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-person-plus fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No Students Found</h5>
            <p class="text-muted">Start by adding your first student.</p>
            <a href="{{ route('admin.students.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg me-1"></i>Add Student</a>
        </div>
    </div>
    @endif
</div>
@endsection
