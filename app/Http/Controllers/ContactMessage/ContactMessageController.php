<?php
namespace App\Http\Controllers\ContactMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function index() { $data = ContactMessage::latest()->paginate(20); return view('admin.ContactMessage.index', compact('data')); }
    public function create() { return view('admin.ContactMessage.create'); }
    public function store(Request $r) { ContactMessage::create($r->all()); return redirect()->route("admin.contact-messages.index")->with('success','Created successfully'); }
    public function show(ContactMessage $item) { return view('admin.ContactMessage.show', compact('item')); }
    public function edit(ContactMessage $item) { return view('admin.ContactMessage.edit', compact('item')); }
    public function update(Request $r, ContactMessage $item) { $item->update($r->all()); return redirect()->route("admin.contact-messages.index")->with('success','Updated successfully'); }
    public function destroy(ContactMessage $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}