@extends('parent.layout')
@section('title', 'Messages')

@section('content')
<div style="max-width:1200px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <h4 style="font-size:18px;font-weight:700;color:var(--text-dark);margin:0;">Messages</h4>
            <p style="font-size:13px;color:var(--text-muted);margin:2px 0 0;">Communicate with teachers and school staff</p>
        </div>
        <button type="button" class="btn-modern btn-modern-primary" data-bs-toggle="modal" data-bs-target="#newChatModal">
            <i class="fas fa-plus"></i> New Conversation
        </button>
    </div>

    <div class="chat-layout" style="display:grid;grid-template-columns:340px 1fr;gap:0;background:#fff;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);border:1px solid var(--border);height:calc(100vh - 220px);min-height:450px;">
        <div style="border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;">
            <div style="padding:0.75rem 1rem;border-bottom:1px solid var(--border);">
                <input type="text" placeholder="Search conversations..." id="chatSearch" style="width:100%;border:1px solid var(--border);border-radius:var(--radius-sm);padding:6px 10px;font-size:13px;font-family:var(--font);outline:none;">
            </div>
            <div class="chat-list" style="overflow-y:auto;flex:1;">
                @forelse($conversations as $conv)
                    <a href="{{ route('parent.chat.show', $conv->id) }}" style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;text-decoration:none;color:var(--text);border-bottom:1px solid #f3f4f6;transition:background 0.15s;{{ request()->route('id') == $conv->id ? 'background:var(--primary-light);border-left:3px solid var(--primary);' : '' }}">
                        <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                            @if($conv->type === 'group')<i class="fas fa-users"></i>@else{{ strtoupper(substr($conv->otherParticipant?->name ?? '?', 0, 1)) }}@endif
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;justify-content:space-between;">
                                <span style="font-weight:600;font-size:0.85rem;color:var(--text-dark);">
                                    @if($conv->type === 'group'){{ $conv->title ?? 'Group' }}@else{{ $conv->otherParticipant?->name ?? 'Unknown' }}@endif
                                </span>
                                <span style="font-size:0.7rem;color:var(--text-muted);">@if($conv->lastMessage){{ $conv->lastMessage->created_at->format('M d') }}@endif</span>
                            </div>
                            <div style="font-size:0.78rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;">
                                @if($conv->lastMessage){{ Str::limit($conv->lastMessage->message, 35) }}@else<em>No messages yet</em>@endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="text-align:center;padding:3rem 1rem;color:var(--text-muted);">
                        <i class="fas fa-comments" style="font-size:2.5rem;margin-bottom:0.5rem;display:block;opacity:0.3;"></i>
                        <p>No conversations yet</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-muted);gap:0.75rem;">
            <i class="fas fa-comment-dots" style="font-size:3rem;opacity:0.3;"></i>
            <h4 style="color:var(--text-muted);font-weight:600;">Select a conversation</h4>
            <p style="font-size:13px;">Choose from the list or start a new one</p>
        </div>
    </div>
</div>

{{-- Floating New Chat Button for Mobile --}}
<a href="#" class="parent-chat-fab-new" onclick="event.preventDefault(); var modal = new bootstrap.Modal(document.getElementById('newChatModal')); modal.show();">
    <i class="fas fa-plus"></i>
</a>

<div class="modal fade" id="newChatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('parent.chat.store') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">New Conversation</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
                        <input type="text" name="title" class="form-control" placeholder="Group name">
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

<style>
/* Mobile: hide header button, show FAB */
@media (max-width: 768px) {
    .parent-chat-fab-new {
        display: flex;
        position: fixed;
        bottom: calc(70px + env(safe-area-inset-bottom, 0px));
        right: 16px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-hover, var(--primary)));
        color: #fff;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        box-shadow: 0 4px 16px rgba(67, 97, 238, 0.4);
        z-index: 900;
        text-decoration: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .parent-chat-fab-new:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 24px rgba(67, 97, 238, 0.5);
        color: #fff;
    }
}
/* Hide FAB on desktop */
@media (min-width: 769px) {
    .parent-chat-fab-new { display: none; }
}
</style>
<script>
document.getElementById('chatType')?.addEventListener('change', function() {
    document.getElementById('groupTitleField').style.display = this.value === 'group' ? 'block' : 'none';
    document.getElementById('participantSelect').multiple = this.value === 'group';
});
document.getElementById('chatSearch')?.addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.chat-list a').forEach(function(item) {
        var text = item.textContent.toLowerCase();
        item.style.display = text.includes(q) ? '' : 'none';
    });
});
</script>
@endsection
