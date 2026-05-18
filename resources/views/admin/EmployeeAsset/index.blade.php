@extends('layouts.admin')
@section('title', 'Employee Assets')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li>HR</li>
                    <li class="active">Employee Assets</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.employee-assets.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i> Add Asset
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
        <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()">&times;</button>
    </div>
    @endif

    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <div class="modern-stat-info">
                <div class="modern-stat-value">{{ $data->total() }}</div>
                <div class="modern-stat-label">Total Assets</div>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="modern-stat-info">
                <div class="modern-stat-value">{{ $data->where('condition', 'Good')->count() }}</div>
                <div class="modern-stat-label">Good Condition</div>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="modern-stat-info">
                <div class="modern-stat-value">{{ $data->where('condition', '!=', 'Good')->count() }}</div>
                <div class="modern-stat-label">Needs Attention</div>
            </div>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">All Assets</h2>
            </div>
            <div class="modern-card-header-right">
                <div class="modern-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="assetSearch" placeholder="Search assets..." onkeyup="filterTable()">
                </div>
            </div>
        </div>
        <div class="modern-card-body">
            @if($data->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table" id="assetTable">
                    <thead>
                        <tr>
                            <th class="th-narrow">#</th>
                            <th>Asset Name</th>
                            <th>Employee</th>
                            <th>Quantity</th>
                            <th>Condition</th>
                            <th>Issue Date</th>
                            <th>Return Date</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                        <tr>
                            <td class="modern-row-number">{{ $loop->iteration }}</td>
                            <td>
                                <div class="modern-cell-title">{{ $item->name ?? '-' }}</div>
                                <div class="modern-cell-sub">{{ $item->description ? \Str::limit($item->description, 40) : '' }}</div>
                            </td>
                            <td>
                                @if($item->employee)
                                    <span class="modern-cell-text">{{ $item->employee->name ?? '-' }}</span>
                                @else
                                    <span class="modern-cell-muted">Unassigned</span>
                                @endif
                            </td>
                            <td class="modern-cell-text">{{ $item->quantity ?? '-' }}</td>
                            <td>
                                @php
                                    $cond = $item->condition ?? '';
                                    $badgeClass = match(strtolower($cond)) {
                                        'good', 'new' => 'modern-badge-success',
                                        'fair', 'used' => 'modern-badge-warning',
                                        'poor', 'damaged' => 'modern-badge-danger',
                                        default => 'modern-badge-light'
                                    };
                                @endphp
                                <span class="modern-badge {{ $badgeClass }}">{{ $cond ?: '-' }}</span>
                            </td>
                            <td class="modern-cell-text">{{ $item->issue_date ? $item->issue_date->format('M d, Y') : '-' }}</td>
                            <td class="modern-cell-text">{{ $item->return_date ? $item->return_date->format('M d, Y') : '-' }}</td>
                            <td>
                                <div class="modern-action-group">
                                    <a href="{{ route('admin.employee-assets.show', $item->id) }}" class="modern-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.employee-assets.edit', $item->id) }}" class="modern-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.employee-assets.destroy', $item->id) }}" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this asset?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="modern-btn-delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modern-pagination-wrapper">
                {{ $data->links() }}
            </div>
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <h3>No Assets Found</h3>
                <p>Start by adding your first employee asset.</p>
                <a href="{{ route('admin.employee-assets.create') }}" class="btn-modern btn-modern-primary">
                    <i class="fas fa-plus"></i> Add Asset
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
.modern-page{animation:fadeSlideIn .4s ease-out;padding:1.5rem}
.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;gap:1rem}
.modern-page-header-left{flex:1}
.modern-page-header-right{display:flex;align-items:center;gap:.75rem;flex-shrink:0}
.modern-breadcrumb{margin-bottom:.5rem}
.modern-breadcrumb ol{display:flex;align-items:center;list-style:none;padding:0;margin:0;gap:.25rem;font-size:.8rem}
.modern-breadcrumb li{color:#94a3b8}
.modern-breadcrumb li:not(:last-child)::after{content:'/';margin-left:.25rem;color:#cbd5e1}
.modern-breadcrumb li a{color:#64748b;text-decoration:none;transition:color .2s}
.modern-breadcrumb li a:hover{color:#4361ee}
.modern-breadcrumb li.active{color:#4361ee;font-weight:600}
.modern-stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem}
.modern-stat-card{background:#fff;border-radius:12px;padding:1.25rem;display:flex;align-items:center;gap:1rem;border:1px solid #e2e8f0;transition:transform .2s,box-shadow .2s}
.modern-stat-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.06)}
.modern-stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0}
.modern-stat-icon-blue{background:rgba(67,97,238,.1);color:#4361ee}
.modern-stat-icon-green{background:rgba(16,185,129,.1);color:#10b981}
.modern-stat-icon-gold{background:rgba(245,158,11,.1);color:#f59e0b}
.modern-stat-info{flex:1}
.modern-stat-value{font-size:1.5rem;font-weight:700;color:#1e293b;line-height:1.2}
.modern-stat-label{font-size:.8rem;color:#64748b;margin-top:.125rem}
.modern-card{background:#fff;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden}
.modern-card-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #e2e8f0;gap:1rem;flex-wrap:wrap}
.modern-card-header-left{display:flex;align-items:center;gap:.75rem}
.modern-card-header-right{display:flex;align-items:center;gap:.75rem}
.modern-card-title{font-size:1.1rem;font-weight:600;color:#1e293b;margin:0}
.modern-card-body{padding:0}
.modern-table-wrapper{overflow-x:auto}
.modern-table{width:100%;border-collapse:collapse}
.modern-table thead th{background:#f8fafc;padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.modern-table tbody td{padding:.875rem 1rem;border-bottom:1px solid #f1f5f9;font-size:.875rem;color:#334155;vertical-align:middle}
.modern-table tbody tr:hover{background:#f8fafc}
.modern-table tbody tr:last-child td{border-bottom:none}
.th-narrow{width:60px!important}
.th-center{text-align:center!important}
.th-actions{text-align:right!important;width:120px}
.modern-row-number{color:#94a3b8;font-weight:500;font-size:.8rem}
.modern-cell-title{font-weight:600;color:#1e293b}
.modern-cell-sub{font-size:.75rem;color:#94a3b8;margin-top:.125rem}
.modern-cell-text{color:#334155}
.modern-cell-muted{color:#94a3b8;font-style:italic}
.modern-badge{display:inline-block;padding:.25rem .75rem;border-radius:9999px;font-size:.75rem;font-weight:500;line-height:1.4}
.modern-badge-light{background:#f1f5f9;color:#64748b}
.modern-badge-success{background:#ecfdf5;color:#059669}
.modern-badge-danger{background:#fef2f2;color:#dc2626}
.modern-badge-gold{background:#fffbeb;color:#b45309}
.modern-badge-warning{background:#fffbeb;color:#d97706}
.modern-badge-info{background:#eff6ff;color:#2563eb}
.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.6rem 1.25rem;border-radius:8px;font-size:.875rem;font-weight:500;cursor:pointer;transition:all .2s;border:none;text-decoration:none;line-height:1.4}
.btn-modern-primary{background:#4361ee;color:#fff;box-shadow:0 1px 3px rgba(67,97,238,.3)}
.btn-modern-primary:hover{background:#3a0ca3;box-shadow:0 4px 12px rgba(67,97,238,.4)}
.btn-modern-outline{background:transparent;color:#4361ee;border:1px solid #4361ee}
.btn-modern-outline:hover{background:#4361ee;color:#fff}
.btn-modern-ghost{background:transparent;color:#64748b}
.btn-modern-ghost:hover{background:#f1f5f9;color:#1e293b}
.modern-btn-icon{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:none;cursor:pointer;transition:all .2s;font-size:.85rem;text-decoration:none;color:#64748b;background:transparent}
.modern-btn-icon:hover{background:#f1f5f9;color:#1e293b}
.modern-btn-view{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:none;cursor:pointer;transition:all .2s;font-size:.85rem;text-decoration:none;color:#4361ee;background:rgba(67,97,238,.06)}
.modern-btn-view:hover{background:rgba(67,97,238,.15)}
.modern-btn-edit{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:none;cursor:pointer;transition:all .2s;font-size:.85rem;text-decoration:none;color:#f59e0b;background:rgba(245,158,11,.06)}
.modern-btn-edit:hover{background:rgba(245,158,11,.15)}
.modern-btn-delete{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:8px;border:none;cursor:pointer;transition:all .2s;font-size:.85rem;text-decoration:none;color:#ef4444;background:rgba(239,68,68,.06)}
.modern-btn-delete:hover{background:rgba(239,68,68,.15)}
.modern-action-group{display:flex;align-items:center;gap:.375rem;justify-content:flex-end}
.modern-empty-state{text-align:center;padding:4rem 2rem}
.modern-empty-icon{width:80px;height:80px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2rem;color:#94a3b8}
.modern-empty-state h3{font-size:1.2rem;font-weight:600;color:#1e293b;margin:0 0 .5rem}
.modern-empty-state p{color:#64748b;margin:0 0 1.5rem;font-size:.9rem}
.modern-search-box{position:relative;display:flex;align-items:center}
.modern-search-box i{position:absolute;left:.75rem;color:#94a3b8;font-size:.85rem}
.modern-search-box input{padding:.5rem .75rem .5rem 2.25rem;border:1px solid #e2e8f0;border-radius:8px;font-size:.85rem;outline:none;transition:border-color .2s,box-shadow .2s;background:#f8fafc;width:220px}
.modern-search-box input:focus{border-color:#4361ee;box-shadow:0 0 0 3px rgba(67,97,238,.1);background:#fff}
.modern-pagination-wrapper{padding:1rem 1.5rem;display:flex;justify-content:center;border-top:1px solid #e2e8f0}
.modern-pagination-wrapper :deep(.pagination){display:flex;gap:.25rem;list-style:none;padding:0;margin:0}
.modern-pagination-wrapper :deep(.page-link){padding:.4rem .75rem;border-radius:6px;font-size:.85rem;border:1px solid #e2e8f0;color:#4361ee;text-decoration:none;transition:all .2s}
.modern-pagination-wrapper :deep(.page-link:hover){background:#4361ee;color:#fff;border-color:#4361ee}
.modern-pagination-wrapper :deep(.active .page-link){background:#4361ee;color:#fff;border-color:#4361ee}
.modern-pagination-wrapper :deep(.disabled .page-link){color:#94a3b8;pointer-events:none}
.modern-alert{display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;border-radius:10px;margin-bottom:1.5rem;font-size:.9rem;animation:fadeSlideIn .3s ease-out}
.modern-alert-success{background:#ecfdf5;color:#059669;border:1px solid #a7f3d0}
.modern-alert-close{margin-left:auto;background:none;border:none;font-size:1.2rem;cursor:pointer;color:inherit;opacity:.7;transition:opacity .2s}
.modern-alert-close:hover{opacity:1}
@media(max-width:768px){
.modern-page{padding:1rem}
.modern-page-header{flex-direction:column;gap:.75rem}
.modern-page-header-right{width:100%;justify-content:flex-start}
.modern-stats-row{grid-template-columns:1fr}
.modern-card-header{flex-direction:column;align-items:flex-start}
.modern-search-box input{width:100%}
.modern-table{font-size:.8rem}
.modern-table thead th,.modern-table tbody td{padding:.6rem .75rem}
}
@media(max-width:480px){
.modern-stat-card{padding:1rem}
.modern-stat-value{font-size:1.25rem}
.btn-modern{padding:.5rem 1rem;font-size:.8rem}
}
</style>
@endpush
@push('scripts')
<script>
function filterTable(){const e=document.getElementById('assetSearch').value.toLowerCase(),t=document.querySelectorAll('#assetTable tbody tr');t.forEach(t=>{t.textContent.toLowerCase().includes(e)?t.style.display='':t.style.display='none'})}
</script>
@endpush
@endsection
