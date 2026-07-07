@extends('layouts.admin')
@section('title', 'News Management')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="#">Website</a></li>
                    <li class="active">News</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.news.create') }}" class="btn-modern btn-modern-primary">
                <i class="fas fa-plus"></i><span>Add News</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="modern-alert modern-alert-success">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="modern-alert-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        </div>
    @endif

    <div class="modern-card">
        <div class="modern-card-header">
            <div class="modern-card-header-left">
                <h2 class="modern-card-title">All News</h2>
            </div>
        </div>
        <div class="modern-card-body">
            @if($news->count() > 0)
            <div class="modern-table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Posted By</th>
                            <th class="th-center">Active</th>
                            <th class="th-center">Approved</th>
                            <th>Priority</th>
                            <th>Show Until</th>
                            <th>Created</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($news as $item)
                        <tr class="modern-table-row" style="{{ !$item->is_approved ? 'background:#fffbeb;' : '' }}">
                            <td>
                                <div class="modern-cell-title">{{ $item->title }}</div>
                                @if($item->content)
                                <div class="modern-cell-sub">{{ Str::limit(strip_tags($item->content), 80) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="modern-cell-text">{{ $item->creator->name ?? 'System' }}</div>
                                @if($item->creator && !in_array($item->creator->role, ['admin', 'super_admin']))
                                <div class="modern-cell-sub">{{ ucfirst(str_replace('_', ' ', $item->creator->role)) }}</div>
                                @endif
                            </td>
                            <td class="td-center">
                                @if($item->is_active)
                                <span class="modern-badge modern-badge-success">Active</span>
                                @else
                                <span class="modern-badge modern-badge-light">Inactive</span>
                                @endif
                            </td>
                            <td class="td-center">
                                @if($item->is_approved)
                                <span class="modern-badge modern-badge-success"><i class="fas fa-check"></i> Approved</span>
                                @else
                                <span class="modern-badge modern-badge-warning"><i class="fas fa-hourglass-half"></i> Pending</span>
                                @endif
                            </td>
                            <td>{{ $item->priority }}</td>
                            <td>{{ $item->show_until ? $item->show_until->format('M d, Y') : 'Until replaced' }}</td>
                            <td>{{ $item->created_at->format('M d, Y') }}</td>
                            <td class="td-actions">
                                <div class="modern-action-group">
                                    @if(!$item->is_approved && in_array(Auth::user()->role, ['admin', 'super_admin']))
                                    <form method="POST" action="{{ route('admin.news.approve', $item) }}" style="display:inline" onsubmit="return confirm('Approve this news?')">
                                        @csrf
                                        <button type="submit" class="modern-btn-icon" style="background:#ecfdf5;color:#059669;" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.news.reject', $item) }}" style="display:inline" onsubmit="return confirm('Reject this news?')">
                                        @csrf
                                        <button type="submit" class="modern-btn-icon modern-btn-delete" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('admin.news.show', $item) }}" class="modern-btn-icon modern-btn-view" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.news.edit', $item) }}" class="modern-btn-icon modern-btn-edit" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.news.destroy', $item) }}" style="display:inline" onsubmit="return confirm('Delete this news?')">
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
            @if($news->hasPages())
            <div class="modern-pagination-wrapper">{{ $news->withQueryString()->links() }}</div>
            @endif
            @else
            <div class="modern-empty-state">
                <div class="modern-empty-icon"><i class="fas fa-newspaper"></i></div>
                <h3>No News Items</h3>
                <p>Create your first news item.</p>
                <a href="{{ route('admin.news.create') }}" class="btn-modern btn-modern-primary"><i class="fas fa-plus"></i> Add News</a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.modern-page{animation:fadeSlideIn .4s ease-out}@keyframes fadeSlideIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}.modern-page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem}.modern-page-header-left{flex:1}.modern-breadcrumb ol{display:flex;list-style:none;padding:0;margin:0 0 .5rem;gap:.5rem;font-size:.8rem;align-items:center}.modern-breadcrumb li{color:#adb5bd}.modern-breadcrumb li a{color:#6c757d;text-decoration:none}.modern-breadcrumb li a:hover{color:#4361ee}.modern-breadcrumb li+li::before{content:'/';margin-right:.5rem;color:#dee2e6}.modern-breadcrumb li.active{color:#4361ee;font-weight:500}.modern-card{background:#fff;border-radius:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #f0f0f0;overflow:hidden;margin-bottom:1.5rem}.modern-card-header{display:flex;justify-content:space-between;align-items:center;padding:1.25rem 1.5rem;border-bottom:1px solid #f0f0f0}.modern-card-header-left{display:flex;align-items:center;gap:.75rem}.modern-card-title{font-size:1.1rem;font-weight:700;color:#1a1a2e;margin:0}.modern-card-body{padding:0}.modern-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .65rem;border-radius:50px;font-size:.75rem;font-weight:600}.modern-badge-light{background:#f3f4f6;color:#6b7280}.modern-badge-success{background:#ecfdf5;color:#059669}.modern-badge-warning{background:#fffbeb;color:#d97706}.modern-table-wrapper{overflow-x:auto}.modern-table{width:100%;border-collapse:collapse;font-size:.9rem}.modern-table thead th{background:#f9fafb;padding:.85rem 1rem;text-align:left;font-weight:600;font-size:.78rem;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;border-bottom:2px solid #e5e7eb;white-space:nowrap}.modern-table tbody tr{border-bottom:1px solid #f3f4f6;transition:background .15s}.modern-table tbody tr:hover{background:#f8f9ff}.modern-table td{padding:.9rem 1rem;vertical-align:middle;color:#374151}.th-center,.td-center{text-align:center!important}.th-actions,.td-actions{text-align:right!important}.modern-cell-title{font-weight:600;color:#1a1a2e;margin-bottom:2px}.modern-cell-sub{font-size:.8rem;color:#9ca3af}.modern-cell-text{color:#4b5563;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.modern-action-group{display:inline-flex;gap:.35rem}.modern-btn-icon{width:34px;height:34px;border-radius:9px;border:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .2s;font-size:.82rem;text-decoration:none}.modern-btn-view{background:#eef2ff;color:#4361ee}.modern-btn-view:hover{background:#4361ee;color:#fff}.modern-btn-edit{background:#fefce8;color:#d97706}.modern-btn-edit:hover{background:#d97706;color:#fff}.modern-btn-delete{background:#fef2f2;color:#dc2626}.modern-btn-delete:hover{background:#dc2626;color:#fff}.modern-alert{display:flex;align-items:center;gap:.65rem;padding:.85rem 1.25rem;margin:1rem 1.5rem;border-radius:10px;font-size:.88rem;font-weight:500}.modern-alert-success{background:#ecfdf5;color:#059669;border:1px solid #a7f3d0}.modern-alert-close{margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;opacity:.6}.modern-alert-close:hover{opacity:1}.modern-empty-state{text-align:center;padding:4rem 2rem}.modern-empty-icon{width:80px;height:80px;border-radius:50%;background:#f3f4f6;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;color:#d1d5db;margin-bottom:1.25rem}.modern-empty-state h3{font-size:1.2rem;font-weight:700;color:#1a1a2e;margin:0 0 .5rem}.modern-empty-state p{color:#9ca3af;font-size:.9rem;margin:0 0 1.5rem}.modern-pagination-wrapper{padding:1rem 1.5rem;border-top:1px solid #f0f0f0;display:flex;justify-content:center}.btn-modern{display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.35rem;border-radius:10px;font-weight:600;font-size:.9rem;text-decoration:none;border:none;cursor:pointer;transition:all .25s}.btn-modern-primary{background:linear-gradient(135deg,#4361ee,#3a0ca3);color:#fff;box-shadow:0 2px 8px rgba(67,97,238,.3)}.btn-modern-primary:hover{transform:translateY(-2px);box-shadow:0 4px 16px rgba(67,97,238,.4);color:#fff}
</style>
@endpush
@endsection
