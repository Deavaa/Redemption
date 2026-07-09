<?php
namespace App\Http\Controllers\TeamMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamMember;
use App\Helpers\ImageCompressor;

class TeamMemberController extends Controller
{
    public function index(Request $r)
    {
        $q = TeamMember::query();
        if ($r->filled('search')) {
            $s = $r->search;
            $q->where('name', 'LIKE', "%$s%")->orWhere('designation', 'LIKE', "%$s%")->orWhere('department', 'LIKE', "%$s%");
        }
        $data = $q->orderBy('sort_order')->paginate(20);
        $totalMembers = TeamMember::count();
        return view('admin.TeamMember.index', compact('data', 'totalMembers'));
    }

    public function create() { return view('admin.TeamMember.create'); }

    public function store(Request $r)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'bio' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $allFields = ['name','designation','department','qualification','experience','phone','email','bio','sort_order'];
        $data = [];
        foreach ($allFields as $field) {
            if ($r->has($field)) {
                $data[$field] = $r->input($field);
            }
        }
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('photo') && $r->file('photo')->isValid()) {
            try {
                $data['photo'] = ImageCompressor::compressAndStore($r->file('photo'), 'team-photos', 2048, 1200);
            } catch (\Throwable $e) {
                \Log::error('TeamMember photo upload failed: ' . $e->getMessage());
            }
        }
        try {
            TeamMember::create($data);
            return redirect()->route("admin.team-members.index")->with('success','Team member created successfully');
        } catch (\Throwable $e) {
            \Log::error('TeamMember create failed: ' . $e->getMessage(), ['data' => $data]);
            return back()->with('error', 'Create failed: ' . $e->getMessage())->withInput();
        }
    }

    public function show(TeamMember $team_member) { return view('admin.TeamMember.show', ['item' => $team_member]); }
    public function edit(TeamMember $team_member) { return view('admin.TeamMember.edit', ['item' => $team_member]); }

    public function update(Request $r, TeamMember $team_member)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'designation' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'experience' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:51200',
            'bio' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        // Only include fields that actually exist as columns on the table
        $allFields = ['name','designation','department','qualification','experience','phone','email','bio','sort_order'];
        $data = [];
        foreach ($allFields as $field) {
            if ($r->has($field)) {
                $data[$field] = $r->input($field);
            }
        }
        $data['is_active'] = $r->has('is_active') ? 1 : 0;

        // Handle photo upload with DETAILED error reporting
        if ($r->hasFile('photo')) {
            $file = $r->file('photo');

            // Check if the upload itself failed (PHP upload error)
            if (!$file->isValid()) {
                $errorMsg = 'The photo failed to upload. Error: ' . $file->getErrorMessage();
                \Log::error('TeamMember photo upload failed (invalid)', [
                    'error' => $file->getError(),
                    'message' => $file->getErrorMessage(),
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType(),
                ]);
                return back()->with('error', $errorMsg)->withInput();
            }

            // Check GD extension
            if (!extension_loaded('gd')) {
                \Log::warning('GD extension not loaded — storing original photo without compression');
                // Fall back to storing the original file
                try {
                    $path = $file->store('team-photos', 'public');
                    $this->copyToPublicFallback($path);
                    $data['photo'] = $path;
                } catch (\Throwable $e) {
                    \Log::error('TeamMember photo store failed (no GD): ' . $e->getMessage());
                    return back()->with('error', 'Photo upload failed: ' . $e->getMessage())->withInput();
                }
            } else {
                // Use ImageCompressor
                try {
                    $compressedPath = ImageCompressor::compressAndStore($file, 'team-photos', 2048, 1200);
                    if ($compressedPath) {
                        $data['photo'] = $compressedPath;
                        \Log::info('TeamMember photo compressed', ['path' => $compressedPath]);
                    } else {
                        \Log::error('TeamMember photo: ImageCompressor returned null');
                        // Fall back to storing the original
                        $path = $file->store('team-photos', 'public');
                        $this->copyToPublicFallback($path);
                        $data['photo'] = $path;
                    }
                } catch (\Throwable $e) {
                    \Log::error('TeamMember photo compression failed: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Fall back to storing the original — don't block the update
                    try {
                        $path = $file->store('team-photos', 'public');
                        $this->copyToPublicFallback($path);
                        $data['photo'] = $path;
                        \Log::info('TeamMember photo stored as fallback (compression failed)', ['path' => $path]);
                    } catch (\Throwable $e2) {
                        return back()->with('error', 'Photo upload failed: ' . $e2->getMessage())->withInput();
                    }
                }
            }
        }

        try {
            $team_member->update($data);
            \Log::info('TeamMember updated', ['id' => $team_member->id, 'fields' => array_keys($data)]);
            return redirect()->route("admin.team-members.index")->with('success','Team member updated successfully');
        } catch (\Throwable $e) {
            \Log::error('TeamMember update failed: ' . $e->getMessage(), [
                'id' => $team_member->id,
                'data' => $data,
            ]);
            return back()->with('error', 'Update failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Copy a file from storage/app/public/ to public/ as a fallback.
     */
    private function copyToPublicFallback($relativePath)
    {
        try {
            $sourcePath = storage_path('app/public/' . $relativePath);
            $destPath = public_path($relativePath);
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            if (file_exists($sourcePath)) {
                @copy($sourcePath, $destPath);
            }
        } catch (\Throwable $e) {
            \Log::warning('copyToPublicFallback failed: ' . $e->getMessage());
        }
    }

    public function destroy(TeamMember $team_member) { $team_member->delete(); return back()->with('success','Team member deleted successfully'); }
}
