<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramMessage extends Model
{
    protected $fillable = ['chat_id', 'from_id', 'from_name', 'message', 'direction', 'status'];

    protected $casts = [];
}
