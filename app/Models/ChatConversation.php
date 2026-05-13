<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    protected $fillable = ['type', 'title', 'created_by', 'last_message_at', 'is_active'];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'conversation_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getOtherParticipantAttribute()
    {
        if ($this->type !== 'private') {
            return null;
        }

        return $this->participants()->where('user_id', '!=', auth()->id())->first()?->user;
    }
}
