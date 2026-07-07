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
     * Helper: Store an uploaded image file in MULTIPLE locations to maximize
     * the chance it will be web-accessible regardless of storage:link state.
     * Returns the relative path stored in the DB (e.g. "news-images/abc.jpg").
     */
    private function storeNewsImage($file): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Generate a clean unique filename
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = 'news_' . date('Ymd_His') . '_' . Str::random(8) . '.' . $extension;
        $relativePath = 'news-images/' . $filename;

        // Location 1: storage/app/public/news-images/ (Laravel standard)
        $storageDir = storage_path('app/public/news-images');
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0775, true);
        }
        $storagePath = $storageDir . '/' . $filename;
        $savedToStorage = @copy($file->getRealPath(), $storagePath) || @move_uploaded_file($file->getRealPath(), $storagePath);

        // Location 2: public/news-images/ (directly web-accessible, no symlink needed)
        $publicDir = public_path('news-images');
        if (!is_dir($publicDir)) {
            @mkdir($publicDir, 0775, true);
        }
        $publicPath = $publicDir . '/' . $filename;
        $savedToPublic = @copy($file->getRealPath(), $publicPath);

        \Log::info('News image stored', [
            'filename' => $filename,
            'storage_path' => $storagePath,
            'storage_exists' => file_exists($storagePath),
            'public_path' => $publicPath,
            'public_exists' => file_exists($publicPath),
        ]);

        return $relativePath;
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:10240', // 10MB
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

        // Handle cover image upload with our robust helper
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            \Log::info('News store: image received', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'valid' => $file->isValid(),
                'error_msg' => $file->getErrorMessage(),
            ]);

            $path = $this->storeNewsImage($file);
            if ($path) {
                $data['image_path'] = $path;
            } else {
                \Log::error('News store: storeNewsImage() returned null');
            }
        }

        $news = News::create($data);

        $msg = in_array(Auth::user()->role, ['admin', 'super_admin'])
            ? 'News item created and published successfully.'
            : 'News item submitted. It will be visible after admin approval.';

        if (!empty($news->image_path)) {
            $msg .= ' Image saved: ' . $news->image_path;
        } elseif ($request->hasFile('image')) {
            $msg .= ' WARNING: Image could not be saved — check storage/logs/laravel.log.';
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
