<?php
namespace App\Http\Controllers\Certificate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate;

class CertificateController extends Controller
{
    public function index() { $data = Certificate::latest()->paginate(20); return view('admin.Certificate.index', compact('data')); }
    public function create() { return view('admin.Certificate.create'); }
    public function store(Request $r) { Certificate::create($r->all()); return redirect()->route("admin.certificates.index")->with('success','Created successfully'); }
    public function show(Certificate $item) { return view('admin.Certificate.show', compact('item')); }
    public function edit(Certificate $item) { return view('admin.Certificate.edit', compact('item')); }
    public function update(Request $r, Certificate $item) { $item->update($r->all()); return redirect()->route("admin.certificates.index")->with('success','Updated successfully'); }
    public function destroy(Certificate $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}