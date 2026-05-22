@extends('layouts.admin')
@section('title', 'Email Inbox')
@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav class="modern-breadcrumb"><ol>
                <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                <li class="active">Email Inbox</li>
            </ol></nav>
            <h1 class="modern-page-title">Email Inbox</h1>
            <p class="modern-page-subtitle">View incoming emails linked to your branch Gmail accounts</p>
        </div>
        <div class="modern-page-header-right">
            @foreach($inboxSettings as $inbox)
            <form method="POST" action="{{ route('admin.email-inbox.sync', $inbox->id) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn-modern btn-modern-outline" title="Sync: {{ $inbox->email_address }}">
                    <i class="fas fa-sync"></i> Sync {{ $inbox->branch?->name ?? 'All' }}
                </button>
            </form>
            @endforeach
            <a href="{{ route('admin.email-inbox.settings') }}" class="btn-modern btn-modern-secondary">
                <i class="fas fa-cog"></i> Settings
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="modern-alert modern-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="modern-alert modern-alert-danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="modern-stats-row">
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-blue"><i class="fas fa-envelope"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $totalMessages }}</span>
                <span class="modern-stat-label">Total Emails</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-orange"><i class="fas fa-envelope-open-text"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $unreadCount }}</span>
                <span class="modern-stat-label">Unread</span>
            </div>
        </div>
        <div class="modern-stat-card">
            <div class="modern-stat-icon modern-stat-icon-gold"><i class="fas fa-star"></i></div>
            <div class="modern-stat-info">
                <span class="modern-stat-value">{{ $starredCount }}</span>
                <span class="modern-stat-label">Starred</span>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="modern-card" style="margin-bottom:1.25rem">
        <div style="padding:1rem 1.25rem">
            <form method="GET" action="{{ route('admin.email-inbox.index') }}" id="filterForm">
                <div class="modern-form-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:0.75rem">
                    <div class="modern-form-group">
                        <select name="category" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Categories</option>
                            @foreach(\App\Models\EmailMessage::categoryOptions() as $key => $label)
                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="is_read" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Status</option>
                            <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>Unread</option>
                            <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>Read</option>
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <select name="inbox_id" class="modern-input modern-select" style="padding-left:0.75rem">
                            <option value="">All Inboxes</option>
                            @foreach($inboxSettings as $inbox)
                            <option value="{{ $inbox->id }}" {{ request('inbox_id') == $inbox->id ? 'selected' : '' }}>{{ $inbox->email_address }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modern-form-group">
                        <input type="text" name="search" class="modern-input" style="padding-left:0.75rem" placeholder="Search emails..." value="{{ request('search') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Email List --}}
    <div class="modern-card">
        @if($messages->count() > 0)
        <div class="modern-table-wrapper">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="th-narrow"></th>
                        <th>From</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Branch</th>
                        <th>Received</th>
                        <th class="th-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($messages as $msg)
                    <tr style="{{ !$msg->is_read ? 'font-weight:700;background:#f0f7ff;' : '' }}">
                        <td>
                            <a href="{{ route('admin.email-inbox.toggle-star', $msg->id) }}" style="color:{{ $msg->is_starred ? '#f59e0b' : '#d1d5db' }};font-size:1rem;text-decoration:none;">
                                <i class="fas fa-star"></i>
                            </a>
                        </td>
                        <td>
                            <div>{{ $msg->from_name ?: $msg->from_email }}</div>
                            @if($msg->from_name)
                            <div style="font-size:0.75rem;color:#9ca3af;">{{ $msg->from_email }}</div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.email-inbox.show', $msg->id) }}" style="color:#4361ee;text-decoration:none;">
                                {{ $msg->subject ?: '(No Subject)' }}
                            </a>
                        </td>
                        <td>
                            <span class="modern-badge modern-badge-{{ match($msg->category) {
                                'admission' => 'info',
                                'fee' => 'success',
                                'complaint' => 'danger',
                                'academic' => 'purple',
                                'hr' => 'cyan',
                                default => 'light'
                            } }}">
                                {{ $msg->category_label }}
                            </span>
                        </td>
                        <td>{{ $msg->inboxSetting?->branch?->name ?? '-' }}</td>
                        <td>{{ $msg->received_at?->format('M d, H:i') ?? '-' }}</td>
                        <td class="td-actions">
                            <a href="{{ route('admin.email-inbox.show', $msg->id) }}" class="modern-btn-icon modern-btn-view" title="View"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="modern-pagination-wrapper">
            {{ $messages->withQueryString()->links() }}
        </div>
        @else
        <div class="modern-empty-state">
            <div class="modern-empty-icon"><i class="fas fa-envelope"></i></div>
            <h3>No emails found</h3>
            <p>Configure an email inbox and sync to see incoming emails here.</p>
            <a href="{{ route('admin.email-inbox.settings') }}" class="btn-modern btn-modern-primary"><i class="fas fa-cog"></i> Configure Inbox</a>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('#filterForm select, #filterForm input').forEach(el => {
        el.addEventListener('change', () => document.getElementById('filterForm').submit());
    });
</script>
@endpush
@endsection
