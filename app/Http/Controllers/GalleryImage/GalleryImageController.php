<?php
namespace App\Http\Controllers\GalleryImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GalleryImage;
use Illuminate\Support\Str;
use App\Helpers\ImageCompressor;

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
        // Support BOTH single file (image_path) and multi-file (images[]) upload.
        // Multi-file upload creates one GalleryImage record per file, all sharing
        // the same group title (with optional per-image suffix "- 1", "- 2", etc.)
        // and the same category/description/sort_order/is_active.

        $r->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'category' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'batch_mode' => 'nullable|boolean',
        ]);

        $baseTitle = $r->input('title') ?: '';
        $description = $r->input('description');
        $category = $r->input('category');
        $sortOrder = $r->input('sort_order', 0);
        $isActive = $r->has('is_active') ? 1 : 0;

        $created = [];

        // ===== Batch upload (multiple files) =====
        if ($r->hasFile('images')) {
            $files = $r->file('images');
            $total = count($files);
            $idx = 1;
            foreach ($files as $file) {
                if (!$file->isValid()) continue;
                $path = $this->storeGalleryImage($file);
                if (!$path) continue;

                // If batch has multiple files, append " - N" suffix to the title
                // so each image has a unique but related name
                $title = $baseTitle;
                if ($total > 1 && !empty($title)) {
                    $title = $baseTitle . ' - ' . $idx;
                } elseif (empty($title)) {
                    $title = $file->getClientOriginalName();
                }

                $created[] = GalleryImage::create([
                    'title' => $title,
                    'description' => $description,
                    'image_path' => $path,
                    'category' => $category,
                    'sort_order' => $sortOrder,
                    'is_active' => $isActive,
                ]);
                $idx++;
            }
            $count = count($created);
            return redirect()->route("admin.gallery-images.index")
                ->with('success', $count . ' image(s) added successfully' . ($count < $total ? ' (' . ($total - $count) . ' failed)' : ''));
        }

        // ===== Single file upload (legacy / single image mode) =====
        $data = $r->only(['title','description','category','sort_order']);
        $data['is_active'] = $isActive;
        if ($r->hasFile('image_path')) {
            $path = $this->storeGalleryImage($r->file('image_path'));
            if ($path) {
                $data['image_path'] = $path;
            } else {
                return back()->with('error', 'Failed to store image file. Check storage/logs/laravel.log.')->withInput();
            }
        } else {
            // No file uploaded — validation should have caught this, but be safe
            return back()->with('error', 'Please select at least one image to upload.')->withInput();
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
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'category' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['title','description','category','sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('image_path')) {
            $path = $this->storeGalleryImage($r->file('image_path'));
            if ($path) {
                $data['image_path'] = $path;
            }
        }
        $gallery_image->update($data);
        return redirect()->route("admin.gallery-images.index")->with('success','Image updated successfully');
    }

    public function destroy(GalleryImage $gallery_image) { $gallery_image->delete(); return back()->with('success','Image deleted successfully'); }

    /**
     * Store an uploaded image — uses ImageCompressor to automatically
     * compress images larger than 2MB before saving.
     */
    private function storeGalleryImage($file): ?string
    {
        if (!$file || !$file->isValid()) {
            \Log::error('storeGalleryImage: invalid file', [
                'error' => $file ? $file->getErrorMessage() : 'null file',
            ]);
            return null;
        }

        $path = ImageCompressor::compressAndStore($file, 'gallery', 2048, 1920);
        if ($path) {
            \Log::info('storeGalleryImage: success via ImageCompressor', ['path' => $path]);
            return $path;
        }

        \Log::error('storeGalleryImage: ImageCompressor returned null');
        return null;
    }
}

