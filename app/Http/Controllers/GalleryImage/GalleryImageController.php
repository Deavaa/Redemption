<?php
namespace App\Http\Controllers\GalleryImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GalleryImage;

class GalleryImageController extends Controller
{
    public function index(Request $r)
    {
        $q = GalleryImage::query();
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('title', 'LIKE', "%$s%")->orWhere('category', 'LIKE', "%$s%");
        }
        if ($r->filled('category')) $q->where('category', $r->category);
        $data = $q->orderBy('sort_order')->paginate(20);
        $totalImages = GalleryImage::count();
        return view('admin.GalleryImage.index', compact('data', 'totalImages'));
    }

    public function create() { return view('admin.GalleryImage.create'); }

    public function store(Request $r)
    {
        $r->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'required|string|max:500',
            'category' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        GalleryImage::create($r->only(['title','description','image_path','category','sort_order','is_active']));
        return redirect()->route("admin.gallery-images.index")->with('success','Image added successfully');
    }

    public function show(GalleryImage $item) { return view('admin.GalleryImage.show', compact('item')); }

    public function edit(GalleryImage $item) { return view('admin.GalleryImage.edit', compact('item')); }

    public function update(Request $r, GalleryImage $item)
    {
        $r->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'required|string|max:500',
            'category' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $item->update($r->only(['title','description','image_path','category','sort_order','is_active']));
        return redirect()->route("admin.gallery-images.index")->with('success','Image updated successfully');
    }

    public function destroy(GalleryImage $item) { $item->delete(); return back()->with('success','Image deleted successfully'); }
}
