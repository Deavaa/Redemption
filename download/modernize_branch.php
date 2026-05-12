<?php
/**
 * Redemption - Branch Modernization Script
 * Run this in your project root: php modernize_branch.php
 */

$base = getcwd();

// ============================================================
// 1. Branch Index Page
// ============================================================
$file = "$base/resources/views/admin/Branch/index.blade.php";
$dir = dirname($file);
if (!is_dir($dir)) mkdir($dir, 0755, true);

file_put_contents($file, <<<'BLADE'
@extends('layouts.admin')
@section('title', 'Branches')

@section('content')
<div class="modern-page">
    {{-- Page Header --}}
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Website</a></li>
                    <li class="active">Branches</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Branches</h1>
            <p class="modern-page-subtitle">Manage school branches and locations</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.branches.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i>
                <span>Add Branch</span>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->total() }}</span>
                <span class="modern-stat-label">Total Branches</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->where('is_active', true)->count() }}</span>
                <span class="modern-stat-label">Active</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-building"></i>
            </div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $data->where('is_headquarters', true)->count() }}</span>
                <span class="modern-stat-label">Headquarters</span>
            </div>
        </div>
    </div>

    {{-- Branches Table Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">All Branches</h2>
                <span class="modern-badge modern-badge-light">{{ $data->total() }} records</span>
            </div>
            <div class="modern-card-header-right">
                <div class="modern-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="branchSearch" placeholder="Search branches..." onkeyup="filterTable()">
                </div>
            </div>
        </div>
        <div class="modern-card-body">
            @if(session('success'))
                <div class="modern-alert modern-alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if($data->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table" id="branchTable">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Branch Name</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th class="th-center">Status</th>
                            <th class="th-center">HQ</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr class="modern-table-row">
                            <td class="td-narrow">
                                <span class="modern-row-number">{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</span>
                            </td>
                            <td>
                                <div class="modern-cell-title">{{ $item->name ?? '-' }}</div>
                                @if($item->email)
                                    <div class="modern-cell-sub">{{ $item->email }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $item->address ?? '-' }}</div>
                            </td>
                            <td>
                                @if($item->phone)
                                    <div class="modern-cell-contact">
                                        <i class="fas fa-phone"></i> {{ $item->phone }}
                                    </div>
                                @else
                                    <span class="modern-cell-muted">-</span>
                                @endif
                            </td>
                            <td class="td-center">
                                @if($item->is_active)
                                    <span class="modern-badge modern-badge-success">Active</span>
                                @else
                                    <span class="modern-badge modern-badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td class="td-center">
                                @if($item->is_headquarters)
                                    <span class="modern-badge modern-badge-gold">HQ</span>
                                @else
                                    <span class="modern-cell-muted">-</span>
                                @endif
                            </td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    <a href="{{ route('admin.branches.show', $item->id) }}" class="modern-btn-icon modern-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.branches.edit', $item->id) }}" class="modern-btn-icon modern-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.branches.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this branch?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="modern-btn-icon modern-btn-delete" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($data->hasPages())
            <div class="modern-pagination-wrapper">
                {{ $data->withQueryString()->links() }}
            </div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>No Branches Yet</h3>
                <p>Get started by adding your first school branch.</p>
                <a href="{{ route('admin.branches.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> Add Branch
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.modern-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.modern-page-header-left { flex: 1; }
.modern-page-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.modern-page-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.modern-stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.75rem; }
.modern-stat-card { background: #fff; border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; transition: transform 0.2s, box-shadow 0.2s; }
.modern-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.modern-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.modern-stat-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-stat-icon-green { background: #ecfdf5; color: #10b981; }
.modern-stat-icon-gold { background: #fefce8; color: #d97706; }
.modern-stat-info { display: flex; flex-direction: column; }
.modern-stat-value { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; line-height: 1.2; }
.modern-stat-label { font-size: 0.8rem; color: #6c757d; font-weight: 500; }
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; margin-bottom: 1.5rem; }
.modern-card-header { display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f0f0; flex-wrap: wrap; gap: 1rem; }
.modern-card-header-left { display: flex; align-items: center; gap: 0.75rem; }
.modern-card-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-card-body { padding: 0; }
.modern-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.65rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.3px; }
.modern-badge-light { background: #f3f4f6; color: #6b7280; }
.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-gold { background: #fefce8; color: #b45309; }
.modern-search-box { position: relative; display: flex; align-items: center; }
.modern-search-box i { position: absolute; left: 12px; color: #adb5bd; font-size: 0.85rem; }
.modern-search-box input { border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.55rem 0.75rem 0.55rem 2.25rem; font-size: 0.875rem; width: 220px; transition: all 0.2s; background: #f9fafb; color: #374151; }
.modern-search-box input:focus { outline: none; border-color: #4361ee; background: #fff; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.modern-table-wrapper { overflow-x: auto; }
.modern-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.modern-table thead th { background: #f9fafb; padding: 0.85rem 1rem; text-align: left; font-weight: 600; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 2px solid #e5e7eb; white-space: nowrap; }
.modern-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background 0.15s; }
.modern-table tbody tr:hover { background: #f8f9ff; }
.modern-table td { padding: 0.9rem 1rem; vertical-align: middle; color: #374151; }
.th-center, .td-center { text-align: center !important; }
.th-actions, .td-actions { text-align: right !important; }
.th-narrow, .td-narrow { width: 50px; }
.modern-row-number { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 8px; background: #f3f4f6; color: #6b7280; font-weight: 600; font-size: 0.8rem; }
.modern-cell-title { font-weight: 600; color: #1a1a2e; margin-bottom: 2px; }
.modern-cell-sub { font-size: 0.8rem; color: #9ca3af; }
.modern-cell-text { color: #4b5563; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.modern-cell-contact { display: inline-flex; align-items: center; gap: 0.4rem; color: #4b5563; font-size: 0.88rem; }
.modern-cell-contact i { color: #4361ee; font-size: 0.75rem; }
.modern-cell-muted { color: #d1d5db; }
.modern-action-group { display: inline-flex; gap: 0.35rem; }
.modern-btn-icon { width: 34px; height: 34px; border-radius: 9px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; font-size: 0.82rem; text-decoration: none; }
.modern-btn-view { background: #eef2ff; color: #4361ee; }
.modern-btn-view:hover { background: #4361ee; color: #fff; transform: translateY(-1px); }
.modern-btn-edit { background: #fefce8; color: #d97706; }
.modern-btn-edit:hover { background: #d97706; color: #fff; transform: translateY(-1px); }
.modern-btn-delete { background: #fef2f2; color: #dc2626; }
.modern-btn-delete:hover { background: #dc2626; color: #fff; transform: translateY(-1px); }
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.modern-alert { display: flex; align-items: center; gap: 0.65rem; padding: 0.85rem 1.25rem; margin: 1rem 1.5rem; border-radius: 10px; font-size: 0.88rem; font-weight: 500; animation: fadeSlideIn 0.3s ease; }
.modern-alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.modern-alert-close { margin-left: auto; background: none; border: none; cursor: pointer; color: inherit; opacity: 0.6; transition: opacity 0.2s; }
.modern-alert-close:hover { opacity: 1; }
.modern-empty-state { text-align: center; padding: 4rem 2rem; }
.modern-empty-icon { width: 80px; height: 80px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; color: #d1d5db; margin-bottom: 1.25rem; }
.modern-empty-state h3 { font-size: 1.2rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.5rem; }
.modern-empty-state p { color: #9ca3af; font-size: 0.9rem; margin: 0 0 1.5rem; }
.modern-pagination-wrapper { padding: 1rem 1.5rem; border-top: 1px solid #f0f0f0; display: flex; justify-content: center; }
@media (max-width: 768px) { .modern-page-header { flex-direction: column; align-items: stretch; } .modern-page-title { font-size: 1.35rem; } .modern-stats-row { grid-template-columns: 1fr; } .modern-card-header { flex-direction: column; align-items: stretch; } .modern-search-box input { width: 100%; } .modern-table { font-size: 0.82rem; } .modern-cell-text { max-width: 150px; } }
</style>
@endpush

@push('scripts')
<script>
function filterTable() {
    const input = document.getElementById('branchSearch');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('branchTable');
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
@endpush
@endsection
BLADE
);

// ============================================================
// 2. Branch Create Page
// ============================================================
$file = "$base/resources/views/admin/Branch/create.blade.php";
file_put_contents($file, <<<'BLADE'
@extends('layouts.admin')
@section('title', 'Add Branch')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.branches.index') }}">Branches</a></li>
                    <li class="active">Add New</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Add New Branch</h1>
            <p class="modern-page-subtitle">Create a new school branch location</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.branches.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.branches.store') }}">
            @csrf

            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Basic Information</h3>
                        <p class="modern-form-section-desc">Enter the branch name and contact details</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="name">Branch Name <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <input type="text" name="name" id="name" class="modern-input {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name') }}" placeholder="e.g. Main Campus" required autofocus>
                            </div>
                            @error('name') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="phone">Phone <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-phone modern-input-icon"></i>
                                <input type="tel" name="phone" id="phone" class="modern-input {{ $errors->has('phone') ? 'is-invalid' : '' }}" value="{{ old('phone') }}" placeholder="e.g. +251 11 234 5678" required>
                            </div>
                            @error('phone') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="email">Email <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-envelope modern-input-icon"></i>
                                <input type="email" name="email" id="email" class="modern-input {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}" placeholder="e.g. branch@redemption.edu" required>
                            </div>
                            @error('email') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="address">Address <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-map-marker-alt modern-input-icon"></i>
                                <textarea name="address" id="address" class="modern-input modern-textarea {{ $errors->has('address') ? 'is-invalid' : '' }}" placeholder="e.g. Bole Road, Addis Ababa, Ethiopia" rows="3" required>{{ old('address') }}</textarea>
                            </div>
                            @error('address') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-map"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Location & Settings</h3>
                        <p class="modern-form-section-desc">GPS coordinates and branch status</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="gps_lat">GPS Latitude <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-crosshairs modern-input-icon"></i>
                                <input type="number" step="any" name="gps_lat" id="gps_lat" class="modern-input" value="{{ old('gps_lat') }}" placeholder="e.g. 9.0222">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="gps_lng">GPS Longitude <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-crosshairs modern-input-icon"></i>
                                <input type="number" step="any" name="gps_lng" id="gps_lng" class="modern-input" value="{{ old('gps_lng') }}" placeholder="e.g. 38.7469">
                            </div>
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="map_embed_url">Google Map Embed URL <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-link modern-input-icon"></i>
                                <input type="url" name="map_embed_url" id="map_embed_url" class="modern-input" value="{{ old('map_embed_url') }}" placeholder="Paste Google Maps embed URL here">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="principal_id">Principal <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user-tie modern-input-icon"></i>
                                <select name="principal_id" id="principal_id" class="modern-input modern-select">
                                    <option value="">-- Select Principal --</option>
                                    @foreach(\App\Models\Teacher::orderBy('first_name')->get() as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('principal_id') == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->first_name }} {{ $teacher->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="order">Display Order <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-sort modern-input-icon"></i>
                                <input type="number" name="order" id="order" class="modern-input" value="{{ old('order', 0) }}" placeholder="0">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <div class="modern-toggle-wrapper">
                                <label class="modern-toggle">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <span class="modern-toggle-slider"></span>
                                </label>
                                <div class="modern-toggle-info">
                                    <span class="modern-toggle-title">Active Status</span>
                                    <span class="modern-toggle-desc">Enable this branch for public display</span>
                                </div>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <div class="modern-toggle-wrapper">
                                <label class="modern-toggle">
                                    <input type="checkbox" name="is_headquarters" value="1" {{ old('is_headquarters') ? 'checked' : '' }}>
                                    <span class="modern-toggle-slider"></span>
                                </label>
                                <div class="modern-toggle-info">
                                    <span class="modern-toggle-title">Headquarters</span>
                                    <span class="modern-toggle-desc">Mark this as the main campus</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-form-actions">
                <a href="{{ route('admin.branches.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-check"></i><span>Create Branch</span></button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.modern-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.modern-page-header-left { flex: 1; }
.modern-page-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.modern-page-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
.modern-form-section { border-bottom: 1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom: none; }
.modern-form-section-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem 2rem 0.75rem; }
.modern-form-section-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }
.modern-form-section-title { font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-form-section-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.15rem 0 0; }
.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }
.modern-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
.modern-form-span-2 { grid-column: span 2; }
.modern-form-group { display: flex; flex-direction: column; }
.modern-form-label { font-weight: 600; color: #374151; margin-bottom: 0.45rem; font-size: 0.88rem; }
.modern-form-label small { font-weight: 400; color: #9ca3af; font-size: 0.78rem; }
.modern-required { color: #ef4444; font-weight: 700; }
.modern-input-wrapper { position: relative; }
.modern-input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.85rem; pointer-events: none; z-index: 1; }
.modern-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.7rem 0.9rem 0.7rem 2.5rem; font-size: 0.9rem; color: #1a1a2e; background: #fff; transition: all 0.2s; }
.modern-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.modern-input::placeholder { color: #c5c9d2; }
.modern-input.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }
.modern-textarea { resize: vertical; min-height: 80px; }
.modern-select { appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25rem; padding-right: 2.5rem; }
.modern-form-error { display: block; color: #ef4444; font-size: 0.8rem; margin-top: 0.35rem; font-weight: 500; }
.modern-toggle-wrapper { display: flex; align-items: center; gap: 0.85rem; padding-top: 0.5rem; }
.modern-toggle { position: relative; display: inline-block; width: 48px; height: 26px; flex-shrink: 0; }
.modern-toggle input { opacity: 0; width: 0; height: 0; }
.modern-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #d1d5db; border-radius: 50px; transition: 0.3s; }
.modern-toggle-slider::before { content: ''; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
.modern-toggle input:checked + .modern-toggle-slider { background: #4361ee; }
.modern-toggle input:checked + .modern-toggle-slider::before { transform: translateX(22px); }
.modern-toggle-info { display: flex; flex-direction: column; }
.modern-toggle-title { font-weight: 600; color: #374151; font-size: 0.88rem; }
.modern-toggle-desc { font-size: 0.78rem; color: #9ca3af; }
.modern-form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.5rem 2rem; border-top: 1px solid #f0f0f0; background: #fafbfc; }
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.btn-modern-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color: #4361ee; color: #4361ee; background: #f8f9ff; }
.btn-modern-ghost { background: transparent; color: #6b7280; padding: 0.65rem 1rem; }
.btn-modern-ghost:hover { color: #1a1a2e; background: #f3f4f6; }
@media (max-width: 768px) { .modern-page-header { flex-direction: column; align-items: stretch; } .modern-page-title { font-size: 1.35rem; } .modern-form-grid { grid-template-columns: 1fr; } .modern-form-span-2 { grid-column: span 1; } .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; } .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; } .modern-form-actions { padding: 1rem 1.25rem; flex-direction: column; } .btn-modern { justify-content: center; width: 100%; } }
</style>
@endpush
@endsection
BLADE
);

// ============================================================
// 3. Branch Edit Page
// ============================================================
$file = "$base/resources/views/admin/Branch/edit.blade.php";
file_put_contents($file, <<<'BLADE'
@extends('layouts.admin')
@section('title', 'Edit Branch')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.branches.index') }}">Branches</a></li>
                    <li class="active">Edit</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">Edit Branch</h1>
            <p class="modern-page-subtitle">Update branch information for <strong>{{ $item->name }}</strong></p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.branches.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    <div class="modern-card">
        <form method="POST" action="{{ route('admin.branches.update', $item->id) }}">
            @csrf @method('PUT')

            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-blue">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Basic Information</h3>
                        <p class="modern-form-section-desc">Update the branch name and contact details</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="name">Branch Name <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-building modern-input-icon"></i>
                                <input type="text" name="name" id="name" class="modern-input {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name', $item->name) }}" placeholder="e.g. Main Campus" required autofocus>
                            </div>
                            @error('name') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="phone">Phone <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-phone modern-input-icon"></i>
                                <input type="tel" name="phone" id="phone" class="modern-input {{ $errors->has('phone') ? 'is-invalid' : '' }}" value="{{ old('phone', $item->phone) }}" placeholder="e.g. +251 11 234 5678" required>
                            </div>
                            @error('phone') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="email">Email <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-envelope modern-input-icon"></i>
                                <input type="email" name="email" id="email" class="modern-input {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email', $item->email) }}" placeholder="e.g. branch@redemption.edu" required>
                            </div>
                            @error('email') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="address">Address <span class="modern-required">*</span></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-map-marker-alt modern-input-icon"></i>
                                <textarea name="address" id="address" class="modern-input modern-textarea {{ $errors->has('address') ? 'is-invalid' : '' }}" placeholder="e.g. Bole Road, Addis Ababa, Ethiopia" rows="3" required>{{ old('address', $item->address) }}</textarea>
                            </div>
                            @error('address') <span class="modern-form-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-form-section">
                <div class="modern-form-section-header">
                    <div class="modern-form-section-icon modern-form-section-icon-green">
                        <i class="fas fa-map"></i>
                    </div>
                    <div>
                        <h3 class="modern-form-section-title">Location & Settings</h3>
                        <p class="modern-form-section-desc">GPS coordinates and branch status</p>
                    </div>
                </div>
                <div class="modern-form-section-body">
                    <div class="modern-form-grid">
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="gps_lat">GPS Latitude <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-crosshairs modern-input-icon"></i>
                                <input type="number" step="any" name="gps_lat" id="gps_lat" class="modern-input" value="{{ old('gps_lat', $item->gps_lat) }}" placeholder="e.g. 9.0222">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="gps_lng">GPS Longitude <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-crosshairs modern-input-icon"></i>
                                <input type="number" step="any" name="gps_lng" id="gps_lng" class="modern-input" value="{{ old('gps_lng', $item->gps_lng) }}" placeholder="e.g. 38.7469">
                            </div>
                        </div>
                        <div class="modern-form-group modern-form-span-2">
                            <label class="modern-form-label" for="map_embed_url">Google Map Embed URL <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-link modern-input-icon"></i>
                                <input type="url" name="map_embed_url" id="map_embed_url" class="modern-input" value="{{ old('map_embed_url', $item->map_embed_url) }}" placeholder="Paste Google Maps embed URL here">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="principal_id">Principal <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-user-tie modern-input-icon"></i>
                                <select name="principal_id" id="principal_id" class="modern-input modern-select">
                                    <option value="">-- Select Principal --</option>
                                    @foreach(\App\Models\Teacher::orderBy('first_name')->get() as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('principal_id', $item->principal_id) == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->first_name }} {{ $teacher->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <label class="modern-form-label" for="order">Display Order <small>(optional)</small></label>
                            <div class="modern-input-wrapper">
                                <i class="fas fa-sort modern-input-icon"></i>
                                <input type="number" name="order" id="order" class="modern-input" value="{{ old('order', $item->order) }}" placeholder="0">
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <div class="modern-toggle-wrapper">
                                <label class="modern-toggle">
                                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                    <span class="modern-toggle-slider"></span>
                                </label>
                                <div class="modern-toggle-info">
                                    <span class="modern-toggle-title">Active Status</span>
                                    <span class="modern-toggle-desc">Enable this branch for public display</span>
                                </div>
                            </div>
                        </div>
                        <div class="modern-form-group">
                            <div class="modern-toggle-wrapper">
                                <label class="modern-toggle">
                                    <input type="checkbox" name="is_headquarters" value="1" {{ old('is_headquarters', $item->is_headquarters) ? 'checked' : '' }}>
                                    <span class="modern-toggle-slider"></span>
                                </label>
                                <div class="modern-toggle-info">
                                    <span class="modern-toggle-title">Headquarters</span>
                                    <span class="modern-toggle-desc">Mark this as the main campus</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modern-form-actions">
                <a href="{{ route('admin.branches.index') }}" class="btn-modern btn-modern-ghost">Cancel</a>
                <button type="submit" class="btn-modern btn-modern-primary"><i class="fas fa-save"></i><span>Save Changes</span></button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.modern-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.modern-page-header-left { flex: 1; }
.modern-page-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.modern-page-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }
.modern-page-subtitle strong { color: #4361ee; }
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
.modern-form-section { border-bottom: 1px solid #f0f0f0; }
.modern-form-section:last-of-type { border-bottom: none; }
.modern-form-section-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem 2rem 0.75rem; }
.modern-form-section-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.modern-form-section-icon-blue { background: #eef2ff; color: #4361ee; }
.modern-form-section-icon-green { background: #ecfdf5; color: #10b981; }
.modern-form-section-title { font-size: 1.05rem; font-weight: 700; color: #1a1a2e; margin: 0; }
.modern-form-section-desc { font-size: 0.82rem; color: #9ca3af; margin: 0.15rem 0 0; }
.modern-form-section-body { padding: 1.25rem 2rem 1.75rem; }
.modern-form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
.modern-form-span-2 { grid-column: span 2; }
.modern-form-group { display: flex; flex-direction: column; }
.modern-form-label { font-weight: 600; color: #374151; margin-bottom: 0.45rem; font-size: 0.88rem; }
.modern-form-label small { font-weight: 400; color: #9ca3af; font-size: 0.78rem; }
.modern-required { color: #ef4444; font-weight: 700; }
.modern-input-wrapper { position: relative; }
.modern-input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.85rem; pointer-events: none; z-index: 1; }
.modern-input { width: 100%; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 0.7rem 0.9rem 0.7rem 2.5rem; font-size: 0.9rem; color: #1a1a2e; background: #fff; transition: all 0.2s; }
.modern-input:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 3px rgba(67,97,238,0.1); }
.modern-input::placeholder { color: #c5c9d2; }
.modern-input.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }
.modern-textarea { resize: vertical; min-height: 80px; }
.modern-select { appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25rem; padding-right: 2.5rem; }
.modern-form-error { display: block; color: #ef4444; font-size: 0.8rem; margin-top: 0.35rem; font-weight: 500; }
.modern-toggle-wrapper { display: flex; align-items: center; gap: 0.85rem; padding-top: 0.5rem; }
.modern-toggle { position: relative; display: inline-block; width: 48px; height: 26px; flex-shrink: 0; }
.modern-toggle input { opacity: 0; width: 0; height: 0; }
.modern-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #d1d5db; border-radius: 50px; transition: 0.3s; }
.modern-toggle-slider::before { content: ''; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
.modern-toggle input:checked + .modern-toggle-slider { background: #4361ee; }
.modern-toggle input:checked + .modern-toggle-slider::before { transform: translateX(22px); }
.modern-toggle-info { display: flex; flex-direction: column; }
.modern-toggle-title { font-weight: 600; color: #374151; font-size: 0.88rem; }
.modern-toggle-desc { font-size: 0.78rem; color: #9ca3af; }
.modern-form-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1.5rem 2rem; border-top: 1px solid #f0f0f0; background: #fafbfc; }
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.btn-modern-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color: #4361ee; color: #4361ee; background: #f8f9ff; }
.btn-modern-ghost { background: transparent; color: #6b7280; padding: 0.65rem 1rem; }
.btn-modern-ghost:hover { color: #1a1a2e; background: #f3f4f6; }
@media (max-width: 768px) { .modern-page-header { flex-direction: column; align-items: stretch; } .modern-page-title { font-size: 1.35rem; } .modern-form-grid { grid-template-columns: 1fr; } .modern-form-span-2 { grid-column: span 1; } .modern-form-section-body { padding: 1rem 1.25rem 1.5rem; } .modern-form-section-header { padding: 1.25rem 1.25rem 0.75rem; } .modern-form-actions { padding: 1rem 1.25rem; flex-direction: column; } .btn-modern { justify-content: center; width: 100%; } }
</style>
@endpush
@endsection
BLADE
);

// ============================================================
// 4. Branch Show Page
// ============================================================
$file = "$base/resources/views/admin/Branch/show.blade.php";
file_put_contents($file, <<<'BLADE'
@extends('layouts.admin')
@section('title', 'Branch Details')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.branches.index') }}">Branches</a></li>
                    <li class="active">{{ $item->name }}</li>
                </ol>
            </nav>
            <h1 class="modern-page-title">{{ $item->name }}</h1>
            <p class="modern-page-subtitle">Branch details and information</p>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.branches.index') }}" class="btn-modern btn-modern-outline">
                <i class="fas fa-arrow-left"></i>
                <span>Back to List</span>
            </a>
            <a href="{{ route('admin.branches.edit', $item->id) }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-pen"></i>
                <span>Edit</span>
            </a>
        </div>
    </div>

    <div class="modern-detail-grid">
        <div class="modern-card modern-detail-main">
            <div class="modern-detail-hero">
                <div class="modern-detail-hero-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="modern-detail-hero-info">
                    <h2 class="modern-detail-hero-title">{{ $item->name }}</h2>
                    <div class="modern-detail-hero-badges">
                        @if($item->is_active)
                            <span class="modern-badge modern-badge-success"><i class="fas fa-check-circle"></i> Active</span>
                        @else
                            <span class="modern-badge modern-badge-danger"><i class="fas fa-times-circle"></i> Inactive</span>
                        @endif
                        @if($item->is_headquarters)
                            <span class="modern-badge modern-badge-gold"><i class="fas fa-crown"></i> Headquarters</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modern-detail-body">
                <div class="modern-detail-row">
                    <div class="modern-detail-label"><i class="fas fa-map-marker-alt"></i> Address</div>
                    <div class="modern-detail-value">{{ $item->address ?? '-' }}</div>
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label"><i class="fas fa-phone"></i> Phone</div>
                    <div class="modern-detail-value">
                        @if($item->phone) <a href="tel:{{ $item->phone }}" class="modern-link">{{ $item->phone }}</a> @else <span class="modern-muted">-</span> @endif
                    </div>
                </div>
                <div class="modern-detail-row">
                    <div class="modern-detail-label"><i class="fas fa-envelope"></i> Email</div>
                    <div class="modern-detail-value">
                        @if($item->email) <a href="mailto:{{ $item->email }}" class="modern-link">{{ $item->email }}</a> @else <span class="modern-muted">-</span> @endif
                    </div>
                </div>
                @if($item->principal)
                <div class="modern-detail-row">
                    <div class="modern-detail-label"><i class="fas fa-user-tie"></i> Principal</div>
                    <div class="modern-detail-value">{{ $item->principal->first_name }} {{ $item->principal->last_name }}</div>
                </div>
                @endif
                @if($item->gps_lat || $item->gps_lng)
                <div class="modern-detail-row">
                    <div class="modern-detail-label"><i class="fas fa-crosshairs"></i> GPS</div>
                    <div class="modern-detail-value">{{ $item->gps_lat }}, {{ $item->gps_lng }}</div>
                </div>
                @endif
            </div>
        </div>

        <div class="modern-detail-sidebar">
            @if($item->map_embed_url)
            <div class="modern-card modern-map-card">
                <div class="modern-card-header-simple"><i class="fas fa-map"></i> Location Map</div>
                <div class="modern-map-embed">
                    <iframe src="{{ $item->map_embed_url }}" width="100%" height="250" style="border:0; border-radius: 0 0 14px 14px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
            @endif

            <div class="modern-card">
                <div class="modern-card-header-simple"><i class="fas fa-bolt"></i> Quick Actions</div>
                <div class="modern-quick-actions">
                    <a href="{{ route('admin.branches.edit', $item->id) }}" class="modern-quick-action"><i class="fas fa-pen"></i><span>Edit Branch</span></a>
                    @if($item->phone)
                    <a href="tel:{{ $item->phone }}" class="modern-quick-action"><i class="fas fa-phone"></i><span>Call Branch</span></a>
                    @endif
                    @if($item->email)
                    <a href="mailto:{{ $item->email }}" class="modern-quick-action"><i class="fas fa-envelope"></i><span>Send Email</span></a>
                    @endif
                    <form method="POST" action="{{ route('admin.branches.destroy', $item->id) }}" onsubmit="return confirm('Are you sure?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="modern-quick-action modern-quick-action-danger"><i class="fas fa-trash-alt"></i><span>Delete Branch</span></button>
                    </form>
                </div>
            </div>

            <div class="modern-card">
                <div class="modern-card-header-simple"><i class="fas fa-clock"></i> Timestamps</div>
                <div class="modern-timestamps">
                    <div class="modern-timestamp"><span class="modern-timestamp-label">Created</span><span class="modern-timestamp-value">{{ $item->created_at->format('M d, Y H:i') }}</span></div>
                    <div class="modern-timestamp"><span class="modern-timestamp-label">Updated</span><span class="modern-timestamp-value">{{ $item->updated_at->format('M d, Y H:i') }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page { animation: fadeSlideIn 0.4s ease-out; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
.modern-page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
.modern-page-header-left { flex: 1; }
.modern-page-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.modern-page-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }
.modern-page-header-right { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.modern-breadcrumb ol { display: flex; list-style: none; padding: 0; margin: 0 0 0.5rem; gap: 0.5rem; font-size: 0.8rem; align-items: center; }
.modern-breadcrumb li { color: #adb5bd; }
.modern-breadcrumb li a { color: #6c757d; text-decoration: none; transition: color 0.2s; }
.modern-breadcrumb li a:hover { color: #4361ee; }
.modern-breadcrumb li + li::before { content: '/'; margin-right: 0.5rem; color: #dee2e6; }
.modern-breadcrumb li.active { color: #4361ee; font-weight: 500; }
.modern-detail-grid { display: grid; grid-template-columns: 1fr 340px; gap: 1.25rem; align-items: start; }
.modern-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; overflow: hidden; }
.modern-detail-hero { display: flex; align-items: center; gap: 1.25rem; padding: 1.75rem 2rem; background: linear-gradient(135deg, #f8f9ff, #eef2ff); border-bottom: 1px solid #e5e8ff; }
.modern-detail-hero-icon { width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #4361ee, #3a0ca3); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff; flex-shrink: 0; box-shadow: 0 4px 12px rgba(67,97,238,0.3); }
.modern-detail-hero-title { font-size: 1.5rem; font-weight: 800; color: #1a1a2e; margin: 0 0 0.5rem; }
.modern-detail-hero-badges { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.modern-badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.65rem; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
.modern-badge-success { background: #ecfdf5; color: #059669; }
.modern-badge-danger { background: #fef2f2; color: #dc2626; }
.modern-badge-gold { background: #fefce8; color: #b45309; }
.modern-detail-body { padding: 0.5rem 0; }
.modern-detail-row { display: flex; padding: 0.9rem 2rem; border-bottom: 1px solid #f8f9fa; transition: background 0.15s; }
.modern-detail-row:last-child { border-bottom: none; }
.modern-detail-row:hover { background: #fafbfc; }
.modern-detail-label { width: 180px; flex-shrink: 0; font-weight: 600; color: #6b7280; font-size: 0.88rem; display: flex; align-items: center; gap: 0.5rem; }
.modern-detail-label i { color: #9ca3af; font-size: 0.82rem; width: 16px; text-align: center; }
.modern-detail-value { color: #1a1a2e; font-size: 0.9rem; }
.modern-link { color: #4361ee; text-decoration: none; font-weight: 500; }
.modern-link:hover { text-decoration: underline; }
.modern-muted { color: #d1d5db; }
.modern-detail-sidebar { display: flex; flex-direction: column; gap: 1.25rem; }
.modern-card-header-simple { padding: 1rem 1.25rem; font-weight: 600; color: #374151; font-size: 0.9rem; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 0.5rem; }
.modern-card-header-simple i { color: #4361ee; font-size: 0.85rem; }
.modern-quick-actions { padding: 0.5rem; display: flex; flex-direction: column; gap: 0.15rem; }
.modern-quick-action { display: flex; align-items: center; gap: 0.65rem; padding: 0.7rem 0.85rem; border-radius: 10px; color: #374151; text-decoration: none; font-size: 0.88rem; font-weight: 500; transition: all 0.15s; border: none; background: none; width: 100%; cursor: pointer; text-align: left; }
.modern-quick-action i { color: #6b7280; width: 18px; text-align: center; }
.modern-quick-action:hover { background: #f3f4f6; color: #1a1a2e; }
.modern-quick-action:hover i { color: #4361ee; }
.modern-quick-action-danger { color: #dc2626; }
.modern-quick-action-danger i { color: #f87171; }
.modern-quick-action-danger:hover { background: #fef2f2; color: #b91c1c; }
.modern-quick-action-danger:hover i { color: #dc2626; }
.modern-timestamps { padding: 0.85rem 1.25rem; }
.modern-timestamp { display: flex; justify-content: space-between; padding: 0.45rem 0; }
.modern-timestamp + .modern-timestamp { border-top: 1px solid #f3f4f6; }
.modern-timestamp-label { color: #9ca3af; font-size: 0.82rem; }
.modern-timestamp-value { color: #374151; font-size: 0.82rem; font-weight: 500; }
.modern-map-card { overflow: hidden; }
.modern-map-embed { line-height: 0; }
.btn-modern { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.35rem; border-radius: 10px; font-weight: 600; font-size: 0.9rem; text-decoration: none; border: none; cursor: pointer; transition: all 0.25s; }
.btn-modern-primary { background: linear-gradient(135deg, #4361ee, #3a0ca3); color: #fff; box-shadow: 0 2px 8px rgba(67,97,238,0.3); }
.btn-modern-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(67,97,238,0.4); color: #fff; }
.btn-modern-outline { background: transparent; color: #6b7280; border: 1.5px solid #e5e7eb; }
.btn-modern-outline:hover { border-color: #4361ee; color: #4361ee; background: #f8f9ff; }
@media (max-width: 992px) { .modern-detail-grid { grid-template-columns: 1fr; } }
@media (max-width: 768px) { .modern-page-header { flex-direction: column; align-items: stretch; } .modern-page-title { font-size: 1.35rem; } .modern-detail-hero { padding: 1.25rem; flex-direction: column; text-align: center; } .modern-detail-hero-badges { justify-content: center; } .modern-detail-row { flex-direction: column; gap: 0.25rem; padding: 0.75rem 1.25rem; } .modern-detail-label { width: auto; } }
</style>
@endpush
@endsection
BLADE
);

// ============================================================
// 5. BranchController
// ============================================================
$file = "$base/app/Http/Controllers/Branch/BranchController.php";
$dir = dirname($file);
if (!is_dir($dir)) mkdir($dir, 0755, true);

file_put_contents($file, <<<'PHP'
<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index()
    {
        $data = Branch::with('principal')->latest()->paginate(20);
        return view('admin.Branch.index', compact('data'));
    }

    public function create()
    {
        $teachers = \App\Models\Teacher::orderBy('first_name')->get();
        return view('admin.Branch.create', compact('teachers'));
    }

    public function store(Request $r)
    {
        $validated = $r->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'phone'          => 'required|string|max:50',
            'email'          => 'required|email|max:255',
            'gps_lat'        => 'nullable|numeric',
            'gps_lng'        => 'nullable|numeric',
            'map_embed_url'  => 'nullable|url|max:1000',
            'principal_id'   => 'nullable|exists:teachers,id',
            'order'          => 'nullable|integer',
            'is_active'      => 'boolean',
            'is_headquarters'=> 'boolean',
        ]);

        Branch::create($validated);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $branch->load('principal');
        return view('admin.Branch.show', ['item' => $branch]);
    }

    public function edit(Branch $branch)
    {
        $teachers = \App\Models\Teacher::orderBy('first_name')->get();
        return view('admin.Branch.edit', ['item' => $branch, 'teachers' => $teachers]);
    }

    public function update(Request $r, Branch $branch)
    {
        $validated = $r->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'phone'          => 'required|string|max:50',
            'email'          => 'required|email|max:255',
            'gps_lat'        => 'nullable|numeric',
            'gps_lng'        => 'nullable|numeric',
            'map_embed_url'  => 'nullable|url|max:1000',
            'principal_id'   => 'nullable|exists:teachers,id',
            'order'          => 'nullable|integer',
            'is_active'      => 'boolean',
            'is_headquarters'=> 'boolean',
        ]);

        $branch->update($validated);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return back()->with('success', 'Branch deleted successfully.');
    }
}
PHP
);

echo "Branch modernization complete!\n";
echo "Files updated:\n";
echo "  - resources/views/admin/Branch/index.blade.php\n";
echo "  - resources/views/admin/Branch/create.blade.php\n";
echo "  - resources/views/admin/Branch/edit.blade.php\n";
echo "  - resources/views/admin/Branch/show.blade.php\n";
echo "  - app/Http/Controllers/Branch/BranchController.php\n";
