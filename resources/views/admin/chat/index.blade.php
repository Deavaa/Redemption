@extends('layouts.admin')
@section('title', 'Chat')

@section('content')
<div class="modern-page">
    <div class="modern-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li class="active">Chat</li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <button type="button" class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#newChatModal">
                <i class="fas fa-plus"></i>
                <span>New Conversation</span>
            </button>
        </div>
    </div>

    <div class="chat-layout">
        {{-- Conversations List --}}
        <div class="chat-sidebar">
            <div class="chat-search">
                <div class="modern-input-wrapper">
                    <i class="fas fa-search modern-input-icon"></i>
                    <input type="text" class="modern-input" placeholder="Search conversations..." id="chatSearch">
                </div>
            </div>
            <div class="chat-list">
                @forelse($conversations as $conv)
                    <a href="{{ route($routePrefix . '.show', $conv->id) }}" class="chat-item {{ request()->route('id') == $conv->id ? 'active' : '' }}">
                        <div class="chat-item-avatar">
                            @if($conv->type === 'group')
                                <i class="fas fa-users"></i>
                            @else
                                {{ strtoupper(substr($conv->otherParticipant?->name ?? '?', 0, 1)) }}
                            @endif
                        </div>
                        <div class="chat-item-info">
                            <div class="chat-item-header">
                                <span class="chat-item-name">
                                    @if($conv->type === 'group')
                                        {{ $conv->title ?? 'Group Chat' }}
                                    @else
                                        {{ $conv->otherParticipant?->name ?? 'Unknown' }}
                                    @endif
                                </span>
                                <span class="chat-item-time">
                                    @if($conv->lastMessage)
                                        {{ $conv->lastMessage->created_at->format('M d') }}
                                    @endif
                                </span>
                            </div>
                            <div class="chat-item-preview">
                                @if($conv->lastMessage)
                                    {{ Str::limit($conv->lastMessage->message, 40) }}
                                @else
                                    <em>No messages yet</em>
                                @endif
                            </div>
                        </div>
                        @php $unread = $conv->messages()->where('sender_id', '!=', auth()->id())->where('is_read', false)->count(); @endphp
                        @if($unread > 0)
                            <span class="chat-item-badge">{{ $unread }}</span>
                        @endif
                    </a>
                @empty
                    <div class="chat-empty">
                        <i class="fas fa-comments"></i>
                        <p>No conversations yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Chat Content --}}
        <div class="chat-content">
            <div class="chat-placeholder">
                <i class="fas fa-comment-dots"></i>
                <h3>Select a conversation</h3>
                <p>Choose a conversation from the list or start a new one</p>
            </div>
        </div>
    </div>
</div>

{{-- New Conversation Modal --}}
<div class="modal fade" id="newChatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route($routePrefix . '.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Conversation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" id="chatType">
                            <option value="private">Private (1-on-1)</option>
                            <option value="group">Group</option>
                        </select>
                    </div>
                    <div class="mb-3" id="groupTitleField" style="display:none;">
                        <label class="form-label">Group Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Science Department">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Member(s)</label>
                        <select name="participant_ids[]" class="form-select" id="participantSelect" size="8" required>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst(str_replace('_', ' ', $u->role ?? 'user')) }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple for group chats</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Conversation</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.chat-layout {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 0;
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
    height: calc(100vh - 200px);
    min-height: 500px;
}
.chat-sidebar {
    border-right: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.chat-search {
    padding: 1rem;
    border-bottom: 1px solid #f0f0f0;
}
.chat-list {
    overflow-y: auto;
    flex: 1;
}
.chat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
    text-decoration: none;
    color: var(--gray-700);
    border-bottom: 1px solid #f8f9fa;
    transition: background 0.15s;
}
.chat-item:hover { background: var(--gray-50); }
.chat-item.active { background: var(--primary-light); border-left: 3px solid var(--primary); }
.chat-item-avatar {
    width: 42px; height: 42px; border-radius: 12px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.9rem; flex-shrink: 0;
}
.chat-item-info { flex: 1; min-width: 0; }
.chat-item-header { display: flex; justify-content: space-between; align-items: center; }
.chat-item-name { font-weight: 600; font-size: 0.88rem; color: var(--gray-800); }
.chat-item-time { font-size: 0.72rem; color: var(--gray-400); }
.chat-item-preview { font-size: 0.8rem; color: var(--gray-500); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
.chat-item-badge {
    background: var(--danger); color: #fff; font-size: 0.7rem; font-weight: 700;
    border-radius: 50px; min-width: 20px; height: 20px; display: flex;
    align-items: center; justify-content: center; padding: 0 6px; flex-shrink: 0;
}
.chat-content { display: flex; flex-direction: column; }
.chat-placeholder {
    flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: var(--gray-400); gap: 0.75rem;
}
.chat-placeholder i { font-size: 4rem; opacity: 0.3; }
.chat-placeholder h3 { color: var(--gray-500); font-weight: 600; }
.chat-empty { text-align: center; padding: 3rem 1rem; color: var(--gray-400); }
.chat-empty i { font-size: 2.5rem; margin-bottom: 0.5rem; }
@media (max-width: 768px) {
    .chat-layout { grid-template-columns: 1fr; height: auto; min-height: 400px; }
    .chat-sidebar { max-height: 300px; }
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('chatType')?.addEventListener('change', function() {
    const titleField = document.getElementById('groupTitleField');
    const select = document.getElementById('participantSelect');
    if (this.value === 'group') {
        titleField.style.display = 'block';
        select.multiple = true;
    } else {
        titleField.style.display = 'none';
        select.multiple = false;
    }
});
document.getElementById('chatSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.chat-item').forEach(item => {
        const name = item.querySelector('.chat-item-name')?.textContent.toLowerCase() || '';
        const preview = item.querySelector('.chat-item-preview')?.textContent.toLowerCase() || '';
        item.style.display = (name.includes(q) || preview.includes(q)) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
