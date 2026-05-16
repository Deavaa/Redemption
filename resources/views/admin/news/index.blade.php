@extends('layouts.admin')
@section('title', 'News Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-newspaper me-2"></i>News Management</h2>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add News</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Active</th>
                        <th>Priority</th>
                        <th>Show Until</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($news as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->title }}</strong>
                            @if($item->content)<br><small class="text-muted">{{ Str::limit(strip_tags($item->content), 80) }}</small>@endif
                        </td>
                        <td>
                            @if($item->is_active)<span class="badge bg-success">Active</span>
                            @else<span class="badge bg-secondary">Inactive</span>@endif
                        </td>
                        <td>{{ $item->priority }}</td>
                        <td>{{ $item->show_until ? $item->show_until->format('M d, Y') : 'Until replaced' }}</td>
                        <td>{{ $item->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('admin.news.destroy', $item) }}" style="display:inline" onsubmit="return confirm('Delete this news item?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No news items yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $news->links() }}
</div>
@endsection
