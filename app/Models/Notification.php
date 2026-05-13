<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'message', 'icon', 'link', 'is_read'];

    protected $casts = ['is_read' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public static function createForUser($userId, $type, $title, $message = null, $icon = 'fas fa-bell', $link = null)
    {
        return static::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'icon'    => $icon,
            'link'    => $link,
        ]);
    }

    public static function createForAllAdmins($type, $title, $message = null, $icon = 'fas fa-bell', $link = null)
    {
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            static::createForUser($admin->id, $type, $title, $message, $icon, $link);
        }
    }
}
