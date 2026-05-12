<?php
namespace App\Http\Controllers\IdCard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdCard;

class IdCardController extends Controller
{
    public function index() { $data = IdCard::latest()->paginate(20); return view('admin.IdCard.index', compact('data')); }
    public function create() { return view('admin.IdCard.create'); }
    public function store(Request $r) { IdCard::create($r->all()); return redirect()->route("admin.id-cards.index")->with('success','Created successfully'); }
    public function show(IdCard $item) { return view('admin.IdCard.show', compact('item')); }
    public function edit(IdCard $item) { return view('admin.IdCard.edit', compact('item')); }
    public function update(Request $r, IdCard $item) { $item->update($r->all()); return redirect()->route("admin.id-cards.index")->with('success','Updated successfully'); }
    public function destroy(IdCard $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}