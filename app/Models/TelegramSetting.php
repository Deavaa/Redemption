<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramSetting extends Model
{
    protected $fillable = ['bot_token', 'chat_id', 'webhook_url', 'is_enabled', 'welcome_message'];

    protected $casts = ['is_enabled' => 'boolean'];

    public static function getSettings()
    {
        return static::first() ?? static::create(['is_enabled' => false]);
    }
}
