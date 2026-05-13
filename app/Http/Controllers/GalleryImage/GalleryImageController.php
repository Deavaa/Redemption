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
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'category' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['title','description','category','sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('image_path')) {
            $data['image_path'] = $r->file('image_path')->store('gallery', 'public');
        }
        GalleryImage::create($data);
        return redirect()->route("admin.gallery-images.index")->with('success','Image added successfully');
    }

    public function show(GalleryImage $gallery_image) { return view('admin.GalleryImage.show', ['item' => $gallery_image]); }

    public function edit(GalleryImage $gallery_image) { return view('admin.GalleryImage.edit', ['item' => $gallery_image]); }

    public function update(Request $r, GalleryImage $gallery_image)
    {
        $r->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'category' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['title','description','category','sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('image_path')) {
            $data['image_path'] = $r->file('image_path')->store('gallery', 'public');
        }
        $gallery_image->update($data);
        return redirect()->route("admin.gallery-images.index")->with('success','Image updated successfully');
    }

    public function destroy(GalleryImage $gallery_image) { $gallery_image->delete(); return back()->with('success','Image deleted successfully'); }
}
