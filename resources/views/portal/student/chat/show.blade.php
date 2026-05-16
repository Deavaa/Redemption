@extends('student.layout')
@section('title', 'Chat')

@section('content')
<div style="max-width:1200px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <h4 style="font-size:18px;font-weight:700;color:var(--text-dark);margin:0;">
                @if($conversation->type === 'group'){{ $conversation->title ?? 'Group Chat' }}@else{{ $conversation->otherParticipant?->name ?? 'Chat' }}@endif
            </h4>
        </div>
        <div style="display:flex;gap:0.5rem;">
            <a href="{{ route('student.chat.index') }}" class="btn-modern btn-modern-outline"><i class="fas fa-arrow-left"></i> Back</a>
            <form method="POST" action="{{ route('student.chat.destroy', $conversation->id) }}" onsubmit="return confirm('Delete this conversation?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-modern" style="background:var(--danger);color:#fff;border:none;padding:7px 14px;border-radius:var(--radius-sm);font-weight:600;font-size:12px;cursor:pointer;"><i class="fas fa-trash"></i> Delete</button>
            </form>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:280px 1fr;gap:0;background:#fff;border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);border:1px solid var(--border);height:calc(100vh - 220px);min-height:450px;">
        {{-- Mini sidebar --}}
        <div style="border-right:1px solid var(--border);overflow-y:auto;">
            @php
                $allConvs = \App\Models\ChatConversation::whereHas('participants', function($q) {
                    $q->where('user_id', auth()->id());
                })->with(['participants.user', 'lastMessage.sender'])
                  ->orderByDesc('last_message_at')->limit(30)->get();
            @endphp
            @foreach($allConvs as $conv)
                <a href="{{ route('student.chat.show', $conv->id) }}" style="display:flex;align-items:center;gap:0.6rem;padding:0.65rem 0.85rem;text-decoration:none;color:var(--text);border-bottom:1px solid #f3f4f6;{{ $conv->id === $conversation->id ? 'background:var(--primary-light);border-left:3px solid var(--primary);' : '' }}">
                    <div style="width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.8rem;flex-shrink:0;">
                        @if($conv->type === 'group')<i class="fas fa-users"></i>@else{{ strtoupper(substr($conv->otherParticipant?->name ?? '?', 0, 1)) }}@endif
                    </div>
                    <div style="flex:1;min-width:0;overflow:hidden;">
                        <div style="font-weight:600;font-size:0.82rem;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            @if($conv->type === 'group'){{ $conv->title ?? 'Group' }}@else{{ $conv->otherParticipant?->name ?? '?' }}@endif
                        </div>
                        <div style="font-size:0.75rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Str::limit($conv->lastMessage?->message ?? 'No messages', 25) }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Chat window --}}
        <div style="display:flex;flex-direction:column;">
            <div style="padding:0.75rem 1rem;border-bottom:1px solid var(--border);background:#fff;display:flex;align-items:center;gap:0.75rem;">
                <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.85rem;flex-shrink:0;">
                    @if($conversation->type === 'group')<i class="fas fa-users"></i>@else{{ strtoupper(substr($conversation->otherParticipant?->name ?? '?', 0, 1)) }}@endif
                </div>
                <div>
                    <strong style="font-size:0.95rem;">@if($conversation->type === 'group'){{ $conversation->title ?? 'Group' }}@else{{ $conversation->otherParticipant?->name ?? 'Unknown' }}@endif</strong>
                    <div style="font-size:0.75rem;color:var(--text-muted);">
                        @if($conversation->type === 'group'){{ $conversation->participants->count() }} members@else{{ $conversation->otherParticipant?->email ?? '' }}@endif
                    </div>
                </div>
            </div>

            <div id="chatMessages" style="flex:1;overflow-y:auto;padding:1rem;background:#f0fdfa;display:flex;flex-direction:column;gap:0.65rem;">
                @foreach($conversation->messages->reverse() as $msg)
                    <div style="display:flex;flex-direction:column;max-width:70%;{{ $msg->sender_id === auth()->id() ? 'align-self:flex-end;' : 'align-self:flex-start;' }}">
                        <div style="padding:0.55rem 0.9rem;border-radius:14px;font-size:0.88rem;line-height:1.5;word-break:break-word;{{ $msg->sender_id === auth()->id() ? 'background:linear-gradient(135deg,var(--primary),var(--primary-hover));color:#fff;border-bottom-right-radius:4px;' : 'background:#fff;border:1px solid var(--border);border-bottom-left-radius:4px;' }}">
                            @if($msg->type === 'system')
                                <em style="color:var(--text-muted);">{{ $msg->message }}</em>
                            @else
                                @if($msg->sender_id !== auth()->id())
                                    <div style="font-size:0.72rem;font-weight:600;color:var(--primary);margin-bottom:2px;">{{ $msg->sender->name }}</div>
                                @endif
                                @if($msg->type === 'image' && $msg->file_path)
                                    <img src="{{ asset('storage/' . $msg->file_path) }}" style="max-width:220px;border-radius:8px;margin-bottom:4px;">
                                @elseif($msg->type === 'file' && $msg->file_path)
                                    <a href="{{ asset('storage/' . $msg->file_path) }}" target="_blank" style="color:var(--primary);font-weight:500;"><i class="fas fa-file-download"></i> {{ basename($msg->file_path) }}</a>
                                @endif
                                @if($msg->message)
                                    <div style="white-space:pre-wrap;">{{ $msg->message }}</div>
                                @endif
                            @endif
                        </div>
                        <div style="font-size:0.68rem;color:var(--text-muted);margin-top:2px;{{ $msg->sender_id === auth()->id() ? 'text-align:right;' : 'text-align:left;' }}">
                            {{ $msg->created_at->format('h:i A') }}
                            @if($msg->sender_id === auth()->id())<i class="fas fa-check{{ $msg->is_read ? '-double' : '' }}" style="color:{{ $msg->is_read ? 'var(--primary)' : 'var(--text-muted)' }};"></i>@endif
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('student.chat.send', $conversation->id) }}" style="padding:0.65rem 1rem;border-top:1px solid var(--border);background:#fff;display:flex;align-items:center;gap:0.5rem;" enctype="multipart/form-data">
                @csrf
                <label style="width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--text-muted);cursor:pointer;flex-shrink:0;" title="Attach file">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" name="file" style="display:none;" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                </label>
                <input type="text" name="message" placeholder="Type a message..." autocomplete="off" required style="flex:1;border:1.5px solid var(--border);border-radius:24px;padding:0.5rem 0.9rem;font-size:0.88rem;outline:none;font-family:var(--font);">
                <button type="submit" style="width:38px;height:38px;border-radius:50%;border:none;background:linear-gradient(135deg,var(--primary),var(--primary-hover));color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
var chatMessages = document.getElementById('chatMessages');
if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
</script>
@endsection
