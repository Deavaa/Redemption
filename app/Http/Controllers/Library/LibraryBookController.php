<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryBook;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LibraryBookController extends Controller
{
    public function index(Request $request)
    {
        $query = LibraryBook::with(['branch', 'uploader']);

        // Non-admin users only see books they can access
        if (auth()->user()->role !== 'admin') {
            $query->where('is_active', true);
            if (!auth()->user()->hasRole('librarian') && !auth()->user()->hasRole('branch_principal')) {
                $query->where(function ($q) {
                    $q->where('access_level', 'all')
                      ->orWhere('access_level', auth()->user()->role);
                });
            }
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by access level
        if ($request->filled('access_level')) {
            $query->where('access_level', $request->access_level);
        }

        $books = $query->latest()->paginate(20);
        $categories = LibraryBook::select('category')->distinct()->whereNotNull('category')->orderBy('category')->pluck('category');
        $branches = Branch::orderBy('name')->get();
        $totalBooks = LibraryBook::count();
        $activeBooks = LibraryBook::where('is_active', true)->count();
        $totalReads = LibraryBook::sum('read_count');

        $canUpload = auth()->user()->role === 'admin'
            || auth()->user()->hasRole('librarian')
            || auth()->user()->hasRole('branch_principal');

        return view('admin.library.index', compact(
            'books', 'categories', 'branches', 'totalBooks', 'activeBooks', 'totalReads', 'canUpload'
        ));
    }

    public function create()
    {
        // Only admin, librarian, and branch_principal can upload
        $user = auth()->user();
        if (!$user->role === 'admin' && !$user->hasRole('librarian') && !$user->hasRole('branch_principal')) {
            abort(403, 'You do not have permission to upload books.');
        }

        $branches = Branch::orderBy('name')->get();
        $categories = LibraryBook::select('category')->distinct()->whereNotNull('category')->orderBy('category')->pluck('category');

        return view('admin.library.create', compact('branches', 'categories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->role === 'admin' && !$user->hasRole('librarian') && !$user->hasRole('branch_principal')) {
            abort(403, 'You do not have permission to upload books.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'publisher' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'file' => 'required|file|mimes:pdf,epub|max:102400', // Max 100MB, PDF/EPUB only
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // Max 5MB
            'branch_id' => 'nullable|exists:branches,id',
            'access_level' => 'required|in:all,teacher,student,staff,admin',
            'is_active' => 'boolean',
        ]);

        // Store the book file
        $file = $request->file('file');
        $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('library/books', $fileName, 'public');

        // Ensure public copy exists
        $this->ensurePublicCopy($filePath);

        $validated['file_path'] = $filePath;
        $validated['file_name'] = $file->getClientOriginalName();
        $validated['file_type'] = $file->getMimeType();
        $validated['file_size'] = $file->getSize();
        $validated['uploaded_by'] = $user->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        // Store cover image
        if ($request->hasFile('cover_image')) {
            $cover = $request->file('cover_image');
            $coverName = Str::slug(pathinfo($cover->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $cover->getClientOriginalExtension();
            $coverPath = $cover->storeAs('library/covers', $coverName, 'public');
            $this->ensurePublicCopy($coverPath);
            $validated['cover_image'] = $coverPath;
        }

        LibraryBook::create($validated);

        return redirect()->route('admin.library.index')->with('success', 'Book uploaded successfully.');
    }

    public function show(LibraryBook $library)
    {
        $library->load(['branch', 'uploader']);
        $canUpload = auth()->user()->role === 'admin'
            || auth()->user()->hasRole('librarian')
            || auth()->user()->hasRole('branch_principal');

        return view('admin.library.show', ['book' => $library, 'canUpload' => $canUpload]);
    }

    public function read(LibraryBook $library)
    {
        // Check if user can read this book
        $user = auth()->user();
        if (!$library->is_active && $user->role !== 'admin') {
            abort(403, 'This book is not currently available.');
        }

        if ($library->access_level !== 'all' && $library->access_level !== $user->role && $user->role !== 'admin') {
            abort(403, 'You do not have permission to read this book.');
        }

        // Increment read count
        $library->incrementReadCount();

        $library->load(['branch', 'uploader']);
        $fileUrl = $library->getFileUrl();
        $isPdf = $library->file_type === 'application/pdf' || str_ends_with(strtolower($library->file_path), '.pdf');

        return view('admin.library.read', compact('library', 'fileUrl', 'isPdf'));
    }

    public function edit(LibraryBook $library)
    {
        $user = auth()->user();
        if (!$user->role === 'admin' && !$user->hasRole('librarian') && !$user->hasRole('branch_principal')) {
            abort(403, 'You do not have permission to edit books.');
        }

        $branches = Branch::orderBy('name')->get();
        $categories = LibraryBook::select('category')->distinct()->whereNotNull('category')->orderBy('category')->pluck('category');

        return view('admin.library.edit', ['book' => $library, 'branches' => $branches, 'categories' => $categories]);
    }

    public function update(Request $request, LibraryBook $library)
    {
        $user = auth()->user();
        if (!$user->role === 'admin' && !$user->hasRole('librarian') && !$user->hasRole('branch_principal')) {
            abort(403, 'You do not have permission to edit books.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'author' => 'nullable|string|max:255',
            'isbn' => 'nullable|string|max:50',
            'publisher' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'file' => 'nullable|file|mimes:pdf,epub|max:102400',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'branch_id' => 'nullable|exists:branches,id',
            'access_level' => 'required|in:all,teacher,student,staff,admin',
            'is_active' => 'boolean',
        ]);

        // Update file if new one uploaded
        if ($request->hasFile('file')) {
            // Delete old file
            try {
                if (Storage::disk('public')->exists($library->file_path)) {
                    Storage::disk('public')->delete($library->file_path);
                }
            } catch (\Throwable $e) {}

            $file = $request->file('file');
            $fileName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('library/books', $fileName, 'public');
            $this->ensurePublicCopy($filePath);

            $validated['file_path'] = $filePath;
            $validated['file_name'] = $file->getClientOriginalName();
            $validated['file_type'] = $file->getMimeType();
            $validated['file_size'] = $file->getSize();
        }

        // Update cover image if new one uploaded
        if ($request->hasFile('cover_image')) {
            try {
                if ($library->cover_image && Storage::disk('public')->exists($library->cover_image)) {
                    Storage::disk('public')->delete($library->cover_image);
                }
            } catch (\Throwable $e) {}

            $cover = $request->file('cover_image');
            $coverName = Str::slug(pathinfo($cover->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . time() . '.' . $cover->getClientOriginalExtension();
            $coverPath = $cover->storeAs('library/covers', $coverName, 'public');
            $this->ensurePublicCopy($coverPath);
            $validated['cover_image'] = $coverPath;
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $library->update($validated);

        return redirect()->route('admin.library.index')->with('success', 'Book updated successfully.');
    }

    public function destroy(LibraryBook $library)
    {
        $user = auth()->user();
        if (!$user->role === 'admin' && !$user->hasRole('librarian') && !$user->hasRole('branch_principal')) {
            abort(403, 'You do not have permission to delete books.');
        }

        // Delete files
        try {
            if (Storage::disk('public')->exists($library->file_path)) {
                Storage::disk('public')->delete($library->file_path);
            }
            if ($library->cover_image && Storage::disk('public')->exists($library->cover_image)) {
                Storage::disk('public')->delete($library->cover_image);
            }
        } catch (\Throwable $e) {}

        $library->delete();

        return back()->with('success', 'Book deleted successfully.');
    }

    /**
     * Serve the book file for reading (prevents direct download by disabling certain headers)
     */
    public function serveBook(LibraryBook $library)
    {
        $user = auth()->user();
        if (!$library->is_active && $user->role !== 'admin') {
            abort(403);
        }
        if ($library->access_level !== 'all' && $library->access_level !== $user->role && $user->role !== 'admin') {
            abort(403);
        }

        $storagePath = storage_path('app/public/' . $library->file_path);
        $publicPath = public_path('storage/' . $library->file_path);

        // Find the actual file
        $filePath = null;
        if (file_exists($publicPath)) {
            $filePath = $publicPath;
        } elseif (file_exists($storagePath)) {
            $filePath = $storagePath;
        }

        if (!$filePath || !file_exists($filePath)) {
            abort(404, 'Book file not found.');
        }

        // Serve with headers that discourage caching and downloading
        return response()->file($filePath, [
            'Content-Type' => $library->file_type ?? 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $library->file_name . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Ensure a file from storage/app/public/ is also copied to public/storage/
     */
    private function ensurePublicCopy(string $relativePath): void
    {
        try {
            $sourcePath = storage_path('app/public/' . $relativePath);
            $destinationPath = public_path('storage/' . $relativePath);

            if (file_exists($sourcePath) && !file_exists($destinationPath)) {
                $destinationDir = dirname($destinationPath);
                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0755, true);
                }
                copy($sourcePath, $destinationPath);
            }
        } catch (\Throwable $e) {
            \Log::warning('LibraryBook ensurePublicCopy failed: ' . $e->getMessage());
        }
    }
}
