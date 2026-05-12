<?php
namespace App\Http\Controllers\GalleryVideo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GalleryVideo;

class GalleryVideoController extends Controller
{
    public function index() { $data = GalleryVideo::latest()->paginate(20); return view('admin.GalleryVideo.index', compact('data')); }
    public function create() { return view('admin.GalleryVideo.create'); }
    public function store(Request $r) { GalleryVideo::create($r->all()); return redirect()->route("admin.gallery-videos.index")->with('success','Created successfully'); }
    public function show(GalleryVideo $item) { return view('admin.GalleryVideo.show', compact('item')); }
    public function edit(GalleryVideo $item) { return view('admin.GalleryVideo.edit', compact('item')); }
    public function update(Request $r, GalleryVideo $item) { $item->update($r->all()); return redirect()->route("admin.gallery-videos.index")->with('success','Updated successfully'); }
    public function destroy(GalleryVideo $item) { $item->delete(); return back()->with('success','Deleted successfully'); }
}