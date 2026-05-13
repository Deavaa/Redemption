<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('admin.Notification.index', compact('notifications'));
    }

    public function markRead($id)
    {
        $n = Notification::where('user_id', Auth::id())->findOrFail($id);
        $n->update(['is_read' => true]);

        if ($n->link) {
            return redirect($n->link);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy($id)
    {
        Notification::where('user_id', Auth::id())->findOrFail($id)->delete();
        return back()->with('success', 'Notification deleted.');
    }

    public function apiUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        $notifications = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'count' => $count,
            'notifications' => $notifications,
        ]);
    }

    public function apiLatest()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}
