@extends('layouts.admin')
@section('title', 'Notifications')

@push('styles')
<style>
.notif-page { animation: notifFadeIn 0.4s ease-out; }
@keyframes notifFadeIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

.notif-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.notif-header-left { flex: 1; }
.notif-page-title { font-size: 1.75rem; font-weight: 800; color: #1a1a2e; margin: 0; letter-spacing: -0.5px; }
.notif-page-subtitle { font-size: 0.9rem; color: #6c757d; margin: 0.25rem 0 0; }

.notif-list { display: flex; flex-direction: column; gap: 0.5rem; }
.notif-card { display: flex; gap: 1rem; padding: 1.15rem 1.5rem; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; text-decoration: none; transition: all 0.2s; align-items: flex-start; }
.notif-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
.notif-card.notif-unread { border-left: 4px solid var(--primary); background: #f8f9ff; }
.notif-card-icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.notif-card-icon.notif-card-icon-info { background: #eff6ff; color: #2563eb; }
.notif-card-icon.notif-card-icon-success { background: #ecfdf5; color: #059669; }
.notif-card-icon.notif-card-icon-warning { background: #fefce8; color: #d97706; }
.notif-card-icon.notif-card-icon-danger { background: #fef2f2; color: #dc2626; }
.notif-card-body { flex: 1; min-width: 0; }
.notif-card-title { font-size: 0.95rem; font-weight: 700; color: #1a1a2e; margin-bottom: 0.2rem; }
.notif-card-msg { font-size: 0.85rem; color: #6b7280; margin-bottom: 0.35rem; }
.notif-card-time { font-size: 0.75rem; color: #9ca3af; }
.notif-card-actions { display: flex; gap: 0.35rem; align-items: center; flex-shrink: 0; }
.notif-action-btn { width: 32px; height: 32px; border-radius: 8px; border: none; background: #f3f4f6; color: #6b7280; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; font-size: 0.82rem; }
.notif-action-btn:hover { background: #e5e7eb; color: #1a1a2e; }
.notif-action-btn.danger:hover { background: #fef2f2; color: #dc2626; }

.notif-empty-state { text-align: center; padding: 4rem 2rem; background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); border: 1px solid #f0f0f0; }
.notif-empty-state i { font-size: 3rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
.notif-empty-state p { color: #9ca3af; font-size: 0.95rem; margin: 0; }
</style>
@endpush

@section('content')
<div class="notif-page">
    <div class="notif-header">
        <div class="notif-header-left">
            <h1 class="notif-page-title">Notifications</h1>
            <p class="notif-page-subtitle">Stay updated with the latest alerts and messages</p>
        </div>
        <form method="POST" action="{{ route('admin.notifications.markAllRead') }}">
            @csrf
            <button type="submit" class="btn btn-primary"><i class="fas fa-check-double me-1"></i> Mark All Read</button>
        </form>
    </div>

    @if($notifications->count() > 0)
        <div class="notif-list">
            @foreach($notifications as $notif)
                <div class="notif-card {{ $notif->is_read ? '' : 'notif-unread' }}">
                    <div class="notif-card-icon notif-card-icon-{{ $notif->type }}">
                        <i class="{{ $notif->icon }}"></i>
                    </div>
                    <div class="notif-card-body">
                        @if(!$notif->is_read)
                            <a href="{{ route('admin.notifications.read', $notif->id) }}" class="text-decoration-none">
                        @endif
                            <div class="notif-card-title">{{ $notif->title }}</div>
                            @if($notif->message)
                                <div class="notif-card-msg">{{ $notif->message }}</div>
                            @endif
                            <div class="notif-card-time"><i class="far fa-clock me-1"></i>{{ $notif->created_at->diffForHumans() }}</div>
                        @if(!$notif->is_read)
                            </a>
                        @endif
                    </div>
                    <div class="notif-card-actions">
                        <form method="POST" action="{{ route('admin.notifications.destroy', $notif->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="notif-action-btn danger" title="Delete" onclick="return confirm('Delete this notification?')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->withQueryString()->links() }}
        </div>
    @else
        <div class="notif-empty-state">
            <i class="fas fa-bell-slash"></i>
            <p>No notifications yet</p>
        </div>
    @endif
</div>
@endsection
