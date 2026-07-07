@extends('layouts.admin')
@section('title', 'View News')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route('admin.news.index') }}">News</a></li>
                    <li class="active">View</li>
                </ol>
            </nav>
            <h2 style="margin:0;font-size:1.5rem;font-weight:700;color:#1a1a2e;">
                <i class="fas fa-newspaper me-2" style="color:#10b981;"></i>{{ $news->title }}
            </h2>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route('admin.news.index') }}" class="btn-modern btn-modern-primary" style="background:#f3f4f6;color:#374151;box-shadow:none;">
                <i class="fas fa-arrow-left"></i><span>Back to List</span>
            </a>
            <a href="{{ route('admin.news.edit', $news) }}" class="btn-modern btn-modern-primary" style="background:#fef3c7;color:#92400e;box-shadow:none;">
                <i class="fas fa-pen"></i><span>Edit</span>
            </a>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-card-body" style="padding:2rem;">

            {{-- Meta row --}}
            <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid #f0f0f0;">
                <span class="modern-badge modern-badge-light">
                    <i class="far fa-calendar-alt"></i>
                    {{ $news->created_at->format('M d, Y \a\t h:i A') }}
                </span>
                @if($news->created_at->gt(now()->subDays(3)))
                    <span class="modern-badge modern-badge-success">
                        <i class="fas fa-bolt"></i> New
                    </span>
                @endif
                @if($news->is_active)
                    <span class="modern-badge modern-badge-success">
                        <i class="fas fa-eye"></i> Active
                    </span>
                @else
                    <span class="modern-badge modern-badge-light">
                        <i class="fas fa-eye-slash"></i> Inactive
                    </span>
                @endif
                @if($news->is_approved)
                    <span class="modern-badge modern-badge-success">
                        <i class="fas fa-check"></i> Approved
                    </span>
                @else
                    <span class="modern-badge modern-badge-warning">
                        <i class="fas fa-hourglass-half"></i> Pending Approval
                    </span>
                @endif
                <span class="modern-badge modern-badge-light">
                    <i class="fas fa-flag"></i> Priority: {{ $news->priority }}
                </span>
                @if($news->show_until)
                    <span class="modern-badge modern-badge-light">
                        <i class="far fa-clock"></i> Show until {{ $news->show_until->format('M d, Y') }}
                    </span>
                @endif
            </div>

            {{-- Author info --}}
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem;">
                <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#10b981,#06b6d4);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;">
                    {{ strtoupper(substr($news->creator->name ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:600;color:#1a1a2e;font-size:0.9rem;">{{ $news->creator->name ?? 'System' }}</div>
                    <div style="font-size:0.78rem;color:#9ca3af;">
                        @if($news->creator && !in_array($news->creator->role, ['admin', 'super_admin']))
                            {{ ucfirst(str_replace('_', ' ', $news->creator->role)) }} ·
                        @endif
                        Posted {{ $news->created_at->diffForHumans() }}
                        @if($news->approver)
                            · Approved by {{ $news->approver->name }}
                        @endif
                    </div>
                </div>
            </div>

            {{-- Cover image — try public/ first, then storage/ --}}
            @php
                $coverUrl = null;
                if ($news->image_path) {
                    $basename = basename($news->image_path);
                    if (file_exists(public_path('news-images/' . $basename))) {
                        $coverUrl = asset('news-images/' . $basename);
                    } elseif (\Storage::disk('public')->exists($news->image_path)) {
                        $coverUrl = asset('storage/' . $news->image_path);
                    }
                }
            @endphp
            @if($coverUrl)
            <div style="margin-bottom:1.75rem;border-radius:14px;overflow:hidden;box-shadow:0 4px 14px rgba(0,0,0,0.08);">
                <img src="{{ $coverUrl }}" alt="{{ $news->title }}" style="width:100%;max-height:400px;object-fit:cover;display:block;">
            </div>
            @endif

            {{-- Content --}}
            @if($news->content)
            <div style="line-height:1.8;color:#374151;font-size:1rem;">
                {!! $news->content !!}
            </div>
            @else
            <div style="padding:2rem;text-align:center;color:#9ca3af;background:#f9fafb;border-radius:12px;">
                <i class="fas fa-file-alt" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>
                No content for this news item.
            </div>
            @endif

        </div>
    </div>

    {{-- Action buttons --}}
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:1.5rem;">
        <a href="{{ route('admin.news.edit', $news) }}" class="btn-modern btn-modern-primary">
            <i class="fas fa-pen"></i><span>Edit News</span>
        </a>
        @if(!$news->is_approved && in_array(Auth::user()->role, ['admin', 'super_admin']))
        <form method="POST" action="{{ route('admin.news.approve', $news) }}" style="display:inline" onsubmit="return confirm('Approve this news?')">
            @csrf
            <button type="submit" class="btn-modern btn-modern-primary" style="background:#ecfdf5;color:#059669;box-shadow:none;">
                <i class="fas fa-check"></i><span>Approve</span>
            </button>
        </form>
        <form method="POST" action="{{ route('admin.news.reject', $news) }}" style="display:inline" onsubmit="return confirm('Reject this news?')">
            @csrf
            <button type="submit" class="btn-modern btn-modern-primary" style="background:#fef2f2;color:#dc2626;box-shadow:none;">
                <i class="fas fa-times"></i><span>Reject</span>
            </button>
        </form>
        @endif
        <form method="POST" action="{{ route('admin.news.destroy', $news) }}" style="display:inline" onsubmit="return confirm('Delete this news? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-modern btn-modern-primary" style="background:#fef2f2;color:#dc2626;box-shadow:none;">
                <i class="fas fa-trash-alt"></i><span>Delete</span>
            </button>
        </form>
    </div>
</div>
@endsection
