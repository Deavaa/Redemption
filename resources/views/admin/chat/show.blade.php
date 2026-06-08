@extends('layouts.admin')
@section('title', 'Chat')

@section('content')
<div class="modern-page chat-full-bleed">
    <div class="modern-page-header chat-page-header">
        <div class="modern-page-header-left">
            <nav aria-label="breadcrumb" class="modern-breadcrumb">
                <ol>
                    <li><a href="{{ route('admin.dashboard') }}"><i class="fas fa-home"></i></a></li>
                    <li><a href="{{ route($routePrefix . '.index') }}">Chat</a></li>
                    <li class="active">
                        @if($conversation->type === 'group')
                            {{ $conversation->title ?? 'Group' }}
                        @else
                            {{ $conversation->otherParticipant?->name ?? 'Chat' }}
                        @endif
                    </li>
                </ol>
            </nav>
        </div>
        <div class="modern-page-header-right">
            <a href="{{ route($routePrefix . '.index') }}" class="btn-modern btn-modern-outline chat-back-btn">
                <i class="fas fa-arrow-left"></i> <span>Back</span>
            </a>
        </div>
    </div>

    <div class="chat-layout">
        {{-- Conversations List (mini) --}}
        <div class="chat-sidebar">
            <div class="chat-list">
                @php
                    $allConvs = \App\Models\ChatConversation::whereHas('participants', function($q) {
                        $q->where('user_id', auth()->id());
                    })->with(['participants.user', 'lastMessage.sender'])
                      ->orderByDesc('last_message_at')->limit(30)->get();
                @endphp
                @foreach($allConvs as $conv)
                    <a href="{{ route($routePrefix . '.show', $conv->id) }}" class="chat-item {{ $conv->id === $conversation->id ? 'active' : '' }}">
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
                                    @if($conv->type === 'group'){{ $conv->title ?? 'Group' }}@else{{ $conv->otherParticipant?->name ?? '?' }}@endif
                                </span>
                            </div>
                            <div class="chat-item-preview">{{ Str::limit($conv->lastMessage?->message ?? 'No messages', 30) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Active Chat --}}
        <div class="chat-window">
            {{-- Header --}}
            <div class="chat-window-header">
                <a href="{{ route($routePrefix . '.index') }}" class="chat-mobile-back" title="Back">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="chat-window-user">
                    <div class="chat-item-avatar">
                        @if($conversation->type === 'group')
                            <i class="fas fa-users"></i>
                        @else
                            {{ strtoupper(substr($conversation->otherParticipant?->name ?? '?', 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <strong>
                            @if($conversation->type === 'group'){{ $conversation->title ?? 'Group' }}@else{{ $conversation->otherParticipant?->name ?? 'Unknown' }}@endif
                        </strong>
                        <div class="text-muted small">
                            @if($conversation->type === 'group')
                                {{ $conversation->participants->count() }} members
                            @else
                                {{ $conversation->otherParticipant?->email ?? '' }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Messages --}}
            <div class="chat-messages" id="chatMessages">
                @foreach($conversation->messages->reverse() as $msg)
                    <div class="chat-msg {{ $msg->sender_id === auth()->id() ? 'chat-msg-sent' : 'chat-msg-received' }}">
                        <div class="chat-msg-bubble">
                            @if($msg->type === 'system')
                                <em class="text-muted">{{ $msg->message }}</em>
                            @else
                                @if($msg->sender_id !== auth()->id())
                                    <div class="chat-msg-sender">{{ $msg->sender->name }}</div>
                                @endif
                                @if($msg->type === 'image' && $msg->file_path)
                                    <img src="{{ asset('storage/' . $msg->file_path) }}" alt="Image" class="chat-msg-image">
                                @elseif($msg->type === 'file' && $msg->file_path)
                                    <a href="{{ asset('storage/' . $msg->file_path) }}" target="_blank" class="chat-msg-file">
                                        <i class="fas fa-file-download"></i> {{ basename($msg->file_path) }}
                                    </a>
                                @endif
                                @if($msg->message)
                                    <div class="chat-msg-text">{{ $msg->message }}</div>
                                @endif
                            @endif
                        </div>
                        <div class="chat-msg-time">
                            {{ $msg->created_at->format('h:i A') }}
                            @if($msg->sender_id === auth()->id())
                                <i class="fas fa-check{{ $msg->is_read ? '-double text-primary' : '' }}"></i>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Message Input --}}
            <form method="POST" action="{{ route($routePrefix . '.send', $conversation->id) }}" class="chat-input-area" enctype="multipart/form-data">
                @csrf
                <div class="chat-input-wrapper">
                    <label class="chat-attach-btn" title="Attach file">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" name="file" class="d-none" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                    </label>
                    <input type="text" name="message" class="chat-input" placeholder="Type a message..." autocomplete="off" id="chatInput" required>
                    <button type="submit" class="chat-send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
.chat-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 0;
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    border: 1px solid #f0f0f0;
    height: calc(100vh - 200px);
    min-height: 500px;
}
.chat-sidebar { border-right: 1px solid #f0f0f0; display: flex; flex-direction: column; overflow: hidden; }
.chat-list { overflow-y: auto; flex: 1; }
.chat-item {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1rem;
    text-decoration: none; color: var(--gray-700); border-bottom: 1px solid #f8f9fa; transition: background 0.15s;
}
.chat-item:hover { background: var(--gray-50); }
.chat-item.active { background: var(--primary-light); border-left: 3px solid var(--primary); }
.chat-item-avatar {
    width: 40px; height: 40px; border-radius: 10px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.85rem; flex-shrink: 0;
}
.chat-item-info { flex: 1; min-width: 0; }
.chat-item-header { display: flex; justify-content: space-between; }
.chat-item-name { font-weight: 600; font-size: 0.85rem; color: var(--gray-800); }
.chat-item-preview { font-size: 0.78rem; color: var(--gray-500); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.chat-window { display: flex; flex-direction: column; }
.chat-window-header {
    padding: 1rem 1.25rem; border-bottom: 1px solid #f0f0f0;
    background: #fff; display: flex; align-items: center; gap: 0.5rem;
}
.chat-window-user { display: flex; align-items: center; gap: 0.75rem; }
.chat-mobile-back {
    display: none;
    width: 32px; height: 32px; border-radius: 8px;
    background: var(--gray-50); border: 1px solid var(--border);
    color: var(--gray-600);
    align-items: center; justify-content: center;
    text-decoration: none; font-size: 0.85rem; flex-shrink: 0;
    transition: background 0.15s;
}
.chat-mobile-back:hover { background: var(--gray-100); color: var(--primary); }

.chat-messages {
    flex: 1; overflow-y: auto; padding: 1.25rem; background: #f8f9fb;
    display: flex; flex-direction: column; gap: 0.75rem;
}
.chat-msg { display: flex; flex-direction: column; max-width: 70%; }
.chat-msg-sent { align-self: flex-end; }
.chat-msg-received { align-self: flex-start; }
.chat-msg-bubble {
    padding: 0.65rem 1rem; border-radius: 14px; font-size: 0.9rem; line-height: 1.5;
    word-break: break-word;
}
.chat-msg-sent .chat-msg-bubble { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: #fff; border-bottom-right-radius: 4px; }
.chat-msg-received .chat-msg-bubble { background: #fff; border: 1px solid #e5e7eb; border-bottom-left-radius: 4px; }
.chat-msg-sender { font-size: 0.75rem; font-weight: 600; color: var(--primary); margin-bottom: 2px; }
.chat-msg-text { white-space: pre-wrap; }
.chat-msg-time { font-size: 0.7rem; color: var(--gray-400); margin-top: 2px; text-align: right; }
.chat-msg-sent .chat-msg-time { text-align: right; }
.chat-msg-received .chat-msg-time { text-align: left; }
.chat-msg-image { max-width: 250px; border-radius: 8px; margin-bottom: 4px; }
.chat-msg-file { color: var(--primary); font-weight: 500; text-decoration: none; }
.chat-msg-file:hover { text-decoration: underline; }

.chat-input-area {
    padding: 0.75rem 1.25rem; border-top: 1px solid #f0f0f0; background: #fff;
    display: flex; align-items: center;
}
.chat-input-wrapper { display: flex; align-items: center; gap: 0.5rem; width: 100%; }
.chat-input {
    flex: 1; border: 1.5px solid #e5e7eb; border-radius: 24px; padding: 0.6rem 1rem;
    font-size: 0.9rem; outline: none; transition: border-color 0.2s;
}
.chat-input:focus { border-color: var(--primary); }
.chat-attach-btn {
    width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center;
    justify-content: center; color: var(--gray-500); cursor: pointer; transition: color 0.2s;
}
.chat-attach-btn:hover { color: var(--primary); }
.chat-send-btn {
    width: 40px; height: 40px; border-radius: 50%; border: none;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: #fff; display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: transform 0.2s;
}
.chat-send-btn:hover { transform: scale(1.1); }

@media (max-width: 768px) {
    .chat-page-header { display: none !important; }
    .admin-content:has(.chat-layout),
    .admin-main:has(.chat-layout) {
        padding: 0 !important;
        padding-bottom: 0 !important;
        overflow: visible !important;
    }
    .admin-main:has(.chat-layout)::after { display: none !important; }
    .modern-page.chat-full-bleed {
        height: auto;
        min-height: calc(100dvh - 56px);
        overflow: visible;
    }
    .chat-layout {
        grid-template-columns: 1fr;
        height: calc(100dvh - 56px - 5vh - 56px);
        min-height: 300px;
        border-radius: 0;
        border: none;
        box-shadow: none;
        margin-bottom: 5vh;
    }
    .chat-sidebar { display: none; }
    .chat-mobile-back { display: flex; }
    .chat-window {
        min-height: 0;
        flex: 1;
    }
    .chat-window-header {
        padding: 0.6rem 0.75rem;
        flex-shrink: 0;
    }
    .chat-window-header .chat-item-avatar {
        width: 32px; height: 32px; font-size: 0.75rem; border-radius: 8px;
    }
    .chat-window-header strong { font-size: 0.88rem; }
    .chat-messages {
        padding: 0.75rem;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
    }
    .chat-msg { max-width: 85%; }
    .chat-input-area {
        padding: 0.5rem 0.75rem;
        padding-bottom: calc(0.5rem + env(safe-area-inset-bottom, 0px));
        flex-shrink: 0;
        border-top: 1px solid #f0f0f0;
        background: #fff;
        position: sticky;
        bottom: 0;
        z-index: 10;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Auto-scroll to bottom
const chatMessages = document.getElementById('chatMessages');
if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
</script>
@endpush
@endsection
