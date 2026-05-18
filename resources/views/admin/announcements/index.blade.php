@extends('layouts.admin')
@section('title', __('app.announcements') ?? 'Announcements')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">{{ __('app.announcements') ?? 'Announcements' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="announcements-grid">
        {{-- Create Announcement Form --}}
        <div class="modern-card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <h3 style="font-size:15px;font-weight:700;color:var(--text-dark);margin:0;"><i class="fas fa-bullhorn" style="color:var(--primary);"></i> {{ __('app.create_announcement') ?? 'Create Announcement' }}</h3>
            </div>
            <form method="POST" action="{{ route('admin.announcements.store') }}" style="padding:16px 20px;">
                @csrf
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">{{ __('app.title') ?? 'Title' }} *</label>
                    <input type="text" name="title" required style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">{{ __('app.description') ?? 'Description' }}</label>
                    <textarea name="description" rows="3" style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;resize:vertical;"></textarea>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">{{ __('app.category') ?? 'Category' }}</label>
                    <select name="category" style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;">
                        <option value="event">Event</option>
                        <option value="holiday">Holiday</option>
                        <option value="deadline">Deadline</option>
                        <option value="meeting">Meeting</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">{{ __('app.date') ?? 'Date' }} *</label>
                    <input type="date" name="start_date" required style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;font-size:13px;">
                </div>
                <div style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="send_telegram" id="sendTelegram" value="1" style="width:18px;height:18px;">
                    <label for="sendTelegram" style="font-size:13px;font-weight:500;color:var(--text);"><i class="fab fa-telegram" style="color:#0088cc;"></i> {{ __('app.send_to_telegram') ?? 'Send to Telegram automatically' }}</label>
                </div>
                <button type="submit" class="btn-modern btn-modern-primary" style="width:100%;"><i class="fas fa-paper-plane"></i> {{ __('app.create_announcement') ?? 'Create Announcement' }}</button>
            </form>
        </div>

        {{-- Upcoming Announcements --}}
        <div class="modern-card">
            <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
                <h3 style="font-size:15px;font-weight:700;color:var(--text-dark);margin:0;"><i class="fas fa-clock" style="color:var(--primary);"></i> {{ __('app.upcoming') ?? 'Upcoming' }}</h3>
            </div>
            <div style="padding:16px 20px;max-height:500px;overflow-y:auto;">
                @forelse($pendingAnnouncements as $event)
                    <div class="announcement-pending-item">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $event->color ?? '#4361ee' }};flex-shrink:0;"></div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:600;color:var(--text-dark);">{{ $event->title }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $event->start_date->format('M d, Y') }} &bull; {{ ucfirst($event->category) }}</div>
                        </div>
                        <div class="announcement-pending-actions">
                            <form method="POST" action="{{ route('admin.announcements.send-telegram', $event->id) }}">
                                @csrf
                                <button type="submit" class="btn-modern btn-modern-sm" style="padding:4px 8px;font-size:10px;background:#0088cc;color:#fff;border:none;border-radius:4px;cursor:pointer;" title="Send to Telegram"><i class="fab fa-telegram"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.announcements.destroy', $event->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-modern btn-modern-sm" style="padding:4px 8px;font-size:10px;background:var(--danger);color:#fff;border:none;border-radius:4px;cursor:pointer;"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p style="text-align:center;color:var(--text-muted);font-size:13px;padding:20px;">{{ __('app.no_announcements') ?? 'No upcoming announcements' }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Past Announcements --}}
    <div class="modern-card" style="margin-top:16px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);">
            <h3 style="font-size:15px;font-weight:700;color:var(--text-dark);margin:0;"><i class="fas fa-history" style="color:var(--primary);"></i> {{ __('app.past_announcements') ?? 'Past Announcements' }}</h3>
        </div>
        {{-- Mobile card view --}}
        <div class="announcement-past-cards">
            @foreach($announcements as $announcement)
            <div class="announcement-past-card">
                <div class="announcement-past-card-header">
                    <div class="announcement-past-card-dot" style="background:{{ $announcement->color ?? '#eee' }}"></div>
                    <div class="announcement-past-card-info">
                        <span class="announcement-past-card-title">{{ $announcement->title }}</span>
                        <span class="announcement-past-card-meta">{{ $announcement->start_date->format('M d, Y') }}</span>
                    </div>
                    <span class="announcement-past-card-cat" style="background:{{ $announcement->color ?? '#eee' }}20;color:{{ $announcement->color ?? '#666' }}">{{ ucfirst($announcement->category) }}</span>
                </div>
                <div class="announcement-past-card-actions">
                    <form method="POST" action="{{ route('admin.announcements.destroy', $announcement->id) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-modern btn-modern-sm" style="padding:4px 10px;font-size:11px;background:var(--danger);color:#fff;border:none;border-radius:4px;cursor:pointer;"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        {{-- Desktop table view --}}
        <div class="announcement-past-table">
            <table class="modern-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:var(--bg-light);border-bottom:1px solid var(--border);">
                        <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Title</th>
                        <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Category</th>
                        <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Date</th>
                        <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--text-muted);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($announcements as $announcement)
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:10px 14px;font-size:13px;font-weight:500;">{{ $announcement->title }}</td>
                        <td style="padding:10px 14px;font-size:12px;"><span style="padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;background:{{ $announcement->color ?? '#eee' }}20;color:{{ $announcement->color ?? '#666' }};">{{ ucfirst($announcement->category) }}</span></td>
                        <td style="padding:10px 14px;font-size:12px;color:var(--text-muted);">{{ $announcement->start_date->format('M d, Y') }}</td>
                        <td style="padding:10px 14px;">
                            <form method="POST" action="{{ route('admin.announcements.destroy', $announcement->id) }}" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:none;border:none;color:var(--danger);cursor:pointer;font-size:12px;"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $announcements->links() }}
    </div>
</div>

@push('styles')
<style>
/* Announcements grid - responsive */
.announcements-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* Pending announcement items */
.announcement-pending-item {
    padding: 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.announcement-pending-actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

/* Past announcements - dual view (mobile cards / desktop table) */
.announcement-past-cards {
    display: none;
    padding: 12px;
}
.announcement-past-card {
    padding: 12px;
    border: 1px solid var(--border);
    border-radius: 10px;
    margin-bottom: 8px;
}
.announcement-past-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.announcement-past-card-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.announcement-past-card-info {
    flex: 1;
    min-width: 0;
}
.announcement-past-card-title {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-dark);
}
.announcement-past-card-meta {
    font-size: 11px;
    color: var(--text-muted);
}
.announcement-past-card-cat {
    font-size: 10px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
    flex-shrink: 0;
}
.announcement-past-card-actions {
    display: flex;
    justify-content: flex-end;
}
.announcement-past-table {
    display: block;
    padding: 0;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .announcements-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .announcement-past-cards {
        display: block;
    }
    .announcement-past-table {
        display: none;
    }
    .announcement-pending-item {
        flex-wrap: wrap;
    }
    .announcement-pending-actions {
        width: 100%;
        justify-content: flex-end;
        margin-top: 4px;
    }
}
</style>
@endpush
@endsection
