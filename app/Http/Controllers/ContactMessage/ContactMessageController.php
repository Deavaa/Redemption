<?php
namespace App\Http\Controllers\ContactMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactMessage;
use App\Models\Branch;

class ContactMessageController extends Controller
{
    /**
     * Store a new contact message from the public website.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Try to associate with the first branch if available
        $branchId = Branch::first()?->id;

        ContactMessage::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'subject'   => $validated['subject'] ?? 'General Inquiry',
            'message'   => $validated['message'],
            'branch_id' => $branchId,
            'is_read'   => false,
        ]);

        return redirect()->back()->with('success', 'Thank you for your message! We will get back to you soon.');
    }

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
