<?php
namespace App\Http\Controllers\ContactMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function index(Request $r)
    {
        $q = ContactMessage::with('branch');
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('name', 'LIKE', "%$s%")->orWhere('subject', 'LIKE', "%$s%")->orWhere('email', 'LIKE', "%$s%");
        }
        if ($r->filled('is_read')) $q->where('is_read', $r->is_read === 'yes');
        $data = $q->latest()->paginate(20);
        $totalMessages = ContactMessage::count();
        $unreadCount = ContactMessage::where('is_read', false)->count();
        return view('admin.ContactMessage.index', compact('data', 'totalMessages', 'unreadCount'));
    }

    public function show(ContactMessage $item)
    {
        if (!$item->is_read) {
            $item->update(['is_read' => true]);
        }
        $item->load('branch');
        return view('admin.ContactMessage.show', compact('item'));
    }

    public function destroy(ContactMessage $item)
    {
        $item->delete();
        return back()->with('success','Message deleted successfully');
    }
}
