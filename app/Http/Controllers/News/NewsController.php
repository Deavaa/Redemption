<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    public function index()
    {
        $news = News::with('creator', 'approver')->orderBy('priority', 'desc')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.news.index', compact('news'));
    }

    /**
     * Display a single news item.
     * Required by Route::resource — without this, visiting
     * /admin/news/{id} throws "Call to undefined method show()".
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

    public function store(Request $request)
    {
        // Log the incoming request for debugging image upload issues
        \Log::info('News store: incoming request', [
            'has_file_image' => $request->hasFile('image'),
            'files_keys' => array_keys($_FILES),
            'content_length' => strlen($request->input('content', '')),
            'title' => $request->input('title'),
        ]);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:10240', // 10MB max (was 2MB — too small for phone photos)
            'is_active' => 'boolean',
            'show_until' => 'nullable|date',
            'priority' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'content', 'is_active', 'show_until', 'priority']);
        $data['created_by'] = Auth::id();
        $data['is_active'] = $request->boolean('is_active', true);

        // If admin/super_admin creates news, auto-approve it
        if (in_array(Auth::user()->role, ['admin', 'super_admin'])) {
            $data['is_approved'] = true;
            $data['approved_by'] = Auth::id();
            $data['approved_at'] = now();
        } else {
            // News from principals/managers needs admin approval
            $data['is_approved'] = false;
        }

        // Handle image upload — with extensive error logging
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            \Log::info('News store: processing image upload', [
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'is_valid' => $file->isValid(),
                'error' => $file->getError(),
                'error_message' => $file->getErrorMessage(),
            ]);

            try {
                $path = $file->store('news-images', 'public');
                $data['image_path'] = $path;
                \Log::info('News store: image stored successfully', ['path' => $path]);

                // Fallback for missing storage:link — also copy to public/news-images/
                $sourceFile = storage_path('app/public/' . $path);
                $fallbackDir = public_path('news-images');
                if (!is_dir($fallbackDir)) {
                    @mkdir($fallbackDir, 0775, true);
                }
                if (is_file($sourceFile)) {
                    @copy($sourceFile, $fallbackDir . '/' . basename($path));
                    \Log::info('News store: image copied to public fallback', [
                        'fallback' => $fallbackDir . '/' . basename($path),
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('News store: image upload FAILED', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Continue saving the news even if image upload fails
                // so the user doesn't lose their title + content.
            }
        }

        $news = News::create($data);
        \Log::info('News store: news created', [
            'id' => $news->id,
            'image_path' => $news->image_path,
            'is_active' => $news->is_active,
            'is_approved' => $news->is_approved,
        ]);

        $msg = in_array(Auth::user()->role, ['admin', 'super_admin'])
            ? 'News item created and published successfully.'
            : 'News item submitted. It will be visible after admin approval.';

        // Include image status in the success message for clarity
        if ($request->hasFile('image') && empty($news->image_path)) {
            $msg .= ' WARNING: Image upload failed — check storage/logs/laravel.log for details.';
        } elseif ($news->image_path) {
            $msg .= ' Cover image saved: ' . $news->image_path;
        }

        return redirect()->route('admin.news.index')->with('success', $msg);
    }

    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    public function update(Request $request, News $news)
    {
        \Log::info('News update: incoming request', [
            'news_id' => $news->id,
            'has_file_image' => $request->hasFile('image'),
            'existing_image_path' => $news->image_path,
        ]);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:10240', // 10MB max
            'is_active' => 'boolean',
            'show_until' => 'nullable|date',
            'priority' => 'nullable|integer',
        ]);

        $data = $request->only(['title', 'content', 'is_active', 'show_until', 'priority']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            \Log::info('News update: processing image upload', [
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'is_valid' => $file->isValid(),
                'error' => $file->getError(),
                'error_message' => $file->getErrorMessage(),
            ]);

            try {
                $path = $file->store('news-images', 'public');
                $data['image_path'] = $path;
                \Log::info('News update: image stored successfully', ['path' => $path]);

                // Fallback for missing storage:link
                $sourceFile = storage_path('app/public/' . $path);
                $fallbackDir = public_path('news-images');
                if (!is_dir($fallbackDir)) {
                    @mkdir($fallbackDir, 0775, true);
                }
                if (is_file($sourceFile)) {
                    @copy($sourceFile, $fallbackDir . '/' . basename($path));
                }
            } catch (\Throwable $e) {
                \Log::error('News update: image upload FAILED', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $news->update($data);
        \Log::info('News update: news updated', [
            'id' => $news->id,
            'image_path' => $news->fresh()->image_path,
        ]);

        $msg = 'News item updated successfully.';
        if ($request->hasFile('image') && empty($news->fresh()->image_path)) {
            $msg .= ' WARNING: Image upload failed — check storage/logs/laravel.log.';
        } elseif ($news->fresh()->image_path && $request->hasFile('image')) {
            $msg .= ' Cover image updated: ' . $news->fresh()->image_path;
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
     * Receives an image from the rich text editor and stores it in
     * public/news-images/. Returns the absolute URL so Summernote can
     * embed it in the editor as a real <img src="..."> instead of a
     * huge base64 data URI.
     *
     * Also ensures the storage symlink exists (php artisan storage:link
     * equivalent) so the returned URL resolves to a real file.
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:5120', // 5MB max
        ]);

        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        try {
            // Ensure the public/storage symlink exists — this is the #1 cause
            // of "image not showing" on XAMPP. Target: storage/app/public
            // Link:    public/storage
            $publicStorage = public_path('storage');
            $target = storage_path('app/public');
            if (!file_exists($publicStorage)) {
                // Windows / XAMPP: symlink may fail without admin privs.
                // Try symlink first; if it fails, create a junction (Windows)
                // or just copy the directory as a last resort.
                @symlink($target, $publicStorage);
                if (!file_exists($publicStorage)) {
                    // Fallback: copy the file directly to public/news-images
                    // so it's web-accessible even without the symlink.
                    $publicNewsDir = public_path('news-images');
                    if (!is_dir($publicNewsDir)) {
                        @mkdir($publicNewsDir, 0775, true);
                    }
                }
            }

            $path = $request->file('file')->store('news-images', 'public');
            $url = asset('storage/' . $path);

            // Fallback: if symlink isn't working, also expose the file via
            // public/news-images/ so it's directly web-accessible.
            $sourceFile = storage_path('app/public/' . $path);
            $fallbackDir = public_path('news-images');
            if (is_file($sourceFile) && is_dir($fallbackDir)) {
                @copy($sourceFile, $fallbackDir . '/' . basename($path));
            }

            return response()->json([
                'url' => $url,
                'path' => $path,
                'filename' => basename($path),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
