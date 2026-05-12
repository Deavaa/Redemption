<?php
namespace App\Http\Controllers\GalleryVideo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GalleryVideo;

class GalleryVideoController extends Controller
{
    public function index(Request $r)
    {
        $q = GalleryVideo::query();
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('title', 'LIKE', "%$s%")->orWhere('video_url', 'LIKE', "%$s%");
        }
        $data = $q->orderBy('sort_order')->paginate(20);
        $totalVideos = GalleryVideo::count();
        return view('admin.GalleryVideo.index', compact('data', 'totalVideos'));
    }

    public function create() { return view('admin.GalleryVideo.create'); }

    public function store(Request $r)
    {
        $r->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'required|string|max:500',
            'thumbnail' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        GalleryVideo::create($r->only(['title','description','video_url','thumbnail','sort_order','is_active']));
        return redirect()->route("admin.gallery-videos.index")->with('success','Video added successfully');
    }

    public function show(GalleryVideo $item) { return view('admin.GalleryVideo.show', compact('item')); }

    public function edit(GalleryVideo $item) { return view('admin.GalleryVideo.edit', compact('item')); }

    public function update(Request $r, GalleryVideo $item)
    {
        $r->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'required|string|max:500',
            'thumbnail' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $item->update($r->only(['title','description','video_url','thumbnail','sort_order','is_active']));
        return redirect()->route("admin.gallery-videos.index")->with('success','Video updated successfully');
    }

    public function destroy(GalleryVideo $item) { $item->delete(); return back()->with('success','Video deleted successfully'); }
}
