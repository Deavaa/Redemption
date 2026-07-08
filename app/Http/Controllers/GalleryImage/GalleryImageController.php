<?php
namespace App\Http\Controllers\GalleryImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GalleryImage;
use Illuminate\Support\Str;

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
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
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
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
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
     * Bulletproof image storage — saves to BOTH storage/app/public/gallery/
     * AND public/gallery/ so the image is web-accessible regardless of
     * storage:link state. Returns the relative path "gallery/<file>".
     */
    private function storeGalleryImage($file): ?string
    {
        if (!$file || !$file->isValid()) {
            \Log::error('storeGalleryImage: invalid file', [
                'error' => $file ? $file->getErrorMessage() : 'null file',
            ]);
            return null;
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
            $extension = 'jpg';
        }
        $filename = 'gallery_' . date('Ymd_His') . '_' . Str::random(8) . '.' . $extension;
        $relativePath = 'gallery/' . $filename;
        $sourcePath = $file->getRealPath();

        // Location 1: public/gallery/ (PRIMARY — directly web-accessible)
        $publicDir = public_path('gallery');
        if (!is_dir($publicDir)) {
            @mkdir($publicDir, 0777, true);
        }
        $publicPath = $publicDir . '/' . $filename;
        $publicSaved = $this->saveFileWithFallback($sourcePath, $publicPath);

        // Location 2: storage/app/public/gallery/ (Laravel standard)
        $storageDir = storage_path('app/public/gallery');
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0777, true);
        }
        $storagePath = $storageDir . '/' . $filename;
        $storageSaved = $this->saveFileWithFallback($sourcePath, $storagePath);

        \Log::info('storeGalleryImage: result', [
            'filename' => $filename,
            'public_saved' => $publicSaved,
            'storage_saved' => $storageSaved,
        ]);

        return ($publicSaved || $storageSaved) ? $relativePath : null;
    }

    /**
     * Save a file using multiple methods. Returns true if any succeeded.
     */
    private function saveFileWithFallback(string $source, string $destination): bool
    {
        if (is_uploaded_file($source)) {
            if (@move_uploaded_file($source, $destination)) return true;
        }
        if (@copy($source, $destination)) return true;
        $data = @file_get_contents($source);
        if ($data !== false && strlen($data) > 0) {
            $written = @file_put_contents($destination, $data);
            if ($written !== false && $written > 0) return true;
        }
        $src = @fopen($source, 'rb');
        $dst = @fopen($destination, 'wb');
        if ($src && $dst) {
            $bytes = stream_copy_to_stream($src, $dst);
            fclose($src); fclose($dst);
            if ($bytes > 0) return true;
        }
        if ($src) fclose($src);
        if ($dst) fclose($dst);
        return false;
    }
}

