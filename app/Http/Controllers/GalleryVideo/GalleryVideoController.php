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
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'required|string|max:500',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['title','description','video_url','sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('thumbnail')) {
            $data['thumbnail'] = $r->file('thumbnail')->store('gallery/thumbnails', 'public');
        }
        GalleryVideo::create($data);
        return redirect()->route("admin.gallery-videos.index")->with('success','Video added successfully');
    }

    public function show(GalleryVideo $gallery_video) { return view('admin.GalleryVideo.show', ['item' => $gallery_video]); }

    public function edit(GalleryVideo $gallery_video) { return view('admin.GalleryVideo.edit', ['item' => $gallery_video]); }

    public function update(Request $r, GalleryVideo $gallery_video)
    {
        $r->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'required|string|max:500',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['title','description','video_url','sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('thumbnail')) {
            $data['thumbnail'] = $r->file('thumbnail')->store('gallery/thumbnails', 'public');
        }
        $gallery_video->update($data);
        return redirect()->route("admin.gallery-videos.index")->with('success','Video updated successfully');
    }

    public function destroy(GalleryVideo $gallery_video) { $gallery_video->delete(); return back()->with('success','Video deleted successfully'); }
}
