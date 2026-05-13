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

    public function show(ContactMessage $contact_message)
    {
        if (!$contact_message->is_read) {
            $contact_message->update(['is_read' => true]);
        }
        $contact_message->load('branch');
        return view('admin.ContactMessage.show', ['item' => $contact_message]);
    }

    public function destroy(ContactMessage $contact_message)
    {
        $contact_message->delete();
        return back()->with('success','Message deleted successfully');
    }
}
