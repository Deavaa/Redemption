<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\VideoLibrary;
use App\Models\Branch;
use Illuminate\Http\Request;

class VideoLibraryController extends Controller
{
    public function index(Request $request)
    {
        $query = VideoLibrary::with(['branch', 'uploader']);

        // Non-admin users only see active videos they can access
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

        // Filter by video type
        if ($request->filled('video_type')) {
            $query->where('video_type', $request->video_type);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by access level
        if ($request->filled('access_level')) {
            $query->where('access_level', $request->access_level);
        }

        $videos = $query->latest()->paginate(20);
        $categories = VideoLibrary::select('category')->distinct()->whereNotNull('category')->orderBy('category')->pluck('category');
        $branches = Branch::orderBy('name')->get();
        $totalVideos = VideoLibrary::count();
        $activeVideos = VideoLibrary::where('is_active', true)->count();
        $totalViews = VideoLibrary::sum('view_count');
        $channels = VideoLibrary::select('channel_name')->distinct()->whereNotNull('channel_name')->orderBy('channel_name')->pluck('channel_name');

        $canManage = auth()->user()->role === 'admin'
            || auth()->user()->hasRole('librarian')
            || auth()->user()->hasRole('branch_principal')
            || auth()->user()->hasRole('teacher');

        return view('admin.video-library.index', compact(
            'videos', 'categories', 'branches', 'totalVideos', 'activeVideos', 'totalViews', 'channels', 'canManage'
        ));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $categories = VideoLibrary::select('category')->distinct()->whereNotNull('category')->orderBy('category')->pluck('category');

        return view('admin.video-library.create', compact('branches', 'categories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && !$user->hasRole('librarian') && !$user->hasRole('branch_principal') && !$user->hasRole('teacher')) {
            abort(403, 'You do not have permission to add videos.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
            'youtube_url' => 'required|string|max:2000',
            'channel_name' => 'nullable|string|max:255',
            'channel_url' => 'nullable|string|max:2000',
            'category' => 'nullable|string|max:255',
            'video_type' => 'required|in:single,channel',
            'branch_id' => 'nullable|exists:branches,id',
            'access_level' => 'required|in:all,teacher,student,staff,admin',
            'is_active' => 'boolean',
            'duration_seconds' => 'nullable|integer|min:0',
        ]);

        // Extract YouTube video ID
        $videoId = VideoLibrary::extractYoutubeVideoId($validated['youtube_url']);
        if (!$videoId && $validated['video_type'] === 'single') {
            return back()->withInput()->withErrors(['youtube_url' => 'Could not extract a valid YouTube video ID from the URL. Please enter a valid YouTube link.']);
        }

        $validated['youtube_video_id'] = $videoId;
        $validated['uploaded_by'] = $user->id;
        $validated['is_active'] = $request->boolean('is_active', true);

        // Auto-fill thumbnail from YouTube
        if ($videoId) {
            $validated['thumbnail'] = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
        }

        // Extract channel info from URL if not provided and it's a channel type
        if ($validated['video_type'] === 'channel' && empty($validated['channel_name'])) {
            $channelId = VideoLibrary::extractChannelId($validated['youtube_url']);
            if ($channelId) {
                $validated['channel_name'] = $channelId;
            }
        }

        VideoLibrary::create($validated);

        return redirect()->route('admin.video-library.index')->with('success', 'Video added successfully.');
    }

    public function show(VideoLibrary $video_library)
    {
        $video_library->load(['branch', 'uploader']);
        $video_library->incrementViewCount();

        $canManage = auth()->user()->role === 'admin'
            || auth()->user()->hasRole('librarian')
            || auth()->user()->hasRole('branch_principal')
            || ($video_library->uploaded_by === auth()->id());

        // Get related videos from same category or channel
        $relatedVideos = VideoLibrary::where('id', '!=', $video_library->id)
            ->where('is_active', true)
            ->where(function ($q) use ($video_library) {
                $q->where('category', $video_library->category)
                  ->orWhere('channel_name', $video_library->channel_name);
            })
            ->where(function ($q) {
                $q->where('access_level', 'all')
                  ->orWhere('access_level', auth()->user()->role);
            })
            ->limit(6)
            ->get();

        return view('admin.video-library.show', [
            'video' => $video_library,
            'canManage' => $canManage,
            'relatedVideos' => $relatedVideos,
        ]);
    }

    public function edit(VideoLibrary $video_library)
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && !$user->hasRole('librarian') && !$user->hasRole('branch_principal') && $video_library->uploaded_by !== $user->id) {
            abort(403, 'You do not have permission to edit videos.');
        }

        $branches = Branch::orderBy('name')->get();
        $categories = VideoLibrary::select('category')->distinct()->whereNotNull('category')->orderBy('category')->pluck('category');

        return view('admin.video-library.edit', [
            'video' => $video_library,
            'branches' => $branches,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, VideoLibrary $video_library)
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && !$user->hasRole('librarian') && !$user->hasRole('branch_principal') && $video_library->uploaded_by !== $user->id) {
            abort(403, 'You do not have permission to edit videos.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
            'youtube_url' => 'required|string|max:2000',
            'channel_name' => 'nullable|string|max:255',
            'channel_url' => 'nullable|string|max:2000',
            'category' => 'nullable|string|max:255',
            'video_type' => 'required|in:single,channel',
            'branch_id' => 'nullable|exists:branches,id',
            'access_level' => 'required|in:all,teacher,student,staff,admin',
            'is_active' => 'boolean',
            'duration_seconds' => 'nullable|integer|min:0',
        ]);

        // Re-extract YouTube video ID if URL changed
        if ($validated['youtube_url'] !== $video_library->youtube_url) {
            $videoId = VideoLibrary::extractYoutubeVideoId($validated['youtube_url']);
            $validated['youtube_video_id'] = $videoId;
            if ($videoId) {
                $validated['thumbnail'] = "https://img.youtube.com/vi/{$videoId}/hqdefault.jpg";
            }
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $video_library->update($validated);

        return redirect()->route('admin.video-library.index')->with('success', 'Video updated successfully.');
    }

    public function destroy(VideoLibrary $video_library)
    {
        $user = auth()->user();
        if ($user->role !== 'admin' && !$user->hasRole('librarian') && !$user->hasRole('branch_principal') && $video_library->uploaded_by !== $user->id) {
            abort(403, 'You do not have permission to delete videos.');
        }

        $video_library->delete();

        return back()->with('success', 'Video deleted successfully.');
    }
}
