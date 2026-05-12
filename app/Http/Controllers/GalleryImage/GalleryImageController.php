<?php
namespace App\Http\Controllers\GalleryImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GalleryImage;

class GalleryImageController extends Controller
{
    public function index() { $data = GalleryImage::latest()->paginate(20); return view('admin.GalleryImage.index', compact('data')); }
    public function create() { return view('admin.GalleryImage.create'); }
    public function store(Request $r) { GalleryImage::create($r->all()); return redirect()->route("admin.gallery-images.index")->with('success','Created successfully'); }
    public function show(GalleryImage $item) { return view('admin.GalleryImage.show', compact('item')); }
    public function edit(GalleryImage $item) { return view('admin.GalleryImage.edit', compact('item')); }
    public function update(Request $r, GalleryImage $item) { $item->update($r->all()); return redirect()->route("admin.gallery-images.index")->with('success','Updated successfully'); }
    public function destroy(GalleryImage $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}