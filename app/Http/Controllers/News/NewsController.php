<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::with('creator', 'approver')->orderBy('priority', 'desc')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.news.index', compact('news'));
    }

    /**
     * Display a single news item.
     */
    public function show(News $news)
    {
        $news->load('creator', 'approver');
        return view('admin.news.show', compact('news'));
    }

    public function create()
    {
        return view('admin.news.create');
    }

    /**
     * Helper: Store an uploaded image file to public/news-images/ with
     * BULLETPROOF multi-method fallback. Logs every attempt so failures
     * are visible in storage/logs/laravel.log.
     *
     * Returns the relative path "news-images/<filename>" on success, null on failure.
     */
    private function storeNewsImage($file): ?string
    {
        if (!$file || !$file->isValid()) {
            \Log::error('storeNewsImage: file invalid', [
                'is_valid' => $file ? $file->isValid() : 'no file',
                'error' => $file ? $file->getErrorMessage() : 'null',
            ]);
            return null;
        }

        // Generate a clean unique filename
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'])) {
            $extension = 'jpg';
        }
        $filename = 'news_' . date('Ymd_His') . '_' . Str::random(8) . '.' . $extension;
        $relativePath = 'news-images/' . $filename;

        $sourcePath = $file->getRealPath();
        \Log::info('storeNewsImage: starting', [
            'filename' => $filename,
            'source_path' => $sourcePath,
            'source_exists' => file_exists($sourcePath),
            'source_readable' => is_readable($sourcePath),
            'source_size' => $sourcePath ? filesize($sourcePath) : 'n/a',
        ]);

        // ===== Location 1: public/news-images/ (PRIMARY — directly web-accessible) =====
        $publicDir = public_path('news-images');
        if (!is_dir($publicDir)) {
            $made = @mkdir($publicDir, 0777, true);
            \Log::info('storeNewsImage: mkdir public dir', [
                'dir' => $publicDir,
                'created' => $made ? 'YES' : 'NO',
                'already_existed' => is_dir($publicDir) ? 'YES' : 'NO',
            ]);
        }
        $publicPath = $publicDir . '/' . $filename;
        $publicSaved = $this->saveFileWithFallback($sourcePath, $publicPath);

        // ===== Location 2: storage/app/public/news-images/ (SECONDARY — for storage:link users) =====
        $storageDir = storage_path('app/public/news-images');
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0777, true);
        }
        $storagePath = $storageDir . '/' . $filename;
        $storageSaved = $this->saveFileWithFallback($sourcePath, $storagePath);

        \Log::info('storeNewsImage: result', [
            'filename' => $filename,
            'public_path' => $publicPath,
            'public_saved' => $publicSaved ? 'YES' : 'NO',
            'public_exists' => file_exists($publicPath) ? 'YES' : 'NO',
            'storage_path' => $storagePath,
            'storage_saved' => $storageSaved ? 'YES' : 'NO',
            'storage_exists' => file_exists($storagePath) ? 'YES' : 'NO',
        ]);

        // Return success if EITHER location got the file
        if ($publicSaved || $storageSaved) {
            return $relativePath;
        }
        \Log::error('storeNewsImage: BOTH locations failed — returning null');
        return null;
    }

    /**
     * Save a file using multiple methods (move_uploaded_file, copy, file_get/put).
     * Returns true if any method succeeded.
     */
    private function saveFileWithFallback(string $source, string $destination): bool
    {
        // Method 1: move_uploaded_file (most reliable for HTTP uploads on PHP)
        if (is_uploaded_file($source)) {
            if (@move_uploaded_file($source, $destination)) {
                \Log::info("saveFile: move_uploaded_file OK -> {$destination}");
                return true;
            }
            \Log::warning("saveFile: move_uploaded_file failed -> {$destination}");
        }

        // Method 2: copy
        if (@copy($source, $destination)) {
            \Log::info("saveFile: copy OK -> {$destination}");
            return true;
        }
        \Log::warning("saveFile: copy failed -> {$destination}");

        // Method 3: file_get_contents + file_put_contents
        $data = @file_get_contents($source);
        if ($data !== false && strlen($data) > 0) {
            $written = @file_put_contents($destination, $data);
            if ($written !== false && $written > 0) {
                \Log::info("saveFile: file_put_contents OK ({$written} bytes) -> {$destination}");
                return true;
            }
            \Log::warning("saveFile: file_put_contents failed -> {$destination}");
        } else {
            \Log::warning("saveFile: file_get_contents failed for source {$source}");
        }

        // Method 4: stream copy
        $src = @fopen($source, 'rb');
        $dst = @fopen($destination, 'wb');
        if ($src && $dst) {
            $bytes = stream_copy_to_stream($src, $dst);
            fclose($src);
            fclose($dst);
            if ($bytes > 0) {
                \Log::info("saveFile: stream_copy OK ({$bytes} bytes) -> {$destination}");
                return true;
            }
        }
        if ($src) fclose($src);
        if ($dst) fclose($dst);

        \Log::error("saveFile: ALL METHODS FAILED -> {$destination}");
        return false;
    }

    public function store(Request $request)
    {
        \Log::info('News store: request received', [
            'has_file_image' => $request->hasFile('image') ? 'YES' : 'NO',
            'all_files' => array_keys($_FILES),
            'post_keys' => array_keys($request->post()),
            'content_length' => strlen($request->input('content', '')),
        ]);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'is_active' => 'boolean',
            'show_until' => 'nullable|date',
            'priority' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'content', 'is_active', 'show_until', 'priority']);
        $data['created_by'] = Auth::id();
        $data['is_active'] = $request->boolean('is_active', true);

        if (in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            $data['is_approved'] = true;
            $data['approved_by'] = Auth::id();
            $data['approved_at'] = now();
        } else {
            $data['is_approved'] = false;
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            \Log::info('News store: image received', [
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'ext' => $file->getClientOriginalExtension(),
                'is_valid' => $file->isValid() ? 'YES' : 'NO',
                'error_const' => $file->getError(),
                'error_msg' => $file->getErrorMessage(),
                'real_path' => $file->getRealPath(),
            ]);

            $path = $this->storeNewsImage($file);
            if ($path) {
                $data['image_path'] = $path;
                \Log::info('News store: image_path set in data', ['path' => $path]);
            } else {
                \Log::error('News store: storeNewsImage returned null — image will NOT be saved');
            }
        } else {
            \Log::info('News store: no image file in request');
        }

        $news = News::create($data);

        \Log::info('News store: news record created', [
            'id' => $news->id,
            'image_path_in_db' => $news->image_path ?? '(empty)',
            'title' => $news->title,
            'is_active' => $news->is_active,
            'is_approved' => $news->is_approved,
        ]);

        $msg = in_array(Auth::user()->role, ['admin', 'super_admin'])
            ? 'News item created and published successfully.'
            : 'News item submitted. It will be visible after admin approval.';

        if (!empty($news->image_path)) {
            $msg .= ' ✓ Image saved: ' . $news->image_path;
        } elseif ($request->hasFile('image')) {
            $msg .= ' ⚠ Image upload FAILED — check storage/logs/laravel.log';
        }

        return redirect()->route('admin.news.index')->with('success', $msg);
    }

    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, News $news)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'is_active' => 'boolean',
            'show_until' => 'nullable|date',
            'priority' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'content', 'is_active', 'show_until', 'priority']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            \Log::info('News update: image received', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'valid' => $file->isValid(),
            ]);

            $path = $this->storeNewsImage($file);
            if ($path) {
                $data['image_path'] = $path;
            }
        }

        $news->update($data);

        $msg = 'News item updated successfully.';
        if (!empty($news->fresh()->image_path) && $request->hasFile('image')) {
            $msg .= ' Image updated: ' . $news->fresh()->image_path;
        }

        return redirect()->route('admin.news.index')->with('success', $msg);
    }

    // Admin: Approve/Reject news posted by principals
    public function approve(News $news)
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Only administrators can approve news.');
        }

        $news->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.news.index')->with('success', 'News item approved and published.');
    }

    public function reject(News $news)
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            abort(403, 'Only administrators can reject news.');
        }

        $news->update([
            'is_approved' => false,
            'is_active' => false,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.news.index')->with('success', 'News item rejected and deactivated.');
    }

    public function destroy(News $news)
    {
        $news->delete();
        return redirect()->route('admin.news.index')->with('success', 'News item deleted.');
    }

    /**
     * Summernote image upload endpoint.
     * Saves to BOTH storage/app/public/news-images/ AND public/news-images/
     * so the image is web-accessible regardless of storage:link state.
     * Returns the absolute URL.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:10240',
        ]);

        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        $file = $request->file('file');
        if (!$file->isValid()) {
            return response()->json(['error' => 'Invalid upload: ' . $file->getErrorMessage()], 400);
        }

        $path = $this->storeNewsImage($file);
        if (!$path) {
            return response()->json(['error' => 'Failed to store image'], 500);
        }

        // Build the URL — prefer the public/ path since it doesn't depend on the storage symlink
        $url = asset($path);

        return response()->json([
            'url' => $url,
            'path' => $path,
            'filename' => basename($path),
        ]);
    }
}
