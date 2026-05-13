<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $conversations = ChatConversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['participants.user', 'lastMessage.sender'])
          ->orderByDesc('last_message_at')
          ->paginate(20);

        $users = User::where('id', '!=', $userId)->orderBy('name')->get(['id', 'name', 'email']);

        $unreadCount = ChatMessage::whereHas('conversation.participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->where('sender_id', '!=', $userId)->where('is_read', false)->count();

        return view('admin.chat.index', compact('conversations', 'users', 'unreadCount'));
    }

    public function show($id)
    {
        $userId = Auth::id();

        $conversation = ChatConversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['participants.user', 'messages.sender', 'messages.reads'])
          ->findOrFail($id);

        // Mark messages as read
        ChatMessage::where('conversation_id', $id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Update participant's last_read_at
        ChatParticipant::where('conversation_id', $id)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);

        return view('admin.chat.show', compact('conversation'));
    }

    public function storeConversation(Request $r)
    {
        $r->validate([
            'type' => 'required|in:private,group',
            'title' => 'nullable|string|max:255',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:users,id',
        ]);

        $userId = Auth::id();

        if ($r->type === 'private' && count($r->participant_ids) === 1) {
            // Check if private conversation already exists
            $existing = ChatConversation::where('type', 'private')
                ->whereHas('participants', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->whereHas('participants', function ($q) use ($r) {
                    $q->where('user_id', $r->participant_ids[0]);
                })
                ->first();

            if ($existing) {
                return redirect()->route('admin.chat.show', $existing->id);
            }
        }

        $conversation = ChatConversation::create([
            'type' => $r->type,
            'title' => $r->title,
            'created_by' => $userId,
        ]);

        // Add creator as admin
        ChatParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'role' => 'admin',
        ]);

        // Add other participants
        foreach ($r->participant_ids as $pid) {
            if ($pid != $userId) {
                ChatParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $pid,
                    'role' => 'member',
                ]);
            }
        }

        return redirect()->route('admin.chat.show', $conversation->id)->with('success', 'Conversation created.');
    }

    public function sendMessage(Request $r, $id)
    {
        $userId = Auth::id();

        $conversation = ChatConversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->findOrFail($id);

        $r->validate([
            'message' => 'required_without:file|string|nullable',
            'file' => 'nullable|file|max:10240',
        ]);

        $type = 'text';
        $filePath = null;

        if ($r->hasFile('file')) {
            $file = $r->file('file');
            $filePath = $file->store('chat-files', 'public');

            if (str_starts_with($file->getMimeType(), 'image/')) {
                $type = 'image';
            } else {
                $type = 'file';
            }
        }

        $msg = ChatMessage::create([
            'conversation_id' => $id,
            'sender_id' => $userId,
            'message' => $r->message,
            'type' => $type,
            'file_path' => $filePath,
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($r->ajax() || $r->wantsJson()) {
            return response()->json($msg->load('sender'));
        }

        return redirect()->route('admin.chat.show', $id);
    }

    public function destroyConversation($id)
    {
        $userId = Auth::id();

        $conversation = ChatConversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('role', 'admin');
        })->findOrFail($id);

        $conversation->delete();

        return redirect()->route('admin.chat.index')->with('success', 'Conversation deleted.');
    }

    public function getMessages($id)
    {
        $userId = Auth::id();

        $conversation = ChatConversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->findOrFail($id);

        $messages = ChatMessage::where('conversation_id', $id)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($messages);
    }
}
