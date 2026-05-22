@extends('layouts.admin')
@section('title', 'Department Detail')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.departments.index') }}">Departments</a></li>
                    <li class="active">{{ $department->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            @if(in_array(Auth::user()->role, ['admin', 'super_admin']))
            <a href="{{ route('admin.departments.edit', $department->id) }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-pen"></i><span>Edit</span>
            </a>
            @endif
            <a href="{{ route('admin.departments.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i><span>Back</span>
            </a>
        </div>
    </div>

    <div class="modern-card" style="margin-bottom:1.5rem;">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">{{ $department->name }}</h2>
                @if($department->is_active)
                <span class="modern-badge modern-badge-success"><i class="fas fa-check-circle"></i> Active</span>
                @else
                <span class="modern-badge modern-badge-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                @endif
            </div>
        </div>
        <div style="padding:1.25rem 1.5rem;">
            <div class="eq-info-row">
                <span class="eq-info-label">Department Head</span>
                <span class="eq-info-value">{{ $department->headUser->name ?? 'Not assigned' }}</span>
            </div>
            @if($department->description)
            <div class="eq-info-row">
                <span class="eq-info-label">Description</span>
                <span class="eq-info-value">{{ $department->description }}</span>
            </div>
            @endif
            <div class="eq-info-row">
                <span class="eq-info-label">Teachers</span>
                <span class="eq-info-value">{{ $department->teachers->count() }} teachers</span>
            </div>
        </div>
    </div>

    {{-- Teachers in Department --}}
    @if($department->teachers->count() > 0)
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">Teachers in this Department</h2>
            </div>
        </div>
        <div class="modern-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Qualification</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($department->teachers as $teacher)
                    <tr>
                        <td><div class="modern-cell-title">{{ $teacher->full_name }}</div></td>
                        <td>{{ $teacher->qualification ?? '-' }}</td>
                        <td><span class="modern-badge {{ $teacher->status === 'active' ? 'modern-badge-success' : 'modern-badge-danger' }}">{{ ucfirst($teacher->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
.modern-page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.75rem; flex-wrap:wrap; gap:1rem; }
.modern-page-header-left { flex:1; }
.modern-breadcrumb ol { display:flex; list-style:none; padding:0; margin:0 0 .5rem; gap:.5rem; font-size:.8rem; align-items:center; }
.modern-breadcrumb li { color:#adb5bd; }
.modern-breadcrumb li a { color:#6c757d; text-decoration:none; }
.modern-breadcrumb li+li::before { content:'/'; margin-right:.5rem; color:#dee2e6; }
.modern-breadcrumb li.active { color:#4361ee; font-weight:500; }
.modern-card { background:#fff; border-radius:14px; box-shadow:0 1px 3px rgba(0,0,0,.06); border:1px solid #f0f0f0; overflow:hidden; }
.modern-card-header { display:flex; justify-content:space-between; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid #f0f0f0; }
.modern-card-header-left { display:flex; align-items:center; gap:.75rem; }
.modern-card-title { font-size:1.1rem; font-weight:700; color:#1a1a2e; margin:0; }
.modern-badge { display:inline-flex; align-items:center; gap:.3rem; padding:.25rem .65rem; border-radius:50px; font-size:.75rem; font-weight:600; }
.modern-badge-success { background:#ecfdf5; color:#059669; }
.modern-badge-danger { background:#fef2f2; color:#dc2626; }
.eq-info-row { display:flex; justify-content:space-between; align-items:center; padding:.6rem 0; border-bottom:1px solid #f3f4f6; }
.eq-info-label { font-size:.85rem; color:#6b7280; font-weight:500; }
.eq-info-value { font-size:.88rem; color:#1a1a2e; font-weight:600; }
.modern-table-wrapper { overflow-x:auto; }
.modern-table { width:100%; border-collapse:collapse; font-size:.9rem; }
.modern-table thead th { background:#f9fafb; padding:.85rem 1rem; text-align:left; font-weight:600; font-size:.78rem; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; border-bottom:2px solid #e5e7eb; }
.modern-table tbody tr { border-bottom:1px solid #f3f4f6; }
.modern-table tbody tr:hover { background:#f8f9ff; }
.modern-table td { padding:.9rem 1rem; vertical-align:middle; }
.modern-cell-title { font-weight:600; color:#1a1a2e; }
.btn-modern { display:inline-flex; align-items:center; gap:.5rem; padding:.65rem 1.35rem; border-radius:10px; font-weight:600; font-size:.9rem; text-decoration:none; border:none; cursor:pointer; transition:all .25s; }
.btn-modern-outline { background:transparent; color:#6b7280; border:1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color:#4361ee; color:#4361ee; }
</style>
@endpush
@endsection
