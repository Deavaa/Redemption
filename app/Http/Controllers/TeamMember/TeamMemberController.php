<?php
namespace App\Http\Controllers\TeamMember;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeamMember;

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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'bio' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['name','designation','department','qualification','experience','phone','email','bio','sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('photo')) {
            $data['photo'] = $r->file('photo')->store('team-photos', 'public');
            $this->copyToPublicStorage($data['photo']);
        }
        TeamMember::create($data);
        return redirect()->route("admin.team-members.index")->with('success','Team member created successfully');
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'bio' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data = $r->only(['name','designation','department','qualification','experience','phone','email','bio','sort_order']);
        $data['is_active'] = $r->has('is_active') ? 1 : 0;
        if ($r->hasFile('photo')) {
            $data['photo'] = $r->file('photo')->store('team-photos', 'public');
            $this->copyToPublicStorage($data['photo']);
        }
        $team_member->update($data);
        return redirect()->route("admin.team-members.index")->with('success','Team member updated successfully');
    }

    public function destroy(TeamMember $team_member) { $team_member->delete(); return back()->with('success','Team member deleted successfully'); }

    /**
     * Copy a file from storage/app/public/ to public/storage/ as a fallback.
     * This ensures images are accessible even when the symlink doesn't exist (XAMPP/cPanel).
     */
    private function copyToPublicStorage($relativePath)
    {
        try {
            $sourcePath = storage_path('app/public/' . $relativePath);
            $destinationPath = public_path('storage/' . $relativePath);

            $destinationDir = dirname($destinationPath);
            if (!is_dir($destinationDir)) {
                mkdir($destinationDir, 0755, true);
            }

            if (file_exists($sourcePath)) {
                copy($sourcePath, $destinationPath);
                // Ensure the file is readable by web server
                chmod($destinationPath, 0644);
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to copy team photo to public storage fallback: ' . $e->getMessage());
        }
    }
}
